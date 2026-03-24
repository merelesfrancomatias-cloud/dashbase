<?php
require_once __DIR__ . '/../../api/bootstrap.php';
header('Content-Type: application/json');
Auth::check();
$negocioId = (int)$_SESSION['negocio_id'];
$pdo       = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $pdo);
$method    = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $activo = isset($_GET['activos']) ? 1 : null;

    if ($id > 0) {
        $st = $pdo->prepare("SELECT * FROM empleados WHERE id=? AND negocio_id=?");
        $st->execute([$id, $negocioId]);
        $e = $st->fetch(PDO::FETCH_ASSOC);
        if (!$e) Response::error('Empleado no encontrado', 404);
        Response::success('ok', $e);
    }

    $where = 'negocio_id=?';
    $params = [$negocioId];
    if ($activo !== null) { $where .= ' AND activo=?'; $params[] = $activo; }

    $st = $pdo->prepare("SELECT * FROM empleados WHERE $where ORDER BY nombre, apellido");
    $st->execute($params);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
    $data     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $nombre   = trim($data['nombre'] ?? '');
    $apellido = trim($data['apellido'] ?? '');
    if (!$nombre) Response::error('Nombre requerido', 400);

    $st = $pdo->prepare("INSERT INTO empleados (negocio_id,nombre,apellido,email,telefono,cargo,activo) VALUES (?,?,?,?,?,?,1)");
    $st->execute([$negocioId,$nombre,$apellido,trim($data['email']??''),trim($data['telefono']??''),trim($data['cargo']??'')]);
    Response::success('Empleado creado', ['id' => $pdo->lastInsertId()]);
}

if ($method === 'PUT') {
    $data     = json_decode(file_get_contents('php://input'), true) ?? [];
    $id       = (int)($data['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    $allowed = ['nombre','apellido','email','telefono','cargo','activo'];
    $fields = []; $params = [];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $data)) { $fields[] = "$f=?"; $params[] = $data[$f]; }
    }
    if (!$fields) Response::error('Nada que actualizar', 400);
    $params[] = $id; $params[] = $negocioId;
    $pdo->prepare("UPDATE empleados SET ".implode(',',$fields)." WHERE id=? AND negocio_id=?")->execute($params);
    Response::success('Empleado actualizado');
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE empleados SET activo=0 WHERE id=? AND negocio_id=?")->execute([$id, $negocioId]);
    Response::success('Empleado desactivado');
}
