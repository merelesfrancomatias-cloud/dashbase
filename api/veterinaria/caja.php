<?php
/**
 * Reportes financieros y estadísticos de Veterinaria.
 * GET ?tipo=resumen   [&fecha=YYYY-MM-DD]    → KPIs del día
 * GET ?tipo=agenda    [&fecha=YYYY-MM-DD]    → consultas del día con montos
 * GET ?tipo=ingresos_dia &desde= &hasta=     → ingresos por día
 * GET ?tipo=por_tipo  &desde= &hasta=        → ingresos/cantidad por tipo de consulta
 * GET ?tipo=por_especie &desde= &hasta=      → consultas por especie
 * GET ?tipo=dias_semana &desde= &hasta=      → consultas por día de semana
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
    $st = $pdo->prepare("SELECT
        COUNT(*)                                           AS total,
        SUM(estado='pendiente')                           AS pendientes,
        SUM(estado='atendido')                            AS atendidos,
        SUM(estado='cancelado')                           AS cancelados,
        COALESCE(SUM(CASE WHEN estado='atendido' THEN monto ELSE 0 END),0) AS facturado,
        COALESCE(SUM(CASE WHEN estado='atendido' AND metodo_pago='efectivo' THEN monto ELSE 0 END),0) AS efectivo,
        COALESCE(SUM(CASE WHEN estado='atendido' AND metodo_pago!='efectivo' THEN monto ELSE 0 END),0) AS digital
        FROM vet_consultas WHERE negocio_id=? AND fecha=?");
    $st->execute([$negocioId, $hoy]);
    $dia = $st->fetch(PDO::FETCH_ASSOC);

    // Pacientes nuevos hoy
    $stNew = $pdo->prepare("SELECT COUNT(*) FROM vet_pacientes WHERE negocio_id=? AND DATE(created_at)=? AND activo=1");
    $stNew->execute([$negocioId, $hoy]);
    $nuevos = (int)$stNew->fetchColumn();

    // Próximas vacunas (alertas 7 días)
    $stVac = $pdo->prepare("SELECT COUNT(*) FROM vet_vacunas v JOIN vet_pacientes p ON p.id=v.paciente_id
        WHERE p.negocio_id=? AND v.proxima_dosis BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $stVac->execute([$negocioId]);
    $alertasVac = (int)$stVac->fetchColumn();

    // Alertas de stock bajo
    $stStk = $pdo->prepare("SELECT COUNT(*) FROM vet_stock WHERE negocio_id=? AND activo=1 AND stock_actual <= stock_minimo");
    $stStk->execute([$negocioId]);
    $alertasStock = (int)$stStk->fetchColumn();

    Response::success('ok', array_merge($dia, [
        'fecha'         => $hoy,
        'pacientes_nuevos' => $nuevos,
        'alertas_vacunas'  => $alertasVac,
        'alertas_stock'    => $alertasStock,
    ]));
    exit;
}

// ── AGENDA del día ─────────────────────────────────────────────────────────────
if ($tipo === 'agenda') {
    $st = $pdo->prepare("SELECT c.*, p.nombre AS pac_nombre, p.especie, p.duenio_nombre, p.duenio_telefono
        FROM vet_consultas c
        JOIN vet_pacientes p ON p.id=c.paciente_id
        WHERE c.negocio_id=? AND c.fecha=?
        ORDER BY c.hora, c.id");
    $st->execute([$negocioId, $hoy]);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── INGRESOS por día ──────────────────────────────────────────────────────────
if ($tipo === 'ingresos_dia') {
    $st = $pdo->prepare("SELECT fecha,
        COUNT(*) AS consultas,
        SUM(estado='atendido') AS atendidas,
        ROUND(SUM(CASE WHEN estado='atendido' THEN monto ELSE 0 END),2) AS ingresos
        FROM vet_consultas WHERE negocio_id=? AND fecha BETWEEN ? AND ?
        GROUP BY fecha ORDER BY fecha");
    $st->execute([$negocioId, $desde, $hasta]);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── POR TIPO de consulta ──────────────────────────────────────────────────────
if ($tipo === 'por_tipo') {
    $st = $pdo->prepare("SELECT tipo,
        COUNT(*) AS total,
        SUM(estado='atendido') AS atendidas,
        ROUND(SUM(CASE WHEN estado='atendido' THEN monto ELSE 0 END),2) AS ingresos
        FROM vet_consultas WHERE negocio_id=? AND fecha BETWEEN ? AND ? AND estado != 'cancelado'
        GROUP BY tipo ORDER BY ingresos DESC");
    $st->execute([$negocioId, $desde, $hasta]);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── POR ESPECIE ───────────────────────────────────────────────────────────────
if ($tipo === 'por_especie') {
    $st = $pdo->prepare("SELECT p.especie,
        COUNT(c.id) AS consultas,
        ROUND(SUM(CASE WHEN c.estado='atendido' THEN c.monto ELSE 0 END),2) AS ingresos
        FROM vet_consultas c
        JOIN vet_pacientes p ON p.id=c.paciente_id
        WHERE c.negocio_id=? AND c.fecha BETWEEN ? AND ? AND c.estado != 'cancelado'
        GROUP BY p.especie ORDER BY consultas DESC");
    $st->execute([$negocioId, $desde, $hasta]);
    Response::success('ok', $st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── DÍAS DE LA SEMANA ─────────────────────────────────────────────────────────
if ($tipo === 'dias_semana') {
    $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $st = $pdo->prepare("SELECT DAYOFWEEK(fecha)-1 AS dia,
        COUNT(*) AS consultas,
        ROUND(SUM(CASE WHEN estado='atendido' THEN monto ELSE 0 END),2) AS ingresos
        FROM vet_consultas WHERE negocio_id=? AND fecha BETWEEN ? AND ? AND estado != 'cancelado'
        GROUP BY DAYOFWEEK(fecha) ORDER BY dia");
    $st->execute([$negocioId, $desde, $hasta]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) { $r['nombre'] = $dias[(int)$r['dia']] ?? ''; }
    unset($r);
    Response::success('ok', $rows);
    exit;
}

Response::error('Tipo desconocido', 400);

} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
