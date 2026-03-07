<?php
// ============================================================
// Punto de entrada de configuración — carga .env y define
// todas las constantes globales del sistema.
// ============================================================

define('ROOT_PATH', dirname(__DIR__));

// Cargar autoloader de Composer (incluye phpdotenv)
$autoloader = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;

    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();

    // Validar variables obligatorias (DB_PASS puede estar vacío en entorno local)
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
    $dotenv->required('DB_PASS'); // permite vacío para MySQL local sin password
    $dotenv->required('APP_ENV')->allowedValues(['development', 'staging', 'production']);
} else {
    // Fallback: si aún no se instaló Composer, leer el .env manualmente
    // Esto es temporal hasta correr "composer install"
    $envFile = ROOT_PATH . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\"'");
            if (!empty($key) && !array_key_exists($key, $_ENV)) {
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// ============================================================
// Constantes de aplicación (desde .env)
// ============================================================
define('APP_NAME',    $_ENV['APP_NAME']    ?? 'DASH CRM');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');
define('APP_ENV',     $_ENV['APP_ENV']     ?? 'production');
define('APP_DEBUG',   filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('BASE_URL',    $_ENV['APP_URL']     ?? '');

// ============================================================
// Manejo de errores según entorno
// ============================================================
if (APP_DEBUG && APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/php_errors.log');
}

// ============================================================
// Zona horaria
// ============================================================
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Argentina/Buenos_Aires');

// ============================================================
// Configuración de sesiones
// ============================================================
$sessionSecure = filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN);
ini_set('session.cookie_httponly', filter_var($_ENV['SESSION_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $sessionSecure ? 1 : 0);
ini_set('session.gc_maxlifetime', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));

// Iniciar sesión una sola vez aquí — ningún otro archivo debe llamar session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
