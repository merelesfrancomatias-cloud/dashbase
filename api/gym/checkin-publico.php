<?php
/**
 * API pública — Check-in QR de socios del gimnasio.
 * No requiere autenticación.
 * POST /api/gym/checkin-publico.php  { token: "..." }
 * GET  /api/gym/checkin-publico.php?token=...  (también acepta GET para facilitar testing)
 */
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$pdo = (new Database())->getConnection();

$token = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d     = json_decode(file_get_contents('php://input'), true) ?? [];
    $token = trim($d['token'] ?? '');
} else {
    $token = trim($_GET['token'] ?? '');
}

if (!$token) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token requerido']);
    exit;
}

// Buscar socio por token
$stmt = $pdo->prepare("
    SELECT s.id, s.nombre, s.apellido, s.estado, s.fecha_vencimiento, s.negocio_id,
           p.nombre AS plan_nombre,
           n.nombre AS gimnasio_nombre
    FROM gym_socios s
    LEFT JOIN gym_planes p  ON p.id = s.plan_id
    LEFT JOIN negocios  n  ON n.id = s.negocio_id
    WHERE s.qr_token = ?
    LIMIT 1
");
$stmt->execute([$token]);
$socio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$socio) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'QR no válido']);
    exit;
}

$hoy  = date('Y-m-d');
$hora = date('H:i:s');

// Verificar membresía
if ($socio['estado'] === 'vencido' || ($socio['fecha_vencimiento'] && $socio['fecha_vencimiento'] < $hoy)) {
    echo json_encode([
        'success'  => false,
        'code'     => 'vencido',
        'nombre'   => $socio['nombre'] . ' ' . $socio['apellido'],
        'plan'     => $socio['plan_nombre'],
        'vencimiento' => $socio['fecha_vencimiento'],
        'gimnasio' => $socio['gimnasio_nombre'],
        'message'  => 'Membresía vencida',
    ]);
    exit;
}

if (in_array($socio['estado'], ['suspendido', 'inactivo'])) {
    echo json_encode([
        'success'  => false,
        'code'     => $socio['estado'],
        'nombre'   => $socio['nombre'] . ' ' . $socio['apellido'],
        'gimnasio' => $socio['gimnasio_nombre'],
        'message'  => 'Membresía ' . $socio['estado'],
    ]);
    exit;
}

// Verificar si ya registró hoy
$stmtDup = $pdo->prepare("SELECT id, hora FROM gym_asistencias WHERE negocio_id=? AND socio_id=? AND fecha=?");
$stmtDup->execute([$socio['negocio_id'], $socio['id'], $hoy]);
$yaRegistro = $stmtDup->fetch(PDO::FETCH_ASSOC);

if ($yaRegistro) {
    echo json_encode([
        'success'  => true,
        'code'     => 'ya_registrado',
        'nombre'   => $socio['nombre'] . ' ' . $socio['apellido'],
        'plan'     => $socio['plan_nombre'],
        'vencimiento' => $socio['fecha_vencimiento'],
        'gimnasio' => $socio['gimnasio_nombre'],
        'hora'     => $yaRegistro['hora'],
        'message'  => 'Ya registrado hoy a las ' . substr($yaRegistro['hora'], 0, 5),
    ]);
    exit;
}

// Registrar asistencia
$pdo->prepare("INSERT INTO gym_asistencias (negocio_id, socio_id, fecha, hora) VALUES (?,?,?,?)")
    ->execute([$socio['negocio_id'], $socio['id'], $hoy, $hora]);

echo json_encode([
    'success'    => true,
    'code'       => 'ok',
    'nombre'     => $socio['nombre'] . ' ' . $socio['apellido'],
    'plan'       => $socio['plan_nombre'],
    'vencimiento'=> $socio['fecha_vencimiento'],
    'gimnasio'   => $socio['gimnasio_nombre'],
    'hora'       => $hora,
    'message'    => '¡Bienvenido!',
]);
