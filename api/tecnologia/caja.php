<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET']);
[$negocioId] = Middleware::auth();
$pdo  = (new Database())->getConnection();
$type = $_GET['type'] ?? 'resumen';
$hoy  = date('Y-m-d');

// ── Resumen ────────────────────────────────────────────────────────────────────
if ($type === 'resumen') {
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_ordenes,
            SUM(estado = 'listo')               AS listas,
            SUM(estado IN ('en_reparacion','diagnosticando')) AS en_taller,
            SUM(estado = 'esperando_repuesto')  AS sin_repuesto,
            SUM(prioridad = 'urgente' AND estado NOT IN ('entregado','cancelado','sin_reparacion')) AS urgentes,
            SUM(saldo > 0 AND estado NOT IN ('cancelado','sin_reparacion','entregado')) AS con_saldo,
            IFNULL(SUM(CASE WHEN saldo > 0 AND estado NOT IN ('cancelado','sin_reparacion','entregado') THEN saldo END), 0) AS monto_saldo
        FROM tec_ordenes
        WHERE negocio_id = :nid
    ");
    $stmt->execute([':nid' => $negocioId]);
    $kpi = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("
        SELECT
            COUNT(*) AS entregados_hoy,
            IFNULL(SUM(total), 0)   AS ingresos_hoy
        FROM tec_ordenes
        WHERE negocio_id = :nid
          AND estado = 'entregado'
          AND DATE(fecha_entrega) = :hoy
    ");
    $stmt2->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $hoyRow = $stmt2->fetch(PDO::FETCH_ASSOC);

    $stmt3 = $pdo->prepare("
        SELECT COUNT(*) AS ingresadas_hoy
        FROM tec_ordenes
        WHERE negocio_id = :nid AND DATE(fecha_ingreso) = :hoy
    ");
    $stmt3->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $ingrRow = $stmt3->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', array_merge($kpi, $hoyRow, $ingrRow));
}

// ── Órdenes del día (listas + entregadas hoy) ─────────────────────────────────
if ($type === 'ordenes_hoy') {
    $stmt = $pdo->prepare("
        SELECT o.*,
               CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
               c.telefono AS cliente_tel
        FROM tec_ordenes o
        JOIN tec_clientes c ON c.id = o.cliente_id
        WHERE o.negocio_id = :nid
          AND (
              (o.estado = 'listo') OR
              (o.estado = 'entregado' AND DATE(o.fecha_entrega) = :hoy)
          )
        ORDER BY FIELD(o.estado,'listo','entregado'), o.prioridad DESC, o.updated_at DESC
    ");
    $stmt->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Ingresos por día ───────────────────────────────────────────────────────────
if ($type === 'ingresos_dia') {
    $dias  = min((int)($_GET['dias'] ?? 30), 90);
    $desde = date('Y-m-d', strtotime("-{$dias} days"));
    $stmt  = $pdo->prepare("
        SELECT DATE(fecha_entrega) AS fecha, COUNT(*) AS cantidad, SUM(total) AS total
        FROM tec_ordenes
        WHERE negocio_id = :nid
          AND estado = 'entregado'
          AND fecha_entrega >= :desde
        GROUP BY DATE(fecha_entrega)
        ORDER BY fecha
    ");
    $stmt->execute([':nid' => $negocioId, ':desde' => $desde]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por tipo de equipo ─────────────────────────────────────────────────────────
if ($type === 'por_tipo') {
    $stmt = $pdo->prepare("
        SELECT equipo_tipo, COUNT(*) AS cantidad, IFNULL(SUM(total),0) AS total
        FROM tec_ordenes
        WHERE negocio_id = :nid AND estado NOT IN ('cancelado','sin_reparacion')
        GROUP BY equipo_tipo
        ORDER BY cantidad DESC
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por estado actual ──────────────────────────────────────────────────────────
if ($type === 'por_estado') {
    $stmt = $pdo->prepare("
        SELECT estado, COUNT(*) AS cantidad
        FROM tec_ordenes
        WHERE negocio_id = :nid AND estado NOT IN ('cancelado')
        GROUP BY estado
        ORDER BY FIELD(estado,'listo','en_reparacion','diagnosticando','esperando_repuesto','ingresado','entregado','sin_reparacion')
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por técnico ───────────────────────────────────────────────────────────────
if ($type === 'por_tecnico') {
    $stmt = $pdo->prepare("
        SELECT IFNULL(tecnico,'Sin asignar') AS tecnico, COUNT(*) AS cantidad,
               SUM(estado='entregado') AS entregadas, SUM(total) AS total
        FROM tec_ordenes
        WHERE negocio_id = :nid AND estado NOT IN ('cancelado')
        GROUP BY tecnico
        ORDER BY cantidad DESC
        LIMIT 10
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
