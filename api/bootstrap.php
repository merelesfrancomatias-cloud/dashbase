<?php
/**
 * bootstrap.php — Punto de entrada común para todos los endpoints de la API.
 *
 * Incluir SIEMPRE como primera línea en cada endpoint:
 *   require_once __DIR__ . '/../bootstrap.php';   (desde api/modulo/)
 *   require_once __DIR__ . '/bootstrap.php';       (desde api/)
 *
 * Se encarga de:
 *   1. Cargar config (variables de entorno, sesión, zona horaria, errores)
 *   2. Cargar la conexión a BD
 *   3. Cargar las clases utilitarias (Response, Auth, PlanGuard)
 *   4. Setear headers comunes de la API
 */

// Carga config.php que a su vez carga .env y arranca la sesión
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// Autoloader de Composer (carga App\Models, App\Controllers, etc.)
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Utilidades
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/utils/Auth.php';
require_once __DIR__ . '/utils/PlanGuard.php';
require_once __DIR__ . '/utils/Middleware.php';
require_once __DIR__ . '/utils/GoogleAuth.php';

// Header JSON por defecto para todas las respuestas de la API
header('Content-Type: application/json; charset=utf-8');
