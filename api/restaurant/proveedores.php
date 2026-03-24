<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM restaurant_proveedores WHERE id=:id AND negocio_id=:nid");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) Response::error('Proveedor no encontrado', 404);
        Response::success('OK', $row);
    }
    $stmt = $pdo->prepare("
        SELECT p.*,
               (SELECT COUNT(*) FROM restaurant_compras c WHERE c.proveedor_id = p.id) AS total_compras,
               (SELECT COALESCE(SUM(c.total),0) FROM restaurant_compras c WHERE c.proveedor_id = p.id) AS total_gastado
        FROM restaurant_proveedores p
        WHERE p.negocio_id = :nid AND p.activo = 1
        ORDER BY p.nombre
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    if (empty($d['nombre'])) Response::error('nombre requerido', 400);
    $stmt = $pdo->prepare("
        INSERT INTO restaurant_proveedores (negocio_id, nombre, contacto, telefono, email, direccion, notas)
        VALUES (:nid, :nom, :con, :tel, :email, :dir, :notas)
    ");
    $stmt->execute([
        ':nid'   => $negocioId,
        ':nom'   => trim($d['nombre']),
        ':con'   => trim($d['contacto'] ?? ''),
        ':tel'   => trim($d['telefono'] ?? ''),
        ':email' => trim($d['email'] ?? ''),
        ':dir'   => trim($d['direccion'] ?? ''),
        ':notas' => trim($d['notas'] ?? ''),
    ]);
    Response::success('Proveedor creado', ['id' => $pdo->lastInsertId()], 201);
}

if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($d['id'] ?? $_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $allow = ['nombre','contacto','telefono','email','direccion','notas'];
    $sets  = []; $params = [':id' => $id, ':nid' => $negocioId];
    foreach ($allow as $f) {
        if (isset($d[$f])) { $sets[] = "$f=:$f"; $params[":$f"] = $d[$f]; }
    }
    if (empty($sets)) Response::error('Sin campos', 400);
    $pdo->prepare("UPDATE restaurant_proveedores SET " . implode(',', $sets) . " WHERE id=:id AND negocio_id=:nid")->execute($params);
    Response::success('Proveedor actualizado');
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE restaurant_proveedores SET activo=0 WHERE id=:id AND negocio_id=:nid")->execute([':id' => $id, ':nid' => $negocioId]);
    Response::success('Proveedor eliminado');
}

Response::error('Método no soportado', 405);
