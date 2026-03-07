<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Response.php';

session_start();
Auth::check();
$negocioId = (int)$_SESSION['negocio_id'];

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ──────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id) {
        // Cliente individual + historial de reservas
        $stmt = $db->prepare("SELECT * FROM clientes_canchas WHERE id = ? AND negocio_id = ?");
        $stmt->execute([$id, $negocioId]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) Response::error('Cliente no encontrado', 404);

        // Historial de reservas del cliente
        $stmtH = $db->prepare("
            SELECT rc.*, c.nombre as cancha_nombre, c.deporte
            FROM reservas_canchas rc
            JOIN canchas c ON c.id = rc.cancha_id
            WHERE rc.cliente_telefono = ? AND c.negocio_id = ?
            ORDER BY rc.fecha DESC, rc.hora_inicio DESC
            LIMIT 30
        ");
        $stmtH->execute([$cliente['telefono'], $negocioId]);
        $cliente['historial'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        // Estadísticas
        $stmtS = $db->prepare("
            SELECT COUNT(*) as total_reservas,
                   SUM(rc.monto) as total_gastado,
                   MAX(rc.fecha) as ultima_visita
            FROM reservas_canchas rc
            JOIN canchas c ON c.id = rc.cancha_id
            WHERE rc.cliente_telefono = ? AND c.negocio_id = ? AND rc.estado = 'confirmada'
        ");
        $stmtS->execute([$cliente['telefono'], $negocioId]);
        $cliente['stats'] = $stmtS->fetch(PDO::FETCH_ASSOC);

        Response::success($cliente);
    }

    // Listado con búsqueda
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    if ($search) {
        $stmt = $db->prepare("
            SELECT * FROM clientes_canchas
            WHERE negocio_id = ? AND (nombre LIKE ? OR telefono LIKE ? OR email LIKE ?)
            ORDER BY nombre ASC LIMIT 100
        ");
        $like = "%$search%";
        $stmt->execute([$negocioId, $like, $like, $like]);
    } else {
        $stmt = $db->prepare("
            SELECT cc.*,
                   COUNT(rc.id) as total_reservas,
                   MAX(rc.fecha) as ultima_reserva,
                   SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END) as total_gastado
            FROM clientes_canchas cc
            LEFT JOIN reservas_canchas rc ON rc.cliente_telefono = cc.telefono
            LEFT JOIN canchas c ON c.id = rc.cancha_id AND c.negocio_id = cc.negocio_id
            WHERE cc.negocio_id = ?
            GROUP BY cc.id
            ORDER BY cc.nombre ASC
        ");
        $stmt->execute([$negocioId]);
    }
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    Response::success($clientes);
}

// ── POST ─────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre   = trim($data['nombre'] ?? '');
    $telefono = trim($data['telefono'] ?? '');
    $email    = trim($data['email'] ?? '');
    $notas    = trim($data['notas'] ?? '');

    if (!$nombre) Response::error('El nombre es requerido', 400);
    if (!$telefono) Response::error('El teléfono es requerido', 400);

    // Verificar duplicado por teléfono
    $dup = $db->prepare("SELECT id FROM clientes_canchas WHERE telefono = ? AND negocio_id = ?");
    $dup->execute([$telefono, $negocioId]);
    if ($dup->fetch()) Response::error('Ya existe un cliente con ese teléfono', 409);

    $stmt = $db->prepare("INSERT INTO clientes_canchas (negocio_id, nombre, telefono, email, notas) VALUES (?,?,?,?,?)");
    $stmt->execute([$negocioId, $nombre, $telefono, $email, $notas]);
    $newId = $db->lastInsertId();

    $nuevo = $db->prepare("SELECT * FROM clientes_canchas WHERE id = ?");
    $nuevo->execute([$newId]);
    Response::success($nuevo->fetch(PDO::FETCH_ASSOC), 'Cliente creado', 201);
}

// ── PUT ──────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id       = (int)($data['id'] ?? 0);
    $nombre   = trim($data['nombre'] ?? '');
    $telefono = trim($data['telefono'] ?? '');
    $email    = trim($data['email'] ?? '');
    $notas    = trim($data['notas'] ?? '');

    if (!$id || !$nombre || !$telefono) Response::error('Datos incompletos', 400);

    // Verificar pertenencia
    $check = $db->prepare("SELECT id FROM clientes_canchas WHERE id = ? AND negocio_id = ?");
    $check->execute([$id, $negocioId]);
    if (!$check->fetch()) Response::error('Cliente no encontrado', 404);

    $stmt = $db->prepare("UPDATE clientes_canchas SET nombre=?, telefono=?, email=?, notas=? WHERE id=? AND negocio_id=?");
    $stmt->execute([$nombre, $telefono, $email, $notas, $id, $negocioId]);
    Response::success(null, 'Cliente actualizado');
}

// ── DELETE ───────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) Response::error('ID requerido', 400);

    $check = $db->prepare("SELECT id FROM clientes_canchas WHERE id = ? AND negocio_id = ?");
    $check->execute([$id, $negocioId]);
    if (!$check->fetch()) Response::error('Cliente no encontrado', 404);

    $db->prepare("DELETE FROM clientes_canchas WHERE id = ? AND negocio_id = ?")->execute([$id, $negocioId]);
    Response::success(null, 'Cliente eliminado');
}
