<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT r.*, CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre, c.telefono AS cliente_tel
            FROM optica_recetas r
            JOIN optica_clientes c ON c.id = r.cliente_id
            WHERE r.id = :id AND r.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) Response::error('Receta no encontrada', 404);
        Response::success('OK', $r);
    }

    // Listado — filtros: ?cliente_id=, ?tipo=, ?q=
    $where  = "r.negocio_id = :nid";
    $params = [':nid' => $negocioId];

    if (!empty($_GET['cliente_id'])) {
        $where .= " AND r.cliente_id = :cid";
        $params[':cid'] = (int)$_GET['cliente_id'];
    }
    if (!empty($_GET['tipo'])) {
        $where .= " AND r.tipo = :tipo";
        $params[':tipo'] = $_GET['tipo'];
    }
    if (!empty($_GET['q'])) {
        $where .= " AND (c.nombre LIKE :q1 OR c.apellido LIKE :q2)";
        $q = '%' . $_GET['q'] . '%';
        $params[':q1'] = $q; $params[':q2'] = $q;
    }

    $stmt = $pdo->prepare("
        SELECT r.*,
               CONCAT(c.nombre,' ',c.apellido) AS cliente_nombre,
               c.telefono AS cliente_tel,
               c.obra_social
        FROM optica_recetas r
        JOIN optica_clientes c ON c.id = r.cliente_id
        WHERE {$where}
        ORDER BY r.fecha_emision DESC, r.id DESC
        LIMIT 200
    ");
    $stmt->execute($params);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['cliente_id']))   Response::error('Cliente requerido', 400);
    if (empty($d['fecha_emision'])) Response::error('Fecha de emisión requerida', 400);

    $stmt = $pdo->prepare("
        INSERT INTO optica_recetas
            (negocio_id, cliente_id,
             od_esfera, od_cilindro, od_eje, od_adicion, od_av,
             oi_esfera, oi_cilindro, oi_eje, oi_adicion, oi_av,
             dnp_od, dnp_oi, altura,
             tipo, medico, fecha_emision, fecha_vencimiento, observaciones, usuario_id)
        VALUES
            (:nid, :cid,
             :ode, :odc, :odej, :oda, :odav,
             :oie, :oic, :oiej, :oia, :oiav,
             :dnpod, :dnpoi, :alt,
             :tipo, :med, :fem, :fven, :obs, :uid)
    ");
    $stmt->execute([
        ':nid'   => $negocioId,
        ':cid'   => (int)$d['cliente_id'],
        ':ode'   => isset($d['od_esfera'])   ? (float)$d['od_esfera']   : null,
        ':odc'   => isset($d['od_cilindro']) ? (float)$d['od_cilindro'] : null,
        ':odej'  => isset($d['od_eje'])      ? (int)$d['od_eje']        : null,
        ':oda'   => isset($d['od_adicion'])  ? (float)$d['od_adicion']  : null,
        ':odav'  => $d['od_av']              ?? null,
        ':oie'   => isset($d['oi_esfera'])   ? (float)$d['oi_esfera']   : null,
        ':oic'   => isset($d['oi_cilindro']) ? (float)$d['oi_cilindro'] : null,
        ':oiej'  => isset($d['oi_eje'])      ? (int)$d['oi_eje']        : null,
        ':oia'   => isset($d['oi_adicion'])  ? (float)$d['oi_adicion']  : null,
        ':oiav'  => $d['oi_av']              ?? null,
        ':dnpod' => isset($d['dnp_od'])  ? (float)$d['dnp_od']  : null,
        ':dnpoi' => isset($d['dnp_oi'])  ? (float)$d['dnp_oi']  : null,
        ':alt'   => isset($d['altura'])  ? (float)$d['altura']   : null,
        ':tipo'  => $d['tipo']           ?? 'lejos',
        ':med'   => $d['medico']         ?? null,
        ':fem'   => $d['fecha_emision'],
        ':fven'  => $d['fecha_vencimiento'] ?? null,
        ':obs'   => $d['observaciones']  ?? null,
        ':uid'   => $usuarioId,
    ]);
    Response::success('Receta guardada', ['id' => $pdo->lastInsertId()], 201);
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];

    $stmt = $pdo->prepare("
        UPDATE optica_recetas SET
            od_esfera=:ode, od_cilindro=:odc, od_eje=:odej, od_adicion=:oda, od_av=:odav,
            oi_esfera=:oie, oi_cilindro=:oic, oi_eje=:oiej, oi_adicion=:oia, oi_av=:oiav,
            dnp_od=:dnpod, dnp_oi=:dnpoi, altura=:alt,
            tipo=:tipo, medico=:med, fecha_emision=:fem, fecha_vencimiento=:fven, observaciones=:obs
        WHERE id = :id AND negocio_id = :nid
    ");
    $stmt->execute([
        ':ode'   => isset($d['od_esfera'])   ? (float)$d['od_esfera']   : null,
        ':odc'   => isset($d['od_cilindro']) ? (float)$d['od_cilindro'] : null,
        ':odej'  => isset($d['od_eje'])      ? (int)$d['od_eje']        : null,
        ':oda'   => isset($d['od_adicion'])  ? (float)$d['od_adicion']  : null,
        ':odav'  => $d['od_av']              ?? null,
        ':oie'   => isset($d['oi_esfera'])   ? (float)$d['oi_esfera']   : null,
        ':oic'   => isset($d['oi_cilindro']) ? (float)$d['oi_cilindro'] : null,
        ':oiej'  => isset($d['oi_eje'])      ? (int)$d['oi_eje']        : null,
        ':oia'   => isset($d['oi_adicion'])  ? (float)$d['oi_adicion']  : null,
        ':oiav'  => $d['oi_av']              ?? null,
        ':dnpod' => isset($d['dnp_od'])  ? (float)$d['dnp_od']  : null,
        ':dnpoi' => isset($d['dnp_oi'])  ? (float)$d['dnp_oi']  : null,
        ':alt'   => isset($d['altura'])  ? (float)$d['altura']   : null,
        ':tipo'  => $d['tipo']           ?? 'lejos',
        ':med'   => $d['medico']         ?? null,
        ':fem'   => $d['fecha_emision']  ?? date('Y-m-d'),
        ':fven'  => $d['fecha_vencimiento'] ?? null,
        ':obs'   => $d['observaciones']  ?? null,
        ':id'    => $id,
        ':nid'   => $negocioId,
    ]);
    Response::success('Receta actualizada');
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $pdo->prepare("DELETE FROM optica_recetas WHERE id = :id AND negocio_id = :nid")
        ->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Receta eliminada');
}
