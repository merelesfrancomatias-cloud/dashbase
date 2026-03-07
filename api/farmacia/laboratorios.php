<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET','POST','PUT','DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();

$method = $_SERVER['REQUEST_METHOD'];
$db     = (new Database())->getConnection();

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id) {
        $s = $db->prepare("SELECT * FROM farmacia_laboratorios WHERE id=? AND negocio_id=?");
        $s->execute([$id, $negocioId]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if (!$row) Response::error('No encontrado', 404);
        Response::success('OK', $row);
    }
    $q = trim($_GET['q'] ?? '');
    $sql = "SELECT * FROM farmacia_laboratorios WHERE negocio_id=? AND activo=1";
    $params = [$negocioId];
    if ($q) { $sql .= " AND nombre LIKE ?"; $params[] = "%$q%"; }
    $sql .= " ORDER BY nombre";
    $s = $db->prepare($sql);
    $s->execute($params);
    Response::success('OK', $s->fetchAll(PDO::FETCH_ASSOC));
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['nombre'])) Response::error('El nombre es obligatorio', 422);
    $s = $db->prepare("INSERT INTO farmacia_laboratorios
        (negocio_id,nombre,cuit,contacto,telefono,email,direccion,condicion_pago,notas)
        VALUES (?,?,?,?,?,?,?,?,?)");
    $s->execute([$negocioId, $d['nombre'], $d['cuit']??null, $d['contacto']??null,
        $d['telefono']??null, $d['email']??null, $d['direccion']??null,
        $d['condicion_pago']??null, $d['notas']??null]);
    Response::success('Laboratorio creado', ['id' => $db->lastInsertId()], 201);
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['id'])) Response::error('ID requerido', 422);
    $s = $db->prepare("UPDATE farmacia_laboratorios SET
        nombre=?,cuit=?,contacto=?,telefono=?,email=?,direccion=?,condicion_pago=?,notas=?,activo=?
        WHERE id=? AND negocio_id=?");
    $s->execute([$d['nombre'], $d['cuit']??null, $d['contacto']??null,
        $d['telefono']??null, $d['email']??null, $d['direccion']??null,
        $d['condicion_pago']??null, $d['notas']??null,
        isset($d['activo']) ? (int)$d['activo'] : 1,
        (int)$d['id'], $negocioId]);
    Response::success('Laboratorio actualizado');
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 422);
    $db->prepare("UPDATE farmacia_laboratorios SET activo=0 WHERE id=? AND negocio_id=?")
       ->execute([$id, $negocioId]);
    Response::success('Eliminado');
}
