<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$entity = $_GET['entity'] ?? 'insumos'; // insumos | compras | recetas

/* ═══════════════════════════════════════════════════════════
   GET
═══════════════════════════════════════════════════════════ */
if ($method === 'GET') {

    // ── Insumos ──────────────────────────────────────────────
    if ($entity === 'insumos') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM restaurant_insumos WHERE id=:id AND negocio_id=:nid");
            $stmt->execute([':id'=>(int)$_GET['id'],':nid'=>$negocioId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) Response::error('Insumo no encontrado', 404);
            Response::success('OK', $row);
        }
        $stmt = $pdo->prepare("
            SELECT i.*,
                   (SELECT COALESCE(SUM(c.total),0) FROM restaurant_compras c WHERE c.insumo_id = i.id) AS gasto_total,
                   (SELECT COUNT(r.id) FROM restaurant_recetas r WHERE r.insumo_id = i.id) AS usado_en_platos
            FROM restaurant_insumos i
            WHERE i.negocio_id = :nid AND i.activo = 1
            ORDER BY i.categoria, i.nombre
        ");
        $stmt->execute([':nid'=>$negocioId]);
        Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ── Compras ──────────────────────────────────────────────
    if ($entity === 'compras') {
        $where  = ["c.negocio_id = :nid"];
        $params = [':nid' => $negocioId];

        if (!empty($_GET['insumo_id'])) { $where[] = "c.insumo_id = :iid"; $params[':iid'] = (int)$_GET['insumo_id']; }
        if (!empty($_GET['desde']))     { $where[] = "c.fecha >= :desde";   $params[':desde'] = $_GET['desde']; }
        if (!empty($_GET['hasta']))     { $where[] = "c.fecha <= :hasta";   $params[':hasta'] = $_GET['hasta']; }

        $stmt = $pdo->prepare("
            SELECT c.*, i.nombre AS insumo_nombre, i.unidad,
                   u.nombre AS usuario_nombre,
                   p.nombre AS proveedor
            FROM restaurant_compras c
            JOIN restaurant_insumos i ON i.id = c.insumo_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            LEFT JOIN restaurant_proveedores p ON p.id = c.proveedor_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY c.fecha DESC, c.id DESC
            LIMIT " . (int)($_GET['limit'] ?? 200)
        );
        $stmt->execute($params);
        $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totales del período
        $totalGasto = array_sum(array_column($compras, 'total'));
        Response::success('OK', ['compras' => $compras, 'total_periodo' => $totalGasto]);
    }

    // ── Recetas ──────────────────────────────────────────────
    if ($entity === 'recetas') {
        // Si piden receta de un plato específico
        if (!empty($_GET['producto_id'])) {
            $stmt = $pdo->prepare("
                SELECT r.*, i.nombre AS insumo_nombre, i.unidad, i.precio_unitario,
                       ROUND(r.cantidad_porcion * i.precio_unitario, 2) AS costo_item
                FROM restaurant_recetas r
                JOIN restaurant_insumos i ON i.id = r.insumo_id
                WHERE r.producto_id = :pid AND r.negocio_id = :nid
                ORDER BY i.nombre
            ");
            $stmt->execute([':pid'=>(int)$_GET['producto_id'],':nid'=>$negocioId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $costo = array_sum(array_column($items, 'costo_item'));
            Response::success('OK', ['items'=>$items,'costo_total'=>$costo]);
        }

        // Listado de todos los platos con su costo calculado
        $stmt = $pdo->prepare("
            SELECT p.id, p.nombre, p.precio_venta, p.foto,
                   cat.nombre AS categoria_nombre,
                   COALESCE(SUM(r.cantidad_porcion * i.precio_unitario),0) AS costo_calculado,
                   COUNT(r.id) AS total_ingredientes
            FROM productos p
            LEFT JOIN categorias cat ON cat.id = p.categoria_id
            LEFT JOIN restaurant_recetas r ON r.producto_id = p.id AND r.negocio_id = p.negocio_id
            LEFT JOIN restaurant_insumos i ON i.id = r.insumo_id
            WHERE p.negocio_id = :nid AND p.activo = 1
            GROUP BY p.id
            ORDER BY cat.nombre, p.nombre
        ");
        $stmt->execute([':nid'=>$negocioId]);
        Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ── Resumen dashboard ────────────────────────────────────
    if ($entity === 'resumen') {
        $gastoMes = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM restaurant_compras WHERE negocio_id=:nid AND MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())");
        $gastoMes->execute([':nid'=>$negocioId]);

        $gastoSemana = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM restaurant_compras WHERE negocio_id=:nid AND fecha >= DATE_SUB(CURDATE(),INTERVAL 7 DAY)");
        $gastoSemana->execute([':nid'=>$negocioId]);

        $stockBajo = $pdo->prepare("SELECT COUNT(*) FROM restaurant_insumos WHERE negocio_id=:nid AND activo=1 AND stock_actual <= stock_minimo AND stock_minimo > 0");
        $stockBajo->execute([':nid'=>$negocioId]);

        $totalInsumos = $pdo->prepare("SELECT COUNT(*) FROM restaurant_insumos WHERE negocio_id=:nid AND activo=1");
        $totalInsumos->execute([':nid'=>$negocioId]);

        Response::success('OK', [
            'gasto_mes'     => (float)$gastoMes->fetchColumn(),
            'gasto_semana'  => (float)$gastoSemana->fetchColumn(),
            'stock_bajo'    => (int)$stockBajo->fetchColumn(),
            'total_insumos' => (int)$totalInsumos->fetchColumn(),
        ]);
    }

    Response::error('Entity no válida', 400);
}

/* ═══════════════════════════════════════════════════════════
   POST
═══════════════════════════════════════════════════════════ */
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);

    // ── Crear insumo ─────────────────────────────────────────
    if ($entity === 'insumos') {
        if (empty($d['nombre'])) Response::error('nombre requerido', 400);
        $stmt = $pdo->prepare("
            INSERT INTO restaurant_insumos (negocio_id, nombre, categoria, unidad, precio_unitario, stock_actual, stock_minimo)
            VALUES (:nid,:nombre,:cat,:unidad,:precio,:stock,:min)
        ");
        $stmt->execute([
            ':nid'    => $negocioId,
            ':nombre' => trim($d['nombre']),
            ':cat'    => trim($d['categoria'] ?? ''),
            ':unidad' => trim($d['unidad'] ?? 'unidad'),
            ':precio' => (float)($d['precio_unitario'] ?? 0),
            ':stock'  => (float)($d['stock_actual'] ?? 0),
            ':min'    => (float)($d['stock_minimo'] ?? 0),
        ]);
        Response::success('Insumo creado', ['id' => $pdo->lastInsertId()], 201);
    }

    // ── Registrar compra ─────────────────────────────────────
    if ($entity === 'compras') {
        $req = ['insumo_id','cantidad','precio_unitario','fecha'];
        foreach ($req as $f) if (empty($d[$f])) Response::error("Campo requerido: {$f}", 400);

        $cantidad = (float)$d['cantidad'];
        $precio   = (float)$d['precio_unitario'];
        $total    = $cantidad * $precio;

        $pdo->beginTransaction();
        try {
            // Insertar compra
            $stmt = $pdo->prepare("
                INSERT INTO restaurant_compras (negocio_id, insumo_id, cantidad, precio_unitario, total, proveedor_id, fecha, notas, usuario_id)
                VALUES (:nid,:iid,:cant,:precio,:total,:prov,:fecha,:notas,:uid)
            ");
            $provId = !empty($d['proveedor_id']) ? (int)$d['proveedor_id'] : null;
            $stmt->execute([
                ':nid'    => $negocioId,
                ':iid'    => (int)$d['insumo_id'],
                ':cant'   => $cantidad,
                ':precio' => $precio,
                ':total'  => $total,
                ':prov'   => $provId,
                ':fecha'  => $d['fecha'],
                ':notas'  => trim($d['notas'] ?? ''),
                ':uid'    => $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null,
            ]);
            $cid = $pdo->lastInsertId();

            // Actualizar stock y precio del insumo
            $pdo->prepare("
                UPDATE restaurant_insumos
                SET stock_actual = stock_actual + :cant,
                    precio_unitario = :precio,
                    updated_at = NOW()
                WHERE id = :id AND negocio_id = :nid
            ")->execute([':cant'=>$cantidad,':precio'=>$precio,':id'=>(int)$d['insumo_id'],':nid'=>$negocioId]);

            $pdo->commit();
            Response::success('Compra registrada', ['id'=>$cid,'total'=>$total], 201);
        } catch(Exception $e) {
            $pdo->rollBack();
            Response::error('Error al registrar compra', 500);
        }
    }

    // ── Guardar/actualizar receta de un plato ─────────────────
    if ($entity === 'recetas') {
        // Recibe array de ingredientes: [{insumo_id, cantidad_porcion}]
        $pid = (int)($d['producto_id'] ?? 0);
        if (!$pid) Response::error('producto_id requerido', 400);
        if (!isset($d['ingredientes']) || !is_array($d['ingredientes']))
            Response::error('ingredientes requerido', 400);

        $pdo->beginTransaction();
        try {
            // Eliminar receta anterior
            $pdo->prepare("DELETE FROM restaurant_recetas WHERE producto_id=:pid AND negocio_id=:nid")
                ->execute([':pid'=>$pid,':nid'=>$negocioId]);

            // Insertar nuevos ingredientes
            $stmt = $pdo->prepare("
                INSERT INTO restaurant_recetas (negocio_id, producto_id, insumo_id, cantidad_porcion)
                VALUES (:nid,:pid,:iid,:cant)
            ");
            foreach ($d['ingredientes'] as $ing) {
                if (empty($ing['insumo_id']) || !isset($ing['cantidad_porcion'])) continue;
                $stmt->execute([
                    ':nid'  => $negocioId,
                    ':pid'  => $pid,
                    ':iid'  => (int)$ing['insumo_id'],
                    ':cant' => (float)$ing['cantidad_porcion'],
                ]);
            }
            $pdo->commit();
            Response::success('Receta guardada');
        } catch(Exception $e) {
            $pdo->rollBack();
            Response::error('Error al guardar receta', 500);
        }
    }

    Response::error('Entity no válida', 400);
}

/* ═══════════════════════════════════════════════════════════
   PUT
═══════════════════════════════════════════════════════════ */
if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($_GET['id'] ?? $d['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    if ($entity === 'insumos') {
        $allow = ['nombre','categoria','unidad','precio_unitario','stock_actual','stock_minimo'];
        $sets = []; $params = [':id'=>$id,':nid'=>$negocioId];
        foreach ($allow as $f) {
            if (isset($d[$f])) { $sets[] = "{$f}=:{$f}"; $params[":{$f}"] = $d[$f]; }
        }
        if (empty($sets)) Response::error('Sin campos', 400);
        $pdo->prepare("UPDATE restaurant_insumos SET ".implode(',',$sets)." WHERE id=:id AND negocio_id=:nid")->execute($params);
        Response::success(null, 'Insumo actualizado');
    }

    Response::error('Entity no válida', 400);
}

/* ═══════════════════════════════════════════════════════════
   DELETE
═══════════════════════════════════════════════════════════ */
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    if ($entity === 'insumos') {
        $pdo->prepare("UPDATE restaurant_insumos SET activo=0 WHERE id=:id AND negocio_id=:nid")->execute([':id'=>$id,':nid'=>$negocioId]);
        Response::success(null, 'Insumo eliminado');
    }
    if ($entity === 'compras') {
        $stmt = $pdo->prepare("SELECT * FROM restaurant_compras WHERE id=:id AND negocio_id=:nid");
        $stmt->execute([':id'=>$id,':nid'=>$negocioId]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($c) {
            $pdo->prepare("UPDATE restaurant_insumos SET stock_actual = stock_actual - :cant WHERE id=:iid AND negocio_id=:nid")
                ->execute([':cant'=>$c['cantidad'],':iid'=>$c['insumo_id'],':nid'=>$negocioId]);
            $pdo->prepare("DELETE FROM restaurant_compras WHERE id=:id AND negocio_id=:nid")->execute([':id'=>$id,':nid'=>$negocioId]);
        }
        Response::success(null, 'Compra eliminada');
    }

    Response::error('Entity no válida', 400);
}
