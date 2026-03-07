<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['POST']);
Middleware::method('POST');

$data = json_decode(file_get_contents("php://input"));

if (empty($data->usuario) || empty($data->password)) {
    Response::error('Usuario y contraseña son requeridos', 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT u.*,
                     n.nombre            AS nombre_negocio,
                     n.activo            AS negocio_activo,
                     n.bloqueado         AS negocio_bloqueado,
                     n.bloqueado_motivo  AS negocio_motivo,
                     n.estado_suscripcion,
                     n.fecha_vencimiento,
                     n.trial_hasta,
                     p.nombre            AS plan_nombre,
                     p.nombre_display    AS plan_display
              FROM usuarios u
              INNER JOIN negocios n ON u.negocio_id = n.id
              LEFT  JOIN planes   p ON p.id = n.plan_id
              WHERE u.usuario = :usuario AND u.activo = 1";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario' => $data->usuario]);

    if ($stmt->rowCount() === 0) {
        Response::error('Usuario o contraseña incorrectos', 401);
    }

    $user = $stmt->fetch();

    // Verificar contraseña con password_verify para bcrypt
    if (!password_verify($data->password, $user['password'])) {
        Response::error('Usuario o contraseña incorrectos', 401);
    }

    // Verificar que el negocio esté activo
    if (!$user['negocio_activo']) {
        Response::error('Esta cuenta está deshabilitada. Contactá con soporte.', 403);
    }

    // Verificar que el negocio no esté bloqueado por el administrador
    if ($user['negocio_bloqueado']) {
        $motivo = $user['negocio_motivo'] ? ' Motivo: ' . $user['negocio_motivo'] : '';
        Response::error('Tu cuenta está suspendida.' . $motivo . ' Contactá con soporte.', 403);
    }

    // Actualizar último acceso
    $updateQuery = "UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([':id' => $user['id']]);

    // Iniciar sesión — incluye datos del plan
    Auth::login(
        (int)$user['id'],
        (int)$user['negocio_id'],
        $user['rol'],
        $user['nombre'],
        $user['plan_nombre']    ?? 'free',
        $user['estado_suscripcion'] ?? 'trial'
    );

    \App\AuditLog::log($db, (int)$user['negocio_id'], (int)$user['id'], \App\AuditLog::LOGIN, 'usuarios', (int)$user['id']);

    Response::success('Inicio de sesión exitoso', [
        'user_id'  => $user['id'],
        'nombre'   => $user['nombre'] . ' ' . $user['apellido'],
        'usuario'  => $user['usuario'],
        'rol'      => $user['rol'],
        'negocio'  => $user['nombre_negocio'],
        'plan'     => [
            'nombre'    => $user['plan_nombre']    ?? 'free',
            'display'   => $user['plan_display']   ?? 'Plan Gratuito',
            'estado'    => $user['estado_suscripcion'] ?? 'trial',
            'vencimiento' => $user['fecha_vencimiento'],
            'trial_hasta' => $user['trial_hasta'],
        ],
    ]);

} catch (Exception $e) {
    Response::error('Error en el servidor: ' . $e->getMessage(), 500);
}
