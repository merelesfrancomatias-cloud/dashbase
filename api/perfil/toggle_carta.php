<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Response.php';

Auth::check();
Auth::requireAdmin();
$negocioId = Auth::getNegocioId();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

$body  = json_decode(file_get_contents('php://input'), true);
$activa = isset($body['activa']) ? (int)(bool)$body['activa'] : null;

if ($activa === null) {
    Response::error('Parámetro activa requerido', 400);
}

try {
    $db   = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("UPDATE negocios SET carta_activa = :activa, fecha_actualizacion = NOW() WHERE id = :id");
    $stmt->execute([':activa' => $activa, ':id' => $negocioId]);

    Response::success($activa ? 'Carta activada' : 'Carta desactivada', ['carta_activa' => $activa]);

} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage(), 500);
}
