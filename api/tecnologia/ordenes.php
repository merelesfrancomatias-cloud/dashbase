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
            SELECT o.*, CONCAT(c.apellido,' ',c.nombre) AS cliente_nombre, c.telefono AS cliente_tel
            FROM tec_ordenes o
            JOIN tec_clientes c ON c.id = o.cliente_id
            WHERE o.id = :id AND o.negocio_id = :nid
        ");
        $st->execute([':id' => $id, ':nid' => $negocioId]);
        $orden = $st->fetch(PDO::FETCH_ASSOC);
        if (!$orden) { Response::error('Orden no encontrada', 404); exit; }
        if ($orden['repuestos']) $orden['repuestos'] = json_decode($orden['repuestos'], true);
        Response::success('OK', $orden);
        exit;
    }

    // Lista con filtros + stats
    $estado    = $_GET['estado']    ?? '';
    $prioridad = $_GET['prioridad'] ?? '';
    $q         = $_GET['q']         ?? '';
    $desde     = $_GET['desde']     ?? '';
    $hasta     = $_GET['hasta']     ?? '';
    $cliente_id= isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;

    $where  = 'o.negocio_id = :nid';
    $params = [':nid' => $negocioId];

    if ($estado)     { $where .= ' AND o.estado = :estado';         $params[':estado']     = $estado; }
    if ($prioridad)  { $where .= ' AND o.prioridad = :prioridad';   $params[':prioridad']  = $prioridad; }
    if ($cliente_id) { $where .= ' AND o.cliente_id = :cid';        $params[':cid']        = $cliente_id; }
    if ($desde)      { $where .= ' AND o.fecha_ingreso >= :desde';  $params[':desde']      = $desde; }
    if ($hasta)      { $where .= ' AND o.fecha_ingreso <= :hasta';  $params[':hasta']      = $hasta; }
    if ($q) {
        $where .= ' AND (c.nombre LIKE :q1 OR c.apellido LIKE :q2 OR o.equipo_marca LIKE :q3 OR o.equipo_modelo LIKE :q4 OR o.equipo_serie LIKE :q5)';
        $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = $params[':q5'] = "%$q%";
    }

    $st = $db->prepare("
        SELECT o.*,
            CONCAT(c.apellido,' ',c.nombre) AS cliente_nombre,
            c.telefono AS cliente_tel
        FROM tec_ordenes o
        JOIN tec_clientes c ON c.id = o.cliente_id
        WHERE $where
        ORDER BY
            FIELD(o.prioridad,'vip','urgente','normal'),
            FIELD(o.estado,'listo','en_reparacion','esperando_repuesto','diagnosticando','ingresado','entregado','sin_reparacion','cancelado'),
            o.fecha_promesa ASC,
            o.created_at DESC
    ");
    $st->execute($params);
    $ordenes = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ordenes as &$o) {
        if ($o['repuestos']) $o['repuestos'] = json_decode($o['repuestos'], true);
    }

    // Stats
    $stSt = $db->prepare("
        SELECT
            COUNT(*)                                                                       AS total,
            SUM(CASE WHEN estado = 'ingresado'           THEN 1 ELSE 0 END)               AS ingresados,
            SUM(CASE WHEN estado = 'en_reparacion'       THEN 1 ELSE 0 END)               AS en_reparacion,
            SUM(CASE WHEN estado = 'esperando_repuesto'  THEN 1 ELSE 0 END)               AS esperando_repuesto,
            SUM(CASE WHEN estado = 'listo'               THEN 1 ELSE 0 END)               AS listos,
            SUM(CASE WHEN estado = 'entregado'           THEN 1 ELSE 0 END)               AS entregados,
            SUM(CASE WHEN estado NOT IN ('entregado','cancelado','sin_reparacion') THEN 1 ELSE 0 END) AS activos,
            SUM(CASE WHEN prioridad = 'urgente' AND estado NOT IN ('entregado','cancelado','sin_reparacion') THEN 1 ELSE 0 END) AS urgentes,
            SUM(CASE WHEN saldo > 0 AND estado NOT IN ('cancelado','sin_reparacion') THEN 1 ELSE 0 END) AS con_saldo
        FROM tec_ordenes
        WHERE negocio_id = :nid
    ");
    $stSt->execute([':nid' => $negocioId]);
    $stats = $stSt->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', ['ordenes' => $ordenes, 'stats' => $stats]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $cliId = (int)($body['cliente_id'] ?? 0);
    $falla = trim($body['falla_reportada'] ?? '');
    if (!$cliId || !$falla) { Response::error('Cliente y falla son obligatorios'); exit; }

    $manoObra       = (float)($body['mano_obra']       ?? 0);
    $repuestosTot   = (float)($body['repuestos_total'] ?? 0);
    $total          = (float)($body['total']           ?? ($manoObra + $repuestosTot));
    $sena           = (float)($body['seña']            ?? 0);
    $saldo          = max(0, $total - $sena);
    $repuestosJson  = isset($body['repuestos']) ? json_encode($body['repuestos']) : null;
    $fechaIngreso   = $body['fecha_ingreso'] ?? date('Y-m-d');

    $st = $db->prepare("INSERT INTO tec_ordenes
        (negocio_id,cliente_id,equipo_tipo,equipo_marca,equipo_modelo,equipo_serie,equipo_color,
         falla_reportada,diagnostico,repuestos,mano_obra,repuestos_total,total,seña,saldo,
         metodo_pago,estado,prioridad,accesorios,contrasena,fecha_ingreso,fecha_promesa,tecnico,observaciones)
        VALUES
        (:nid,:cid,:tipo,:marca,:modelo,:serie,:color,
         :falla,:diag,:reps,:mo,:rt,:total,:sena,:saldo,
         :metodo,:estado,:prio,:acc,:pass,:fi,:fp,:tec,:obs)");
    $st->execute([
        ':nid'    => $negocioId,   ':cid'    => $cliId,
        ':tipo'   => $body['equipo_tipo']    ?? 'otro',
        ':marca'  => trim($body['equipo_marca']  ?? '') ?: null,
        ':modelo' => trim($body['equipo_modelo'] ?? '') ?: null,
        ':serie'  => trim($body['equipo_serie']  ?? '') ?: null,
        ':color'  => trim($body['equipo_color']  ?? '') ?: null,
        ':falla'  => $falla,
        ':diag'   => trim($body['diagnostico']   ?? '') ?: null,
        ':reps'   => $repuestosJson,
        ':mo'     => $manoObra,    ':rt'     => $repuestosTot,
        ':total'  => $total,       ':sena'   => $sena,  ':saldo' => $saldo,
        ':metodo' => $body['metodo_pago']   ?? 'efectivo',
        ':estado' => $body['estado']        ?? 'ingresado',
        ':prio'   => $body['prioridad']     ?? 'normal',
        ':acc'    => trim($body['accesorios']  ?? '') ?: null,
        ':pass'   => trim($body['contrasena']  ?? '') ?: null,
        ':fi'     => $fechaIngreso,
        ':fp'     => $body['fecha_promesa']  ?? null,
        ':tec'    => trim($body['tecnico']   ?? '') ?: null,
        ':obs'    => trim($body['observaciones'] ?? '') ?: null,
    ]);
    $newId = $db->lastInsertId();

    // Registrar seña en caja si corresponde
    if ($sena > 0 && $body['estado'] !== 'ingresado') {
        try {
            $stCaja = $db->prepare("SELECT id FROM cajas WHERE negocio_id=? AND estado='abierta' ORDER BY fecha_apertura DESC LIMIT 1");
            $stCaja->execute([$negocioId]);
            $cajaId = $stCaja->fetchColumn();
            if ($cajaId) {
                $db->prepare("INSERT INTO ventas (negocio_id,caja_id,usuario_id,total,metodo_pago,notas,created_at)
                    VALUES (?,?,?,?,?,'Seña O/S #'||?,NOW())")
                   ->execute([$negocioId, $cajaId, $_SESSION['user_id'] ?? $_SESSION['usuario_id'], $sena, $body['metodo_pago'] ?? 'efectivo', $newId]);
            }
        } catch (Exception $e) {}
    }

    Response::success('Orden creada', ['id' => $newId], 201);
    exit;
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT' && $id) {
    // Quick state change
    if (count($body) <= 2 && isset($body['estado'])) {
        $nuevoEstado = $body['estado'];
        $extra = '';
        $params = [];
        if ($nuevoEstado === 'entregado') {
            $extra = ', fecha_entrega = CURDATE()';
            // Cobrar saldo si hay
            $stOrd = $db->prepare("SELECT saldo, metodo_pago FROM tec_ordenes WHERE id=? AND negocio_id=?");
            $stOrd->execute([$id, $negocioId]);
            $ord = $stOrd->fetch(PDO::FETCH_ASSOC);
            if ($ord && $ord['saldo'] > 0) {
                $metodo = $body['metodo_pago'] ?? $ord['metodo_pago'];
                $extra .= ', saldo = 0, metodo_pago = :metodo';
                $params[':metodo'] = $metodo;
                try {
                    $stCaja = $db->prepare("SELECT id FROM cajas WHERE negocio_id=? AND estado='abierta' ORDER BY fecha_apertura DESC LIMIT 1");
                    $stCaja->execute([$negocioId]);
                    $cajaId = $stCaja->fetchColumn();
                    if ($cajaId) {
                        $db->prepare("INSERT INTO ventas (negocio_id,caja_id,usuario_id,total,metodo_pago,notas,created_at)
                            VALUES (?,?,?,?,?,?,NOW())")
                           ->execute([$negocioId, $cajaId, $_SESSION['user_id'] ?? $_SESSION['usuario_id'], $ord['saldo'], $metodo, "Saldo O/S #$id"]);
                    }
                } catch (Exception $e) {}
            }
        }
        $params = array_merge($params, [':estado' => $nuevoEstado, ':id' => $id, ':nid' => $negocioId]);
        $db->prepare("UPDATE tec_ordenes SET estado=:estado$extra, updated_at=NOW() WHERE id=:id AND negocio_id=:nid")->execute($params);
        Response::success('Estado actualizado');
        exit;
    }

    // Actualización completa
    $manoObra     = (float)($body['mano_obra']       ?? 0);
    $repuestosTot = (float)($body['repuestos_total'] ?? 0);
    $total        = (float)($body['total']           ?? ($manoObra + $repuestosTot));
    $sena         = (float)($body['seña']            ?? 0);
    $saldo        = max(0, $total - $sena);
    $repuestosJson = isset($body['repuestos']) ? json_encode($body['repuestos']) : null;

    $st = $db->prepare("UPDATE tec_ordenes SET
        cliente_id=:cid, equipo_tipo=:tipo, equipo_marca=:marca, equipo_modelo=:modelo,
        equipo_serie=:serie, equipo_color=:color, falla_reportada=:falla, diagnostico=:diag,
        repuestos=:reps, mano_obra=:mo, repuestos_total=:rt, total=:total,
        seña=:sena, saldo=:saldo, metodo_pago=:metodo, estado=:estado,
        prioridad=:prio, accesorios=:acc, contrasena=:pass,
        fecha_ingreso=:fi, fecha_promesa=:fp, tecnico=:tec, observaciones=:obs,
        updated_at=NOW()
        WHERE id=:id AND negocio_id=:nid");
    $st->execute([
        ':cid'    => (int)($body['cliente_id'] ?? 0),
        ':tipo'   => $body['equipo_tipo']    ?? 'otro',
        ':marca'  => trim($body['equipo_marca']  ?? '') ?: null,
        ':modelo' => trim($body['equipo_modelo'] ?? '') ?: null,
        ':serie'  => trim($body['equipo_serie']  ?? '') ?: null,
        ':color'  => trim($body['equipo_color']  ?? '') ?: null,
        ':falla'  => trim($body['falla_reportada'] ?? ''),
        ':diag'   => trim($body['diagnostico']    ?? '') ?: null,
        ':reps'   => $repuestosJson,
        ':mo'     => $manoObra, ':rt' => $repuestosTot,
        ':total'  => $total, ':sena' => $sena, ':saldo' => $saldo,
        ':metodo' => $body['metodo_pago']  ?? 'efectivo',
        ':estado' => $body['estado']       ?? 'ingresado',
        ':prio'   => $body['prioridad']    ?? 'normal',
        ':acc'    => trim($body['accesorios']  ?? '') ?: null,
        ':pass'   => trim($body['contrasena']  ?? '') ?: null,
        ':fi'     => $body['fecha_ingreso'] ?? date('Y-m-d'),
        ':fp'     => $body['fecha_promesa'] ?? null,
        ':tec'    => trim($body['tecnico']  ?? '') ?: null,
        ':obs'    => trim($body['observaciones'] ?? '') ?: null,
        ':id'     => $id, ':nid' => $negocioId,
    ]);
    Response::success('Orden actualizada');
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    $db->prepare("UPDATE tec_ordenes SET estado='cancelado' WHERE id=? AND negocio_id=?")->execute([$id, $negocioId]);
    Response::success('Orden cancelada');
    exit;
}

Response::error('Método no permitido', 405);
