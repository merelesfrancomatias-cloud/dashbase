<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();

$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
PlanGuard::requireActive((int)$negocio_id, $db);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM gym_planes WHERE negocio_id=? ORDER BY precio ASC");
    $stmt->execute([$negocio_id]);
    $planes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    Response::success('ok', $planes);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $nombre      = trim($data['nombre'] ?? '');
    $descripcion = trim($data['descripcion'] ?? '');
    $precio      = (float)($data['precio'] ?? 0);
    $duracion    = (int)($data['duracion_dias'] ?? 30);
    $clases      = !empty($data['clases_semana']) ? (int)$data['clases_semana'] : null;
    $color       = $data['color'] ?? '#f97316';

    if (!$nombre) Response::error('Nombre requerido', 400);

    $stmt = $db->prepare("INSERT INTO gym_planes (negocio_id,nombre,descripcion,precio,duracion_dias,clases_semana,color) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$negocio_id,$nombre,$descripcion,$precio,$duracion,$clases,$color]);
    Response::success('Plan creado', ['id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($data['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    $fields = [];
    $params = [];
    foreach (['nombre','descripcion','precio','duracion_dias','clases_semana','color','activo'] as $f) {
        if (isset($data[$f])) {
            $fields[] = "$f=?";
            $params[] = $data[$f] === '' ? null : $data[$f];
        }
    }
    if (empty($fields)) Response::error('Nada que actualizar', 400);
    $params[] = $id; $params[] = $negocio_id;
    $db->prepare("UPDATE gym_planes SET ".implode(',',$fields)." WHERE id=? AND negocio_id=?")->execute($params);
    Response::success('Plan actualizado');
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("UPDATE gym_planes SET activo=0 WHERE id=? AND negocio_id=?")->execute([$id,$negocio_id]);
    Response::success('Plan desactivado');
}
