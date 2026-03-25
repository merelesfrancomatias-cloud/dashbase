<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET']);
[$negocioId] = Middleware::auth();
$pdo  = (new Database())->getConnection();
$type = $_GET['type'] ?? 'resumen';
$hoy  = date('Y-m-d');

// ── Resumen del día ────────────────────────────────────────────────────────────
if ($type === 'resumen') {
    // Recetas de hoy y del mes
    $stmt = $pdo->prepare("
        SELECT
            SUM(DATE(updated_at) = :hoy AND estado = 'despachada') AS despachadas_hoy,
            SUM(estado = 'pendiente') AS pendientes,
            SUM(DATE_FORMAT(updated_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') AND estado = 'despachada') AS despachadas_mes,
            SUM(estado = 'vencida' OR (estado = 'pendiente' AND fecha_vencimiento < CURDATE())) AS recetas_vencidas,
            COUNT(*) AS total_mes
        FROM farmacia_recetas
        WHERE negocio_id = :nid
          AND DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')
    ");
    $stmt->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $recetas = $stmt->fetch(PDO::FETCH_ASSOC);

    // Alertas: vencimientos próximos 30 días
    $stmt2 = $pdo->prepare("
        SELECT
            SUM(fecha_vencimiento < CURDATE()) AS productos_vencidos,
            SUM(fecha_vencimiento >= CURDATE() AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)) AS proximos_vencer
        FROM productos
        WHERE negocio_id = :nid AND activo = 1 AND fecha_vencimiento IS NOT NULL
    ");
    $stmt2->execute([':nid' => $negocioId]);
    $venc = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Stock bajo
    $stmt3 = $pdo->prepare("
        SELECT COUNT(*) AS stock_bajo
        FROM productos
        WHERE negocio_id = :nid AND activo = 1 AND stock <= stock_minimo AND stock_minimo > 0
    ");
    $stmt3->execute([':nid' => $negocioId]);
    $stockRow = $stmt3->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', array_merge($recetas, $venc, $stockRow));
}

// ── Recetas despachadas hoy ────────────────────────────────────────────────────
if ($type === 'recetas_hoy') {
    $stmt = $pdo->prepare("
        SELECT r.*, COUNT(i.id) AS cantidad_items
        FROM farmacia_recetas r
        LEFT JOIN farmacia_receta_items i ON i.receta_id = r.id
        WHERE r.negocio_id = :nid
          AND r.estado = 'despachada'
          AND DATE(r.updated_at) = :hoy
        GROUP BY r.id
        ORDER BY r.updated_at DESC
    ");
    $stmt->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Recetas despachadas por día (últimos N días) ───────────────────────────────
if ($type === 'recetas_dia') {
    $dias  = min((int)($_GET['dias'] ?? 30), 90);
    $desde = date('Y-m-d', strtotime("-{$dias} days"));
    $stmt  = $pdo->prepare("
        SELECT DATE(updated_at) AS fecha, COUNT(*) AS cantidad
        FROM farmacia_recetas
        WHERE negocio_id = :nid
          AND estado = 'despachada'
          AND updated_at >= :desde
        GROUP BY DATE(updated_at)
        ORDER BY fecha
    ");
    $stmt->execute([':nid' => $negocioId, ':desde' => $desde]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Por obra social ────────────────────────────────────────────────────────────
if ($type === 'por_obra_social') {
    $stmt = $pdo->prepare("
        SELECT IFNULL(NULLIF(obra_social,''),'Particular') AS obra_social,
               COUNT(*) AS cantidad
        FROM farmacia_recetas
        WHERE negocio_id = :nid AND estado = 'despachada'
        GROUP BY obra_social
        ORDER BY cantidad DESC
        LIMIT 8
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Alertas de vencimiento ─────────────────────────────────────────────────────
if ($type === 'alertas_vencimiento') {
    $dias  = min((int)($_GET['dias'] ?? 30), 90);
    $stmt  = $pdo->prepare("
        SELECT p.nombre, p.codigo_barras, p.stock, p.fecha_vencimiento,
               DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_restantes,
               CASE WHEN p.fecha_vencimiento < CURDATE() THEN 'vencido' ELSE 'proximo' END AS estado
        FROM productos p
        WHERE p.negocio_id = :nid AND p.activo = 1
          AND p.fecha_vencimiento IS NOT NULL
          AND p.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
        ORDER BY p.fecha_vencimiento ASC
        LIMIT 30
    ");
    $stmt->execute([':nid' => $negocioId, ':dias' => $dias]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── Alertas de stock bajo ─────────────────────────────────────────────────────
if ($type === 'alertas_stock') {
    $stmt = $pdo->prepare("
        SELECT nombre, codigo_barras, stock, stock_minimo,
               CASE WHEN stock = 0 THEN 'sin_stock' ELSE 'bajo' END AS nivel
        FROM productos
        WHERE negocio_id = :nid AND activo = 1
          AND stock <= stock_minimo AND stock_minimo > 0
        ORDER BY stock ASC
        LIMIT 30
    ");
    $stmt->execute([':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
