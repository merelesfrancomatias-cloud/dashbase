<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Response.php';

// session_start() ya fue llamado en config.php

// Verificar autenticación
Auth::check();
Auth::requireAdmin();
$negocioId = Auth::getNegocioId();

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Obtener logo actual del tenant desde negocios
    $stmt = $conn->prepare("SELECT logo FROM negocios WHERE id = :negocio_id LIMIT 1");
    $stmt->execute([':negocio_id' => $negocioId]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$perfil || !$perfil['logo']) {
        Response::error('No hay logo para eliminar');
    }
    
    // Eliminar archivo físico
    $logoPath = __DIR__ . '/../../public/uploads/logos/' . $perfil['logo'];
    if (file_exists($logoPath)) {
        unlink($logoPath);
    }
    
    // Actualizar tabla negocios del tenant
    $stmt = $conn->prepare("UPDATE negocios SET logo = NULL, fecha_actualizacion = NOW() WHERE id = :negocio_id");
    $stmt->execute([':negocio_id' => $negocioId]);
    
    Response::success(null, 'Logo eliminado correctamente');
    
} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage());
}
