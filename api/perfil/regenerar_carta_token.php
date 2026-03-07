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

try {
    $db   = new Database();
    $conn = $db->getConnection();

    // Generar nuevo token SHA-256 único
    $nuevoToken = hash('sha256', $negocioId . uniqid('', true) . microtime(true) . random_bytes(16));

    $stmt = $conn->prepare("UPDATE negocios SET carta_token = :token, fecha_actualizacion = NOW() WHERE id = :id");
    $stmt->execute([':token' => $nuevoToken, ':id' => $negocioId]);

    Response::success('Token regenerado correctamente', ['carta_token' => $nuevoToken]);

} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage(), 500);
}
