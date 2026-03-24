<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();

$negocio_id = $_SESSION['negocio_id'];
$db   = (new Database())->getConnection();
$tipo = $_GET['tipo'] ?? 'resumen';

/* ═══════════════════════════════════════════════════════════
   RESUMEN GENERAL
═══════════════════════════════════════════════════════════ */
if ($tipo === 'resumen') {
    // KPIs de socios
    $stmtSocios = $db->prepare("
        SELECT
            COUNT(*)                                                       AS total,
            SUM(CASE WHEN estado='activo' THEN 1 ELSE 0 END)              AS activos,
            SUM(CASE WHEN estado='vencido' THEN 1 ELSE 0 END)             AS vencidos,
            SUM(CASE WHEN estado='suspendido' THEN 1 ELSE 0 END)          AS suspendidos,
            SUM(CASE WHEN estado='activo' AND DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 0 AND 7 THEN 1 ELSE 0 END) AS por_vencer_7d,
            SUM(CASE WHEN estado='activo' AND DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 0 AND 30 THEN 1 ELSE 0 END) AS por_vencer_30d
        FROM gym_socios WHERE negocio_id=?
    ");
    $stmtSocios->execute([$negocio_id]);
    $socios = $stmtSocios->fetch(PDO::FETCH_ASSOC);

    // Ingresos mes actual vs mes anterior
    $stmtMes = $db->prepare("
        SELECT
            SUM(CASE WHEN DATE_FORMAT(fecha,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m') THEN monto ELSE 0 END) AS mes_actual,
            SUM(CASE WHEN DATE_FORMAT(fecha,'%Y-%m')=DATE_FORMAT(DATE_SUB(CURDATE(),INTERVAL 1 MONTH),'%Y-%m') THEN monto ELSE 0 END) AS mes_anterior
        FROM gym_pagos WHERE negocio_id=?
    ");
    $stmtMes->execute([$negocio_id]);
    $ingresos = $stmtMes->fetch(PDO::FETCH_ASSOC);

    $mesActual   = (float)$ingresos['mes_actual'];
    $mesAnterior = (float)$ingresos['mes_anterior'];
    $varIngresos = $mesAnterior > 0 ? round(($mesActual - $mesAnterior) / $mesAnterior * 100, 1) : null;

    // Distribución por plan
    $stmtPlanes = $db->prepare("
        SELECT p.nombre, p.color, COUNT(s.id) AS cantidad
        FROM gym_socios s
        JOIN gym_planes p ON p.id = s.plan_id
        WHERE s.negocio_id=? AND s.estado='activo'
        GROUP BY p.id, p.nombre, p.color
        ORDER BY cantidad DESC
    ");
    $stmtPlanes->execute([$negocio_id]);
    $porPlan = $stmtPlanes->fetchAll(PDO::FETCH_ASSOC);

    Response::success('OK', [
        'socios'       => $socios,
        'mes_actual'   => $mesActual,
        'mes_anterior' => $mesAnterior,
        'var_ingresos' => $varIngresos,
        'por_plan'     => $porPlan,
    ]);
}

/* ═══════════════════════════════════════════════════════════
   INGRESOS POR MES (últimos 12 meses)
═══════════════════════════════════════════════════════════ */
if ($tipo === 'ingresos_mes') {
    $stmt = $db->prepare("
        SELECT
            DATE_FORMAT(fecha,'%Y-%m') AS mes,
            SUM(monto)                 AS total,
            COUNT(*)                   AS pagos
        FROM gym_pagos
        WHERE negocio_id=? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(fecha,'%Y-%m')
        ORDER BY mes ASC
    ");
    $stmt->execute([$negocio_id]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   ASISTENCIAS POR DÍA (últimos 30 días)
═══════════════════════════════════════════════════════════ */
if ($tipo === 'asistencias') {
    $stmt = $db->prepare("
        SELECT fecha, COUNT(*) AS total
        FROM gym_asistencias
        WHERE negocio_id=? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY fecha
        ORDER BY fecha ASC
    ");
    $stmt->execute([$negocio_id]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   SOCIOS QUE VENCEN PRONTO (próximos N días)
═══════════════════════════════════════════════════════════ */
if ($tipo === 'proximos_vencimientos') {
    $dias = max(1, min(60, (int)($_GET['dias'] ?? 30)));
    $stmt = $db->prepare("
        SELECT s.id, s.nombre, s.apellido, s.telefono, s.email,
               s.fecha_vencimiento,
               DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes,
               p.nombre AS plan_nombre, p.precio AS plan_precio
        FROM gym_socios s
        LEFT JOIN gym_planes p ON p.id = s.plan_id
        WHERE s.negocio_id=?
          AND s.estado='activo'
          AND s.fecha_vencimiento IS NOT NULL
          AND DATEDIFF(s.fecha_vencimiento, CURDATE()) BETWEEN 0 AND ?
        ORDER BY s.fecha_vencimiento ASC
    ");
    $stmt->execute([$negocio_id, $dias]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   SOCIOS VENCIDOS (sin renovar)
═══════════════════════════════════════════════════════════ */
if ($tipo === 'vencidos') {
    $stmt = $db->prepare("
        SELECT s.id, s.nombre, s.apellido, s.telefono, s.email,
               s.fecha_vencimiento,
               DATEDIFF(CURDATE(), s.fecha_vencimiento) AS dias_vencido,
               p.nombre AS plan_nombre, p.precio AS plan_precio
        FROM gym_socios s
        LEFT JOIN gym_planes p ON p.id = s.plan_id
        WHERE s.negocio_id=?
          AND s.estado='vencido'
          AND s.fecha_vencimiento IS NOT NULL
        ORDER BY s.fecha_vencimiento DESC
    ");
    $stmt->execute([$negocio_id]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
