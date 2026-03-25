<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $s = $pdo->prepare("SELECT * FROM farmacia_proveedores WHERE id=:id AND negocio_id=:nid AND activo=1");
        $s->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if (!$row) Response::error('Proveedor no encontrado', 404);
        Response::success('OK', $row);
    }

    $s = $pdo->prepare("SELECT * FROM farmacia_proveedores WHERE negocio_id=:nid AND activo=1 ORDER BY nombre ASC");
    $s->execute([':nid' => $negocioId]);
    Response::success('OK', $s->fetchAll(PDO::FETCH_ASSOC));
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['nombre'])) Response::error('El nombre es requerido', 422);

    $s = $pdo->prepare("
        INSERT INTO farmacia_proveedores
            (negocio_id, nombre, cuit, tipo, condicion_pago, contacto, telefono, email, direccion, notas)
        VALUES (:nid, :nombre, :cuit, :tipo, :cpago, :contacto, :tel, :email, :dir, :notas)
    ");
    $s->execute([
        ':nid'      => $negocioId,
        ':nombre'   => trim($d['nombre']),
        ':cuit'     => $d['cuit'] ?? null,
        ':tipo'     => $d['tipo'] ?? 'Distribuidora',
        ':cpago'    => $d['condicion_pago'] ?? null,
        ':contacto' => $d['contacto'] ?? null,
        ':tel'      => $d['telefono'] ?? null,
        ':email'    => $d['email'] ?? null,
        ':dir'      => $d['direccion'] ?? null,
        ':notas'    => $d['notas'] ?? null,
    ]);
    Response::success('Proveedor creado', ['id' => $pdo->lastInsertId()]);
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['id'])) Response::error('ID requerido', 422);
    if (empty($d['nombre'])) Response::error('El nombre es requerido', 422);

    $s = $pdo->prepare("
        UPDATE farmacia_proveedores
        SET nombre=:nombre, cuit=:cuit, tipo=:tipo, condicion_pago=:cpago,
            contacto=:contacto, telefono=:tel, email=:email, direccion=:dir, notas=:notas
        WHERE id=:id AND negocio_id=:nid
    ");
    $s->execute([
        ':nombre'   => trim($d['nombre']),
        ':cuit'     => $d['cuit'] ?? null,
        ':tipo'     => $d['tipo'] ?? 'Distribuidora',
        ':cpago'    => $d['condicion_pago'] ?? null,
        ':contacto' => $d['contacto'] ?? null,
        ':tel'      => $d['telefono'] ?? null,
        ':email'    => $d['email'] ?? null,
        ':dir'      => $d['direccion'] ?? null,
        ':notas'    => $d['notas'] ?? null,
        ':id'       => (int)$d['id'],
        ':nid'      => $negocioId,
    ]);
    Response::success('Proveedor actualizado');
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['id'])) Response::error('ID requerido', 422);
    $pdo->prepare("UPDATE farmacia_proveedores SET activo=0 WHERE id=:id AND negocio_id=:nid")
        ->execute([':id' => (int)$d['id'], ':nid' => $negocioId]);
    Response::success('Proveedor eliminado');
}

Response::error('Método no permitido', 405);
