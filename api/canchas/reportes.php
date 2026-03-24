<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
Middleware::method('GET');

[$negocioId] = Middleware::auth();

$db = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $db);

$tipo  = $_GET['tipo']  ?? 'resumen';
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-29 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');

/* ═══════════════════════════════════════════════════════════
   RESUMEN
═══════════════════════════════════════════════════════════ */
if ($tipo === 'resumen') {
    // Período actual
    $stmt = $db->prepare("
        SELECT
            COUNT(rc.id)                                                             AS total_reservas,
            COUNT(CASE WHEN rc.estado='confirmada' THEN 1 END)                       AS confirmadas,
            COUNT(CASE WHEN rc.estado='cancelada'  THEN 1 END)                       AS canceladas,
            COUNT(CASE WHEN rc.estado='pendiente'  THEN 1 END)                       AS pendientes,
            COALESCE(SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto   ELSE 0 END), 0) AS ingresos,
            COALESCE(AVG(CASE WHEN rc.estado='confirmada' THEN rc.monto   END),       0) AS ticket_promedio,
            COALESCE(SUM(CASE WHEN rc.estado='confirmada' THEN rc.duracion_horas ELSE 0 END), 0) AS horas_reservadas
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.fecha BETWEEN ? AND ?
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    // Período anterior
    $dias      = (new DateTime($desde))->diff(new DateTime($hasta))->days + 1;
    $desdePrev = date('Y-m-d', strtotime("$desde -$dias days"));
    $hastaPrev = date('Y-m-d', strtotime("$hasta -$dias days"));

    $stmtPrev = $db->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END), 0) AS ingresos,
            COUNT(CASE WHEN rc.estado='confirmada' THEN 1 END) AS confirmadas
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.fecha BETWEEN ? AND ?
    ");
    $stmtPrev->execute([$negocioId, $desdePrev, $hastaPrev]);
    $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

    // Métodos de pago
    $stmtPago = $db->prepare("
        SELECT rc.metodo_pago, COUNT(*) AS cantidad, SUM(rc.monto) AS total
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.estado = 'confirmada'
          AND rc.fecha BETWEEN ? AND ? AND rc.metodo_pago IS NOT NULL
        GROUP BY rc.metodo_pago ORDER BY total DESC
    ");
    $stmtPago->execute([$negocioId, $desde, $hasta]);
    $pagos = $stmtPago->fetchAll(PDO::FETCH_ASSOC);

    $ingActual = (float)$actual['ingresos'];
    $ingPrev   = (float)$prev['ingresos'];
    $varIng    = $ingPrev > 0 ? round(($ingActual - $ingPrev) / $ingPrev * 100, 1) : null;

    Response::success('OK', [
        'ingresos'        => $ingActual,
        'ingresos_prev'   => $ingPrev,
        'var_ingresos'    => $varIng,
        'total_reservas'  => (int)$actual['total_reservas'],
        'confirmadas'     => (int)$actual['confirmadas'],
        'canceladas'      => (int)$actual['canceladas'],
        'pendientes'      => (int)$actual['pendientes'],
        'ticket_promedio' => round((float)$actual['ticket_promedio'], 2),
        'horas_reservadas'=> round((float)$actual['horas_reservadas'], 1),
        'metodos_pago'    => $pagos,
        'periodo_dias'    => $dias,
    ]);
}

/* ═══════════════════════════════════════════════════════════
   INGRESOS POR DÍA
═══════════════════════════════════════════════════════════ */
if ($tipo === 'ingresos_dia') {
    $stmt = $db->prepare("
        SELECT rc.fecha,
               SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END) AS total,
               COUNT(CASE WHEN rc.estado='confirmada' THEN 1 END)              AS confirmadas
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.fecha BETWEEN ? AND ?
        GROUP BY rc.fecha ORDER BY rc.fecha ASC
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   POR CANCHA
═══════════════════════════════════════════════════════════ */
if ($tipo === 'por_cancha') {
    $stmt = $db->prepare("
        SELECT c.id AS cancha_id,
               c.nombre AS cancha,
               c.deporte,
               COUNT(rc.id)                                                             AS total_reservas,
               COUNT(CASE WHEN rc.estado='confirmada' THEN 1 END)                       AS confirmadas,
               COALESCE(SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto   ELSE 0 END), 0) AS ingresos,
               COALESCE(SUM(CASE WHEN rc.estado='confirmada' THEN rc.duracion_horas ELSE 0 END), 0) AS horas_ocupadas
        FROM canchas c
        LEFT JOIN reservas_canchas rc ON rc.cancha_id = c.id AND rc.fecha BETWEEN ? AND ?
        WHERE c.negocio_id = ? AND c.activo = 1
        GROUP BY c.id, c.nombre, c.deporte
        ORDER BY ingresos DESC
    ");
    $stmt->execute([$desde, $hasta, $negocioId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['ingresos']     = round((float)$r['ingresos'], 2);
        $r['horas_ocupadas'] = round((float)$r['horas_ocupadas'], 1);
    }
    Response::success('OK', $rows);
}

/* ═══════════════════════════════════════════════════════════
   OCUPACIÓN HORARIA (heatmap por hora del día)
═══════════════════════════════════════════════════════════ */
if ($tipo === 'ocupacion_horaria') {
    $stmt = $db->prepare("
        SELECT HOUR(rc.hora_inicio) AS hora,
               COUNT(*)             AS reservas,
               SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END) AS ingresos
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.fecha BETWEEN ? AND ?
          AND rc.estado != 'cancelada'
        GROUP BY HOUR(rc.hora_inicio)
        ORDER BY hora ASC
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   ACTIVIDAD POR DÍA DE LA SEMANA
═══════════════════════════════════════════════════════════ */
if ($tipo === 'dias_semana') {
    $stmt = $db->prepare("
        SELECT DAYOFWEEK(rc.fecha) AS dia_num,
               DAYNAME(rc.fecha)   AS dia_nombre,
               COUNT(*)            AS reservas,
               SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END) AS ingresos
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.estado != 'cancelada' AND rc.fecha BETWEEN ? AND ?
        GROUP BY DAYOFWEEK(rc.fecha), DAYNAME(rc.fecha)
        ORDER BY dia_num ASC
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
