<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET','POST','PUT','DELETE']);

[$negocioId] = Middleware::auth();
$db     = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Crear tabla si no existe
$db->exec("CREATE TABLE IF NOT EXISTS elec_clientes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id    INT NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100) DEFAULT NULL,
    dni           VARCHAR(20)  DEFAULT NULL,
    telefono      VARCHAR(30)  DEFAULT NULL,
    email         VARCHAR(120) DEFAULT NULL,
    direccion     VARCHAR(200) DEFAULT NULL,
    observaciones TEXT         DEFAULT NULL,
    activo        TINYINT(1)   DEFAULT 1,
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if ($id) {
        $st = $db->prepare("
            SELECT c.*,
                COUNT(DISTINCT s.id) AS total_servicios,
                SUM(CASE WHEN s.estado NOT IN ('entregado','cancelado') THEN 1 ELSE 0 END) AS servicios_activos
            FROM elec_clientes c
            LEFT JOIN elec_servicios s ON s.cliente_id = c.id AND s.negocio_id = :nid2
            WHERE c.id = :id AND c.negocio_id = :nid
            GROUP BY c.id
        ");
        $st->execute([':nid' => $negocioId, ':nid2' => $negocioId, ':id' => $id]);
        $cliente = $st->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) { Response::error('Cliente no encontrado', 404); exit; }
        Response::success('OK', $cliente);
        exit;
    }

    $q = $_GET['q'] ?? '';
    $where  = 'c.negocio_id = :nid AND c.activo = 1';
    $params = [':nid' => $negocioId];
    if ($q) {
        $where .= ' AND (c.nombre LIKE :q1 OR c.apellido LIKE :q2 OR c.dni LIKE :q3 OR c.telefono LIKE :q4)';
        $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = "%$q%";
    }
    $st = $db->prepare("
        SELECT c.*,
            COUNT(DISTINCT s.id) AS total_servicios,
            SUM(CASE WHEN s.estado NOT IN ('entregado','cancelado') THEN 1 ELSE 0 END) AS servicios_activos,
            SUM(CASE WHEN s.estado = 'listo' THEN 1 ELSE 0 END) AS listos_para_entregar
        FROM elec_clientes c
        LEFT JOIN elec_servicios s ON s.cliente_id = c.id AND s.negocio_id = :nid2
        WHERE $where
        GROUP BY c.id
        ORDER BY c.apellido, c.nombre
    ");
    $st->execute(array_merge($params, [':nid2' => $negocioId]));
    $clientes = $st->fetchAll(PDO::FETCH_ASSOC);

    $stStats = $db->prepare("
        SELECT COUNT(*) AS total,
            SUM(CASE WHEN s.estado NOT IN ('entregado','cancelado') THEN 1 ELSE 0 END) AS activos,
            SUM(CASE WHEN s.estado = 'listo' THEN 1 ELSE 0 END) AS listos
        FROM elec_clientes c
        LEFT JOIN elec_servicios s ON s.cliente_id = c.id AND s.negocio_id = :nid2
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
    $st = $db->prepare("INSERT INTO elec_clientes (negocio_id,nombre,apellido,dni,telefono,email,direccion,observaciones)
        VALUES (:nid,:nombre,:apellido,:dni,:tel,:email,:dir,:obs)");
    $st->execute([
        ':nid'      => $negocioId,
        ':nombre'   => $nombre,
        ':apellido' => trim($body['apellido'] ?? '') ?: null,
        ':dni'      => trim($body['dni']      ?? '') ?: null,
        ':tel'      => trim($body['telefono'] ?? '') ?: null,
        ':email'    => trim($body['email']    ?? '') ?: null,
        ':dir'      => trim($body['direccion']?? '') ?: null,
        ':obs'      => trim($body['observaciones'] ?? '') ?: null,
    ]);
    Response::success('Cliente creado', ['id' => $db->lastInsertId()], 201);
    exit;
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT' && $id) {
    $nombre = trim($body['nombre'] ?? '');
    if (!$nombre) { Response::error('El nombre es obligatorio'); exit; }
    $st = $db->prepare("UPDATE elec_clientes SET nombre=:nombre,apellido=:apellido,dni=:dni,telefono=:tel,email=:email,direccion=:dir,observaciones=:obs WHERE id=:id AND negocio_id=:nid");
    $st->execute([
        ':nombre'   => $nombre,
        ':apellido' => trim($body['apellido'] ?? '') ?: null,
        ':dni'      => trim($body['dni']      ?? '') ?: null,
        ':tel'      => trim($body['telefono'] ?? '') ?: null,
        ':email'    => trim($body['email']    ?? '') ?: null,
        ':dir'      => trim($body['direccion']?? '') ?: null,
        ':obs'      => trim($body['observaciones'] ?? '') ?: null,
        ':id'       => $id,
        ':nid'      => $negocioId,
    ]);
    Response::success('Cliente actualizado');
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    $db->prepare("UPDATE elec_clientes SET activo=0 WHERE id=:id AND negocio_id=:nid")
       ->execute([':id' => $id, ':nid' => $negocioId]);
    Response::success('Cliente eliminado');
    exit;
}

Response::error('Método no permitido', 405);
