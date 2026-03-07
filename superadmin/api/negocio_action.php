<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

header('Content-Type: application/json');
$db = sa_db();

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = intval($body['id'] ?? 0);
$action = $body['action'] ?? '';

if (!$id || !$action) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']); exit;
}

// Verificar que el negocio existe
$neg = $db->prepare("SELECT id, nombre, plan_id, fecha_vencimiento FROM negocios WHERE id = ?");
$neg->execute([$id]);
$neg = $neg->fetch();
if (!$neg) { echo json_encode(['ok' => false, 'error' => 'Negocio no encontrado']); exit; }

try {
    switch ($action) {

        case 'bloquear':
            $motivo = trim($body['motivo'] ?? '');
            if (!$motivo) { echo json_encode(['ok'=>false,'error'=>'El motivo es obligatorio']); exit; }
            $db->prepare("UPDATE negocios SET bloqueado = 1, bloqueado_motivo = ?, activo = 0 WHERE id = ?")
               ->execute([$motivo, $id]);
            sa_log('negocio_bloqueado', "Negocio ID $id bloqueado. Motivo: $motivo", $id);
            break;

        case 'desbloquear':
            $db->prepare("UPDATE negocios SET bloqueado = 0, bloqueado_motivo = NULL, activo = 1 WHERE id = ?")
               ->execute([$id]);
            sa_log('negocio_desbloqueado', "Negocio ID $id desbloqueado", $id);
            break;

        case 'activar':
            $db->prepare("UPDATE negocios SET activo = 1 WHERE id = ?")->execute([$id]);
            sa_log('negocio_activado', "Negocio ID $id activado", $id);
            break;

        case 'desactivar':
            $db->prepare("UPDATE negocios SET activo = 0 WHERE id = ?")->execute([$id]);
            sa_log('negocio_desactivado', "Negocio ID $id desactivado", $id);
            break;

        case 'renovar':
            $planId = intval($body['plan_id'] ?? $neg['plan_id']);
            $meses  = max(1, intval($body['meses'] ?? 1));
            $monto  = floatval($body['monto'] ?? 0);
            $metodo = $body['metodo'] ?? 'transferencia';
            $ref    = trim($body['ref'] ?? '');
            $notas  = trim($body['notas'] ?? '');

            // Calcular nueva fecha: si ya tiene vence futuro, sumar desde ahí; si vencido, desde hoy
            $base = $neg['fecha_vencimiento'] && strtotime($neg['fecha_vencimiento']) > time()
                    ? $neg['fecha_vencimiento']
                    : date('Y-m-d');
            $nuevaFecha = date('Y-m-d', strtotime("+{$meses} months", strtotime($base)));

            // Actualizar negocio
            $db->prepare("UPDATE negocios SET plan_id = ?, fecha_vencimiento = ?, activo = 1, bloqueado = 0 WHERE id = ?")
               ->execute([$planId, $nuevaFecha, $id]);

            // Registrar pago (si hay monto)
            if ($monto > 0) {
                $db->prepare("
                    INSERT INTO pagos (negocio_id, plan_id, monto, metodo_pago, referencia, fecha_pago, fecha_desde, fecha_hasta, notas, registrado_por)
                    VALUES (?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?)
                ")->execute([$id, $planId, $monto, $metodo, $ref, date('Y-m-d'), $nuevaFecha, $notas, $_SESSION['sa_id']]);
            }

            sa_log('negocio_renovado', "Negocio ID $id renovado hasta $nuevaFecha. Plan $planId. Monto: $$monto", $id);
            echo json_encode(['ok' => true, 'nueva_fecha' => $nuevaFecha]); exit;

        default:
            echo json_encode(['ok' => false, 'error' => 'Acción desconocida']); exit;
    }

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
