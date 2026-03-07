<?php
require_once __DIR__ . '/../../api/bootstrap.php';
header('Content-Type: application/json');
Auth::check();
$negocioId = (int)$_SESSION['negocio_id'];
$pdo       = (new Database())->getConnection();
$method    = $_SERVER['REQUEST_METHOD'];

// Crear tabla si no existe
$pdo->exec("CREATE TABLE IF NOT EXISTS clientes_peluqueria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(40) NOT NULL,
    email VARCHAR(120) DEFAULT NULL,
    notas TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id),
    UNIQUE KEY uq_tel_negocio (telefono, negocio_id)
)");

if ($method === 'GET') {
    $id     = (int)($_GET['id'] ?? 0);
    $search = trim($_GET['search'] ?? '');

    if ($id) {
        // Cliente individual + historial
        $stC = $pdo->prepare("SELECT * FROM clientes_peluqueria WHERE id=? AND negocio_id=?");
        $stC->execute([$id, $negocioId]);
        $cliente = $stC->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) { Response::error('Cliente no encontrado', 404); exit; }

        $stH = $pdo->prepare("
            SELECT t.fecha, t.hora_inicio AS hora, t.servicio_nombre, t.precio,
                   e.nombre AS empleado_nombre
            FROM turnos t
            LEFT JOIN empleados e ON e.id = t.empleado_id
            WHERE t.negocio_id = ? AND t.cliente_id = ?
              AND t.estado IN ('completado','confirmado')
            ORDER BY t.fecha DESC, t.hora_inicio DESC
            LIMIT 30");
        $stH->execute([$negocioId, $id]);
        $historial = $stH->fetchAll(PDO::FETCH_ASSOC);

        $stS = $pdo->prepare("
            SELECT COUNT(*) AS total_turnos,
                   COALESCE(SUM(CASE WHEN estado IN ('completado','confirmado') THEN precio ELSE 0 END),0) AS total_gastado,
                   MAX(fecha) AS ultima_visita
            FROM turnos
            WHERE negocio_id = ? AND cliente_id = ?");
        $stS->execute([$negocioId, $id]);
        $stats = $stS->fetch(PDO::FETCH_ASSOC);

        $cliente['historial'] = $historial;
        $cliente['stats']     = $stats;
        echo json_encode(['success' => true, 'message' => 'Cliente encontrado', 'data' => $cliente, 'historial' => $historial, 'stats' => $stats]);
        http_response_code(200);
        exit;
    }

    // Lista con stats resumidas
    if ($search) {
        $st = $pdo->prepare("
            SELECT c.*,
                COALESCE((SELECT COUNT(*) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id),0) AS total_turnos,
                COALESCE((SELECT COUNT(*) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id AND MONTH(t.fecha)=MONTH(CURDATE()) AND YEAR(t.fecha)=YEAR(CURDATE())),0) AS turnos_mes,
                COALESCE((SELECT SUM(CASE WHEN estado IN ('completado','confirmado') THEN precio ELSE 0 END) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id),0) AS total_gastado,
                (SELECT MAX(fecha) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id) AS ultima_visita
            FROM clientes_peluqueria c
            WHERE c.negocio_id=? AND (c.nombre LIKE ? OR c.telefono LIKE ? OR c.email LIKE ?)
            ORDER BY c.nombre");
        $like = "%$search%";
        $st->execute([$negocioId, $like, $like, $like]);
    } else {
        $st = $pdo->prepare("
            SELECT c.*,
                COALESCE((SELECT COUNT(*) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id),0) AS total_turnos,
                COALESCE((SELECT COUNT(*) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id AND MONTH(t.fecha)=MONTH(CURDATE()) AND YEAR(t.fecha)=YEAR(CURDATE())),0) AS turnos_mes,
                COALESCE((SELECT SUM(CASE WHEN estado IN ('completado','confirmado') THEN precio ELSE 0 END) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id),0) AS total_gastado,
                (SELECT MAX(fecha) FROM turnos t WHERE t.negocio_id=c.negocio_id AND t.cliente_id=c.id) AS ultima_visita
            FROM clientes_peluqueria c
            WHERE c.negocio_id=?
            ORDER BY c.nombre");
        $st->execute([$negocioId]);
    }
    Response::success('Clientes obtenidos', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?: [];
    if (empty($d['nombre']))   { Response::error('Nombre requerido', 400); exit; }
    if (empty($d['telefono'])) { Response::error('Teléfono requerido', 400); exit; }
    // Verificar duplicado
    $chk = $pdo->prepare("SELECT id FROM clientes_peluqueria WHERE telefono=? AND negocio_id=?");
    $chk->execute([$d['telefono'], $negocioId]);
    if ($chk->fetch()) { Response::error('Ya existe un cliente con ese teléfono', 409); exit; }
    $st = $pdo->prepare("INSERT INTO clientes_peluqueria (negocio_id,nombre,telefono,email,notas) VALUES (?,?,?,?,?)");
    $st->execute([$negocioId, $d['nombre'], $d['telefono'], $d['email']??null, $d['notas']??null]);
    Response::success('Cliente creado', ['id' => $pdo->lastInsertId()]);
    exit;
}

if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int)($d['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }
    if (empty($d['nombre']))   { Response::error('Nombre requerido', 400); exit; }
    if (empty($d['telefono'])) { Response::error('Teléfono requerido', 400); exit; }
    $st = $pdo->prepare("UPDATE clientes_peluqueria SET nombre=?,telefono=?,email=?,notas=? WHERE id=? AND negocio_id=?");
    $st->execute([$d['nombre'], $d['telefono'], $d['email']??null, $d['notas']??null, $id, $negocioId]);
    Response::success('Cliente actualizado', []);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }
    $st = $pdo->prepare("DELETE FROM clientes_peluqueria WHERE id=? AND negocio_id=?");
    $st->execute([$id, $negocioId]);
    Response::success('Cliente eliminado', []);
    exit;
}

Response::error('Método no permitido', 405);
