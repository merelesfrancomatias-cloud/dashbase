<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET']);
[$negocioId] = Middleware::auth();
$pdo  = (new Database())->getConnection();
$type = $_GET['type'] ?? 'resumen';
$hoy  = date('Y-m-d');

// ── Resumen del día ────────────────────────────────────────────────────────────
if ($type === 'resumen') {
    // KPIs generales
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_pedidos,
            SUM(estado = 'listo')       AS listos,
            SUM(estado = 'laboratorio') AS en_laboratorio,
            SUM(estado = 'pendiente')   AS pendientes,
            SUM(estado = 'presupuesto') AS presupuestos,
            SUM(saldo > 0 AND estado NOT IN ('cancelado','presupuesto','entregado')) AS con_saldo,
            IFNULL(SUM(CASE WHEN saldo > 0 AND estado NOT IN ('cancelado','presupuesto','entregado') THEN saldo END), 0) AS monto_saldo
        FROM optica_pedidos
        WHERE negocio_id = :nid
    ");
    $stmt->execute([':nid' => $negocioId]);
    $kpi = $stmt->fetch(PDO::FETCH_ASSOC);

    // Entregados hoy e ingresos del día
    $stmt2 = $pdo->prepare("
        SELECT
            COUNT(*) AS entregados_hoy,
            IFNULL(SUM(total), 0) AS ingresos_hoy,
            IFNULL(SUM(seña), 0)  AS senas_hoy
        FROM optica_pedidos
        WHERE negocio_id = :nid
          AND estado = 'entregado'
          AND DATE(fecha_entrega) = :hoy
    ");
    $stmt2->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $hoyRow = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Nuevos clientes hoy
    $stmt3 = $pdo->prepare("
        SELECT COUNT(*) AS clientes_nuevos
        FROM optica_clientes
        WHERE negocio_id = :nid AND DATE(created_at) = :hoy AND activo = 1
    ");
    $stmt3->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $cliRow = $stmt3->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', array_merge($kpi, $hoyRow, $cliRow));
}

// ── Pedidos del día (entregados hoy + listos para retirar) ─────────────────────
if ($type === 'pedidos_hoy') {
    $stmt = $pdo->prepare("
        SELECT p.*,
               CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
               c.telefono AS cliente_tel, c.obra_social
        FROM optica_pedidos p
        JOIN optica_clientes c ON c.id = p.cliente_id
        WHERE p.negocio_id = :nid
          AND (
              (p.estado = 'listo') OR
              (p.estado = 'entregado' AND DATE(p.fecha_entrega) = :hoy)
          )
        ORDER BY FIELD(p.estado,'listo','entregado'), p.updated_at DESC
    ");
    $stmt->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Ingresos por día (últimos N días) ─────────────────────────────────────────
if ($type === 'ingresos_dia') {
    $dias   = min((int)($_GET['dias'] ?? 30), 90);
    $desde  = date('Y-m-d', strtotime("-{$dias} days"));
    $stmt = $pdo->prepare("
        SELECT DATE(fecha_entrega) AS fecha, COUNT(*) AS cantidad, SUM(total) AS total
        FROM optica_pedidos
        WHERE negocio_id = :nid
          AND estado = 'entregado'
          AND fecha_entrega >= :desde
        GROUP BY DATE(fecha_entrega)
        ORDER BY fecha
    ");
    $stmt->execute([':nid' => $negocioId, ':desde' => $desde]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por tipo de lente (pedidos activos + entregados) ──────────────────────────
if ($type === 'por_tipo') {
    $stmt = $pdo->prepare("
        SELECT lente_tipo, COUNT(*) AS cantidad, IFNULL(SUM(total),0) AS total
        FROM optica_pedidos
        WHERE negocio_id = :nid AND estado NOT IN ('cancelado','presupuesto')
        GROUP BY lente_tipo
        ORDER BY cantidad DESC
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por estado actual ──────────────────────────────────────────────────────────
if ($type === 'por_estado') {
    $stmt = $pdo->prepare("
        SELECT estado, COUNT(*) AS cantidad
        FROM optica_pedidos
        WHERE negocio_id = :nid AND estado NOT IN ('cancelado')
        GROUP BY estado
        ORDER BY FIELD(estado,'listo','laboratorio','pendiente','presupuesto','entregado')
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por día de semana ──────────────────────────────────────────────────────────
if ($type === 'dias_semana') {
    $stmt = $pdo->prepare("
        SELECT DAYOFWEEK(fecha_entrega) AS dow, COUNT(*) AS cantidad, SUM(total) AS total
        FROM optica_pedidos
        WHERE negocio_id = :nid
          AND estado = 'entregado'
          AND fecha_entrega >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        GROUP BY DAYOFWEEK(fecha_entrega)
        ORDER BY dow
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
