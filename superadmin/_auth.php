<?php
// ============================================================
// Super Admin — Auth Helper
// Incluir al inicio de cada página protegida del panel
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

function sa_check_auth() {
    if (empty($_SESSION['sa_id'])) {
        header('Location: ' . sa_base() . '/login.php');
        exit;
    }
}

function sa_base(): string {
    // Ruta base del superadmin relativa al servidor web
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Subir hasta /superadmin si estamos en un subdirectorio
    if (str_contains($script, '/superadmin')) {
        return substr($script, 0, strpos($script, '/superadmin') + strlen('/superadmin'));
    }
    return $script;
}

function sa_db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    // Intentar cargar config del sistema
    $root = dirname(__DIR__);
    $env  = $root . '/.env';
    if (file_exists($env)) {
        foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k); $v = trim($v, " \t\n\r\"'");
            if (!array_key_exists($k, $_ENV)) { $_ENV[$k] = $v; putenv("$k=$v"); }
        }
    }
    $host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_NAME'] ?? 'dashbase_local';
    $user   = $_ENV['DB_USER'] ?? 'root';
    $pass   = $_ENV['DB_PASS'] ?? '';
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function sa_log(string $accion, string $detalle = '', ?int $negocio_id = null): void {
    try {
        $db = sa_db();
        $db->prepare("INSERT INTO logs_actividad (negocio_id, usuario_id, accion, detalle, ip, user_agent) VALUES (?,?,?,?,?,?)")
           ->execute([
               $negocio_id,
               $_SESSION['sa_id'] ?? null,
               $accion,
               $detalle,
               $_SERVER['REMOTE_ADDR'] ?? null,
               substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
           ]);
    } catch (Exception $e) {}
}

function sa_admin_nombre(): string {
    return $_SESSION['sa_nombre'] ?? 'Super Admin';
}

function sa_format_money(float $v): string {
    return '$' . number_format($v, 2, ',', '.');
}

function sa_dias_restantes(?string $fecha): ?int {
    if (!$fecha) return null;
    $diff = (new DateTime($fecha))->diff(new DateTime())->days;
    return (new DateTime($fecha)) >= (new DateTime()) ? $diff : -$diff;
}
