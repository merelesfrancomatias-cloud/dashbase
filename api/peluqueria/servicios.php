<?php
require_once __DIR__ . '/../../api/bootstrap.php';
header('Content-Type: application/json');
Auth::check();

$negocioId = (int)$_SESSION['negocio_id'];
$pdo       = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $pdo);
$method    = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $st = $pdo->prepare("SELECT * FROM servicios WHERE negocio_id=? AND activo=1 ORDER BY categoria, nombre");
    $st->execute([$negocioId]);
    Response::success('Servicios obtenidos', $st->fetchAll());
    exit;
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?: [];
    if (empty($d['nombre'])) { Response::error('Nombre requerido', 400); exit; }
    $st = $pdo->prepare("INSERT INTO servicios (negocio_id,nombre,descripcion,duracion_min,precio,comision_porcentaje,categoria,color) VALUES (?,?,?,?,?,?,?,?)");
    $st->execute([$negocioId, $d['nombre'], $d['descripcion']??null, (int)($d['duracion_min']??30), (float)($d['precio']??0), (float)($d['comision_porcentaje']??0), $d['categoria']??'General', $d['color']??'#8b5cf6']);
    Response::success('Servicio creado', ['id' => $pdo->lastInsertId()]);
    exit;
}

if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int)($d['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }
    $st = $pdo->prepare("UPDATE servicios SET nombre=?,descripcion=?,duracion_min=?,precio=?,comision_porcentaje=?,categoria=?,color=? WHERE id=? AND negocio_id=?");
    $st->execute([$d['nombre'], $d['descripcion']??null, (int)($d['duracion_min']??30), (float)($d['precio']??0), (float)($d['comision_porcentaje']??0), $d['categoria']??'General', $d['color']??'#8b5cf6', $id, $negocioId]);
    Response::success('Servicio actualizado', []);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }
    $pdo->prepare("UPDATE servicios SET activo=0 WHERE id=? AND negocio_id=?")->execute([$id, $negocioId]);
    Response::success('Servicio eliminado', []);
    exit;
}

Response::error('Método no soportado', 405);
