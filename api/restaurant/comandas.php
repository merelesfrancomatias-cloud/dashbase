<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $pdo);
$method = $_SERVER['REQUEST_METHOD'];

/* ─── helpers ──────────────────────────────────────────────── */
function recalcularComanda(PDO $pdo, int $comandaId): void {
    // Sumar subtotales de ítems no cancelados
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(subtotal), 0)
        FROM restaurant_comanda_items
        WHERE comanda_id = :cid AND estado_cocina != 'cancelado'
    ");
    $stmt->execute([':cid' => $comandaId]);
    $subtotal = (float)$stmt->fetchColumn();

    // Obtener descuento actual
    $stmt2 = $pdo->prepare("SELECT descuento FROM restaurant_comandas WHERE id = :cid");
    $stmt2->execute([':cid' => $comandaId]);
    $descuento = (float)$stmt2->fetchColumn();

    // Actualizar comanda
    $pdo->prepare("
        UPDATE restaurant_comandas
        SET subtotal = :sub, total = :tot
        WHERE id = :cid
    ")->execute([
        ':sub' => $subtotal,
        ':tot' => $subtotal - $descuento,
        ':cid' => $comandaId,
    ]);
}

function siguienteNumeroComanda(PDO $pdo, int $negocioId): int {
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(numero),0)+1 AS siguiente FROM restaurant_comandas WHERE negocio_id=:nid");
    $stmt->execute([':nid' => $negocioId]);
    return (int)$stmt->fetchColumn();
}

