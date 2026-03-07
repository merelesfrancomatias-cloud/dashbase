<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['POST']);
Middleware::method('POST');

// Audit log opcional — no debe impedir el logout si falla
try {
    $negocioId = $_SESSION['negocio_id'] ?? null;
    $usuarioId = $_SESSION['user_id']    ?? null;

    if ($negocioId && $usuarioId) {
        $db = (new Database())->getConnection();
        \App\AuditLog::log($db, $negocioId, $usuarioId, \App\AuditLog::LOGOUT, 'usuarios', $usuarioId);
    }
} catch (Exception $e) {
    // Ignorar error del audit, el logout debe continuar igual
}

// Destruir sesión siempre
Auth::logout();
Response::success('Sesión cerrada correctamente');
