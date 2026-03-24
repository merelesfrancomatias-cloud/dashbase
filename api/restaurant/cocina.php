<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $pdo);
$method = $_SERVER['REQUEST_METHOD'];

// La pantalla de cocina (KDS) muestra los ítems pendientes/en preparación
// agrupados por sector_cocina, en tiempo real (polling cada 10s)

if ($method === 'GET') {
    $sector = $_GET['sector'] ?? null;  // filtrar por sector: parrilla, barra, etc.
    $where  = ["ci.negocio_id = :nid", "ci.estado_cocina IN ('pendiente','en_preparacion')"];
    $params = [':nid' => $negocioId];

    if ($sector) { $where[] = "ci.sector_cocina = :sec"; $params[':sec'] = $sector; }

    // Ítems agrupados con info de mesa y comanda
    $stmt = $pdo->prepare("
        SELECT
            ci.id, ci.comanda_id, ci.producto_id, ci.nombre_item,
            ci.cantidad, ci.observaciones, ci.estado_cocina,
            ci.sector_cocina, ci.enviado_at, ci.created_at,
            c.numero  AS comanda_numero,
            m.numero  AS mesa_numero,
            s.nombre  AS sector_salon,
            TIMESTAMPDIFF(MINUTE, ci.enviado_at, NOW()) AS minutos_espera
        FROM restaurant_comanda_items ci
        JOIN restaurant_comandas  c ON c.id = ci.comanda_id
        JOIN restaurant_mesas     m ON m.id = c.mesa_id
        LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY ci.enviado_at ASC, ci.id ASC
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sectores de cocina para el negocio
    $sectores = $pdo->prepare("SELECT * FROM restaurant_cocina_sectores WHERE negocio_id=:nid AND activo=1 ORDER BY orden");
    $sectores->execute([':nid' => $negocioId]);

    // Agrupar ítems por sector_cocina
    $agrupados = [];
    foreach ($items as $item) {
        $agrupados[$item['sector_cocina']][] = $item;
    }

    Response::success('OK', [
        'items'    => $items,
        'agrupado' => $agrupados,
        'sectores_activos' => $sectores->fetchAll(PDO::FETCH_ASSOC),
        'total_pendientes' => count($items),
    ]);
}

// PUT: cambiar estado de un ítem desde la pantalla de cocina
if ($method === 'PUT') {
    $d   = json_decode(file_get_contents('php://input'), true);
    $id  = (int)($d['item_id'] ?? 0);
    $est = $d['estado'] ?? '';

    $validos = ['en_preparacion','listo','entregado','cancelado'];
    if (!$id || !in_array($est, $validos)) Response::error('Datos inválidos', 400);

    $pdo->prepare("
        UPDATE restaurant_comanda_items SET
            estado_cocina = :est,
            listo_at      = IF(:est2='listo',     NOW(), listo_at),
            entregado_at  = IF(:est3='entregado', NOW(), entregado_at)
        WHERE id = :id AND negocio_id = :nid
    ")->execute([
        ':est'  => $est,
        ':est2' => $est,
        ':est3' => $est,
        ':id'   => $id,
        ':nid'  => $negocioId,
    ]);

    // Obtener comanda_id y revisar si todos los ítems están listos
    $row = $pdo->query("SELECT comanda_id FROM restaurant_comanda_items WHERE id={$id}")->fetch();
    if ($row) {
        $cid     = (int)$row['comanda_id'];
        $pending = $pdo->query("SELECT COUNT(*) FROM restaurant_comanda_items WHERE comanda_id={$cid} AND estado_cocina IN ('pendiente','en_preparacion')")->fetchColumn();
        if ($pending == 0) {
            $pdo->prepare("UPDATE restaurant_comandas SET estado='lista' WHERE id=:id")->execute([':id' => $cid]);
        }
    }

    Response::success(null, 'Estado actualizado');
}
