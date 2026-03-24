<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo  = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $pdo);
$tipo = $_GET['tipo'] ?? 'resumen';

// Rango de fechas (default: últimos 30 días)
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-29 days'));

// Rango comparativo (mismo período anterior)
$dias   = (new DateTime($desde))->diff(new DateTime($hasta))->days + 1;
$desdePrev = date('Y-m-d', strtotime($desde . " -{$dias} days"));
$hastaPrev = date('Y-m-d', strtotime($hasta . " -{$dias} days"));

/* ═══════════════════════════════════════════════════════════
   RESUMEN GENERAL
═══════════════════════════════════════════════════════════ */
if ($tipo === 'resumen') {
    // Período actual
    $stmt = $pdo->prepare("
        SELECT
            COUNT(c.id)                        AS total_comandas,
            COALESCE(SUM(c.total), 0)          AS ingresos,
            COALESCE(AVG(NULLIF(c.total,0)),0) AS ticket_promedio,
            COALESCE(AVG(c.personas),0)        AS personas_promedio,
            COALESCE(SUM(
                TIMESTAMPDIFF(MINUTE, c.abierta_at, c.cerrada_at)
            ),0) / NULLIF(COUNT(c.id),0)       AS minutos_mesa_promedio
        FROM restaurant_comandas c
        WHERE c.negocio_id = :nid
          AND c.estado = 'cerrada'
          AND DATE(c.cerrada_at) BETWEEN :desde AND :hasta
    ");
    $stmt->execute([':nid'=>$negocioId,':desde'=>$desde,':hasta'=>$hasta]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    // Período anterior (comparativa)
    $stmt2 = $pdo->prepare("
        SELECT
            COUNT(c.id)               AS total_comandas,
            COALESCE(SUM(c.total), 0) AS ingresos
        FROM restaurant_comandas c
        WHERE c.negocio_id = :nid
          AND c.estado = 'cerrada'
          AND DATE(c.cerrada_at) BETWEEN :desde AND :hasta
    ");
    $stmt2->execute([':nid'=>$negocioId,':desde'=>$desdePrev,':hasta'=>$hastaPrev]);
    $prev = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Métodos de pago
    $stmtPago = $pdo->prepare("
        SELECT v.metodo_pago, COUNT(*) AS cantidad, COALESCE(SUM(v.total),0) AS total
        FROM ventas v
        JOIN restaurant_comandas c ON c.venta_id = v.id
        WHERE v.negocio_id = :nid
          AND v.estado = 'completada'
          AND DATE(c.cerrada_at) BETWEEN :desde AND :hasta
        GROUP BY v.metodo_pago
        ORDER BY total DESC
    ");
    $stmtPago->execute([':nid'=>$negocioId,':desde'=>$desde,':hasta'=>$hasta]);
    $pagos = $stmtPago->fetchAll(PDO::FETCH_ASSOC);

    $ingresosActual = (float)$actual['ingresos'];
    $ingresosPrev   = (float)$prev['ingresos'];
    $varIngresos    = $ingresosPrev > 0
        ? round(($ingresosActual - $ingresosPrev) / $ingresosPrev * 100, 1)
        : null;

    $comanActual = (int)$actual['total_comandas'];
    $comanPrev   = (int)$prev['total_comandas'];
    $varComan    = $comanPrev > 0
        ? round(($comanActual - $comanPrev) / $comanPrev * 100, 1)
        : null;

    Response::success('OK', [
        'ingresos'           => $ingresosActual,
        'ingresos_anterior'  => $ingresosPrev,
        'var_ingresos'       => $varIngresos,
        'total_comandas'     => $comanActual,
        'comandas_anterior'  => $comanPrev,
        'var_comandas'       => $varComan,
        'ticket_promedio'    => round((float)$actual['ticket_promedio'], 2),
        'personas_promedio'  => round((float)$actual['personas_promedio'], 1),
        'minutos_mesa_prom'  => round((float)$actual['minutos_mesa_promedio'], 0),
        'metodos_pago'       => $pagos,
        'periodo_dias'       => $dias,
    ]);
}

/* ═══════════════════════════════════════════════════════════
   VENTAS POR DÍA (gráfico de línea)
═══════════════════════════════════════════════════════════ */
if ($tipo === 'ventas_dia') {
    $stmt = $pdo->prepare("
        SELECT
            DATE(c.cerrada_at)        AS fecha,
            COUNT(c.id)               AS comandas,
            COALESCE(SUM(c.total), 0) AS total
        FROM restaurant_comandas c
        WHERE c.negocio_id = :nid
          AND c.estado = 'cerrada'
          AND DATE(c.cerrada_at) BETWEEN :desde AND :hasta
        GROUP BY DATE(c.cerrada_at)
        ORDER BY fecha ASC
    ");
    $stmt->execute([':nid'=>$negocioId,':desde'=>$desde,':hasta'=>$hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   PLATOS MÁS VENDIDOS
═══════════════════════════════════════════════════════════ */
if ($tipo === 'platos') {
    $limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
    $stmt = $pdo->prepare("
        SELECT
            ci.nombre_item                AS nombre,
            ci.producto_id,
            SUM(ci.cantidad)              AS unidades,
            COALESCE(SUM(ci.subtotal), 0) AS total,
            AVG(ci.precio_unit)           AS precio_promedio
        FROM restaurant_comanda_items ci
        JOIN restaurant_comandas c ON c.id = ci.comanda_id
        WHERE ci.negocio_id = :nid
          AND c.estado = 'cerrada'
          AND DATE(c.cerrada_at) BETWEEN :desde AND :hasta
          AND ci.estado_cocina != 'cancelado'
        GROUP BY ci.producto_id, ci.nombre_item
        ORDER BY unidades DESC
        LIMIT {$limit}
    ");
    $stmt->execute([':nid'=>$negocioId,':desde'=>$desde,':hasta'=>$hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/* ═══════════════════════════════════════════════════════════
   FRANJAS HORARIAS (hora pico)
═══════════════════════════════════════════════════════════ */
if ($tipo === 'franjas') {
    $stmt = $pdo->prepare("
        SELECT
            HOUR(c.abierta_at)            AS hora,
            COUNT(c.id)                   AS comandas,
            COALESCE(SUM(c.total), 0)     AS total,
            COALESCE(AVG(c.personas), 0)  AS personas_prom
        FROM restaurant_comandas c
        WHERE c.negocio_id = :nid
          AND c.estado = 'cerrada'
          AND DATE(c.cerrada_at) BETWEEN :desde AND :hasta
        GROUP BY HOUR(c.abierta_at)
        ORDER BY hora ASC
    ");
    $stmt->execute([':nid'=>$negocioId,':desde'=>$desde,':hasta'=>$hasta]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Rellenar horas vacías (0–23)
    $mapa = [];
    foreach ($rows as $r) $mapa[(int)$r['hora']] = $r;
    $resultado = [];
    for ($h = 0; $h <= 23; $h++) {
        $resultado[] = $mapa[$h] ?? ['hora'=>$h,'comandas'=>0,'total'=>0,'personas_prom'=>0];
    }
    Response::success('OK', $resultado);
}

/* ═══════════════════════════════════════════════════════════
   CATEGORÍAS MÁS VENDIDAS
═══════════════════════════════════════════════════════════ */
if ($tipo === 'categorias') {
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(cat.nombre, 'Sin categoría') AS categoria,
            SUM(ci.cantidad)                       AS unidades,
            COALESCE(SUM(ci.subtotal), 0)          AS total
        FROM restaurant_comanda_items ci
        JOIN restaurant_comandas c ON c.id = ci.comanda_id
        LEFT JOIN productos p ON p.id = ci.producto_id
        LEFT JOIN categorias cat ON cat.id = p.categoria_id
        WHERE ci.negocio_id = :nid
          AND c.estado = 'cerrada'
          AND DATE(c.cerrada_at) BETWEEN :desde AND :hasta
          AND ci.estado_cocina != 'cancelado'
        GROUP BY cat.id, cat.nombre
        ORDER BY total DESC
    ");
    $stmt->execute([':nid'=>$negocioId,':desde'=>$desde,':hasta'=>$hasta]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

Response::error('Tipo no válido', 400);
