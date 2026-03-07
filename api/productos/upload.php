<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['POST']);
Middleware::method('POST');

try {
    Middleware::auth();
    
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        Response::error('No se recibió ninguna foto válida', 400);
    }
    
    $file = $_FILES['foto'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Validar tipo de archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        Response::error('Tipo de archivo no permitido. Solo se permiten: JPG, PNG, WEBP', 400);
    }
    
    // Validar tamaño
    if ($file['size'] > $maxSize) {
        Response::error('El archivo es demasiado grande. Máximo 5MB', 400);
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_') . '_' . time() . '.' . $extension;
    
    // Crear directorio si no existe — ruta absoluta desde ROOT_PATH
    $uploadDir = ROOT_PATH . '/public/uploads/productos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $filename;

    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // URL relativa al proyecto, funciona en cualquier subdirectorio
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        // Detectar subfolder si corre en htdocs/DASHBASE
        $scriptPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH);
        $url = $scriptPath . '/public/uploads/productos/' . $filename;

        Response::success('Foto subida exitosamente', [
            'filename' => $filename,
            'url'      => $url,
        ]);
    } else {
        Response::error('Error al guardar la foto. Verificá permisos del directorio uploads/', 500);
    }
    
} catch (Exception $e) {
    Response::error('Error en el servidor: ' . $e->getMessage(), 500);
}
