<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET','POST','PUT','DELETE']);

[$negocioId] = Middleware::auth();
$db     = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Crear tabla si no existe
$db->exec("CREATE TABLE IF NOT EXISTS elec_servicios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id      INT NOT NULL,
    numero          VARCHAR(20)  NOT NULL,
    cliente_id      INT          DEFAULT NULL,
    cliente_nombre  VARCHAR(200) DEFAULT NULL,
    cliente_tel     VARCHAR(30)  DEFAULT NULL,
    articulo        VARCHAR(200) NOT NULL,
    marca           VARCHAR(100) DEFAULT NULL,
    modelo          VARCHAR(100) DEFAULT NULL,
    numero_serie    VARCHAR(100) DEFAULT NULL,
    falla_declarada TEXT         DEFAULT NULL,
    diagnostico     TEXT         DEFAULT NULL,
    presupuesto     DECIMAL(12,2) DEFAULT NULL,
    presupuesto_aprobado TINYINT(1) DEFAULT NULL,
    costo_repuestos DECIMAL(12,2) DEFAULT 0,
    precio_final    DECIMAL(12,2) DEFAULT NULL,
    en_garantia     TINYINT(1)   DEFAULT 0,
    vence_garantia  DATE         DEFAULT NULL,
    estado          ENUM('ingresado','diagnosticando','esperando_repuesto','en_reparacion','listo','entregado','cancelado','sin_reparacion') DEFAULT 'ingresado',
    fecha_ingreso   DATE         DEFAULT (CURRENT_DATE),
    fecha_prometida DATE         DEFAULT NULL,
    fecha_entrega   DATE         DEFAULT NULL,
    tecnico         VARCHAR(100) DEFAULT NULL,
    observaciones   TEXT         DEFAULT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id),
    INDEX idx_estado  (estado),
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if ($id) {
        $st = $db->prepare("SELECT * FROM elec_servicios WHERE id=:id AND negocio_id=:nid");
        $st->execute([':id' => $id, ':nid' => $negocioId]);
        $s = $st->fetch(PDO::FETCH_ASSOC);
        if (!$s) { Response::error('Servicio no encontrado', 404); exit; }
        Response::success('OK', $s);
        exit;
    }

    $estado    = $_GET['estado']    ?? '';
    $cliente   = isset($_GET['cliente']) ? (int)$_GET['cliente'] : null;
    $q         = $_GET['q']         ?? '';
    $where     = 'negocio_id = :nid';
    $params    = [':nid' => $negocioId];

    if ($estado)  { $where .= ' AND estado = :est';  $params[':est']  = $estado; }
    if ($cliente) { $where .= ' AND cliente_id = :cid'; $params[':cid'] = $cliente; }
    if ($q) {
        $where .= ' AND (numero LIKE :q1 OR articulo LIKE :q2 OR marca LIKE :q3 OR cliente_nombre LIKE :q4 OR modelo LIKE :q5)';
        $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = $params[':q5'] = "%$q%";
    }

    $st = $db->prepare("SELECT * FROM elec_servicios WHERE $where ORDER BY created_at DESC LIMIT 200");
    $st->execute($params);
    $servicios = $st->fetchAll(PDO::FETCH_ASSOC);

    // Stats por estado
    $stStats = $db->prepare("SELECT estado, COUNT(*) AS total FROM elec_servicios WHERE negocio_id=:nid GROUP BY estado");
    $stStats->execute([':nid' => $negocioId]);
    $statsRaw = $stStats->fetchAll(PDO::FETCH_ASSOC);
    $stats = ['total'=>0,'ingresado'=>0,'diagnosticando'=>0,'esperando_repuesto'=>0,'en_reparacion'=>0,'listo'=>0,'entregado'=>0,'cancelado'=>0,'sin_reparacion'=>0];
    foreach ($statsRaw as $row) {
        $stats[$row['estado']] = (int)$row['total'];
        $stats['total'] += (int)$row['total'];
    }

    Response::success('OK', ['servicios' => $servicios, 'stats' => $stats]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $articulo = trim($body['articulo'] ?? '');
    if (!$articulo) { Response::error('El artículo es obligatorio'); exit; }

    // Generar número correlativo
    $stNum = $db->prepare("SELECT COUNT(*)+1 FROM elec_servicios WHERE negocio_id=:nid");
    $stNum->execute([':nid' => $negocioId]);
    $num = 'SRV-' . str_pad($stNum->fetchColumn(), 5, '0', STR_PAD_LEFT);

    $st = $db->prepare("INSERT INTO elec_servicios
        (negocio_id,numero,cliente_id,cliente_nombre,cliente_tel,articulo,marca,modelo,numero_serie,
         falla_declarada,presupuesto,en_garantia,vence_garantia,estado,fecha_ingreso,fecha_prometida,tecnico,observaciones)
        VALUES
        (:nid,:num,:cid,:cnombre,:ctel,:articulo,:marca,:modelo,:serie,
         :falla,:presupuesto,:garantia,:vence,:estado,:ingreso,:prometida,:tecnico,:obs)");
    $st->execute([
        ':nid'        => $negocioId,
        ':num'        => $num,
        ':cid'        => $body['cliente_id']     ? (int)$body['cliente_id'] : null,
        ':cnombre'    => trim($body['cliente_nombre'] ?? '') ?: null,
        ':ctel'       => trim($body['cliente_tel']    ?? '') ?: null,
        ':articulo'   => $articulo,
        ':marca'      => trim($body['marca']     ?? '') ?: null,
        ':modelo'     => trim($body['modelo']    ?? '') ?: null,
        ':serie'      => trim($body['numero_serie'] ?? '') ?: null,
        ':falla'      => trim($body['falla_declarada'] ?? '') ?: null,
        ':presupuesto'=> is_numeric($body['presupuesto'] ?? '') ? $body['presupuesto'] : null,
        ':garantia'   => ($body['en_garantia'] ?? false) ? 1 : 0,
        ':vence'      => $body['vence_garantia'] ?? null,
        ':estado'     => $body['estado'] ?? 'ingresado',
        ':ingreso'    => $body['fecha_ingreso']   ?? date('Y-m-d'),
        ':prometida'  => $body['fecha_prometida'] ?? null,
        ':tecnico'    => trim($body['tecnico'] ?? '') ?: null,
        ':obs'        => trim($body['observaciones'] ?? '') ?: null,
    ]);
    Response::success('Servicio creado', ['id' => $db->lastInsertId(), 'numero' => $num], 201);
    exit;
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT' && $id) {
    $sets = [];
    $params = [':id' => $id, ':nid' => $negocioId];
    $campos = ['cliente_id','cliente_nombre','cliente_tel','articulo','marca','modelo','numero_serie',
               'falla_declarada','diagnostico','presupuesto','presupuesto_aprobado','costo_repuestos',
               'precio_final','en_garantia','vence_garantia','estado','fecha_ingreso','fecha_prometida',
               'fecha_entrega','tecnico','observaciones'];
    foreach ($campos as $c) {
        if (array_key_exists($c, $body)) {
            $sets[]     = "$c = :$c";
            $params[":$c"] = $body[$c];
        }
    }
    if (!$sets) { Response::error('Nada que actualizar'); exit; }
    $db->prepare("UPDATE elec_servicios SET ".implode(',',$sets)." WHERE id=:id AND negocio_id=:nid")
       ->execute($params);
    Response::success('Servicio actualizado');
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    $db->prepare("UPDATE elec_servicios SET estado='cancelado' WHERE id=:id AND negocio_id=:nid")
       ->execute([':id' => $id, ':nid' => $negocioId]);
    Response::success('Servicio cancelado');
    exit;
}

Response::error('Método no permitido', 405);
