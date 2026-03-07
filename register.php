<?php
/**
 * Punto de entrada para registro de nuevos negocios.
 * Ruta pública: /DASHBASE/register.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya hay sesión activa, redirigir al dashboard
if (!empty($_SESSION['usuario_id']) || !empty($_SESSION['user_id'])) {
    $base = rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '', str_replace(chr(92), chr(47), __DIR__)), '/');
    header("Location: {$base}/views/dashboard/index.php");
    exit;
}

// Servir la vista
require_once __DIR__ . '/views/auth/register.php';
