<?php
/**
 * api/optica/alertas.php
 * GET → devuelve alertas urgentes del módulo óptica:
 *   - Pedidos listos sin entregar > 3 días
 *   - Pedidos en laboratorio con fecha estimada vencida
 *   - Pedidos en laboratorio sin fecha estimada hace > 5 días
 *   - Saldos pendientes en pedidos listos/lab
 */
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET']);
Middleware::method('GET');

[$negocioId] = Middleware::auth();
$pdo = (new Database())->getConnection();
$hoy = date('Y-m-d');

// 1. Listos sin entregar (estado='listo' actualizado hace ≥ 3 días)
$stLisios = $pdo->prepare("
    SELECT p.id, p.armazon, p.lente_tipo, p.total, p.saldo,
           CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
           c.telefono  AS cliente_tel,
           p.updated_at,
           DATEDIFF(CURDATE(), DATE(p.updated_at)) AS dias_esperando
    FROM optica_pedidos p
    JOIN optica_clientes c ON c.id = p.cliente_id
    WHERE p.negocio_id = :nid
      AND p.estado = 'listo'
      AND DATEDIFF(CURDATE(), DATE(p.updated_at)) >= 3
    ORDER BY dias_esperando DESC
");
$stLisios->execute([':nid' => $negocioId]);
$listos = $stLisios->fetchAll(PDO::FETCH_ASSOC);

// 2. En laboratorio con fecha estimada vencida
$stRetras = $pdo->prepare("
    SELECT p.id, p.armazon, p.laboratorio, p.fecha_entrega_est,
           CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
           c.telefono  AS cliente_tel,
           DATEDIFF(CURDATE(), p.fecha_entrega_est) AS dias_retraso
    FROM optica_pedidos p
    JOIN optica_clientes c ON c.id = p.cliente_id
    WHERE p.negocio_id = :nid
      AND p.estado = 'laboratorio'
      AND p.fecha_entrega_est IS NOT NULL
      AND p.fecha_entrega_est < :hoy
    ORDER BY dias_retraso DESC
");
$stRetras->execute([':nid' => $negocioId, ':hoy' => $hoy]);
$retrasados = $stRetras->fetchAll(PDO::FETCH_ASSOC);

// 3. En laboratorio sin fecha estimada hace > 5 días
$stSinFecha = $pdo->prepare("
    SELECT p.id, p.armazon, p.laboratorio,
           CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
           c.telefono  AS cliente_tel,
           DATEDIFF(CURDATE(), DATE(p.created_at)) AS dias_en_lab
    FROM optica_pedidos p
    JOIN optica_clientes c ON c.id = p.cliente_id
    WHERE p.negocio_id = :nid
      AND p.estado = 'laboratorio'
      AND (p.fecha_entrega_est IS NULL OR p.fecha_entrega_est = '')
      AND DATEDIFF(CURDATE(), DATE(p.created_at)) > 5
    ORDER BY dias_en_lab DESC
");
$stSinFecha->execute([':nid' => $negocioId]);
$sinFecha = $stSinFecha->fetchAll(PDO::FETCH_ASSOC);

// 4. Pedidos listos/laboratorio con saldo pendiente
$stSaldo = $pdo->prepare("
    SELECT p.id, p.armazon, p.estado, p.saldo, p.total,
           CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
           c.telefono  AS cliente_tel
    FROM optica_pedidos p
    JOIN optica_clientes c ON c.id = p.cliente_id
    WHERE p.negocio_id = :nid
      AND p.saldo > 0
      AND p.estado IN ('listo','laboratorio','pendiente')
    ORDER BY p.saldo DESC
    LIMIT 50
");
$stSaldo->execute([':nid' => $negocioId]);
$conSaldo = $stSaldo->fetchAll(PDO::FETCH_ASSOC);

Response::success('OK', [
    'listos_sin_entregar' => ['count' => count($listos),     'items' => $listos],
    'lab_retrasados'      => ['count' => count($retrasados), 'items' => $retrasados],
    'lab_sin_fecha'       => ['count' => count($sinFecha),   'items' => $sinFecha],
    'con_saldo'           => ['count' => count($conSaldo),   'items' => $conSaldo],
    'total_alertas'       => count($listos) + count($retrasados) + count($sinFecha),
]);
