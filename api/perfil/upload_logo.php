<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Response.php';

// session_start() ya fue llamado en config.php

// Verificar autenticación y rol admin
Auth::check();
Auth::requireAdmin();
$negocioId = Auth::getNegocioId();

header('Content-Type: application/json');

try {
    if (!isset($_FILES['logo'])) {
        Response::error('No se ha enviado ninguna imagen');
    }
    
    $file = $_FILES['logo'];
    
    // Validar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        Response::error('Error al subir el archivo');
    }
    
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
    if (!in_array($file['type'], $allowedTypes)) {
        Response::error('Tipo de archivo no permitido. Use JPG, PNG, GIF o SVG');
    }
    
    // Validar tamaño (max 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        Response::error('El archivo no debe superar 2MB');
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Obtener logo actual del tenant desde negocios
    $stmt = $conn->prepare("SELECT logo FROM negocios WHERE id = :negocio_id LIMIT 1");
    $stmt->execute([':negocio_id' => $negocioId]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($perfil && $perfil['logo']) {
        $oldLogoPath = __DIR__ . '/../../public/uploads/logos/' . $perfil['logo'];
        if (file_exists($oldLogoPath)) {
            unlink($oldLogoPath);
        }
    }
    
    // Generar nombre único para el logo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . uniqid() . '_' . time() . '.' . $extension;
    
    // Crear directorio si no existe
    $uploadDir = __DIR__ . '/../../public/uploads/logos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadPath = $uploadDir . $filename;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        Response::error('Error al guardar el archivo');
    }
    
    // Actualizar logo en la tabla negocios
    $stmt = $conn->prepare("UPDATE negocios SET logo = :logo, fecha_actualizacion = NOW() WHERE id = :negocio_id");
    $stmt->execute([':logo' => $filename, ':negocio_id' => $negocioId]);
    
    Response::success('Logo subido correctamente', [
        'filename' => $filename,
        'path' => '/public/uploads/logos/' . $filename
    ]);
    
} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage());
}
