<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
Middleware::method('GET');

[$negocioId] = Middleware::auth();

try {
    $db   = new Database();
    $conn = $db->getConnection();

    // Total de productos del negocio
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE negocio_id = :negocio_id AND activo = 1");
    $stmt->execute([':negocio_id' => $negocioId]);
    $productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de clientes del negocio
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM clientes WHERE negocio_id = :negocio_id AND activo = 1");
    $stmt->execute([':negocio_id' => $negocioId]);
    $clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Ventas del día del negocio
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_ventas,
            COALESCE(SUM(total), 0) as total_monto
        FROM ventas 
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) = CURDATE()
          AND estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ventas del mes del negocio
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_ventas,
            COALESCE(SUM(total), 0) as total_monto
        FROM ventas 
        WHERE negocio_id = :negocio_id
          AND MONTH(fecha_venta) = MONTH(CURDATE()) 
          AND YEAR(fecha_venta) = YEAR(CURDATE())
          AND estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $ventasMes = $stmt->fetch(PDO::FETCH_ASSOC);

    // Caja activa del negocio
    $stmt = $conn->prepare("
        SELECT 
            id,
            monto_inicial,
            fecha_apertura
        FROM cajas
        WHERE negocio_id = :negocio_id
          AND estado = 'abierta'
        ORDER BY fecha_apertura DESC 
        LIMIT 1
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $cajaActiva = $stmt->fetch(PDO::FETCH_ASSOC);

    // Gastos del día
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(monto), 0) as total FROM gastos
        WHERE negocio_id = :negocio_id AND DATE(fecha_gasto) = CURDATE()
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $gastosHoy = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Ganancia neta del día (ventas - costo productos)
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(dv.cantidad * (dv.precio_unitario - COALESCE(p.precio_costo,0))),0) as ganancia
        FROM detalle_ventas dv
        INNER JOIN ventas v ON v.id = dv.venta_id
        LEFT JOIN productos p ON p.id = dv.producto_id
        WHERE v.negocio_id = :negocio_id
          AND DATE(v.fecha_venta) = CURDATE()
          AND v.estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $gananciaNeta = (float)$stmt->fetch(PDO::FETCH_ASSOC)['ganancia'];

    // Productos con stock bajo (stock <= 5)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM productos
        WHERE negocio_id = :negocio_id AND activo = 1 AND stock <= 5 AND stock IS NOT NULL
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $stockBajo = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Últimas 5 ventas del día
    $stmt = $conn->prepare("
        SELECT v.id, v.total, v.metodo_pago, v.fecha_venta,
               COUNT(dv.id) as items
        FROM ventas v
        LEFT JOIN detalle_ventas dv ON dv.venta_id = v.id
        WHERE v.negocio_id = :negocio_id
          AND DATE(v.fecha_venta) = CURDATE()
          AND v.estado != 'cancelada'
        GROUP BY v.id
        ORDER BY v.fecha_venta DESC
        LIMIT 5
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $ultimasVentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ventas por hora hoy (para mini gráfico)
    $stmt = $conn->prepare("
        SELECT HOUR(fecha_venta) as hora, COALESCE(SUM(total),0) as total
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) = CURDATE()
          AND estado != 'cancelada'
        GROUP BY HOUR(fecha_venta)
        ORDER BY hora ASC
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $ventasPorHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ventas de ayer (para comparar)
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total),0) as monto, COUNT(*) as cantidad
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
          AND estado != 'cancelada'
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $ventasAyer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Métodos de pago de hoy
    $stmt = $conn->prepare("
        SELECT metodo_pago, COUNT(*) as cantidad, SUM(total) as total
        FROM ventas
        WHERE negocio_id = :negocio_id
          AND DATE(fecha_venta) = CURDATE()
          AND estado != 'cancelada'
        GROUP BY metodo_pago
        ORDER BY total DESC
    ");
    $stmt->execute([':negocio_id' => $negocioId]);
    $metodosPago = $stmt->fetchAll(PDO::FETCH_ASSOC);

    Response::success('Estadísticas obtenidas correctamente', [
        'productos' => (int)$productos,
        'clientes' => (int)$clientes,
        'stock_bajo' => $stockBajo,
        'ventas_hoy' => [
            'cantidad' => (int)$ventasHoy['total_ventas'],
            'monto' => (float)$ventasHoy['total_monto']
        ],
        'ventas_mes' => [
            'cantidad' => (int)$ventasMes['total_ventas'],
            'monto' => (float)$ventasMes['total_monto']
        ],
        'ventas_ayer' => [
            'cantidad' => (int)$ventasAyer['cantidad'],
            'monto' => (float)$ventasAyer['monto']
        ],
        'gastos_hoy' => $gastosHoy,
        'ganancia_neta_hoy' => $gananciaNeta,
        'caja_activa' => $cajaActiva ? [
            'id' => (int)$cajaActiva['id'],
            'monto_inicial' => (float)$cajaActiva['monto_inicial'],
            'fecha_apertura' => $cajaActiva['fecha_apertura']
        ] : null,
        'ultimas_ventas' => $ultimasVentas,
        'ventas_por_hora' => $ventasPorHora,
        'metodos_pago_hoy' => $metodosPago,
    ]);

} catch (Exception $e) {
    Response::error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
}
