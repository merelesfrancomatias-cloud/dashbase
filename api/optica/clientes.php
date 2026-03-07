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
            SELECT c.*,
                   (SELECT COUNT(*) FROM optica_recetas  WHERE cliente_id = c.id AND negocio_id = :nid1) AS total_recetas,
                   (SELECT COUNT(*) FROM optica_pedidos  WHERE cliente_id = c.id AND negocio_id = :nid2 AND estado NOT IN ('cancelado')) AS total_pedidos,
                   (SELECT MAX(created_at) FROM optica_pedidos WHERE cliente_id = c.id AND negocio_id = :nid3) AS ultimo_pedido
            FROM optica_clientes c
            WHERE c.id = :id AND c.negocio_id = :nid AND c.activo = 1
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId, ':nid1' => $negocioId, ':nid2' => $negocioId, ':nid3' => $negocioId]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$c) Response::error('Cliente no encontrado', 404);

        // Recetas del cliente
        $recetas = $pdo->prepare("SELECT * FROM optica_recetas WHERE cliente_id = :cid AND negocio_id = :nid ORDER BY fecha_emision DESC LIMIT 20");
        $recetas->execute([':cid' => (int)$_GET['id'], ':nid' => $negocioId]);
        $c['recetas'] = $recetas->fetchAll(PDO::FETCH_ASSOC);

        // Últimos pedidos
        $pedidos = $pdo->prepare("SELECT id, armazon, lente_tipo, estado, total, saldo, created_at FROM optica_pedidos WHERE cliente_id = :cid AND negocio_id = :nid ORDER BY created_at DESC LIMIT 10");
        $pedidos->execute([':cid' => (int)$_GET['id'], ':nid' => $negocioId]);
        $c['pedidos'] = $pedidos->fetchAll(PDO::FETCH_ASSOC);

        Response::success('OK', $c);
    }

    // Listado con búsqueda
    $where  = "c.negocio_id = :nid AND c.activo = 1";
    $params = [':nid' => $negocioId];

    if (!empty($_GET['q'])) {
        $where .= " AND (c.nombre LIKE :q1 OR c.apellido LIKE :q2 OR c.dni LIKE :q3 OR c.telefono LIKE :q4)";
        $q = '%' . $_GET['q'] . '%';
        $params[':q1'] = $q; $params[':q2'] = $q;
        $params[':q3'] = $q; $params[':q4'] = $q;
    }

    $stmt = $pdo->prepare("
        SELECT c.*,
               (SELECT MAX(r.fecha_emision) FROM optica_recetas r WHERE r.cliente_id = c.id) AS ultima_receta,
               (SELECT COUNT(*) FROM optica_pedidos p WHERE p.cliente_id = c.id AND p.estado NOT IN ('entregado','cancelado')) AS pedidos_activos
        FROM optica_clientes c
        WHERE {$where}
        ORDER BY c.apellido, c.nombre
        LIMIT 300
    ");
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $stats = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            (SELECT COUNT(*) FROM optica_pedidos WHERE negocio_id = :nid2 AND estado NOT IN ('entregado','cancelado','presupuesto')) AS pedidos_activos,
            (SELECT COUNT(*) FROM optica_pedidos WHERE negocio_id = :nid3 AND estado = 'listo') AS listos_para_entregar,
            (SELECT COUNT(*) FROM optica_pedidos WHERE negocio_id = :nid4 AND estado = 'laboratorio') AS en_laboratorio
        FROM optica_clientes
        WHERE negocio_id = :nid AND activo = 1
    ");
    $stats->execute([':nid' => $negocioId, ':nid2' => $negocioId, ':nid3' => $negocioId, ':nid4' => $negocioId]);
    $statsRow = $stats->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', ['clientes' => $clientes, 'stats' => $statsRow]);
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['nombre']))   Response::error('El nombre es requerido', 400);
    if (empty($d['apellido'])) Response::error('El apellido es requerido', 400);

    $stmt = $pdo->prepare("
        INSERT INTO optica_clientes
            (negocio_id, nombre, apellido, dni, telefono, email, fecha_nac, obra_social, nro_afiliado, observaciones)
        VALUES
            (:nid, :nombre, :apellido, :dni, :tel, :email, :fnac, :os, :naf, :obs)
    ");
    $stmt->execute([
        ':nid'      => $negocioId,
        ':nombre'   => trim($d['nombre']),
        ':apellido' => trim($d['apellido']),
        ':dni'      => $d['dni']         ?? null,
        ':tel'      => $d['telefono']    ?? null,
        ':email'    => $d['email']       ?? null,
        ':fnac'     => $d['fecha_nac']   ?? null,
        ':os'       => $d['obra_social'] ?? null,
        ':naf'      => $d['nro_afiliado']?? null,
        ':obs'      => $d['observaciones']?? null,
    ]);
    Response::success('Cliente creado', ['id' => $pdo->lastInsertId()], 201);
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['nombre']))   Response::error('El nombre es requerido', 400);
    if (empty($d['apellido'])) Response::error('El apellido es requerido', 400);

    $stmt = $pdo->prepare("
        UPDATE optica_clientes SET
            nombre       = :nombre,
            apellido     = :apellido,
            dni          = :dni,
            telefono     = :tel,
            email        = :email,
            fecha_nac    = :fnac,
            obra_social  = :os,
            nro_afiliado = :naf,
            observaciones= :obs
        WHERE id = :id AND negocio_id = :nid
    ");
    $stmt->execute([
        ':nombre'   => trim($d['nombre']),
        ':apellido' => trim($d['apellido']),
        ':dni'      => $d['dni']          ?? null,
        ':tel'      => $d['telefono']     ?? null,
        ':email'    => $d['email']        ?? null,
        ':fnac'     => $d['fecha_nac']    ?? null,
        ':os'       => $d['obra_social']  ?? null,
        ':naf'      => $d['nro_afiliado'] ?? null,
        ':obs'      => $d['observaciones']?? null,
        ':id'       => $id,
        ':nid'      => $negocioId,
    ]);
    Response::success('Cliente actualizado');
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE optica_clientes SET activo = 0 WHERE id = :id AND negocio_id = :nid")
        ->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Cliente eliminado');
}
