<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['POST']);
Middleware::method('POST');

$data = json_decode(file_get_contents('php://input'), true) ?: [];
$idToken = trim((string)($data['id_token'] ?? ''));
$accessToken = trim((string)($data['access_token'] ?? ''));

if ($idToken === '' && $accessToken === '') {
    Response::error('Token de Google requerido', 400);
}

try {
    $google = $idToken !== ''
        ? GoogleAuth::verifyIdToken($idToken)
        : GoogleAuth::verifyAccessToken($accessToken);

    $db = (new Database())->getConnection();

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
              WHERE u.google_sub = :google_sub AND u.activo = 1
              LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->execute([':google_sub' => $google['sub']]);
    $user = $stmt->fetch();

    if (!$user) {
        $queryByEmail = "SELECT u.*,
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
                         WHERE LOWER(u.email) = LOWER(:email) AND u.activo = 1
                         LIMIT 1";
        $stmt = $db->prepare($queryByEmail);
        $stmt->execute([':email' => $google['email']]);
        $user = $stmt->fetch();

        if ($user && !empty($user['google_sub']) && $user['google_sub'] !== $google['sub']) {
            Response::error('Esta cuenta ya está vinculada a otro Google.', 409);
        }

        if ($user && empty($user['google_sub'])) {
            $updateLink = $db->prepare("UPDATE usuarios
                                        SET google_sub = :google_sub,
                                            auth_provider = 'google',
                                            avatar_url = CASE
                                                WHEN (avatar_url IS NULL OR avatar_url = '') THEN :avatar_url
                                                ELSE avatar_url
                                            END,
                                            email = :email
                                        WHERE id = :id");
            $updateLink->execute([
                ':google_sub' => $google['sub'],
                ':avatar_url' => $google['picture'],
                ':email' => $google['email'],
                ':id' => (int)$user['id'],
            ]);

            $user['google_sub'] = $google['sub'];
            $user['auth_provider'] = 'google';
            if (empty($user['avatar_url'])) {
                $user['avatar_url'] = $google['picture'];
            }
            $user['email'] = $google['email'];
        }
    }

    if (!$user) {
        Response::error('No existe una cuenta para este Google. Registrate primero.', 404);
    }

    if (!$user['negocio_activo']) {
        Response::error('Esta cuenta está deshabilitada. Contactá con soporte.', 403);
    }

    if ($user['negocio_bloqueado']) {
        $motivo = $user['negocio_motivo'] ? ' Motivo: ' . $user['negocio_motivo'] : '';
        Response::error('Tu cuenta está suspendida.' . $motivo . ' Contactá con soporte.', 403);
    }

    $updateAccess = $db->prepare("UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id");
    $updateAccess->execute([':id' => (int)$user['id']]);

    Auth::login(
        (int)$user['id'],
        (int)$user['negocio_id'],
        (string)$user['rol'],
        (string)$user['nombre'],
        $user['plan_nombre'] ?? 'free',
        $user['estado_suscripcion'] ?? 'trial'
    );

    \App\AuditLog::log($db, (int)$user['negocio_id'], (int)$user['id'], \App\AuditLog::LOGIN, 'usuarios', (int)$user['id']);

    Response::success('Inicio de sesión con Google exitoso', [
        'user_id'  => $user['id'],
        'nombre'   => trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '')),
        'usuario'  => $user['usuario'],
        'rol'      => $user['rol'],
        'negocio'  => $user['nombre_negocio'],
        'auth_provider' => 'google',
        'plan'     => [
            'nombre'      => $user['plan_nombre'] ?? 'free',
            'display'     => $user['plan_display'] ?? 'Plan Gratuito',
            'estado'      => $user['estado_suscripcion'] ?? 'trial',
            'vencimiento' => $user['fecha_vencimiento'],
            'trial_hasta' => $user['trial_hasta'],
        ],
    ]);
} catch (RuntimeException $e) {
    Response::error($e->getMessage(), 401);
} catch (Exception $e) {
    Response::error('Error en el servidor: ' . $e->getMessage(), 500);
}
