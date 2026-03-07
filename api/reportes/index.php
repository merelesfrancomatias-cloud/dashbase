<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
Middleware::method('GET');

[$negocioId] = Middleware::auth();

try {
    $db   = new Database();
    $conn = $db->getConnection();

    // Obtener parámetros
    $periodo = $_GET['periodo'] ?? 'mes';
    $fecha_desde = null;
    $fecha_hasta = null;
    
    // Calcular fechas según período
    if ($periodo === 'personalizado') {
        $fecha_desde = $_GET['fecha_desde'] ?? null;
        $fecha_hasta = $_GET['fecha_hasta'] ?? null;
        
        if (!$fecha_desde || !$fecha_hasta) {
            Response::error('Fechas personalizadas requeridas');
        }
    } else {
        $fecha_hasta = date('Y-m-d');
        
        switch ($periodo) {
            case 'hoy':
                $fecha_desde = date('Y-m-d');
                break;
            case 'ayer':
                $fecha_desde = date('Y-m-d', strtotime('-1 day'));
                $fecha_hasta = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'semana':
                $fecha_desde = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'mes':
                $fecha_desde = date('Y-m-01'); // Primer día del mes actual
                break;
            case 'mes_anterior':
                $fecha_desde = date('Y-m-01', strtotime('first day of last month'));
                $fecha_hasta = date('Y-m-t', strtotime('last day of last month'));
                break;
            case 'trimestre':
                $fecha_desde = date('Y-m-d', strtotime('-3 months'));
                break;
            case 'año':
            case 'anio':
                $fecha_desde = date('Y-01-01');
                break;
            default:
                $fecha_desde = date('Y-m-01');
        }
    }
    
    // MÉTRICAS PRINCIPALES
    
    // Total de ventas
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total), 0) as total_ventas
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde, ':fecha_hasta' => $fecha_hasta]);
    $total_ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total_ventas'];
    
    // Ganancias netas (ventas - gastos - costo de productos)
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(dv.cantidad * (dv.precio_unitario - COALESCE(p.precio_costo, 0))), 0) as ganancia_productos
        FROM detalle_ventas dv
        INNER JOIN ventas v ON v.id = dv.venta_id
        LEFT JOIN productos p ON p.id = dv.producto_id
        WHERE v.negocio_id = :negocio_id
          AND DATE(v.fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND v.estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde, ':fecha_hasta' => $fecha_hasta]);
    $ganancia_productos = $stmt->fetch(PDO::FETCH_ASSOC)['ganancia_productos'];
    
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(monto), 0) as total_gastos
        FROM gastos
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_gasto) BETWEEN :fecha_desde AND :fecha_hasta
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde, ':fecha_hasta' => $fecha_hasta]);
    $total_gastos = $stmt->fetch(PDO::FETCH_ASSOC)['total_gastos'];
    
    $ganancias_netas = $ganancia_productos - $total_gastos;
    
    // Total de tickets vendidos
    $stmt = $conn->prepare("
        SELECT COUNT(*) as tickets
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde, ':fecha_hasta' => $fecha_hasta]);
    $tickets_vendidos = $stmt->fetch(PDO::FETCH_ASSOC)['tickets'];
    
    // Ticket promedio
    $ticket_promedio = $tickets_vendidos > 0 ? $total_ventas / $tickets_vendidos : 0;
    
    // TENDENCIAS (comparación con período anterior)
    $dias_periodo = (strtotime($fecha_hasta) - strtotime($fecha_desde)) / 86400 + 1;
    $fecha_desde_anterior = date('Y-m-d', strtotime($fecha_desde . " -$dias_periodo days"));
    $fecha_hasta_anterior = date('Y-m-d', strtotime($fecha_hasta . " -$dias_periodo days"));
    
    // Ventas período anterior
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total), 0) as total_ventas
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde_anterior, ':fecha_hasta' => $fecha_hasta_anterior]);
    $total_ventas_anterior = $stmt->fetch(PDO::FETCH_ASSOC)['total_ventas'];
    
    $trend_ventas = $total_ventas_anterior > 0 
        ? (($total_ventas - $total_ventas_anterior) / $total_ventas_anterior) * 100 
        : 0;
    
    // Ganancias período anterior
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(dv.cantidad * (dv.precio_unitario - COALESCE(p.precio_costo, 0))), 0) as ganancia_productos
        FROM detalle_ventas dv
        INNER JOIN ventas v ON v.id = dv.venta_id
        LEFT JOIN productos p ON p.id = dv.producto_id
        WHERE v.negocio_id = :negocio_id
          AND DATE(v.fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND v.estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde_anterior, ':fecha_hasta' => $fecha_hasta_anterior]);
    $ganancia_productos_anterior = $stmt->fetch(PDO::FETCH_ASSOC)['ganancia_productos'];
    
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(monto), 0) as total_gastos
        FROM gastos
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_gasto) BETWEEN :fecha_desde AND :fecha_hasta
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde_anterior, ':fecha_hasta' => $fecha_hasta_anterior]);
    $total_gastos_anterior = $stmt->fetch(PDO::FETCH_ASSOC)['total_gastos'];
    
    $ganancias_netas_anterior = $ganancia_productos_anterior - $total_gastos_anterior;
    
    $trend_ganancias = $ganancias_netas_anterior > 0 
        ? (($ganancias_netas - $ganancias_netas_anterior) / $ganancias_netas_anterior) * 100 
        : 0;
    
    // Tickets período anterior
    $stmt = $conn->prepare("
        SELECT COUNT(*) as tickets
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde_anterior, ':fecha_hasta' => $fecha_hasta_anterior]);
    $tickets_anterior = $stmt->fetch(PDO::FETCH_ASSOC)['tickets'];
    
    $trend_tickets = $tickets_anterior > 0 
        ? (($tickets_vendidos - $tickets_anterior) / $tickets_anterior) * 100 
        : 0;
    
    // Ticket promedio anterior
    $ticket_promedio_anterior = $tickets_anterior > 0 ? $total_ventas_anterior / $tickets_anterior : 0;
    
    $trend_promedio = $ticket_promedio_anterior > 0 
        ? (($ticket_promedio - $ticket_promedio_anterior) / $ticket_promedio_anterior) * 100 
        : 0;
    
    // VENTAS POR DÍA (para gráfico)
    $stmt = $conn->prepare("
        SELECT 
            DATE(fecha_venta) as fecha,
            SUM(total) as total
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND estado != 'cancelada'
        GROUP BY DATE(fecha_venta)
        ORDER BY fecha ASC
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde, ':fecha_hasta' => $fecha_hasta]);
    $ventas_por_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas para mejor visualización
    foreach ($ventas_por_dia as &$venta) {
        $venta['fecha'] = date('d/m', strtotime($venta['fecha']));
    }
    
    // VENTAS POR CATEGORÍA
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(c.nombre, 'Sin categoría') as categoria,
            SUM(dv.cantidad * dv.precio_unitario) as total
        FROM detalle_ventas dv
        INNER JOIN ventas v ON v.id = dv.venta_id
        LEFT JOIN productos p ON p.id = dv.producto_id
        LEFT JOIN categorias c ON c.id = p.categoria_id
        WHERE v.negocio_id = :negocio_id
          AND DATE(v.fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND v.estado != 'cancelada'
        GROUP BY c.id, c.nombre
        ORDER BY total DESC
        LIMIT 6
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde, ':fecha_hasta' => $fecha_hasta]);
    $ventas_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // VENTAS POR MÉTODO DE PAGO
    $stmt = $conn->prepare("
        SELECT 
            metodo_pago as metodo,
            SUM(total) as total
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta
          AND estado != 'cancelada'
        GROUP BY metodo_pago
        ORDER BY total DESC
    ");
    $stmt->execute([':negocio_id' => $negocioId, ':fecha_desde' => $fecha_desde, ':fecha_hasta' => $fecha_hasta]);
    $ventas_por_metodo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Respuesta
    Response::success('Reportes obtenidos correctamente', [
        'metricas' => [
            'total_ventas' => floatval($total_ventas),
            'ganancias_netas' => floatval($ganancias_netas),
            'tickets_vendidos' => intval($tickets_vendidos),
            'ticket_promedio' => floatval($ticket_promedio),
            'trend_ventas' => floatval($trend_ventas),
            'trend_ganancias' => floatval($trend_ganancias),
            'trend_tickets' => floatval($trend_tickets),
            'trend_promedio' => floatval($trend_promedio)
        ],
        'ventas_por_dia' => $ventas_por_dia,
        'ventas_por_categoria' => $ventas_por_categoria,
        'ventas_por_metodo' => $ventas_por_metodo,
        'periodo' => [
            'tipo' => $periodo,
            'desde' => $fecha_desde,
            'hasta' => $fecha_hasta
        ]
    ]);
    
} catch (Exception $e) {
    Response::error('Error al obtener reportes: ' . $e->getMessage());
}
