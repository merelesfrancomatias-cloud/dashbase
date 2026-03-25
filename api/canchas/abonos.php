<?php
/**
 * Abonos/membresías de canchas.
 * GET    → lista de abonos del negocio
 * POST   → crear abono + generar sus reservas
 * PUT    → editar estado/notas de un abono
 * DELETE → cancelar abono y sus reservas futuras
 */
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
Middleware::method($_SERVER['REQUEST_METHOD']);

$method = $_SERVER['REQUEST_METHOD'];

try {
    [$negocio_id, $userId] = Middleware::auth();

    $pdo = (new Database())->getConnection();
    PlanGuard::requireActive((int)$negocio_id, $pdo);

    // ── GET ──────────────────────────────────────────────────────────────────
    if ($method === 'GET') {
        $id = intval($_GET['id'] ?? 0);

        if ($id) {
            $st = $pdo->prepare("
                SELECT a.*, c.nombre AS cancha_nombre, c.deporte
                FROM abonos_canchas a
                JOIN canchas c ON c.id = a.cancha_id
                WHERE a.id = ? AND a.negocio_id = ?");
            $st->execute([$id, $negocio_id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) { Response::error('Abono no encontrado', 404); exit; }
            Response::success('ok', $row);
            exit;
        }

        // Listado con filtros opcionales
        $estado   = $_GET['estado']   ?? '';
        $canchaId = intval($_GET['cancha_id'] ?? 0);

        $where  = ['a.negocio_id = ?'];
        $params = [$negocio_id];

        if ($estado)   { $where[] = 'a.estado = ?';    $params[] = $estado; }
        if ($canchaId) { $where[] = 'a.cancha_id = ?'; $params[] = $canchaId; }

        $sql = "SELECT a.*, c.nombre AS cancha_nombre, c.deporte
                FROM abonos_canchas a
                JOIN canchas c ON c.id = a.cancha_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.fecha_inicio DESC, a.cliente_nombre";

        $st = $pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Contar reservas generadas por cada abono
        foreach ($rows as &$r) {
            $stC = $pdo->prepare("SELECT COUNT(*) FROM reservas_canchas WHERE abono_id = ?");
            $stC->execute([$r['id']]);
            $r['total_reservas'] = (int)$stC->fetchColumn();
        }
        unset($r);

        Response::success('ok', $rows);
        exit;
    }

    // ── POST ─────────────────────────────────────────────────────────────────
    if ($method === 'POST') {
        $d = json_decode(file_get_contents('php://input'), true) ?: [];

        $canchaId  = intval($d['cancha_id']  ?? 0);
        $nombre    = trim($d['cliente_nombre']    ?? '');
        $telefono  = trim($d['cliente_telefono']  ?? '');
        $diaSemana = intval($d['dia_semana'] ?? -1);
        $horaIni   = $d['hora_inicio']  ?? '';
        $horaFin   = $d['hora_fin']     ?? '';
        $durHoras  = max(1, (float)($d['duracion_horas'] ?? 1));
        $monto     = max(0, (float)($d['monto_mensual'] ?? 0));
        $fechaIni  = $d['fecha_inicio'] ?? '';
        $fechaFin  = $d['fecha_fin']    ?? '';
        $notas     = trim($d['notas']   ?? '');

        if (!$canchaId || !$nombre || $diaSemana < 0 || $diaSemana > 6 || !$horaIni || !$horaFin || !$fechaIni || !$fechaFin) {
            Response::error('Datos incompletos', 400); exit;
        }

        // Validar cancha pertenece al negocio
        $stC = $pdo->prepare("SELECT id FROM canchas WHERE id = ? AND negocio_id = ? AND activo = 1");
        $stC->execute([$canchaId, $negocio_id]);
        if (!$stC->fetch()) { Response::error('Cancha no encontrada', 404); exit; }

        $pdo->beginTransaction();

        // Insertar abono
        $stA = $pdo->prepare("INSERT INTO abonos_canchas
            (negocio_id, cancha_id, cliente_nombre, cliente_telefono, dia_semana, hora_inicio, hora_fin, duracion_horas, monto_mensual, fecha_inicio, fecha_fin, estado, notas)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,'activo',?)");
        $stA->execute([$negocio_id, $canchaId, $nombre, $telefono ?: null, $diaSemana, $horaIni, $horaFin, $durHoras, $monto, $fechaIni, $fechaFin, $notas ?: null]);
        $abonoId = $pdo->lastInsertId();

        // Generar reservas para cada ocurrencia del día de semana en el rango
        $generadas = generarReservas($pdo, $abonoId, $canchaId, $nombre, $telefono, $diaSemana, $horaIni, $horaFin, $durHoras, $monto, $fechaIni, $fechaFin);

        $pdo->commit();

        Response::success('Abono creado', ['id' => $abonoId, 'reservas_generadas' => $generadas]);
        exit;
    }

    // ── PUT ──────────────────────────────────────────────────────────────────
    if ($method === 'PUT') {
        $d  = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = intval($d['id'] ?? 0);
        if (!$id) { Response::error('id requerido', 400); exit; }

        // Verificar pertenece al negocio
        $stV = $pdo->prepare("SELECT id FROM abonos_canchas WHERE id = ? AND negocio_id = ?");
        $stV->execute([$id, $negocio_id]);
        if (!$stV->fetch()) { Response::error('Abono no encontrado', 404); exit; }

        $allowed = ['estado', 'notas', 'monto_mensual'];
        $sets = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $d)) {
                $sets[]   = "$f = ?";
                $params[] = $d[$f];
            }
        }
        if (!$sets) { Response::error('Sin cambios', 400); exit; }

        $params[] = $id;
        $pdo->prepare("UPDATE abonos_canchas SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);

        Response::success('Abono actualizado');
        exit;
    }

    // ── DELETE ───────────────────────────────────────────────────────────────
    if ($method === 'DELETE') {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) { Response::error('id requerido', 400); exit; }

        $stV = $pdo->prepare("SELECT id FROM abonos_canchas WHERE id = ? AND negocio_id = ?");
        $stV->execute([$id, $negocio_id]);
        if (!$stV->fetch()) { Response::error('Abono no encontrado', 404); exit; }

        $pdo->beginTransaction();

        // Cancelar reservas futuras del abono
        $hoy = date('Y-m-d');
        $pdo->prepare("UPDATE reservas_canchas SET estado='cancelada' WHERE abono_id = ? AND fecha >= ?")->execute([$id, $hoy]);
        $pdo->prepare("UPDATE abonos_canchas SET estado='cancelado' WHERE id = ?")->execute([$id]);

        $pdo->commit();

        Response::success('Abono cancelado');
        exit;
    }

    Response::error('Método no permitido', 405);

} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Genera registros en reservas_canchas para cada ocurrencia del dia_semana
 * dentro del rango [fecha_inicio, fecha_fin]. Salta días ya ocupados (no sobreescribe).
 */