/* ─── GET ───────────────────────────────────────────────────── */
if ($method === 'GET') {

    // Detalle de una comanda con sus ítems
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT c.*, m.numero AS mesa_numero, s.nombre AS sector_nombre,
                   u.nombre AS mozo_nombre
            FROM restaurant_comandas c
            JOIN restaurant_mesas m ON m.id = c.mesa_id
            LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
            LEFT JOIN usuarios u ON u.id = c.mozo_id
            WHERE c.id = :id AND c.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $comanda = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$comanda) Response::error('Comanda no encontrada', 404);

        $stmtI = $pdo->prepare("
            SELECT ci.*, p.foto
            FROM restaurant_comanda_items ci
            LEFT JOIN productos p ON p.id = ci.producto_id
            WHERE ci.comanda_id = :cid
            ORDER BY ci.id ASC
        ");
        $stmtI->execute([':cid' => (int)$_GET['id']]);
        $comanda['items'] = $stmtI->fetchAll(PDO::FETCH_ASSOC);

        Response::success('OK', $comanda);
    }

    // Listado de comandas abiertas (para vista de salón)
    $where  = ["c.negocio_id = :nid"];
    $params = [':nid' => $negocioId];

    if (!empty($_GET['mesa_id'])) { $where[] = "c.mesa_id = :mid";          $params[':mid']   = (int)$_GET['mesa_id']; }
    if (!empty($_GET['fecha']))   { $where[] = "DATE(c.cerrada_at) = :fec"; $params[':fec']   = $_GET['fecha']; }
    if (!empty($_GET['estado']))  { $where[] = "c.estado = :est";           $params[':est']   = $_GET['estado']; }
    elseif (empty($_GET['todas'])){ $where[] = "c.estado IN ('abierta','en_cocina','lista')"; }

    $stmt = $pdo->prepare("
        SELECT c.*, m.numero AS mesa_numero, s.nombre AS sector_nombre,
               u.nombre AS mozo_nombre,
               (SELECT COUNT(*) FROM restaurant_comanda_items WHERE comanda_id=c.id AND estado_cocina='pendiente') AS items_pendientes,
               (SELECT COUNT(*) FROM restaurant_comanda_items WHERE comanda_id=c.id AND estado_cocina='listo')     AS items_listos,
               (SELECT COUNT(*) FROM restaurant_comanda_items WHERE comanda_id=c.id AND estado_cocina != 'cancelado') AS total_items
        FROM restaurant_comandas c
        JOIN restaurant_mesas m ON m.id = c.mesa_id
        LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
        LEFT JOIN usuarios u ON u.id = c.mozo_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY c.abierta_at DESC
    ");
    $stmt->execute($params);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ─── POST: abrir comanda ───────────────────────────────────── */
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);

    // Abrir comanda en una mesa
    if (isset($d['action']) && $d['action'] === 'abrir') {
        if (empty($d['mesa_id'])) Response::error('mesa_id requerido', 400);
        $mesaId = (int)$d['mesa_id'];

        // Verificar que no haya comanda abierta en esa mesa
        $existe = $pdo->prepare("SELECT id FROM restaurant_comandas WHERE mesa_id=:mid AND negocio_id=:nid AND estado IN ('abierta','en_cocina','lista')");
        $existe->execute([':mid' => $mesaId, ':nid' => $negocioId]);
        if ($row = $existe->fetch()) Response::success('Comanda ya existe', ['id' => $row['id']]);

        $numero = siguienteNumeroComanda($pdo, $negocioId);
        $stmt   = $pdo->prepare("
            INSERT INTO restaurant_comandas (negocio_id, mesa_id, reserva_id, numero, mozo_id, personas)
            VALUES (:nid, :mid, :rid, :num, :uid, :per)
        ");
        $stmt->execute([
            ':nid' => $negocioId,
            ':mid' => $mesaId,
            ':rid' => !empty($d['reserva_id']) ? (int)$d['reserva_id'] : null,
            ':num' => $numero,
            ':uid' => $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null,
            ':per' => (int)($d['personas'] ?? 1),
        ]);
        $comandaId = $pdo->lastInsertId();
        $pdo->prepare("UPDATE restaurant_mesas SET estado='ocupada' WHERE id=:id AND negocio_id=:nid")
            ->execute([':id' => $mesaId, ':nid' => $negocioId]);

        Response::success('Comanda abierta', ['id' => $comandaId, 'numero' => $numero], 201);
    }

    // Agregar ítem a comanda
    if (isset($d['action']) && $d['action'] === 'agregar_item') {
        $required = ['comanda_id', 'producto_id', 'cantidad'];
        foreach ($required as $f) if (empty($d[$f])) Response::error("Campo requerido: {$f}", 400);

        // Obtener precio del producto
        $prod = $pdo->prepare("SELECT nombre, precio_venta FROM productos WHERE id=:id AND negocio_id=:nid");
        $prod->execute([':id' => (int)$d['producto_id'], ':nid' => $negocioId]);
        $producto = $prod->fetch(PDO::FETCH_ASSOC);
        if (!$producto) Response::error('Producto no encontrado', 404);

        $precio   = (float)($d['precio_unit'] ?? $producto['precio_venta']);
        $cantidad = (int)$d['cantidad'];
        $subtotal = $precio * $cantidad;

        $stmt = $pdo->prepare("
            INSERT INTO restaurant_comanda_items
                (comanda_id, negocio_id, producto_id, nombre_item, precio_unit, cantidad, subtotal, observaciones, sector_cocina)
            VALUES
                (:cid, :nid, :pid, :nombre, :precio, :cant, :sub, :obs, :sec)
        ");
        $stmt->execute([
            ':cid'    => (int)$d['comanda_id'],
            ':nid'    => $negocioId,
            ':pid'    => (int)$d['producto_id'],
            ':nombre' => $d['nombre_item'] ?? $producto['nombre'],
            ':precio' => $precio,
            ':cant'   => $cantidad,
            ':sub'    => $subtotal,
            ':obs'    => trim($d['observaciones'] ?? ''),
            ':sec'    => $d['sector_cocina'] ?? 'principal',
        ]);
        $itemId = $pdo->lastInsertId();
        recalcularComanda($pdo, (int)$d['comanda_id']);

        Response::success('Ítem agregado', ['item_id' => $itemId, 'subtotal' => $subtotal], 201);
    }

    // Enviar comanda a cocina
    if (isset($d['action']) && $d['action'] === 'enviar_cocina') {
        if (empty($d['comanda_id'])) Response::error('comanda_id requerido', 400);
        $cid = (int)$d['comanda_id'];

        $pdo->prepare("
            UPDATE restaurant_comanda_items
            SET estado_cocina='pendiente', enviado_at=NOW()
            WHERE comanda_id=:cid AND estado_cocina='pendiente' AND enviado_at IS NULL
        ")->execute([':cid' => $cid]);

        $pdo->prepare("UPDATE restaurant_comandas SET estado='en_cocina' WHERE id=:id AND negocio_id=:nid")
            ->execute([':id' => $cid, ':nid' => $negocioId]);

        Response::success(null, 'Comanda enviada a cocina');
    }

    // Cerrar/cobrar comanda → genera venta
    if (isset($d['action']) && $d['action'] === 'cerrar') {
        if (empty($d['comanda_id'])) Response::error('comanda_id requerido', 400);
        $cid = (int)$d['comanda_id'];

        $stmtCmd = $pdo->prepare("SELECT * FROM restaurant_comandas WHERE id=:id AND negocio_id=:nid");
        $stmtCmd->execute([':id' => $cid, ':nid' => $negocioId]);
        $comanda = $stmtCmd->fetch(PDO::FETCH_ASSOC);
        if (!$comanda) Response::error('Comanda no encontrada', 404);

        // Recalcular subtotal antes de cobrar (por si quedó desactualizado)
        recalcularComanda($pdo, $cid);
        $stmtCmd2 = $pdo->prepare("SELECT * FROM restaurant_comandas WHERE id=:id");
        $stmtCmd2->execute([':id' => $cid]);
        $comanda = $stmtCmd2->fetch(PDO::FETCH_ASSOC);

        $metodoPago = $d['metodo_pago'] ?? 'efectivo';
        $descuento  = (float)($d['descuento'] ?? 0);
        $total      = $comanda['subtotal'] - $descuento;

        // Crear venta
        $pdo->prepare("
            INSERT INTO ventas (negocio_id, usuario_id, caja_id, cliente_nombre, subtotal, descuento, total, metodo_pago, observaciones)
            VALUES (:nid, :uid, :caj, :cn, :sub, :desc, :tot, :mp, :obs)
        ")->execute([
            ':nid'  => $negocioId,
            ':uid'  => $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null,
            ':caj'  => $d['caja_id'] ?? null,
            ':cn'   => $d['cliente_nombre'] ?? null,
            ':sub'  => $comanda['subtotal'],
            ':desc' => $descuento,
            ':tot'  => $total,
            ':mp'   => $metodoPago,
            ':obs'  => "Comanda #{$comanda['numero']} - Mesa {$comanda['mesa_id']}",
        ]);
        $ventaId = $pdo->lastInsertId();

        // Copiar ítems a detalle_ventas
        $stmtItems = $pdo->prepare("SELECT * FROM restaurant_comanda_items WHERE comanda_id=:cid AND estado_cocina != 'cancelado'");
        $stmtItems->execute([':cid' => $cid]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        $stmtDV = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (:vid, :pid, :cant, :precio, :sub)");
        foreach ($items as $item) {
            if (empty($item['producto_id'])) continue;
            $stmtDV->execute([
                ':vid'    => $ventaId,
                ':pid'    => $item['producto_id'],
                ':cant'   => $item['cantidad'],
                ':precio' => $item['precio_unit'],
                ':sub'    => $item['subtotal'],
            ]);
        }

        // Cerrar comanda y liberar mesa
        $pdo->prepare("UPDATE restaurant_comandas SET estado='cerrada', venta_id=:vid, descuento=:desc, total=:tot, cerrada_at=NOW() WHERE id=:id")
            ->execute([':vid' => $ventaId, ':desc' => $descuento, ':tot' => $total, ':id' => $cid]);
        $pdo->prepare("UPDATE restaurant_mesas SET estado='libre' WHERE id=:mid AND negocio_id=:nid")
            ->execute([':mid' => $comanda['mesa_id'], ':nid' => $negocioId]);

        Response::success('Comanda cerrada y venta generada', ['venta_id' => $ventaId, 'total' => $total]);
    }

    Response::error('Acción no reconocida', 400);
}

/* ─── PUT: modificar ítem o comanda ────────────────────────── */
if ($method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true);

    // Actualizar estado de un ítem (desde cocina)
    if (!empty($d['item_id'])) {
        $stmt = $pdo->prepare("
            UPDATE restaurant_comanda_items
            SET estado_cocina = :est,
                listo_at     = IF(:est2 = 'listo',      NOW(), listo_at),
                entregado_at = IF(:est3 = 'entregado',  NOW(), entregado_at)
            WHERE id = :id AND negocio_id = :nid
        ");
        $stmt->execute([
            ':est'  => $d['estado_cocina'],
            ':est2' => $d['estado_cocina'],
            ':est3' => $d['estado_cocina'],
            ':id'   => (int)$d['item_id'],
            ':nid'  => $negocioId,
        ]);

        // Actualizar estado de la comanda si todos los ítems están listos
        if (!empty($d['comanda_id'])) {
            $cid     = (int)$d['comanda_id'];
            $stmtPend = $pdo->prepare("SELECT COUNT(*) FROM restaurant_comanda_items WHERE comanda_id=:cid AND estado_cocina IN ('pendiente','en_preparacion')");
            $stmtPend->execute([':cid' => $cid]);
            $pending = $stmtPend->fetchColumn();
            if ($pending == 0) {
                $pdo->prepare("UPDATE restaurant_comandas SET estado='lista' WHERE id=:id AND estado='en_cocina'")
                    ->execute([':id' => $cid]);
            }
        }
        Response::success(null, 'Estado actualizado');
    }

    // Actualizar descuento / observaciones de comanda
    $id = (int)($_GET['id'] ?? $d['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $allow  = ['descuento','observaciones','personas','mozo_id'];
    $sets   = [];
    $params = [':id' => $id, ':nid' => $negocioId];
    foreach ($allow as $f) {
        if (isset($d[$f])) { $sets[] = "{$f}=:{$f}"; $params[":{$f}"] = $d[$f]; }
    }
    if (isset($d['descuento'])) {
        $sets[] = "total = subtotal - :desc2";
        $params[':desc2'] = (float)$d['descuento'];
    }
    if (!empty($sets)) {
        $pdo->prepare("UPDATE restaurant_comandas SET ".implode(',',$sets)." WHERE id=:id AND negocio_id=:nid")
            ->execute($params);
    }
    Response::success(null, 'Comanda actualizada');
}

/* ─── DELETE: cancelar ítem ────────────────────────────────── */
if ($method === 'DELETE') {
    $itemId    = (int)($_GET['item_id'] ?? 0);
    $comandaId = (int)($_GET['comanda_id'] ?? 0);
    if (!$itemId) Response::error('item_id requerido', 400);

    $pdo->prepare("UPDATE restaurant_comanda_items SET estado_cocina='cancelado' WHERE id=:id AND negocio_id=:nid")
        ->execute([':id' => $itemId, ':nid' => $negocioId]);

    if ($comandaId) recalcularComanda($pdo, $comandaId);
    Response::success(null, 'Ítem cancelado');
}
