<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();

$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $socio_id = isset($_GET['socio_id']) ? (int)$_GET['socio_id'] : 0;

    if ($socio_id > 0) {
        // Historial del socio
        $stmt = $db->prepare("
            SELECT a.*, CONCAT(s.nombre,' ',s.apellido) AS socio_nombre
            FROM gym_asistencias a
            LEFT JOIN gym_socios s ON s.id = a.socio_id
            WHERE a.negocio_id=? AND a.socio_id=?
            ORDER BY a.fecha DESC, a.hora DESC
            LIMIT 50
        ");
        $stmt->execute([$negocio_id, $socio_id]);
        Response::success('ok', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Asistencias del día
    $stmt = $db->prepare("
        SELECT a.id, a.socio_id, a.fecha, a.hora,
               CONCAT(s.nombre,' ',s.apellido) AS socio_nombre,
               s.estado AS socio_estado,
               p.nombre AS plan_nombre
        FROM gym_asistencias a
        LEFT JOIN gym_socios s ON s.id = a.socio_id
        LEFT JOIN gym_planes p ON p.id = s.plan_id
        WHERE a.negocio_id=? AND a.fecha=?
        ORDER BY a.hora ASC
    ");
    $stmt->execute([$negocio_id, $fecha]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtCount = $db->prepare("SELECT COUNT(*) FROM gym_asistencias WHERE negocio_id=? AND fecha=?");
    $stmtCount->execute([$negocio_id, $fecha]);
    $total = $stmtCount->fetchColumn();

    Response::success('ok', ['asistencias' => $asistencias, 'total' => (int)$total, 'fecha' => $fecha]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $socio_id = (int)($data['socio_id'] ?? 0);
    $fecha    = $data['fecha'] ?? date('Y-m-d');
    $hora     = $data['hora'] ?? date('H:i:s');

    if (!$socio_id) Response::error('socio_id requerido', 400);

    // Verificar que el socio exista y esté activo
    $stmtV = $db->prepare("SELECT id, CONCAT(nombre,' ',apellido) AS nombre_completo, estado, fecha_vencimiento FROM gym_socios WHERE id=? AND negocio_id=?");
    $stmtV->execute([$socio_id, $negocio_id]);
    $socio = $stmtV->fetch(PDO::FETCH_ASSOC);
    if (!$socio) Response::error('Socio no encontrado', 404);

    // Verificar si ya registró hoy
    $stmtDup = $db->prepare("SELECT id FROM gym_asistencias WHERE negocio_id=? AND socio_id=? AND fecha=?");
    $stmtDup->execute([$negocio_id, $socio_id, $fecha]);
    if ($stmtDup->fetch()) Response::error('El socio ya registró asistencia hoy', 409);

    $stmt = $db->prepare("INSERT INTO gym_asistencias (negocio_id,socio_id,fecha,hora) VALUES (?,?,?,?)");
    $stmt->execute([$negocio_id, $socio_id, $fecha, $hora]);

    Response::success('Asistencia registrada', [
        'id'    => $db->lastInsertId(),
        'socio' => $socio['nombre_completo'],
        'estado'=> $socio['estado'],
        'vencimiento' => $socio['fecha_vencimiento']
    ]);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("DELETE FROM gym_asistencias WHERE id=? AND negocio_id=?")->execute([$id, $negocio_id]);
    Response::success('Asistencia eliminada');
}
