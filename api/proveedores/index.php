<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

Auth::check();

$negocioId = (int)$_SESSION['negocio_id'];
$pdo       = (new Database())->getConnection();
$method    = $_SERVER['REQUEST_METHOD'];

// GET — listar / buscar
if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    $sql = "SELECT p.*,
                (SELECT COUNT(*) FROM productos pr WHERE pr.proveedor_id = p.id AND pr.negocio_id = ?) AS total_productos
            FROM proveedores p
            WHERE p.negocio_id = ? AND p.activo = 1";
    $params = [$negocioId, $negocioId];
    if ($q) {
        $sql .= " AND (p.nombre LIKE ? OR p.contacto LIKE ? OR p.email LIKE ?)";
        $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
    }
    $sql .= " ORDER BY p.nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    Response::success('Proveedores obtenidos', $stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// POST — crear
if ($method === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true) ?? [];
    $nombre = trim($data['nombre'] ?? '');
    if (!$nombre) { Response::error('Nombre requerido', 400); exit; }

    $sql = "INSERT INTO proveedores
                (negocio_id, nombre, razon_social, cuit, contacto, telefono, email, direccion, notas)
            VALUES (?,?,?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $negocioId,
        $nombre,
        $data['razon_social'] ?? null,
        $data['cuit']         ?? null,
        $data['contacto']     ?? null,
        $data['telefono']     ?? null,
        $data['email']        ?? null,
        $data['direccion']    ?? null,
        $data['notas']        ?? null,
    ]);
    Response::success('Proveedor creado', ['id' => $pdo->lastInsertId()]);
    exit;
}

// PUT — editar
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($data['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }

    $sql = "UPDATE proveedores SET
                nombre=?, razon_social=?, cuit=?, contacto=?, telefono=?, email=?, direccion=?, notas=?
            WHERE id=? AND negocio_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['nombre']       ?? '',
        $data['razon_social'] ?? null,
        $data['cuit']         ?? null,
        $data['contacto']     ?? null,
        $data['telefono']     ?? null,
        $data['email']        ?? null,
        $data['direccion']    ?? null,
        $data['notas']        ?? null,
        $id, $negocioId,
    ]);
    Response::success('Proveedor actualizado', []);
    exit;
}

// DELETE — desactivar
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }
    $stmt = $pdo->prepare("UPDATE proveedores SET activo=0 WHERE id=? AND negocio_id=?");
    $stmt->execute([$id, $negocioId]);
    Response::success('Proveedor eliminado', []);
    exit;
}

Response::error('Método no soportado', 405);
