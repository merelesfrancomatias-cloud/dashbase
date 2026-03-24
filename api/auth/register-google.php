<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['POST']);
Middleware::method('POST');

function normalizeUsername(string $value): string
{
    $value = mb_strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9_]/', '_', $value) ?? '';
    $value = preg_replace('/_+/', '_', $value) ?? '';
    $value = trim($value, '_');
    return substr($value, 0, 50);
}

function buildUniqueUsername(PDO $db, string $base): string
{
    $base = normalizeUsername($base);
    if ($base === '') {
        $base = 'usuario';
    }

    $candidate = $base;
    $suffix = 1;

    $stmt = $db->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
    while (true) {
        $stmt->execute([$candidate]);
        if (!$stmt->fetch()) {
            return $candidate;
        }

        $suffix++;
        $prefix = substr($base, 0, max(1, 50 - strlen((string)$suffix) - 1));
        $candidate = $prefix . '_' . $suffix;
    }
}

$data = json_decode(file_get_contents('php://input'), true) ?: [];
$idToken = trim((string)($data['id_token'] ?? ''));
$accessToken = trim((string)($data['access_token'] ?? ''));

if ($idToken === '' && $accessToken === '') {
    Response::error('Token de Google requerido', 400);
}

if (empty(trim((string)($data['nombre_negocio'] ?? '')))) {
    Response::error("El campo 'nombre_negocio' es requerido", 400);
}

$rubroId = (int)($data['rubro_id'] ?? 0);
if ($rubroId <= 0) {
    Response::error("El campo 'rubro_id' es requerido", 400);
}

try {
    $google = $idToken !== ''
        ? GoogleAuth::verifyIdToken($idToken)
        : GoogleAuth::verifyAccessToken($accessToken);
    $db = (new Database())->getConnection();

    $db->beginTransaction();

    $stmt = $db->prepare('SELECT id FROM rubros WHERE id = ? AND activo = 1');
    $stmt->execute([$rubroId]);
    if (!$stmt->fetch()) {
        Response::error('Rubro no válido', 400);
    }

    $stmt = $db->prepare('SELECT id FROM usuarios WHERE google_sub = ? OR LOWER(email) = LOWER(?) LIMIT 1');
    $stmt->execute([$google['sub'], $google['email']]);
    if ($stmt->fetch()) {
        Response::error('Ya existe una cuenta con este email/Google. Iniciá sesión con Google.', 409);
    }

    $nombre = trim((string)($data['nombre'] ?? $google['given_name']));
    if ($nombre === '') {
        $nombre = trim((string)($google['name'] !== '' ? $google['name'] : 'Usuario'));
    }

    $apellido = trim((string)($data['apellido'] ?? $google['family_name']));
    if ($apellido === '') {
        $apellido = '-';
    }

    $requestedUsername = trim((string)($data['usuario'] ?? ''));
    if ($requestedUsername !== '' && !preg_match('/^[a-z0-9_]+$/', $requestedUsername)) {
        Response::error('El nombre de usuario solo puede contener letras minúsculas, números y guion bajo', 400);
    }

    if ($requestedUsername === '') {
        $emailPrefix = explode('@', $google['email'])[0] ?? 'usuario';
        $requestedUsername = normalizeUsername($emailPrefix);
    }

    $username = buildUniqueUsername($db, $requestedUsername);

    $stmt = $db->prepare("INSERT INTO negocios (nombre, rubro_id, rubro, email, activo, estado_suscripcion, trial_hasta)
                          VALUES (?, ?, (SELECT nombre FROM rubros WHERE id = ?), ?, 1, 'trial', DATE_ADD(NOW(), INTERVAL 14 DAY))");
    $stmt->execute([
        trim((string)$data['nombre_negocio']),
        $rubroId,
        $rubroId,
        $google['email'],
    ]);
    $negocioId = (int)$db->lastInsertId();

    $stmt = $db->prepare("INSERT INTO usuarios
                          (negocio_id, nombre, apellido, usuario, email, password, auth_provider, google_sub, rol, activo, avatar_url)
                          VALUES (?, ?, ?, ?, ?, NULL, 'google', ?, 'admin', 1, ?)");
    $stmt->execute([
        $negocioId,
        $nombre,
        $apellido,
        $username,
        $google['email'],
        $google['sub'],
        $google['picture'],
    ]);
    $adminId = (int)$db->lastInsertId();

    require_once dirname(__DIR__) . '/../app/Services/NegocioProvisioner.php';
    $provisioner = new App\Services\NegocioProvisioner($db);
    $provisioner->provision($negocioId, $adminId, $rubroId);

    $db->commit();

    Auth::login(
        $adminId,
        $negocioId,
        'admin',
        $nombre,
        'free',
        'trial'
    );

    \App\AuditLog::log($db, $negocioId, $adminId, \App\AuditLog::LOGIN, 'usuarios', $adminId);

    Response::success('Registro con Google exitoso', [
        'negocio_id' => $negocioId,
        'usuario_id' => $adminId,
        'trial_hasta' => date('Y-m-d', strtotime('+14 days')),
        'user' => [
            'user_id' => $adminId,
            'nombre' => trim($nombre . ' ' . $apellido),
            'usuario' => $username,
            'rol' => 'admin',
            'negocio' => trim((string)$data['nombre_negocio']),
            'auth_provider' => 'google',
            'plan' => [
                'nombre' => 'free',
                'display' => 'Plan Gratuito',
                'estado' => 'trial',
                'vencimiento' => null,
                'trial_hasta' => date('Y-m-d', strtotime('+14 days')),
            ],
        ],
    ], 201);
} catch (RuntimeException $e) {
    Response::error($e->getMessage(), 401);
} catch (Exception $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    Response::error('Error al registrar con Google: ' . $e->getMessage(), 500);
}
