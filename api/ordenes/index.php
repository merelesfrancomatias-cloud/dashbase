<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

Auth::check();

$negocioId = (int)$_SESSION['negocio_id'];
$userId    = (int)($_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? 0);
$pdo       = (new Database())->getConnection();
$method    = $_SERVER['REQUEST_METHOD'];

// ---- GET ----
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id) {
        $sql = "SELECT oc.*, p.nombre AS proveedor_nombre
                FROM ordenes_compra oc
                LEFT JOIN proveedores p ON p.id = oc.proveedor_id
                WHERE oc.id = ? AND oc.negocio_id = ?";
        $st = $pdo->prepare($sql);
        $st->execute([$id, $negocioId]);
        $orden = $st->fetch();
        if (!$orden) { Response::error('Orden no encontrada', 404); exit; }

        $stI = $pdo->prepare("SELECT oi.*, pr.nombre AS producto_nombre
                               FROM ordenes_compra_items oi
                               LEFT JOIN productos pr ON pr.id = oi.producto_id
                               WHERE oi.orden_id = ?");
        $stI->execute([$id]);
        $orden['items'] = $stI->fetchAll();

        Response::success('Orden obtenida', $orden); exit;
    }

    $estado = $_GET['estado'] ?? '';
    $prov   = (int)($_GET['proveedor_id'] ?? 0);
    $where  = 'oc.negocio_id = ?';
    $params = [$negocioId];
    if ($estado) { $where .= ' AND oc.estado = ?'; $params[] = $estado; }
    if ($prov)   { $where .= ' AND oc.proveedor_id = ?'; $params[] = $prov; }

    $sql = "SELECT oc.*, p.nombre AS proveedor_nombre,
                   (SELECT COUNT(*) FROM ordenes_compra_items WHERE orden_id = oc.id) AS total_items
            FROM ordenes_compra oc
            LEFT JOIN proveedores p ON p.id = oc.proveedor_id
            WHERE $where ORDER BY oc.created_at DESC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    Response::success('Órdenes obtenidas', $st->fetchAll()); exit;
}

// ---- POST ----
if ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?: [];
    $provId = (int)($body['proveedor_id'] ?? 0);
    $items  = $body['items'] ?? [];

    if (!$provId)       { Response::error('Proveedor requerido', 400); exit; }
    if (empty($items))  { Response::error('Debe agregar al menos un ítem', 400); exit; }

    // Número correlativo
    $stNum = $pdo->prepare("SELECT COUNT(*)+1 AS n FROM ordenes_compra WHERE negocio_id=?");
    $stNum->execute([$negocioId]);
    $numero = 'OC-' . str_pad($stNum->fetchColumn(), 4, '0', STR_PAD_LEFT);

    $subtotal = 0;
    foreach ($items as &$item) {
        $item['precio_unitario'] = (float)($item['precio_unitario'] ?? 0);
        $item['cantidad']        = (float)($item['cantidad'] ?? 0);
        $item['subtotal']        = $item['precio_unitario'] * $item['cantidad'];
        $subtotal += $item['subtotal'];
    }
    unset($item);

    $pdo->beginTransaction();
    try {
        $sql = "INSERT INTO ordenes_compra
                    (negocio_id, proveedor_id, numero, fecha, fecha_entrega_esperada,
                     estado, subtotal, total, notas, created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        $st = $pdo->prepare($sql);
        $st->execute([
            $negocioId,
            $provId,
            $numero,
            $body['fecha'] ?? date('Y-m-d'),
            $body['fecha_entrega_esperada'] ?: null,
            $body['estado'] ?? 'borrador',
            $subtotal,
            $subtotal,
            $body['notas'] ?? null,
            $userId,
        ]);
        $ordenId = (int)$pdo->lastInsertId();

        $sqlI = "INSERT INTO ordenes_compra_items
                     (orden_id, producto_id, descripcion, cantidad, precio_unitario, subtotal)
                 VALUES (?,?,?,?,?,?)";
        $stI = $pdo->prepare($sqlI);
        foreach ($items as $item) {
            $stI->execute([
                $ordenId,
                $item['producto_id'] ?: null,
                trim($item['descripcion'] ?? ''),
                (float)$item['cantidad'],
                (float)$item['precio_unitario'],
                (float)$item['subtotal'],
            ]);
        }

        $pdo->commit();
        Response::success('Orden creada', ['id' => $ordenId, 'numero' => $numero]); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        Response::error('Error al crear: ' . $e->getMessage(), 500); exit;
    }
}

// ---- PUT ----
if ($method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $id   = (int)($body['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }

    $stChk = $pdo->prepare("SELECT id, estado FROM ordenes_compra WHERE id=? AND negocio_id=?");
    $stChk->execute([$id, $negocioId]);
    $orden = $stChk->fetch();
    if (!$orden) { Response::error('Orden no encontrada', 404); exit; }

    $recibirStock = isset($body['estado']) && $body['estado'] === 'recibida' && $orden['estado'] !== 'recibida';

    $allowed = ['estado','notas','fecha_entrega_esperada','proveedor_id','total'];
    $sets = []; $vals = [];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $body)) {
            $sets[] = "$f = ?";
            $vals[] = $body[$f];
        }
    }
    if (!empty($sets)) {
        $vals[] = $id; $vals[] = $negocioId;
        $pdo->prepare("UPDATE ordenes_compra SET " . implode(',', $sets) . " WHERE id=? AND negocio_id=?")
            ->execute($vals);
    }

    if ($recibirStock) {
        $stItems = $pdo->prepare("SELECT producto_id, cantidad FROM ordenes_compra_items WHERE orden_id=? AND producto_id IS NOT NULL");
        $stItems->execute([$id]);
        foreach ($stItems->fetchAll() as $item) {
            $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id=? AND negocio_id=?")
                ->execute([$item['cantidad'], $item['producto_id'], $negocioId]);
        }
        $pdo->prepare("UPDATE ordenes_compra_items SET recibido=1 WHERE orden_id=?")->execute([$id]);
    }

    Response::success('Orden actualizada', []); exit;
}

// ---- DELETE ----
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }
    $st = $pdo->prepare("UPDATE ordenes_compra SET estado='cancelada' WHERE id=? AND negocio_id=?");
    $st->execute([$id, $negocioId]);
    if ($st->rowCount() === 0) { Response::error('Orden no encontrada', 404); exit; }
    Response::success('Orden cancelada', []); exit;
}

Response::error('Método no soportado', 405);
