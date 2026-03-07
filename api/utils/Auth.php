<?php
class Auth {

    // ----------------------------------------------------------
    // Verificación de autenticación
    // ----------------------------------------------------------

    public static function check(): bool {
        if (!isset($_SESSION['user_id'])) {
            Response::error('No autorizado', 401);
        }
        return true;
    }

    public static function isAdmin(): bool {
        self::check();
        return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
    }

    public static function requireAdmin(): bool {
        if (!self::isAdmin()) {
            Response::error('Requiere permisos de administrador', 403);
        }
        return true;
    }

    // ----------------------------------------------------------
    // Getters de sesión
    // ----------------------------------------------------------

    public static function getUserId(): int {
        self::check();
        return (int)$_SESSION['user_id'];
    }

    public static function getNegocioId(): int {
        self::check();
        return (int)$_SESSION['negocio_id'];
    }

    public static function getNombre(): string {
        self::check();
        return $_SESSION['nombre'] ?? '';
    }

    public static function getRol(): string {
        self::check();
        return $_SESSION['rol'] ?? 'empleado';
    }

    // ----------------------------------------------------------
    // Login / Logout
    // ----------------------------------------------------------

    /**
     * Inicia la sesión del usuario y guarda los datos del plan.
     *
     * @param int    $userId
     * @param int    $negocioId
     * @param string $rol
     * @param string $nombre
     * @param string $planNombre   Nombre del plan activo (ej: 'free', 'pro')
     * @param string $estadoSub    Estado de la suscripción
     */
    public static function login(
        int    $userId,
        int    $negocioId,
        string $rol,
        string $nombre,
        string $planNombre  = 'free',
        string $estadoSub   = 'trial'
    ): void {
        // Regenerar ID de sesión al login para prevenir session fixation
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user_id']             = $userId;
        $_SESSION['negocio_id']          = $negocioId;
        $_SESSION['rol']                 = $rol;
        $_SESSION['nombre']              = $nombre;
        $_SESSION['plan']                = $planNombre;
        $_SESSION['estado_suscripcion']  = $estadoSub;
    }

    public static function logout(): void {
        session_unset();
        // Borrar la cookie de sesión en el navegador
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    // ----------------------------------------------------------
    // Helpers de plan (lectura rápida desde sesión)
    // ----------------------------------------------------------

    public static function getPlan(): string {
        self::check();
        return $_SESSION['plan'] ?? 'free';
    }

    public static function getEstadoSuscripcion(): string {
        self::check();
        return $_SESSION['estado_suscripcion'] ?? 'trial';
    }
}
