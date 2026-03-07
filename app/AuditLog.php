<?php
namespace App;

use PDO;

/**
 * AuditLog — Registra acciones en la tabla audit_logs.
 *
 * Uso desde un Controller o Model:
 *   AuditLog::log($db, $negocioId, $usuarioId, 'create', 'productos', $id, null, $datos);
 *   AuditLog::log($db, $negocioId, $usuarioId, 'update', 'productos', $id, $viejo, $nuevo);
 *   AuditLog::log($db, $negocioId, $usuarioId, 'delete', 'productos', $id, $datos);
 *   AuditLog::log($db, $negocioId, $usuarioId, 'login',  'usuarios',  $userId);
 */
class AuditLog
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const LOGIN  = 'login';
    public const LOGOUT = 'logout';

    /**
     * Registra una acción de auditoría.
     *
     * @param PDO        $db
     * @param int        $negocioId
     * @param int|null   $usuarioId
     * @param string     $accion      AuditLog::CREATE|UPDATE|DELETE|LOGIN|LOGOUT
     * @param string     $tabla       Nombre de la tabla afectada
     * @param int|null   $registroId  ID del registro (null para login/logout)
     * @param array|null $datosAntes  Snapshot anterior (UPDATE/DELETE)
     * @param array|null $datosNuevos Snapshot nuevo   (CREATE/UPDATE)
     */
    public static function log(
        PDO    $db,
        int    $negocioId,
        ?int   $usuarioId,
        string $accion,
        string $tabla,
        ?int   $registroId  = null,
        ?array $datosAntes  = null,
        ?array $datosNuevos = null
    ): void {
        try {
            // Eliminar campos sensibles antes de guardar
            $datosAntes  = self::sanitize($datosAntes);
            $datosNuevos = self::sanitize($datosNuevos);

            $stmt = $db->prepare("
                INSERT INTO audit_logs
                    (negocio_id, usuario_id, accion, tabla, registro_id,
                     datos_antes, datos_nuevos, ip, user_agent)
                VALUES
                    (:negocio_id, :usuario_id, :accion, :tabla, :registro_id,
                     :datos_antes, :datos_nuevos, :ip, :user_agent)
            ");

            $stmt->execute([
                ':negocio_id'   => $negocioId,
                ':usuario_id'   => $usuarioId,
                ':accion'       => $accion,
                ':tabla'        => $tabla,
                ':registro_id'  => $registroId,
                ':datos_antes'  => $datosAntes  ? json_encode($datosAntes,  JSON_UNESCAPED_UNICODE) : null,
                ':datos_nuevos' => $datosNuevos ? json_encode($datosNuevos, JSON_UNESCAPED_UNICODE) : null,
                ':ip'           => self::getIp(),
                ':user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // El log nunca debe romper la operación principal
            // En producción se puede loggear en archivo
            error_log("[AuditLog] Error al registrar: " . $e->getMessage());
        }
    }

    /**
     * Elimina campos sensibles del snapshot (password, tokens).
     */
    private static function sanitize(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }
        $sensitive = ['password', 'token', 'secret', 'api_key'];
        foreach ($sensitive as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***';
            }
        }
        return $data;
    }

    /**
     * Devuelve la IP real del cliente (considerando proxies).
     */
    private static function getIp(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // En caso de múltiples IPs (proxy chain), tomar la primera
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return 'unknown';
    }
}
