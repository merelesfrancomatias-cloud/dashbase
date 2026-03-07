<?php
// API pública para obtener perfil del negocio (sin autenticación)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// negocio_id es obligatorio para aislar datos por tenant
$negocioId = isset($_GET['negocio_id']) ? (int)$_GET['negocio_id'] : 0;
if ($negocioId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'negocio_id requerido']);
    exit;
}

try {
    $db   = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        SELECT nombre AS nombre_negocio, telefono, whatsapp, logo, imagen_portada
        FROM negocios
        WHERE id = :negocio_id AND activo = 1
        LIMIT 1
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($perfil) {
        echo json_encode(['success' => true, 'data' => $perfil]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No hay perfil configurado para este negocio']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar perfil']);
}
