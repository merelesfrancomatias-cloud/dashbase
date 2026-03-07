<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET','POST','PUT','DELETE']);

[$negocioId] = Middleware::auth();
$db     = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if ($id) {
        $st = $db->prepare("
            SELECT c.*,
                COUNT(DISTINCT o.id)                                            AS total_ordenes,
                SUM(CASE WHEN o.estado NOT IN ('entregado','cancelado','sin_reparacion') THEN 1 ELSE 0 END) AS ordenes_activas
            FROM tec_clientes c
            LEFT JOIN tec_ordenes o ON o.cliente_id = c.id AND o.negocio_id = :nid2
            WHERE c.id = :id AND c.negocio_id = :nid
            GROUP BY c.id
        ");
        $st->execute([':nid' => $negocioId, ':nid2' => $negocioId, ':id' => $id]);
        $cliente = $st->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) { Response::error('Cliente no encontrado', 404); exit; }

        $sto = $db->prepare("SELECT * FROM tec_ordenes WHERE negocio_id = :nid AND cliente_id = :cid ORDER BY created_at DESC LIMIT 20");
        $sto->execute([':nid' => $negocioId, ':cid' => $id]);
        $cliente['ordenes'] = $sto->fetchAll(PDO::FETCH_ASSOC);
        Response::success('OK', $cliente);
        exit;
    }

    // Lista con stats
    $q = $_GET['q'] ?? '';
    $where = 'c.negocio_id = :nid AND c.activo = 1';
    $params = [':nid' => $negocioId];
    if ($q) {
        $where .= ' AND (c.nombre LIKE :q1 OR c.apellido LIKE :q2 OR c.dni LIKE :q3 OR c.telefono LIKE :q4)';
        $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = "%$q%";
    }
    $st = $db->prepare("
        SELECT c.*,
            COUNT(DISTINCT o.id)                                            AS total_ordenes,
            SUM(CASE WHEN o.estado NOT IN ('entregado','cancelado','sin_reparacion') THEN 1 ELSE 0 END) AS ordenes_activas,
            SUM(CASE WHEN o.estado = 'listo' THEN 1 ELSE 0 END)            AS listos_para_entregar
        FROM tec_clientes c
        LEFT JOIN tec_ordenes o ON o.cliente_id = c.id AND o.negocio_id = :nid2
        WHERE $where
        GROUP BY c.id
        ORDER BY c.apellido, c.nombre
    ");
    $st->execute(array_merge($params, [':nid2' => $negocioId]));
    $clientes = $st->fetchAll(PDO::FETCH_ASSOC);

    $stStats = $db->prepare("
        SELECT
            COUNT(*)                                                              AS total,
            SUM(CASE WHEN o.estado NOT IN ('entregado','cancelado','sin_reparacion') THEN 1 ELSE 0 END) AS activos,
            SUM(CASE WHEN o.estado = 'listo' THEN 1 ELSE 0 END)                  AS listos
        FROM tec_clientes c
        LEFT JOIN tec_ordenes o ON o.cliente_id = c.id AND o.negocio_id = :nid2
        WHERE c.negocio_id = :nid AND c.activo = 1
    ");
    $stStats->execute([':nid' => $negocioId, ':nid2' => $negocioId]);
    $stats = $stStats->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', ['clientes' => $clientes, 'stats' => $stats]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $nombre = trim($body['nombre'] ?? '');
    if (!$nombre) { Response::error('El nombre es obligatorio'); exit; }
    $st = $db->prepare("INSERT INTO tec_clientes (negocio_id,nombre,apellido,dni,telefono,email,direccion,observaciones)
        VALUES (:nid,:nombre,:apellido,:dni,:tel,:email,:dir,:obs)");
    $st->execute([
        ':nid'      => $negocioId,
        ':nombre'   => $nombre,
        ':apellido' => trim($body['apellido'] ?? ''),
        ':dni'      => trim($body['dni']       ?? '') ?: null,
        ':tel'      => trim($body['telefono']  ?? '') ?: null,
        ':email'    => trim($body['email']     ?? '') ?: null,
        ':dir'      => trim($body['direccion'] ?? '') ?: null,
        ':obs'      => trim($body['observaciones'] ?? '') ?: null,
    ]);
    Response::success('Cliente creado', ['id' => $db->lastInsertId()], 201);
    exit;
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT' && $id) {
    $st = $db->prepare("UPDATE tec_clientes SET nombre=:nombre,apellido=:apellido,dni=:dni,
        telefono=:tel,email=:email,direccion=:dir,observaciones=:obs,updated_at=NOW()
        WHERE id=:id AND negocio_id=:nid");
    $st->execute([
        ':nombre'   => trim($body['nombre']   ?? ''),
        ':apellido' => trim($body['apellido'] ?? ''),
        ':dni'      => trim($body['dni']       ?? '') ?: null,
        ':tel'      => trim($body['telefono']  ?? '') ?: null,
        ':email'    => trim($body['email']     ?? '') ?: null,
        ':dir'      => trim($body['direccion'] ?? '') ?: null,
        ':obs'      => trim($body['observaciones'] ?? '') ?: null,
        ':id'       => $id,
        ':nid'      => $negocioId,
    ]);
    Response::success('Cliente actualizado');
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    $db->prepare("UPDATE tec_clientes SET activo=0 WHERE id=? AND negocio_id=?")->execute([$id, $negocioId]);
    Response::success('Cliente eliminado');
    exit;
}

Response::error('Método no permitido', 405);
