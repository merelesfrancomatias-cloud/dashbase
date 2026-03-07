<?php
// API pública para categorías de la tienda (sin autenticación)
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
        SELECT id, nombre
        FROM categorias
        WHERE negocio_id = :negocio_id
        ORDER BY nombre ASC
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $categorias]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar categorías']);
}
