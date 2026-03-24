<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();

$negocioId = (int)$_SESSION['negocio_id'];
$pdo       = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $pdo);
$tipo      = $_GET['tipo'] ?? 'resumen';
$desde     = $_GET['desde'] ?? date('Y-m-d', strtotime('-29 days'));
$hasta     = $_GET['hasta'] ?? date('Y-m-d');

/* ═══════════════════════════════════════════════════════════
   RESUMEN
═══════════════════════════════════════════════════════════ */
if ($tipo === 'resumen') {
    // Período actual
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*)                                                          AS total_turnos,
            SUM(estado='completado')                                          AS completados,
            SUM(estado='cancelado')                                           AS cancelados,
            SUM(estado='no_show')                                             AS no_show,
            COALESCE(SUM(CASE WHEN estado='completado' THEN precio ELSE 0 END),0) AS ingresos,
            COALESCE(AVG(CASE WHEN estado='completado' THEN precio END),0)    AS ticket_promedio
        FROM turnos
        WHERE negocio_id=? AND fecha BETWEEN ? AND ?
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    // Período anterior (misma cantidad de días)
    $dias = (new DateTime($desde))->diff(new DateTime($hasta))->days + 1;
    $desdePrev = date('Y-m-d', strtotime("$desde -$dias days"));
    $hastaPrev = date('Y-m-d', strtotime("$hasta -$dias days"));

    $stmt2 = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN estado='completado' THEN precio ELSE 0 END),0) AS ingresos,
               SUM(estado='completado') AS completados
        FROM turnos WHERE negocio_id=? AND fecha BETWEEN ? AND ?
    ");
    $stmt2->execute([$negocioId, $desdePrev, $hastaPrev]);
    $prev = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Métodos de pago
    $stmtPago = $pdo->prepare("
        SELECT metodo_pago, COUNT(*) AS cantidad, SUM(precio) AS total
        FROM turnos
        WHERE negocio_id=? AND estado='completado' AND fecha BETWEEN ? AND ?
          AND metodo_pago IS NOT NULL
        GROUP BY metodo_pago ORDER BY total DESC
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
        'total_turnos'    => (int)$actual['total_turnos'],
        'completados'     => (int)$actual['completados'],
        'cancelados'      => (int)$actual['cancelados'],
        'no_show'         => (int)$actual['no_show'],
        'ticket_promedio' => round((float)$actual['ticket_promedio'], 2),
        'metodos_pago'    => $pagos,
        'periodo_dias'    => $dias,
    ]);
}

/* ═══════════════════════════════════════════════════════════
   INGRESOS POR DÍA
═══════════════════════════════════════════════════════════ */
if ($tipo === 'ingresos_dia') {
    $stmt = $pdo->prepare("
        SELECT fecha,
               SUM(CASE WHEN estado='completado' THEN precio ELSE 0 END) AS total,
               SUM(estado='completado') AS completados
        FROM turnos
        WHERE negocio_id=? AND fecha BETWEEN ? AND ?
        GROUP BY fecha ORDER BY fecha ASC
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   SERVICIOS MÁS VENDIDOS
═══════════════════════════════════════════════════════════ */
if ($tipo === 'servicios') {
    $stmt = $pdo->prepare("
        SELECT servicio_nombre,
               COUNT(*) AS cantidad,
               SUM(precio) AS total,
               AVG(precio) AS precio_prom
        FROM turnos
        WHERE negocio_id=? AND estado='completado'
          AND fecha BETWEEN ? AND ?
          AND servicio_nombre IS NOT NULL
        GROUP BY servicio_nombre
        ORDER BY cantidad DESC
        LIMIT 10
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   RENDIMIENTO POR EMPLEADO
═══════════════════════════════════════════════════════════ */
if ($tipo === 'empleados') {
    // Ingresos y comisiones por empleado
    // La comisión se calcula desde turno_servicios (si existe) o desde servicios (por servicio_id del turno)
    $stmt = $pdo->prepare("
        SELECT e.id AS empleado_id,
               e.nombre AS empleado,
               e.cargo,
               COUNT(t.id)                                                             AS turnos,
               SUM(t.estado='completado')                                              AS completados,
               COALESCE(SUM(CASE WHEN t.estado='completado' THEN t.precio ELSE 0 END),0) AS ingresos,
               COALESCE(SUM(
                   CASE WHEN t.estado='completado' THEN
                       CASE
                           WHEN t.servicio_id IS NOT NULL THEN
                               t.precio * COALESCE(s.comision_porcentaje, 0) / 100
                           ELSE (
                               SELECT COALESCE(SUM(ts.precio * COALESCE(sv.comision_porcentaje, 0) / 100), 0)
                               FROM turno_servicios ts
                               LEFT JOIN servicios sv ON sv.id = ts.servicio_id AND sv.negocio_id = t.negocio_id
                               WHERE ts.turno_id = t.id
                           )
                       END
                   ELSE 0 END
               ), 0) AS comision_estimada
        FROM turnos t
        JOIN empleados e ON e.id = t.empleado_id
        LEFT JOIN servicios s ON s.id = t.servicio_id AND s.negocio_id = t.negocio_id
        WHERE t.negocio_id=? AND t.fecha BETWEEN ? AND ?
        GROUP BY e.id, e.nombre, e.cargo
        ORDER BY ingresos DESC
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear valores numéricos
    foreach ($rows as &$row) {
        $row['ingresos']          = round((float)$row['ingresos'], 2);
        $row['comision_estimada'] = round((float)$row['comision_estimada'], 2);
    }

    Response::success('OK', $rows);
}

/* ═══════════════════════════════════════════════════════════
   ACTIVIDAD POR DÍA DE LA SEMANA
═══════════════════════════════════════════════════════════ */
if ($tipo === 'dias_semana') {
    $stmt = $pdo->prepare("
        SELECT DAYOFWEEK(fecha) AS dia_num,
               DAYNAME(fecha)   AS dia_nombre,
               COUNT(*)         AS turnos,
               SUM(CASE WHEN estado='completado' THEN precio ELSE 0 END) AS ingresos
        FROM turnos
        WHERE negocio_id=? AND estado != 'cancelado' AND fecha BETWEEN ? AND ?
        GROUP BY DAYOFWEEK(fecha), DAYNAME(fecha)
        ORDER BY dia_num ASC
    ");
    $stmt->execute([$negocioId, $desde, $hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
