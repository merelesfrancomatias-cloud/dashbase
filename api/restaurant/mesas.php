<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// GET /api/restaurant/mesas/
// ?sector_id=X  → filtrar por sector
// ?id=X         → detalle de una mesa + comanda activa
// POST          → crear mesa
// PUT           → actualizar estado / datos
// DELETE        → eliminar mesa

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Detalle de mesa + comanda activa si tiene
        $stmt = $pdo->prepare("
            SELECT m.*, s.nombre AS sector_nombre, s.color AS sector_color,
                   c.id AS comanda_id, c.numero AS comanda_numero, c.estado AS comanda_estado,
                   c.personas AS comanda_personas,
                   COALESCE((SELECT SUM(ci.subtotal) FROM restaurant_comanda_items ci WHERE ci.comanda_id = c.id AND ci.estado_cocina != 'cancelado'), 0) AS comanda_total
            FROM restaurant_mesas m
            LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
            LEFT JOIN restaurant_comandas c ON c.mesa_id = m.id AND c.estado IN ('abierta','en_cocina','lista')
            WHERE m.id = :id AND m.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$mesa) Response::error('Mesa no encontrada', 404);
        Response::success('OK', $mesa);
    }

    // Listado con sectores y comanda activa
    $where = "m.negocio_id = :nid AND m.activo = 1";
    $params = [':nid' => $negocioId];
    if (!empty($_GET['sector_id'])) {
        $where .= " AND m.sector_id = :sid";
        $params[':sid'] = (int)$_GET['sector_id'];
    }

    $stmt = $pdo->prepare("
        SELECT m.*,
               s.nombre AS sector_nombre, s.color AS sector_color,
               c.id     AS comanda_id,
               c.numero AS comanda_numero,
               c.estado AS comanda_estado,
               c.personas AS comanda_personas,
               c.abierta_at AS comanda_desde,
               COALESCE((SELECT SUM(ci.subtotal) FROM restaurant_comanda_items ci WHERE ci.comanda_id = c.id AND ci.estado_cocina != 'cancelado'), 0) AS comanda_total
        FROM restaurant_mesas m
        LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
        LEFT JOIN restaurant_comandas c
               ON c.mesa_id = m.id AND c.negocio_id = m.negocio_id
               AND c.estado IN ('abierta','en_cocina','lista')
        WHERE {$where}
        ORDER BY s.orden, m.numero + 0, m.numero
    ");
    $stmt->execute($params);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sectores
    $stmtS = $pdo->prepare("SELECT * FROM restaurant_sectores WHERE negocio_id=:nid AND activo=1 ORDER BY orden");
    $stmtS->execute([':nid' => $negocioId]);
    $sectores = $stmtS->fetchAll(PDO::FETCH_ASSOC);

    Response::success('OK', ['mesas' => $mesas, 'sectores' => $sectores]);
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    if (empty($d['numero'])) Response::error('El número de mesa es requerido', 400);

    $stmt = $pdo->prepare("
        INSERT INTO restaurant_mesas (negocio_id, sector_id, numero, nombre, capacidad)
        VALUES (:nid, :sid, :numero, :nombre, :cap)
    ");
    $stmt->execute([
        ':nid'    => $negocioId,
        ':sid'    => !empty($d['sector_id']) ? (int)$d['sector_id'] : null,
        ':numero' => trim($d['numero']),
        ':nombre' => trim($d['nombre'] ?? ''),
        ':cap'    => (int)($d['capacidad'] ?? 4),
    ]);
    Response::success('Mesa creada', ['id' => $pdo->lastInsertId()], 201);
}

if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($_GET['id'] ?? $d['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    $sets   = [];
    $params = [':id' => $id, ':nid' => $negocioId];
    $allow  = ['numero','nombre','capacidad','estado','sector_id','pos_x','pos_y','activo'];
    foreach ($allow as $f) {
        if (isset($d[$f])) {
            $sets[] = "{$f} = :{$f}";
            $params[":{$f}"] = $d[$f];
        }
    }
    if (empty($sets)) Response::error('Sin campos para actualizar', 400);

    $pdo->prepare("UPDATE restaurant_mesas SET ".implode(',',$sets)." WHERE id=:id AND negocio_id=:nid")
        ->execute($params);
    Response::success(null, 'Mesa actualizada');
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE restaurant_mesas SET activo=0 WHERE id=:id AND negocio_id=:nid")
        ->execute([':id' => $id, ':nid' => $negocioId]);
    Response::success(null, 'Mesa eliminada');
}
