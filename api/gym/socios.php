<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();

$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Auto-actualizar socios vencidos (fecha pasó y aún figuran como activo)
$db->prepare("UPDATE gym_socios SET estado='vencido' WHERE negocio_id=? AND estado='activo' AND fecha_vencimiento IS NOT NULL AND fecha_vencimiento < CURDATE()")
   ->execute([$negocio_id]);

// GET - listar o buscar socios
if ($method === 'GET') {
    $estado = $_GET['estado'] ?? '';
    $buscar = $_GET['q'] ?? '';
    $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
        $stmt = $db->prepare("
            SELECT s.*, p.nombre AS plan_nombre, p.precio AS plan_precio, p.duracion_dias
            FROM gym_socios s
            LEFT JOIN gym_planes p ON p.id = s.plan_id
            WHERE s.id = ? AND s.negocio_id = ?
        ");
        $stmt->execute([$id, $negocio_id]);
        $socio = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$socio) Response::error('Socio no encontrado', 404);
        Response::success('ok', $socio);
    }

    $where = "s.negocio_id = ?";
    $params = [$negocio_id];

    if ($estado) {
        $where .= " AND s.estado = ?";
        $params[] = $estado;
    }
    if ($buscar) {
        $where .= " AND (s.nombre LIKE ? OR s.apellido LIKE ? OR s.email LIKE ? OR s.telefono LIKE ?)";
        $b = "%$buscar%";
        $params = array_merge($params, [$b, $b, $b, $b]);
    }

    $stmt = $db->prepare("
        SELECT s.id, s.nombre, s.apellido, s.email, s.telefono, s.plan_id,
               s.fecha_inicio, s.fecha_vencimiento, s.estado, s.notas, s.qr_token,
               p.nombre AS plan_nombre,
               p.precio AS plan_precio,
               DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes
        FROM gym_socios s
        LEFT JOIN gym_planes p ON p.id = s.plan_id
        WHERE $where
        ORDER BY s.apellido, s.nombre
    ");
    $stmt->execute($params);
    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats generales
    $stmtStats = $db->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN estado='activo' THEN 1 ELSE 0 END) AS activos,
            SUM(CASE WHEN estado='vencido' THEN 1 ELSE 0 END) AS vencidos,
            SUM(CASE WHEN estado='suspendido' THEN 1 ELSE 0 END) AS suspendidos,
            SUM(CASE WHEN estado='activo' AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 7 THEN 1 ELSE 0 END) AS por_vencer
        FROM gym_socios WHERE negocio_id = ?
    ");
    $stmtStats->execute([$negocio_id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    Response::success('ok', ['socios' => $socios, 'stats' => $stats]);
}

// POST - crear socio
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $nombre    = trim($data['nombre'] ?? '');
    $apellido  = trim($data['apellido'] ?? '');
    $email     = trim($data['email'] ?? '');
    $telefono  = trim($data['telefono'] ?? '');
    $plan_id   = !empty($data['plan_id']) ? (int)$data['plan_id'] : null;
    $fecha_ini = $data['fecha_inicio'] ?? date('Y-m-d');
    $notas     = trim($data['notas'] ?? '');

    if (!$nombre || !$apellido) Response::error('Nombre y apellido requeridos', 400);

    // Calcular vencimiento según plan
    $fecha_venc = null;
    if ($plan_id) {
        $planStmt = $db->prepare("SELECT duracion_dias FROM gym_planes WHERE id=? AND negocio_id=?");
        $planStmt->execute([$plan_id, $negocio_id]);
        $plan = $planStmt->fetch(PDO::FETCH_ASSOC);
        if ($plan) {
            $fecha_venc = date('Y-m-d', strtotime($fecha_ini . ' +' . $plan['duracion_dias'] . ' days'));
        }
    }

    $qr_token = bin2hex(random_bytes(32));
    $stmt = $db->prepare("
        INSERT INTO gym_socios (negocio_id,nombre,apellido,email,telefono,plan_id,fecha_inicio,fecha_vencimiento,estado,notas,qr_token)
        VALUES (?,?,?,?,?,?,?,?,'activo',?,?)
    ");
    $stmt->execute([$negocio_id,$nombre,$apellido,$email,$telefono,$plan_id,$fecha_ini,$fecha_venc,$notas,$qr_token]);
    $id = $db->lastInsertId();

    // Registrar pago si hay monto
    if (!empty($data['monto']) && $data['monto'] > 0) {
        $stmtPago = $db->prepare("
            INSERT INTO gym_pagos (negocio_id,socio_id,plan_id,monto,fecha,metodo,periodo_desde,periodo_hasta)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmtPago->execute([
            $negocio_id, $id, $plan_id,
            (float)$data['monto'],
            date('Y-m-d'),
            $data['metodo'] ?? 'efectivo',
            $fecha_ini,
            $fecha_venc
        ]);
    }

    Response::success('Socio creado', ['id' => $id]);
}

// PUT - actualizar socio
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($data['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    $fields = [];
    $params = [];
    $allowed = ['nombre','apellido','email','telefono','plan_id','fecha_inicio','fecha_vencimiento','estado','notas'];
    foreach ($allowed as $f) {
        if (isset($data[$f])) {
            $fields[] = "$f = ?";
            $params[] = $data[$f] === '' ? null : $data[$f];
        }
    }

    // Si cambia el plan, recalcular vencimiento
    if (isset($data['plan_id']) && $data['plan_id'] && !isset($data['fecha_vencimiento'])) {
        $fi = $data['fecha_inicio'] ?? date('Y-m-d');
        $planStmt = $db->prepare("SELECT duracion_dias FROM gym_planes WHERE id=? AND negocio_id=?");
        $planStmt->execute([$data['plan_id'], $negocio_id]);
        $plan = $planStmt->fetch(PDO::FETCH_ASSOC);
        if ($plan) {
            $fields[] = "fecha_vencimiento = ?";
            $params[] = date('Y-m-d', strtotime($fi . ' +' . $plan['duracion_dias'] . ' days'));
        }
    }

    if (empty($fields)) Response::error('Nada que actualizar', 400);

    $params[] = $id;
    $params[] = $negocio_id;
    $db->prepare("UPDATE gym_socios SET " . implode(', ', $fields) . " WHERE id=? AND negocio_id=?")->execute($params);

    Response::success('Socio actualizado');
}

// DELETE - marcar inactivo
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("UPDATE gym_socios SET estado='inactivo' WHERE id=? AND negocio_id=?")->execute([$id, $negocio_id]);
    Response::success('Socio desactivado');
}
