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

    $stmt = $conn->prepare("SELECT imagen_portada FROM negocios WHERE id = :negocio_id LIMIT 1");
    $stmt->execute([':negocio_id' => $negocioId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['imagen_portada']) {
        $path = __DIR__ . '/../../public/uploads/portadas/' . $row['imagen_portada'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $stmt = $conn->prepare("UPDATE negocios SET imagen_portada = NULL, fecha_actualizacion = NOW() WHERE id = :negocio_id");
    $stmt->execute([':negocio_id' => $negocioId]);

    Response::success('Imagen de portada eliminada correctamente');

} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage(), 500);
}
