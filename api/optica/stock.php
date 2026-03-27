<?php
/**
 * api/optica/stock.php
 * GET    ?id=X            → item específico con últimos movimientos
 * GET    ?tipo=&q=&bajo=1 → listado con filtros
 * POST                    → crear item
 * PUT    ?id=X            → editar item
 * DELETE ?id=X            → desactivar item
 *
 * POST ?accion=mov        → registrar movimiento de stock
 * GET  ?accion=movimientos&item_id=X → historial de movimientos
 */
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── MOVIMIENTOS ────────────────────────────────────────────────────────────────
if ($method === 'GET' && ($_GET['accion'] ?? '') === 'movimientos') {
    $itemId = (int)($_GET['item_id'] ?? 0);
    if (!$itemId) Response::error('item_id requerido', 400);

    // Verificar que el item pertenece al negocio
    $chk = $pdo->prepare("SELECT id FROM optica_stock WHERE id = :id AND negocio_id = :nid");
    $chk->execute([':id' => $itemId, ':nid' => $negocioId]);
    if (!$chk->fetch()) Response::error('Item no encontrado', 404);

    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre AS usuario_nombre
        FROM optica_stock_mov m
        LEFT JOIN usuarios u ON u.id = m.usuario_id
        WHERE m.negocio_id = :nid AND m.item_id = :item_id
        ORDER BY m.created_at DESC
        LIMIT 100
    ");
    $stmt->execute([':nid' => $negocioId, ':item_id' => $itemId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST' && ($_GET['accion'] ?? '') === 'mov') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    $itemId  = (int)($d['item_id']  ?? 0);
    $tipo    = $d['tipo']    ?? 'entrada';
    $cant    = (int)($d['cantidad'] ?? 0);
    $notas   = trim($d['notas']     ?? '');
    if (!$itemId || !in_array($tipo, ['entrada','salida','ajuste'], true) || $cant === 0)
        Response::error('item_id, tipo y cantidad son requeridos', 400);

    // Obtener y bloquear el item
    $item = $pdo->prepare("SELECT id, stock_actual FROM optica_stock WHERE id = :id AND negocio_id = :nid AND activo = 1 FOR UPDATE");
    $item->execute([':id' => $itemId, ':nid' => $negocioId]);
    $row = $item->fetch(PDO::FETCH_ASSOC);
    if (!$row) Response::error('Item no encontrado', 404);

    $stockAnt = (int)$row['stock_actual'];
    if ($tipo === 'entrada')    $stockNuevo = $stockAnt + abs($cant);
    elseif ($tipo === 'salida') $stockNuevo = max(0, $stockAnt - abs($cant));
    else                        $stockNuevo = max(0, $stockAnt + $cant); // ajuste puede ser negativo

    $pdo->beginTransaction();
    $pdo->prepare("UPDATE optica_stock SET stock_actual = :s WHERE id = :id AND negocio_id = :nid")
        ->execute([':s' => $stockNuevo, ':id' => $itemId, ':nid' => $negocioId]);
    $pdo->prepare("INSERT INTO optica_stock_mov
        (negocio_id, item_id, tipo, cantidad, stock_anterior, stock_nuevo, pedido_id, notas, usuario_id)
        VALUES (:nid, :item, :tipo, :cant, :sant, :snuevo, :pid, :notas, :uid)")
        ->execute([
            ':nid'    => $negocioId,
            ':item'   => $itemId,
            ':tipo'   => $tipo,
            ':cant'   => $cant,
            ':sant'   => $stockAnt,
            ':snuevo' => $stockNuevo,
            ':pid'    => !empty($d['pedido_id']) ? (int)$d['pedido_id'] : null,
            ':notas'  => $notas ?: null,
            ':uid'    => $usuarioId,
        ]);
    $pdo->commit();
    Response::success('Movimiento registrado', ['stock_nuevo' => $stockNuevo]);
}

// ── GET ────────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM optica_stock WHERE id = :id AND negocio_id = :nid");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) Response::error('Item no encontrado', 404);

        $movs = $pdo->prepare("
            SELECT m.*, u.nombre AS usuario_nombre
            FROM optica_stock_mov m
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            WHERE m.negocio_id = :nid AND m.item_id = :item_id
            ORDER BY m.created_at DESC LIMIT 20
        ");
        $movs->execute([':nid' => $negocioId, ':item_id' => $item['id']]);
        $item['movimientos'] = $movs->fetchAll(PDO::FETCH_ASSOC);
        Response::success('OK', $item);
    }

    $where  = "negocio_id = :nid AND activo = 1";
    $params = [':nid' => $negocioId];

    if (!empty($_GET['tipo'])) {
        $where .= " AND tipo = :tipo";
        $params[':tipo'] = $_GET['tipo'];
    }
    if (!empty($_GET['q'])) {
        $where .= " AND (nombre LIKE :q1 OR marca LIKE :q2 OR modelo LIKE :q3 OR codigo LIKE :q4)";
        $q = '%' . $_GET['q'] . '%';
        $params[':q1'] = $q; $params[':q2'] = $q;
        $params[':q3'] = $q; $params[':q4'] = $q;
    }
    if (!empty($_GET['bajo'])) {
        $where .= " AND stock_actual <= stock_minimo";
    }

    $stmt = $pdo->prepare("SELECT * FROM optica_stock WHERE {$where} ORDER BY tipo, nombre LIMIT 500");
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $stats = $pdo->prepare("
        SELECT
            COUNT(*) AS total_items,
            SUM(stock_actual <= stock_minimo) AS stock_bajo,
            SUM(stock_actual = 0) AS sin_stock,
            SUM(tipo = 'montura') AS monturas,
            SUM(tipo = 'lente')   AS lentes,
            SUM(tipo = 'contacto')AS contacto,
            IFNULL(SUM(stock_actual * precio_costo), 0) AS valor_inventario
        FROM optica_stock
        WHERE negocio_id = :nid AND activo = 1
    ");
    $stats->execute([':nid' => $negocioId]);

    Response::success('OK', [
        'items' => $items,
        'stats' => $stats->fetch(PDO::FETCH_ASSOC),
    ]);
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['nombre'])) Response::error('El nombre es requerido', 400);

    $stmt = $pdo->prepare("
        INSERT INTO optica_stock
            (negocio_id, tipo, nombre, marca, modelo, color, material,
             descripcion, codigo, precio_costo, precio_venta, stock_actual, stock_minimo)
        VALUES
            (:nid, :tipo, :nombre, :marca, :modelo, :color, :material,
             :desc, :codigo, :pcosto, :pventa, :stock, :minimo)
    ");
    $stmt->execute([
        ':nid'    => $negocioId,
        ':tipo'   => $d['tipo']          ?? 'montura',
        ':nombre' => trim($d['nombre']),
        ':marca'  => $d['marca']         ?? null,
        ':modelo' => $d['modelo']        ?? null,
        ':color'  => $d['color']         ?? null,
        ':material'=> $d['material']     ?? null,
        ':desc'   => $d['descripcion']   ?? null,
        ':codigo' => $d['codigo']        ?? null,
        ':pcosto' => (float)($d['precio_costo'] ?? 0),
        ':pventa' => (float)($d['precio_venta'] ?? 0),
        ':stock'  => (int)($d['stock_actual']   ?? 0),
        ':minimo' => (int)($d['stock_minimo']   ?? 2),
    ]);
    $newId = (int)$pdo->lastInsertId();

    // Si se ingresó stock inicial, registrar movimiento
    if ((int)($d['stock_actual'] ?? 0) > 0) {
        $pdo->prepare("INSERT INTO optica_stock_mov
            (negocio_id, item_id, tipo, cantidad, stock_anterior, stock_nuevo, notas, usuario_id)
            VALUES (:nid, :item, 'entrada', :cant, 0, :snuevo, 'Stock inicial', :uid)")
            ->execute([
                ':nid'    => $negocioId,
                ':item'   => $newId,
                ':cant'   => (int)$d['stock_actual'],
                ':snuevo' => (int)$d['stock_actual'],
                ':uid'    => $usuarioId,
            ]);
    }

    Response::success('Item creado', ['id' => $newId], 201);
}

// ── PUT ────────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['nombre'])) Response::error('El nombre es requerido', 400);

    $pdo->prepare("
        UPDATE optica_stock SET
            tipo = :tipo, nombre = :nombre, marca = :marca, modelo = :modelo,
            color = :color, material = :material, descripcion = :desc,
            codigo = :codigo, precio_costo = :pcosto, precio_venta = :pventa,
            stock_minimo = :minimo
        WHERE id = :id AND negocio_id = :nid
    ")->execute([
        ':tipo'   => $d['tipo']          ?? 'montura',
        ':nombre' => trim($d['nombre']),
        ':marca'  => $d['marca']         ?? null,
        ':modelo' => $d['modelo']        ?? null,
        ':color'  => $d['color']         ?? null,
        ':material'=> $d['material']     ?? null,
        ':desc'   => $d['descripcion']   ?? null,
        ':codigo' => $d['codigo']        ?? null,
        ':pcosto' => (float)($d['precio_costo'] ?? 0),
        ':pventa' => (float)($d['precio_venta'] ?? 0),
        ':minimo' => (int)($d['stock_minimo']   ?? 2),
        ':id'     => $id,
        ':nid'    => $negocioId,
    ]);
    Response::success('Item actualizado');
}

// ── DELETE ─────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE optica_stock SET activo = 0 WHERE id = :id AND negocio_id = :nid")
        ->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Item eliminado');
}
