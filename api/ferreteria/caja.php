<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET']);
[$negocioId] = Middleware::auth();
$pdo  = (new Database())->getConnection();
$type = $_GET['type'] ?? 'resumen';
$hoy  = date('Y-m-d');

// ── Resumen ────────────────────────────────────────────────────────────────────
if ($type === 'resumen') {
    // Presupuestos
    $stmt = $pdo->prepare("
        SELECT
            SUM(DATE(fecha_creacion) = :hoy)                        AS presupuestos_hoy,
            SUM(estado = 'enviado')                                  AS enviados,
            SUM(estado = 'aprobado')                                 AS aprobados,
            SUM(estado = 'borrador')                                 AS borradores,
            SUM(estado = 'rechazado')                                AS rechazados,
            IFNULL(SUM(CASE WHEN estado='aprobado' THEN total END), 0) AS total_aprobado,
            IFNULL(SUM(CASE WHEN estado='enviado'  THEN total END), 0) AS total_enviado
        FROM presupuestos
        WHERE negocio_id = :nid
          AND DATE_FORMAT(fecha_creacion,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')
    ");
    $stmt->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $pres = $stmt->fetch(PDO::FETCH_ASSOC);

    // Órdenes de compra
    $stmt2 = $pdo->prepare("
        SELECT
            SUM(estado = 'borrador')                                    AS oc_borradores,
            SUM(estado = 'enviada')                                     AS oc_enviadas,
            SUM(DATE(updated_at) = :hoy AND estado = 'recibida')        AS oc_recibidas_hoy,
            IFNULL(SUM(CASE WHEN estado='enviada' THEN total END), 0)   AS oc_pendiente
        FROM ordenes_compra
        WHERE negocio_id = :nid
    ");
    $stmt2->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $oc = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Stock
    $stmt3 = $pdo->prepare("
        SELECT
            COUNT(*) AS total_productos,
            SUM(stock <= stock_minimo AND stock_minimo > 0) AS stock_bajo,
            IFNULL(SUM(stock * precio_costo), 0) AS valor_inventario
        FROM productos
        WHERE negocio_id = :nid AND activo = 1
    ");
    $stmt3->execute([':nid' => $negocioId]);
    $stock = $stmt3->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', array_merge($pres, $oc, $stock));
}

// ── Presupuestos del día ───────────────────────────────────────────────────────
if ($type === 'presupuestos_hoy') {
    $stmt = $pdo->prepare("
        SELECT p.*
        FROM presupuestos p
        WHERE p.negocio_id = :nid
          AND (
              DATE(p.fecha_creacion) = :hoy
              OR (p.estado = 'enviado' AND p.fecha_vencimiento >= :hoy)
          )
        ORDER BY FIELD(p.estado,'enviado','aprobado','borrador','rechazado','vencido'), p.fecha_creacion DESC
        LIMIT 50
    ");
    $stmt->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Presupuestos por día (últimos N días) ──────────────────────────────────────
if ($type === 'presupuestos_dia') {
    $dias  = min((int)($_GET['dias'] ?? 30), 90);
    $desde = date('Y-m-d', strtotime("-{$dias} days"));
    $stmt  = $pdo->prepare("
        SELECT DATE(fecha_creacion) AS fecha, COUNT(*) AS cantidad, SUM(total) AS total
        FROM presupuestos
        WHERE negocio_id = :nid
          AND fecha_creacion >= :desde
          AND estado NOT IN ('rechazado')
        GROUP BY DATE(fecha_creacion)
        ORDER BY fecha
    ");
    $stmt->execute([':nid' => $negocioId, ':desde' => $desde]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por estado (presupuestos activos) ─────────────────────────────────────────
if ($type === 'por_estado') {
    $stmt = $pdo->prepare("
        SELECT estado, COUNT(*) AS cantidad, IFNULL(SUM(total),0) AS total
        FROM presupuestos
        WHERE negocio_id = :nid AND estado NOT IN ('rechazado','vencido')
        GROUP BY estado
        ORDER BY FIELD(estado,'aprobado','enviado','borrador')
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Órdenes de compra por proveedor ───────────────────────────────────────────
if ($type === 'oc_por_proveedor') {
    $stmt = $pdo->prepare("
        SELECT p.nombre AS proveedor, COUNT(oc.id) AS cantidad,
               SUM(oc.total) AS total
        FROM ordenes_compra oc
        JOIN proveedores p ON p.id = oc.proveedor_id
        WHERE oc.negocio_id = :nid
          AND oc.estado NOT IN ('cancelada')
          AND oc.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        GROUP BY oc.proveedor_id, p.nombre
        ORDER BY total DESC
        LIMIT 8
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Órdenes de compra por estado ──────────────────────────────────────────────
if ($type === 'oc_por_estado') {
    $stmt = $pdo->prepare("
        SELECT estado, COUNT(*) AS cantidad, IFNULL(SUM(total),0) AS total
        FROM ordenes_compra
        WHERE negocio_id = :nid AND estado != 'cancelada'
        GROUP BY estado
        ORDER BY FIELD(estado,'enviada','recibida','borrador')
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Stock bajo ────────────────────────────────────────────────────────────────
if ($type === 'stock_bajo') {
    $stmt = $pdo->prepare("
        SELECT nombre, codigo_barras, stock, stock_minimo, precio_venta,
               CASE WHEN stock = 0 THEN 'sin_stock' ELSE 'bajo' END AS nivel
        FROM productos
        WHERE negocio_id = :nid AND activo = 1
          AND stock <= stock_minimo AND stock_minimo > 0
        ORDER BY stock ASC
        LIMIT 20
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
