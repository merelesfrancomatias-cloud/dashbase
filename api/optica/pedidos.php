<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT p.*,
                   CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
                   c.telefono AS cliente_tel, c.obra_social
            FROM optica_pedidos p
            JOIN optica_clientes c ON c.id = p.cliente_id
            WHERE p.id = :id AND p.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) Response::error('Pedido no encontrado', 404);
        Response::success('OK', $p);
    }

    // Filtros: ?estado=, ?q=, ?desde=, ?hasta=
    $where  = "p.negocio_id = :nid";
    $params = [':nid' => $negocioId];

    if (!empty($_GET['estado'])) {
        $where .= " AND p.estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    if (!empty($_GET['desde'])) {
        $where .= " AND DATE(p.created_at) >= :desde";
        $params[':desde'] = $_GET['desde'];
    }
    if (!empty($_GET['hasta'])) {
        $where .= " AND DATE(p.created_at) <= :hasta";
        $params[':hasta'] = $_GET['hasta'];
    }
    if (!empty($_GET['q'])) {
        $where .= " AND (c.nombre LIKE :q1 OR c.apellido LIKE :q2 OR p.armazon LIKE :q3)";
        $q = '%' . $_GET['q'] . '%';
        $params[':q1'] = $q; $params[':q2'] = $q; $params[':q3'] = $q;
    }

    $stmt = $pdo->prepare("
        SELECT p.*,
               CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
               c.telefono AS cliente_tel, c.obra_social
        FROM optica_pedidos p
        JOIN optica_clientes c ON c.id = p.cliente_id
        WHERE {$where}
        ORDER BY FIELD(p.estado,'listo','laboratorio','pendiente','presupuesto','entregado','cancelado'), p.created_at DESC
        LIMIT 300
    ");
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $stats = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(estado = 'pendiente')   AS pendientes,
            SUM(estado = 'laboratorio') AS en_laboratorio,
            SUM(estado = 'listo')       AS listos,
            SUM(estado = 'entregado')   AS entregados,
            SUM(estado = 'presupuesto') AS presupuestos,
            SUM(saldo > 0 AND estado NOT IN ('cancelado','presupuesto')) AS con_saldo
        FROM optica_pedidos
        WHERE negocio_id = :nid
    ");
    $stats->execute([':nid' => $negocioId]);
    $statsRow = $stats->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', ['pedidos' => $pedidos, 'stats' => $statsRow]);
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['cliente_id'])) Response::error('Cliente requerido', 400);

    $subtotal = (float)($d['armazon_precio'] ?? 0) + (float)($d['lente_precio'] ?? 0);
    $descuento = (float)($d['descuento'] ?? 0);
    $total   = isset($d['total']) ? (float)$d['total'] : $subtotal - $descuento;
    $sena    = (float)($d['seña'] ?? 0);
    $saldo   = $total - $sena;

    $stmt = $pdo->prepare("
        INSERT INTO optica_pedidos
            (negocio_id, cliente_id, receta_id,
             armazon, armazon_color, armazon_precio,
             lente_tipo, lente_material, lente_tratamiento, lente_precio,
             subtotal, descuento, total, seña, saldo, metodo_pago,
             estado, laboratorio, fecha_envio_lab, fecha_entrega_est, observaciones, usuario_id)
        VALUES
            (:nid, :cid, :rid,
             :arm, :armcolor, :armprecio,
             :ltipo, :lmat, :ltrat, :lprecio,
             :sub, :desc, :tot, :sena, :saldo, :mp,
             :estado, :lab, :fenvio, :festim, :obs, :uid)
    ");
    $stmt->execute([
        ':nid'      => $negocioId,
        ':cid'      => (int)$d['cliente_id'],
        ':rid'      => !empty($d['receta_id']) ? (int)$d['receta_id'] : null,
        ':arm'      => $d['armazon']           ?? null,
        ':armcolor' => $d['armazon_color']     ?? null,
        ':armprecio'=> (float)($d['armazon_precio'] ?? 0),
        ':ltipo'    => $d['lente_tipo']        ?? 'monofocal',
        ':lmat'     => $d['lente_material']    ?? null,
        ':ltrat'    => $d['lente_tratamiento'] ?? null,
        ':lprecio'  => (float)($d['lente_precio'] ?? 0),
        ':sub'      => $subtotal,
        ':desc'     => $descuento,
        ':tot'      => $total,
        ':sena'     => $sena,
        ':saldo'    => $saldo,
        ':mp'       => $d['metodo_pago']       ?? 'efectivo',
        ':estado'   => $d['estado']            ?? 'pendiente',
        ':lab'      => $d['laboratorio']       ?? null,
        ':fenvio'   => $d['fecha_envio_lab']   ?? null,
        ':festim'   => $d['fecha_entrega_est'] ?? null,
        ':obs'      => $d['observaciones']     ?? null,
        ':uid'      => $usuarioId,
    ]);
    $pedidoId = $pdo->lastInsertId();

    // Registrar en ventas/caja si hay seña o pago total
    $monto = $sena > 0 ? $sena : $total;
    if ($monto > 0 && ($d['estado'] ?? '') !== 'presupuesto') {
        $cajaStmt = $pdo->prepare("SELECT id FROM cajas WHERE usuario_id = :uid AND estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
        $cajaStmt->execute([':uid' => $usuarioId]);
        $cajaId = ($cajaStmt->fetch(PDO::FETCH_ASSOC))['id'] ?? null;

        $clienteStmt = $pdo->prepare("SELECT CONCAT(nombre,' ',apellido) FROM optica_clientes WHERE id = :id");
        $clienteStmt->execute([':id' => (int)$d['cliente_id']]);
        $clienteNombre = $clienteStmt->fetchColumn() ?: 'Cliente';

        $desc = ($sena > 0 ? 'Seña ' : '') . "Óptica — {$clienteNombre}" . (!empty($d['armazon']) ? " ({$d['armazon']})" : '');
        $pdo->prepare("
            INSERT INTO ventas (negocio_id, usuario_id, caja_id, cliente_nombre, subtotal, descuento, total, metodo_pago, observaciones, estado)
            VALUES (:nid, :uid, :caj, :cn, :sub, 0, :tot, :mp, :obs, 'completada')
        ")->execute([
            ':nid' => $negocioId, ':uid' => $usuarioId, ':caj' => $cajaId,
            ':cn'  => $clienteNombre, ':sub' => $monto, ':tot' => $monto,
            ':mp'  => $d['metodo_pago'] ?? 'efectivo', ':obs' => $desc,
        ]);
    }

    // Log seguimiento (estado inicial)
    $pdo->prepare("
        INSERT INTO optica_seguimiento (negocio_id, pedido_id, estado_anterior, estado_nuevo, notas, usuario_id)
        VALUES (:nid, :pid, NULL, :estado, 'Pedido creado', :uid)
    ")->execute([
        ':nid'    => $negocioId,
        ':pid'    => $pedidoId,
        ':estado' => $d['estado'] ?? 'pendiente',
        ':uid'    => $usuarioId,
    ]);

    Response::success('Pedido creado', ['id' => $pedidoId], 201);
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];

    // Obtener pedido actual
    $curr = $pdo->prepare("SELECT * FROM optica_pedidos WHERE id = :id AND negocio_id = :nid");
    $curr->execute([':id' => $id, ':nid' => $negocioId]);
    $pedido = $curr->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) Response::error('Pedido no encontrado', 404);

    // Solo cambio de estado rápido
    if (isset($d['estado']) && count($d) <= 2) {
        $nuevoEstado = $d['estado'];
        $fields = "estado = :estado";
        $params = [':estado' => $nuevoEstado, ':id' => $id, ':nid' => $negocioId];
        if ($nuevoEstado === 'entregado') {
            $fields .= ", fecha_entrega = CURDATE()";
        }
        $pdo->prepare("UPDATE optica_pedidos SET {$fields} WHERE id = :id AND negocio_id = :nid")
            ->execute($params);

        // Log de seguimiento
        $pdo->prepare("
            INSERT INTO optica_seguimiento (negocio_id, pedido_id, estado_anterior, estado_nuevo, notas, usuario_id)
            VALUES (:nid, :pid, :eant, :enuevo, :notas, :uid)
        ")->execute([
            ':nid'    => $negocioId,
            ':pid'    => $id,
            ':eant'   => $pedido['estado'],
            ':enuevo' => $nuevoEstado,
            ':notas'  => $d['notas'] ?? null,
            ':uid'    => $usuarioId,
        ]);

        // Si se entrega y hay saldo pendiente, registrar en caja
        if ($nuevoEstado === 'entregado' && (float)$pedido['saldo'] > 0) {
            $metodo = $d['metodo_pago'] ?? 'efectivo';
            $cajaStmt = $pdo->prepare("SELECT id FROM cajas WHERE usuario_id = :uid AND estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
            $cajaStmt->execute([':uid' => $usuarioId]);
            $cajaId = ($cajaStmt->fetch(PDO::FETCH_ASSOC))['id'] ?? null;

            $clienteStmt = $pdo->prepare("SELECT CONCAT(nombre,' ',apellido) FROM optica_clientes WHERE id = :id");
            $clienteStmt->execute([':id' => (int)$pedido['cliente_id']]);
            $clienteNombre = $clienteStmt->fetchColumn() ?: 'Cliente';

            $desc = "Saldo Óptica — {$clienteNombre}";
            $pdo->prepare("
                INSERT INTO ventas (negocio_id, usuario_id, caja_id, cliente_nombre, subtotal, descuento, total, metodo_pago, observaciones, estado)
                VALUES (:nid, :uid, :caj, :cn, :sub, 0, :tot, :mp, :obs, 'completada')
            ")->execute([
                ':nid' => $negocioId, ':uid' => $usuarioId, ':caj' => $cajaId,
                ':cn'  => $clienteNombre, ':sub' => (float)$pedido['saldo'],
                ':tot' => (float)$pedido['saldo'], ':mp' => $metodo, ':obs' => $desc,
            ]);
            // Limpiar saldo
            $pdo->prepare("UPDATE optica_pedidos SET saldo = 0 WHERE id = :id")->execute([':id' => $id]);
        }

        Response::success('Estado actualizado');
    }

    // Actualización completa
    $subtotal  = (float)($d['armazon_precio'] ?? $pedido['armazon_precio']) + (float)($d['lente_precio'] ?? $pedido['lente_precio']);
    $descuento = (float)($d['descuento']      ?? $pedido['descuento']);
    $total     = isset($d['total']) ? (float)$d['total'] : $subtotal - $descuento;
    $sena      = (float)($d['seña']           ?? $pedido['seña']);
    $saldo     = $total - $sena;

    $stmt = $pdo->prepare("
        UPDATE optica_pedidos SET
            armazon=:arm, armazon_color=:armcolor, armazon_precio=:armprecio,
            lente_tipo=:ltipo, lente_material=:lmat, lente_tratamiento=:ltrat, lente_precio=:lprecio,
            subtotal=:sub, descuento=:desc, total=:tot, seña=:sena, saldo=:saldo,
            metodo_pago=:mp, estado=:estado, laboratorio=:lab,
            fecha_envio_lab=:fenvio, fecha_entrega_est=:festim, observaciones=:obs
        WHERE id = :id AND negocio_id = :nid
    ");
    $nuevoEstadoFull = $d['estado'] ?? $pedido['estado'];
    $stmt->execute([
        ':arm'      => $d['armazon']           ?? $pedido['armazon'],
        ':armcolor' => $d['armazon_color']     ?? $pedido['armazon_color'],
        ':armprecio'=> (float)($d['armazon_precio'] ?? $pedido['armazon_precio']),
        ':ltipo'    => $d['lente_tipo']        ?? $pedido['lente_tipo'],
        ':lmat'     => $d['lente_material']    ?? $pedido['lente_material'],
        ':ltrat'    => $d['lente_tratamiento'] ?? $pedido['lente_tratamiento'],
        ':lprecio'  => (float)($d['lente_precio'] ?? $pedido['lente_precio']),
        ':sub'      => $subtotal,
        ':desc'     => $descuento,
        ':tot'      => $total,
        ':sena'     => $sena,
        ':saldo'    => $saldo,
        ':mp'       => $d['metodo_pago']       ?? $pedido['metodo_pago'],
        ':estado'   => $nuevoEstadoFull,
        ':lab'      => $d['laboratorio']       ?? $pedido['laboratorio'],
        ':fenvio'   => $d['fecha_envio_lab']   ?? $pedido['fecha_envio_lab'],
        ':festim'   => $d['fecha_entrega_est'] ?? $pedido['fecha_entrega_est'],
        ':obs'      => $d['observaciones']     ?? $pedido['observaciones'],
        ':id'       => $id,
        ':nid'      => $negocioId,
    ]);

    // Log seguimiento si cambió el estado
    if ($nuevoEstadoFull !== $pedido['estado']) {
        $pdo->prepare("
            INSERT INTO optica_seguimiento (negocio_id, pedido_id, estado_anterior, estado_nuevo, notas, usuario_id)
            VALUES (:nid, :pid, :eant, :enuevo, :notas, :uid)
        ")->execute([
            ':nid'    => $negocioId,
            ':pid'    => $id,
            ':eant'   => $pedido['estado'],
            ':enuevo' => $nuevoEstadoFull,
            ':notas'  => $d['observaciones'] ?? null,
            ':uid'    => $usuarioId,
        ]);
    }

    Response::success('Pedido actualizado');
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE optica_pedidos SET estado = 'cancelado' WHERE id = :id AND negocio_id = :nid")
        ->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Pedido cancelado');
}
