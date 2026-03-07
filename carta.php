<?php
/**
 * carta.php — Entrada pública de la Carta Digital
 *
 * Acceso: carta.php?t=TOKEN_DEL_NEGOCIO
 * El token se genera por negocio y puede regenerarse desde el panel de perfil.
 * Sin token válido → página de error, no se expone ningún dato.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$token = trim($_GET['t'] ?? '');

if (strlen($token) < 32) {
    http_response_code(404);
    mostrarError('Enlace inválido', 'Este enlace no es válido. Solicitá el QR al establecimiento.');
    exit;
}

try {
    $db   = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        SELECT id, nombre, carta_activa
        FROM negocios
        WHERE carta_token = :token AND activo = 1
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $negocio = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    http_response_code(500);
    mostrarError('Error del servidor', 'Intente nuevamente en unos momentos.');
    exit;
}

if (!$negocio) {
    http_response_code(404);
    mostrarError('Carta no encontrada', 'Este enlace no corresponde a ningún negocio activo.');
    exit;
}

if (!$negocio['carta_activa']) {
    http_response_code(403);
    mostrarError(
        'Carta temporalmente desactivada',
        'El establecimiento desactivó temporalmente su carta digital. Consultá al personal.'
    );
    exit;
}

// Token válido → redirigir a la tienda con el negocio_id
// Usamos una sesión temporal para no exponer el ID en la URL final
session_start();
$_SESSION['carta_negocio_id']    = (int)$_SESSION['negocio_id'] ?? null; // no pisar sesión de usuario logueado
$_SESSION['carta_token_validado'] = $token;

// Redirigir con negocio_id en la URL (la carta es pública, el id no es secreto, el token sí)
$url = '/views/tienda/index.php?negocio_id=' . (int)$negocio['id'] . '&from_carta=1';
header('Location: ' . $url);
exit;

// ─── Función helper para mostrar página de error ───────────────────────────
function mostrarError(string $titulo, string $mensaje): void {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> — DASH</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #F8F9FC;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
        }
        .card {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 420px; width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,.08);
        }
        .icon-wrap {
            width: 72px; height: 72px;
            background: #FEF2F2;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
        }
        h1 { font-size: 20px; font-weight: 700; color: #1A202C; margin-bottom: 10px; }
        p  { font-size: 14px; color: #64748B; line-height: 1.6; }
        .logo-wrap {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: 32px; padding-top: 24px;
            border-top: 1px solid #E2E8F0;
        }
        .logo-badge {
            width: 32px; height: 32px; background: #0FD186;
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
        }
        .logo-badge img { height: 18px; filter: brightness(0) invert(1); }
        .logo-text { font-size: 13px; font-weight: 600; color: #94A3B8; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon-wrap">⚠️</div>
    <h1><?= htmlspecialchars($titulo) ?></h1>
    <p><?= htmlspecialchars($mensaje) ?></p>
    <div class="logo-wrap">
        <div class="logo-badge">
            <img src="/public/img/DASHLOGOSF.png" alt="DASH">
        </div>
        <span class="logo-text">Powered by DASH</span>
    </div>
</div>
</body>
</html>
<?php
}
