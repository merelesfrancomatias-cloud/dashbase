<?php
require_once __DIR__ . '/../../api/bootstrap.php';
header('Content-Type: application/json');
Auth::check();
$negocioId = (int)$_SESSION['negocio_id'];
$pdo       = (new Database())->getConnection();
$method    = $_SERVER['REQUEST_METHOD'];

// ── Helper: guardar lista de servicios del turno ──────────────────────────────
function guardarTurnoServicios(PDO $pdo, int $turnoId, int $negocioId, array $servicios): void {
    $pdo->prepare("DELETE FROM turno_servicios WHERE turno_id=? AND negocio_id=?")->execute([$turnoId, $negocioId]);
    $st = $pdo->prepare("INSERT INTO turno_servicios (turno_id,negocio_id,servicio_id,servicio_nombre,duracion_min,precio) VALUES (?,?,?,?,?,?)");
    foreach ($servicios as $s) {
        if (empty($s['servicio_nombre'])) continue;
        $st->execute([$turnoId, $negocioId, $s['servicio_id']??null, $s['servicio_nombre'], (int)($s['duracion_min']??30), (float)($s['precio']??0)]);
    }
}

// ── Helper: cargar servicios de un turno ──────────────────────────────────────
function cargarTurnoServicios(PDO $pdo, int $turnoId): array {
    $st = $pdo->prepare("SELECT * FROM turno_servicios WHERE turno_id=? ORDER BY id");
    $st->execute([$turnoId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

// ── Helper: calcular totales desde lista de servicios ────────────────────────
function calcTotales(array $servicios): array {
    $duracion = array_sum(array_column($servicios, 'duracion_min'));
    $precio   = array_sum(array_column($servicios, 'precio'));
    $nombres  = implode(' + ', array_column($servicios, 'servicio_nombre'));
    return ['duracion' => $duracion ?: 30, 'precio' => $precio, 'nombres' => $nombres];
}

// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $fecha   = $_GET['fecha']   ?? date('Y-m-d');
    $empId   = (int)($_GET['empleado_id'] ?? 0);
    $cliente = trim($_GET['cliente'] ?? '');
    $estado  = $_GET['estado'] ?? '';

    // Modo agenda semanal
    if (isset($_GET['semana'])) {
        $inicio = $_GET['semana'];
        $fin    = date('Y-m-d', strtotime($inicio . ' +6 days'));
        $st = $pdo->prepare("SELECT t.*, e.nombre AS empleado_nombre
            FROM turnos t LEFT JOIN empleados e ON e.id = t.empleado_id
            WHERE t.negocio_id=? AND t.fecha BETWEEN ? AND ?
            ORDER BY t.fecha, t.hora_inicio");
        $st->execute([$negocioId, $inicio, $fin]);
        $turnos = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($turnos as &$t) { $t['servicios'] = cargarTurnoServicios($pdo, (int)$t['id']); }
        Response::success('Semana obtenida', $turnos);
        exit;
    }

    // Detalle por id
    if (isset($_GET['id'])) {
        $st = $pdo->prepare("SELECT t.*, e.nombre AS empleado_nombre
            FROM turnos t LEFT JOIN empleados e ON e.id=t.empleado_id
            WHERE t.id=? AND t.negocio_id=?");
        $st->execute([(int)$_GET['id'], $negocioId]);
        $t = $st->fetch(PDO::FETCH_ASSOC);
        if (!$t) { Response::error('Turno no encontrado', 404); exit; }
        $t['servicios'] = cargarTurnoServicios($pdo, (int)$t['id']);
        Response::success('Turno obtenido', $t);
        exit;
    }

    // Listado del día o filtrado
    $where  = 't.negocio_id=?';
    $params = [$negocioId];
    if (!$cliente && !$estado && !$empId) { $where .= ' AND t.fecha=?'; $params[] = $fecha; }
    if ($empId)   { $where .= ' AND t.empleado_id=?';        $params[] = $empId; }
    if ($estado)  { $where .= ' AND t.estado=?';              $params[] = $estado; }
    if ($cliente) { $where .= ' AND t.cliente_nombre LIKE ?'; $params[] = "%$cliente%"; }

    $st = $pdo->prepare("SELECT t.*, e.nombre AS empleado_nombre
        FROM turnos t LEFT JOIN empleados e ON e.id=t.empleado_id
        WHERE $where ORDER BY t.fecha, t.hora_inicio");
    $st->execute($params);
    $turnos = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($turnos as &$t) { $t['servicios'] = cargarTurnoServicios($pdo, (int)$t['id']); }

    $stStats = $pdo->prepare("SELECT
        COUNT(*) AS total,
        SUM(estado='pendiente')  AS pendientes,
        SUM(estado='confirmado') AS confirmados,
        SUM(estado='en_curso')   AS en_curso,
        SUM(estado='completado') AS completados,
        SUM(estado='cancelado')  AS cancelados,
        SUM(estado='no_show')    AS no_show,
        COALESCE(SUM(CASE WHEN estado='completado' THEN precio ELSE 0 END),0) AS facturado
        FROM turnos WHERE negocio_id=? AND fecha=?");
    $stStats->execute([$negocioId, $fecha]);
    $stats = $stStats->fetch(PDO::FETCH_ASSOC);

    Response::success('Turnos obtenidos', ['turnos' => $turnos, 'stats' => $stats, 'fecha' => $fecha]);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?: [];
    if (empty($d['cliente_nombre'])) { Response::error('Nombre del cliente requerido', 400); exit; }
    if (empty($d['fecha']))          { Response::error('Fecha requerida', 400); exit; }
    if (empty($d['hora_inicio']))    { Response::error('Hora de inicio requerida', 400); exit; }

    $serviciosList = $d['servicios'] ?? [];
    if (!empty($serviciosList)) {
        $tot = calcTotales($serviciosList);
        $duracion = $tot['duracion']; $precioTotal = $tot['precio']; $servicioNombre = $tot['nombres']; $servicioId = null;
    } else {
        $duracion = (int)($d['duracion_min'] ?? 30); $precioTotal = (float)($d['precio'] ?? 0);
        $servicioNombre = $d['servicio_nombre'] ?? null; $servicioId = $d['servicio_id'] ?? null;
    }
    $horaFin = $d['hora_fin'] ?? date('H:i', strtotime($d['hora_inicio']) + $duracion * 60);

    $st = $pdo->prepare("INSERT INTO turnos
        (negocio_id,cliente_id,cliente_nombre,cliente_telefono,empleado_id,servicio_id,servicio_nombre,fecha,hora_inicio,hora_fin,duracion_min,precio,estado,notas)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $st->execute([
        $negocioId, $d['cliente_id'] ?? null, $d['cliente_nombre'], $d['cliente_telefono'] ?? null,
        $d['empleado_id'] ?? null, $servicioId, $servicioNombre,
        $d['fecha'], $d['hora_inicio'], $horaFin, $duracion,
        $precioTotal, $d['estado'] ?? 'pendiente', $d['notas'] ?? null,
    ]);
    $turnoId = (int)$pdo->lastInsertId();
    if (!empty($serviciosList)) { guardarTurnoServicios($pdo, $turnoId, $negocioId, $serviciosList); }
    Response::success('Turno creado', ['id' => $turnoId]);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int)($d['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }

    // Solo cambio de estado
    if (isset($d['estado']) && count($d) === 2) {
        $pdo->prepare("UPDATE turnos SET estado=? WHERE id=? AND negocio_id=?")->execute([$d['estado'], $id, $negocioId]);
        Response::success('Estado actualizado', []);
        exit;
    }

    $serviciosList = $d['servicios'] ?? [];
    if (!empty($serviciosList)) {
        $tot = calcTotales($serviciosList);
        $duracion = $tot['duracion']; $precioTotal = $tot['precio']; $servicioNombre = $tot['nombres']; $servicioId = null;
    } else {
        $duracion = (int)($d['duracion_min'] ?? 30); $precioTotal = (float)($d['precio'] ?? 0);
        $servicioNombre = $d['servicio_nombre'] ?? null; $servicioId = $d['servicio_id'] ?: null;
    }
    $horaFin = $d['hora_fin'] ?? date('H:i', strtotime($d['hora_inicio']) + $duracion * 60);

    $st = $pdo->prepare("UPDATE turnos SET
        cliente_nombre=?,cliente_telefono=?,empleado_id=?,servicio_id=?,servicio_nombre=?,
        fecha=?,hora_inicio=?,hora_fin=?,duracion_min=?,precio=?,estado=?,notas=?
        WHERE id=? AND negocio_id=?");
    $st->execute([
        $d['cliente_nombre'], $d['cliente_telefono']??null,
        $d['empleado_id']??null, $servicioId, $servicioNombre,
        $d['fecha'], $d['hora_inicio'], $horaFin, $duracion,
        $precioTotal, $d['estado']??'pendiente', $d['notas']??null,
        $id, $negocioId,
    ]);
    if (!empty($serviciosList)) { guardarTurnoServicios($pdo, $id, $negocioId, $serviciosList); }
    Response::success('Turno actualizado', []);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { Response::error('ID requerido', 400); exit; }
    $pdo->prepare("UPDATE turnos SET estado='cancelado' WHERE id=? AND negocio_id=?")->execute([$id, $negocioId]);
    Response::success('Turno cancelado', []);
    exit;
}
Response::error('Método no soportado', 405);
