<?php
/**
 * Reportes financieros y de ocupación de Hospedaje
 * GET ?tipo=resumen   [&fecha=YYYY-MM-DD]       → KPIs del día
 * GET ?tipo=movimientos [&fecha=YYYY-MM-DD]      → checkins/outs del día
 * GET ?tipo=ingresos_dia &desde= &hasta=         → ingresos por día
 * GET ?tipo=por_tipo  &desde= &hasta=            → ingresos por tipo de habitación
 * GET ?tipo=por_estadia &desde= &hasta=          → ingresos por tipo de estadía
 * GET ?tipo=ocupacion_diaria &desde= &hasta=     → % ocupación por día
 * GET ?tipo=dias_semana &desde= &hasta=          → reservas por día de semana
 */
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo  = (new Database())->getConnection();
$tipo = $_GET['tipo'] ?? 'resumen';

$hoy   = $_GET['fecha'] ?? date('Y-m-d');
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-29 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');

try {

// ── RESUMEN del día ───────────────────────────────────────────────────────────
if ($tipo === 'resumen') {
    // Habitaciones total y ocupadas ahora
    $stHab = $pdo->prepare("SELECT COUNT(*) AS total,
        SUM(estado='ocupada') AS ocupadas,
        SUM(estado='limpieza') AS limpieza,
        SUM(estado='libre') AS libres
        FROM hospedaje_habitaciones WHERE negocio_id=? AND activo=1");
    $stHab->execute([$negocioId]);
    $habs = $stHab->fetch(PDO::FETCH_ASSOC);

    // Check-ins hoy
    $stCI = $pdo->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(seña),0) AS senias
        FROM hospedaje_reservas WHERE negocio_id=? AND checkin_fecha=? AND estado IN ('checkin','checkout')");
    $stCI->execute([$negocioId, $hoy]);
    $ciHoy = $stCI->fetch(PDO::FETCH_ASSOC);

    // Check-outs hoy
    $stCO = $pdo->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(total),0) AS ingresos, COALESCE(SUM(seña),0) AS senias
        FROM hospedaje_reservas WHERE negocio_id=? AND checkout_fecha=? AND estado='checkout'");
    $stCO->execute([$negocioId, $hoy]);
    $coHoy = $stCO->fetch(PDO::FETCH_ASSOC);

    // Ingresos hoy = checkouts del día + señas de check-ins del día que no hicieron checkout hoy
    $ingresosHoy = round((float)$coHoy['ingresos'] + (float)$ciHoy['senias'], 2);

    // Reservas futuras (pendientes)
    $stPend = $pdo->prepare("SELECT COUNT(*) AS total FROM hospedaje_reservas WHERE negocio_id=? AND checkin_fecha > ? AND estado='reservada'");
    $stPend->execute([$negocioId, $hoy]);
    $pendientes = (int)$stPend->fetchColumn();

    // Cargos extra hoy
    $stEx = $pdo->prepare("SELECT COALESCE(SUM(e.total),0) AS total FROM hospedaje_cargos_extra e
        JOIN hospedaje_reservas r ON r.id=e.reserva_id
        WHERE e.negocio_id=? AND DATE(e.created_at)=?");
    $stEx->execute([$negocioId, $hoy]);
    $extrasHoy = round((float)$stEx->fetchColumn(), 2);

    $ocupacionPct = $habs['total'] > 0 ? round($habs['ocupadas'] / $habs['total'] * 100) : 0;

    Response::success('ok', [
        'fecha'          => $hoy,
        'hab_total'      => (int)$habs['total'],
        'hab_ocupadas'   => (int)$habs['ocupadas'],
        'hab_libres'     => (int)$habs['libres'],
        'hab_limpieza'   => (int)$habs['limpieza'],
        'ocupacion_pct'  => $ocupacionPct,
        'checkins_hoy'   => (int)$ciHoy['total'],
        'checkouts_hoy'  => (int)$coHoy['total'],
        'ingresos_hoy'   => $ingresosHoy,
        'extras_hoy'     => $extrasHoy,
        'reservas_futuras' => $pendientes,
    ]);
    exit;
}

// ── MOVIMIENTOS del día ───────────────────────────────────────────────────────
if ($tipo === 'movimientos') {
    // Check-ins del día
    $stCI = $pdo->prepare("SELECT r.id, r.huesped_nombre, r.huesped_telefono,
        r.checkin_fecha, r.checkin_hora, r.checkout_fecha, r.noches, r.tipo_estadia,
        r.total, r.seña, r.estado, h.numero AS hab_numero, h.tipo AS hab_tipo
        FROM hospedaje_reservas r
        JOIN hospedaje_habitaciones h ON h.id=r.habitacion_id
        WHERE r.negocio_id=? AND r.checkin_fecha=? AND r.estado IN ('checkin','checkout','reservada')
        ORDER BY r.checkin_hora");
    $stCI->execute([$negocioId, $hoy]);
    $checkins = $stCI->fetchAll(PDO::FETCH_ASSOC);

    // Check-outs del día
    $stCO = $pdo->prepare("SELECT r.id, r.huesped_nombre, r.huesped_telefono,
        r.checkin_fecha, r.checkout_fecha, r.checkout_hora, r.noches, r.tipo_estadia,
        r.total, r.seña, r.estado, h.numero AS hab_numero, h.tipo AS hab_tipo
        FROM hospedaje_reservas r
        JOIN hospedaje_habitaciones h ON h.id=r.habitacion_id
        WHERE r.negocio_id=? AND r.checkout_fecha=? AND r.estado='checkout'
        ORDER BY r.checkout_hora");
    $stCO->execute([$negocioId, $hoy]);
    $checkouts = $stCO->fetchAll(PDO::FETCH_ASSOC);

    Response::success('ok', ['checkins' => $checkins, 'checkouts' => $checkouts]);
    exit;
}

// ── INGRESOS por día ──────────────────────────────────────────────────────────
if ($tipo === 'ingresos_dia') {
    $st = $pdo->prepare("SELECT checkout_fecha AS fecha,
        COUNT(*) AS reservas,
        ROUND(SUM(total),2) AS ingresos,
        ROUND(SUM(seña),2) AS senias
        FROM hospedaje_reservas
        WHERE negocio_id=? AND estado='checkout' AND checkout_fecha BETWEEN ? AND ?
        GROUP BY checkout_fecha ORDER BY checkout_fecha");
    $st->execute([$negocioId, $desde, $hasta]);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── INGRESOS por tipo de habitación ──────────────────────────────────────────
if ($tipo === 'por_tipo') {
    $st = $pdo->prepare("SELECT h.tipo,
        COUNT(r.id) AS reservas,
        ROUND(SUM(r.total),2) AS ingresos,
        ROUND(AVG(r.noches),1) AS noches_promedio
        FROM hospedaje_reservas r
        JOIN hospedaje_habitaciones h ON h.id=r.habitacion_id
        WHERE r.negocio_id=? AND r.estado='checkout' AND r.checkout_fecha BETWEEN ? AND ?
        GROUP BY h.tipo ORDER BY ingresos DESC");
    $st->execute([$negocioId, $desde, $hasta]);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── INGRESOS por tipo de estadía ─────────────────────────────────────────────
if ($tipo === 'por_estadia') {
    $st = $pdo->prepare("SELECT tipo_estadia,
        COUNT(*) AS reservas,
        ROUND(SUM(total),2) AS ingresos,
        ROUND(AVG(noches),1) AS promedio_unidades
        FROM hospedaje_reservas
        WHERE negocio_id=? AND estado='checkout' AND checkout_fecha BETWEEN ? AND ?
        GROUP BY tipo_estadia ORDER BY ingresos DESC");
    $st->execute([$negocioId, $desde, $hasta]);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── OCUPACIÓN diaria ──────────────────────────────────────────────────────────
if ($tipo === 'ocupacion_diaria') {
    // Total de habitaciones activas
    $stTotal = $pdo->prepare("SELECT COUNT(*) FROM hospedaje_habitaciones WHERE negocio_id=? AND activo=1");
    $stTotal->execute([$negocioId]);
    $totalHabs = max(1, (int)$stTotal->fetchColumn());

    // Por cada día, cuántas habitaciones estaban ocupadas (reserva activa ese día)
    $st = $pdo->prepare("SELECT fechas.fecha,
        COUNT(DISTINCT r.habitacion_id) AS ocupadas,
        ? AS total_habs
        FROM (
            SELECT DATE_ADD(?, INTERVAL seq DAY) AS fecha
            FROM (SELECT 0 seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
                  UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
                  UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION SELECT 21
                  UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29) nums
            WHERE DATE_ADD(?, INTERVAL seq DAY) <= ?
        ) fechas
        LEFT JOIN hospedaje_reservas r
            ON r.negocio_id=? AND r.estado IN ('checkin','checkout')
            AND r.checkin_fecha <= fechas.fecha AND r.checkout_fecha > fechas.fecha
        GROUP BY fechas.fecha ORDER BY fechas.fecha");
    $st->execute([$totalHabs, $desde, $desde, $hasta, $negocioId]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        $row['pct'] = round($row['ocupadas'] / $totalHabs * 100);
    }
    unset($row);
    Response::success('ok', ['rows' => $rows, 'total_habs' => $totalHabs]);
    exit;
}

// ── DÍAS DE LA SEMANA ─────────────────────────────────────────────────────────
if ($tipo === 'dias_semana') {
    $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $st = $pdo->prepare("SELECT DAYOFWEEK(checkin_fecha)-1 AS dia,
        COUNT(*) AS reservas,
        ROUND(SUM(total),2) AS ingresos
        FROM hospedaje_reservas
        WHERE negocio_id=? AND checkin_fecha BETWEEN ? AND ? AND estado != 'cancelada'
        GROUP BY DAYOFWEEK(checkin_fecha) ORDER BY dia");
    $st->execute([$negocioId, $desde, $hasta]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) { $r['nombre'] = $dias[(int)$r['dia']] ?? ''; }
    unset($r);
    Response::success('ok', $rows);
    exit;
}

Response::error('Tipo de reporte desconocido', 400);

} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