function generarReservas(PDO $pdo, int $abonoId, int $canchaId, string $nombre, string $telefono,
    int $diaSemana, string $horaIni, string $horaFin, float $durHoras, float $monto,
    string $fechaIni, string $fechaFin): int
{
    $stCheck = $pdo->prepare("SELECT COUNT(*) FROM reservas_canchas
        WHERE cancha_id=? AND fecha=? AND estado NOT IN ('cancelada')
        AND hora_inicio < ? AND hora_fin > ?");

    $stIns = $pdo->prepare("INSERT INTO reservas_canchas
        (cancha_id, abono_id, cliente_nombre, cliente_telefono, fecha, hora_inicio, hora_fin, duracion_horas, monto, estado)
        VALUES (?,?,?,?,?,?,?,?,?,'confirmada')");

    $cursor  = new DateTime($fechaIni);
    $fin     = new DateTime($fechaFin);
    $generadas = 0;

    // PHP: 0=Dom,1=Lun,...,6=Sab — igual que MySQL DAYOFWEEK-1
    while ($cursor <= $fin) {
        if ((int)$cursor->format('w') === $diaSemana) {
            $fechaStr = $cursor->format('Y-m-d');
            $stCheck->execute([$canchaId, $fechaStr, $horaFin, $horaIni]);
            if ($stCheck->fetchColumn() == 0) {
                $stIns->execute([$canchaId, $abonoId, $nombre, $telefono ?: null, $fechaStr, $horaIni, $horaFin, $durHoras, $monto / 4]);
                $generadas++;
            }
        }
        $cursor->modify('+1 day');
    }

    return $generadas;
}
