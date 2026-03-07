<?php
/**
 * POST /api/auth/register.php
 * Crea un negocio nuevo + usuario admin + provisioning automático.
 *
 * Body JSON:
 *   nombre_negocio   string  requerido
 *   rubro_id         int     requerido
 *   nombre           string  requerido  (del admin)
 *   apellido         string  requerido
 *   email            string  requerido
 *   usuario          string  requerido
 *   password         string  requerido  (min 6 chars)
 */
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['POST']);
Middleware::method('POST');

$data = json_decode(file_get_contents('php://input'), true) ?: [];

// ── Validaciones ──────────────────────────────────────────────────────────
$required = ['nombre_negocio', 'rubro_id', 'nombre', 'apellido', 'email', 'usuario', 'password'];
foreach ($required as $field) {
    if (empty(trim($data[$field] ?? ''))) {
        Response::error("El campo '$field' es requerido", 400);
        exit;
    }
}

if (strlen($data['password']) < 6) {
    Response::error('La contraseña debe tener al menos 6 caracteres', 400);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    Response::error('El email no es válido', 400);
    exit;
}

$db = (new Database())->getConnection();

try {
    $db->beginTransaction();

    // Verificar que el rubro exista
    $stmt = $db->prepare("SELECT id FROM rubros WHERE id = ? AND activo = 1");
    $stmt->execute([(int)$data['rubro_id']]);
    if (!$stmt->fetch()) {
        Response::error('Rubro no válido', 400);
        exit;
    }

    // Verificar que el usuario no esté ya en uso (global)
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmt->execute([trim($data['usuario'])]);
    if ($stmt->fetch()) {
        Response::error('El nombre de usuario ya está en uso', 409);
        exit;
    }

    // Verificar que el email no esté en uso
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([trim($data['email'])]);
    if ($stmt->fetch()) {
        Response::error('El email ya está registrado', 409);
        exit;
    }

    // ── 1. Crear negocio ──────────────────────────────────────────────────
    $stmt = $db->prepare("
        INSERT INTO negocios (nombre, rubro_id, rubro, email, activo, estado_suscripcion, trial_hasta)
        VALUES (?, ?, (SELECT nombre FROM rubros WHERE id = ?), ?, 1, 'trial', DATE_ADD(NOW(), INTERVAL 14 DAY))
    ");
    $stmt->execute([
        trim($data['nombre_negocio']),
        (int)$data['rubro_id'],
        (int)$data['rubro_id'],
        trim($data['email']),
    ]);
    $negocioId = (int)$db->lastInsertId();

    // ── 2. Crear usuario admin ────────────────────────────────────────────
    $stmt = $db->prepare("
        INSERT INTO usuarios (negocio_id, nombre, apellido, usuario, email, password, rol, activo)
        VALUES (?, ?, ?, ?, ?, ?, 'admin', 1)
    ");
    $stmt->execute([
        $negocioId,
        trim($data['nombre']),
        trim($data['apellido']),
        trim($data['usuario']),
        trim($data['email']),
        password_hash($data['password'], PASSWORD_BCRYPT),
    ]);
    $adminId = (int)$db->lastInsertId();

    // ── 3. Provisioning automático ────────────────────────────────────────
    require_once dirname(__DIR__) . '/../app/Services/NegocioProvisioner.php';
    $provisioner = new App\Services\NegocioProvisioner($db);
    $provisioner->provision($negocioId, $adminId, (int)$data['rubro_id']);

    $db->commit();

    Response::success('Negocio registrado correctamente', [
        'negocio_id' => $negocioId,
        'usuario_id' => $adminId,
        'trial_hasta' => date('Y-m-d', strtotime('+14 days')),
    ], 201);

} catch (Exception $e) {
    $db->rollBack();
    Response::error('Error al registrar: ' . $e->getMessage(), 500);
}
