<?php
/**
 * PlanGuard — Verifica límites y features del plan del tenant.
 *
 * Uso básico:
 *   PlanGuard::requireFeature('tiene_reportes');
 *   PlanGuard::checkLimit('productos', $negocioId, $db);
 */
class PlanGuard {

    // Cache del plan en sesión para no consultar la BD en cada request
    private static function getPlan(int $negocioId, PDO $db): ?array {
        // Si ya está en sesión y es del mismo negocio, reusar
        if (
            isset($_SESSION['_plan'], $_SESSION['_plan_negocio_id']) &&
            $_SESSION['_plan_negocio_id'] === $negocioId
        ) {
            return $_SESSION['_plan'];
        }

        $stmt = $db->prepare("
            SELECT p.*, n.estado_suscripcion, n.fecha_vencimiento, n.trial_hasta
            FROM negocios n
            INNER JOIN planes p ON p.id = n.plan_id
            WHERE n.id = :negocio_id
            LIMIT 1
        ");
        $stmt->execute([':negocio_id' => $negocioId]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        // Guardar en sesión para reusar durante este request
        $_SESSION['_plan']           = $plan ?: null;
        $_SESSION['_plan_negocio_id'] = $negocioId;

        return $plan ?: null;
    }

    /**
     * Limpia la cache del plan (llamar tras cambiar de plan).
     */
    public static function clearCache(): void {
        unset($_SESSION['_plan'], $_SESSION['_plan_negocio_id']);
    }

    /**
     * Verifica si la suscripción del negocio está activa.
     * Si no lo está, responde con error 402 y detiene la ejecución.
     */
    public static function requireActive(int $negocioId, PDO $db): void {
        $plan = self::getPlan($negocioId, $db);

        if (!$plan) {
            Response::error('Plan no configurado para este negocio.', 402);
        }

        $estado = $plan['estado_suscripcion'];

        // Trial válido
        if ($estado === 'trial') {
            if ($plan['trial_hasta'] && $plan['trial_hasta'] < date('Y-m-d')) {
                Response::error(
                    'Tu período de prueba ha vencido. Por favor suscribite a un plan.',
                    402
                );
            }
            return;
        }

        // Suscripción activa y no vencida
        if ($estado === 'activa') {
            if ($plan['fecha_vencimiento'] && $plan['fecha_vencimiento'] < date('Y-m-d')) {
                Response::error(
                    'Tu suscripción ha vencido. Renovar en el panel de configuración.',
                    402
                );
            }
            return;
        }

        if ($estado === 'vencida') {
            Response::error('Tu suscripción ha vencido. Renovar en el panel de configuración.', 402);
        }

        if ($estado === 'cancelada') {
            Response::error('Tu suscripción fue cancelada.', 402);
        }
    }

    /**
     * Verifica que el plan tenga habilitado un feature específico.
     *
     * Ejemplo: PlanGuard::requireFeature('tiene_reportes', $negocioId, $db);
     *
     * Features disponibles:
     *   tiene_reportes, tiene_empleados, tiene_clientes,
     *   tiene_api_publica, tiene_tienda_online
     */
    public static function requireFeature(string $feature, int $negocioId, PDO $db): void {
        self::requireActive($negocioId, $db);

        $plan = self::getPlan($negocioId, $db);

        if (!isset($plan[$feature]) || !$plan[$feature]) {
            Response::error(
                "Esta funcionalidad no está disponible en tu plan actual ({$plan['nombre_display']}). " .
                "Actualizá tu plan para acceder.",
                403
            );
        }
    }

    /**
     * Verifica que el negocio no haya alcanzado el límite de un recurso.
     *
     * @param string $recurso   'usuarios' | 'productos' | 'ventas_mes'
     * @param int    $negocioId
     * @param PDO    $db
     */
    public static function checkLimit(string $recurso, int $negocioId, PDO $db): void {
        self::requireActive($negocioId, $db);

        $plan = self::getPlan($negocioId, $db);

        // NULL = ilimitado (plan enterprise)
        $limite = $plan["max_{$recurso}"] ?? null;
        if ($limite === null) {
            return;
        }

        $actual = self::contarRecurso($recurso, $negocioId, $db);

        if ($actual >= (int)$limite) {
            Response::error(
                "Alcanzaste el límite de {$limite} {$recurso} de tu plan ({$plan['nombre_display']}). " .
                "Actualizá tu plan para continuar.",
                403
            );
        }
    }

    /**
     * Retorna la cantidad actual de un recurso para el negocio.
     */
    private static function contarRecurso(string $recurso, int $negocioId, PDO $db): int {
        switch ($recurso) {
            case 'usuarios':
                $stmt = $db->prepare(
                    "SELECT COUNT(*) FROM usuarios WHERE negocio_id = :nid AND activo = 1"
                );
                break;

            case 'productos':
                $stmt = $db->prepare(
                    "SELECT COUNT(*) FROM productos WHERE negocio_id = :nid AND activo = 1"
                );
                break;

            case 'ventas_mes':
                $stmt = $db->prepare(
                    "SELECT COUNT(*) FROM ventas
                     WHERE negocio_id = :nid
                       AND MONTH(fecha_venta) = MONTH(CURDATE())
                       AND YEAR(fecha_venta)  = YEAR(CURDATE())
                       AND estado != 'cancelada'"
                );
                break;

            default:
                return 0;
        }

        $stmt->execute([':nid' => $negocioId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Devuelve el resumen del plan activo del negocio
     * (para mostrar en el frontend).
     */
    public static function getPlanInfo(int $negocioId, PDO $db): array {
        $plan = self::getPlan($negocioId, $db);

        if (!$plan) {
            return ['plan' => null, 'activo' => false];
        }

        return [
            'plan'               => $plan['nombre'],
            'plan_display'       => $plan['nombre_display'],
            'estado'             => $plan['estado_suscripcion'],
            'vencimiento'        => $plan['fecha_vencimiento'],
            'trial_hasta'        => $plan['trial_hasta'],
            'limites'            => [
                'max_usuarios'    => $plan['max_usuarios'],
                'max_productos'   => $plan['max_productos'],
                'max_ventas_mes'  => $plan['max_ventas_mes'],
            ],
            'features'           => [
                'reportes'        => (bool)$plan['tiene_reportes'],
                'empleados'       => (bool)$plan['tiene_empleados'],
                'clientes'        => (bool)$plan['tiene_clientes'],
                'api_publica'     => (bool)$plan['tiene_api_publica'],
                'tienda_online'   => (bool)$plan['tiene_tienda_online'],
            ],
        ];
    }
}
