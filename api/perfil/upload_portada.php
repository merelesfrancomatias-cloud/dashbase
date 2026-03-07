<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Response.php';

Auth::check();
Auth::requireAdmin();
$negocioId = Auth::getNegocioId();

header('Content-Type: application/json');

try {
    if (!isset($_FILES['portada'])) {
        Response::error('No se ha enviado ninguna imagen');
    }

    $file = $_FILES['portada'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        Response::error('Error al subir el archivo');
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        Response::error('Tipo de archivo no permitido. Use JPG, PNG o WebP');
    }

    // Max 5MB para portada (imagen más grande)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        Response::error('El archivo no debe superar 5MB');
    }

    $db   = new Database();
    $conn = $db->getConnection();

    // Eliminar portada anterior si existe
    $stmt = $conn->prepare("SELECT imagen_portada FROM negocios WHERE id = :negocio_id LIMIT 1");
    $stmt->execute([':negocio_id' => $negocioId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['imagen_portada']) {
        $oldPath = __DIR__ . '/../../public/uploads/portadas/' . $row['imagen_portada'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    // Generar nombre único
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename  = 'portada_' . $negocioId . '_' . uniqid() . '.' . $extension;

    // Crear directorio si no existe
    $uploadDir = __DIR__ . '/../../public/uploads/portadas/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        Response::error('Error al guardar el archivo');
    }

    // Guardar en DB
    $stmt = $conn->prepare("UPDATE negocios SET imagen_portada = :img, fecha_actualizacion = NOW() WHERE id = :negocio_id");
    $stmt->execute([':img' => $filename, ':negocio_id' => $negocioId]);

    Response::success('Imagen de portada subida correctamente', [
        'filename' => $filename,
        'url'      => '/public/uploads/portadas/' . $filename
    ]);

} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage(), 500);
}
