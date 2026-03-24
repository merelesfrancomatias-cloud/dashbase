<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();

$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
PlanGuard::requireActive((int)$negocio_id, $db);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM gym_clases WHERE negocio_id=? AND activo=1 ORDER BY dia_semana, hora_inicio");
    $stmt->execute([$negocio_id]);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    Response::success('ok', $clases);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $nombre     = trim($data['nombre'] ?? '');
    $instructor = trim($data['instructor'] ?? '');
    $dia        = (int)($data['dia_semana'] ?? 0);
    $hora       = $data['hora_inicio'] ?? '09:00';
    $duracion   = (int)($data['duracion_min'] ?? 60);
    $capacidad  = (int)($data['capacidad'] ?? 20);
    $color      = $data['color'] ?? '#f97316';

    if (!$nombre) Response::error('Nombre requerido', 400);

    $stmt = $db->prepare("INSERT INTO gym_clases (negocio_id,nombre,instructor,dia_semana,hora_inicio,duracion_min,capacidad,color) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$negocio_id,$nombre,$instructor,$dia,$hora,$duracion,$capacidad,$color]);
    Response::success('Clase creada', ['id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($data['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    $fields = []; $params = [];
    foreach (['nombre','instructor','dia_semana','hora_inicio','duracion_min','capacidad','color','activo'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f=?"; $params[] = $data[$f]; }
    }
    if (empty($fields)) Response::error('Nada que actualizar', 400);
    $params[] = $id; $params[] = $negocio_id;
    $db->prepare("UPDATE gym_clases SET ".implode(',',$fields)." WHERE id=? AND negocio_id=?")->execute($params);
    Response::success('Clase actualizada');
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("UPDATE gym_clases SET activo=0 WHERE id=? AND negocio_id=?")->execute([$id,$negocio_id]);
    Response::success('Clase desactivada');
}
