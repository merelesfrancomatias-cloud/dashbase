<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();

$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $socio_id = isset($_GET['socio_id']) ? (int)$_GET['socio_id'] : 0;
    $mes      = $_GET['mes'] ?? date('Y-m');

    $where = "p.negocio_id=?";
    $params = [$negocio_id];

    if ($socio_id) { $where .= " AND p.socio_id=?"; $params[] = $socio_id; }
    if ($mes) { $where .= " AND DATE_FORMAT(p.fecha,'%Y-%m')=?"; $params[] = $mes; }

    $stmt = $db->prepare("
        SELECT p.*,
               CONCAT(s.nombre,' ',s.apellido) AS socio_nombre,
               pl.nombre AS plan_nombre
        FROM gym_pagos p
        LEFT JOIN gym_socios s ON s.id = p.socio_id
        LEFT JOIN gym_planes pl ON pl.id = p.plan_id
        WHERE $where
        ORDER BY p.fecha DESC
    ");
    $stmt->execute($params);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtTot = $db->prepare("SELECT SUM(monto) FROM gym_pagos WHERE negocio_id=? AND DATE_FORMAT(fecha,'%Y-%m')=?");
    $stmtTot->execute([$negocio_id, $mes]);
    $total_mes = (float)($stmtTot->fetchColumn() ?? 0);

    Response::success('ok', ['pagos' => $pagos, 'total_mes' => $total_mes]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $socio_id = (int)($data['socio_id'] ?? 0);
    $plan_id  = !empty($data['plan_id']) ? (int)$data['plan_id'] : null;
    $monto    = (float)($data['monto'] ?? 0);
    $fecha    = $data['fecha'] ?? date('Y-m-d');
    $metodo   = $data['metodo'] ?? 'efectivo';
    $notas    = trim($data['notas'] ?? '');

    if (!$socio_id || $monto <= 0) Response::error('socio_id y monto requeridos', 400);

    // Calcular periodos según plan
    $periodo_desde = $data['periodo_desde'] ?? $fecha;
    $periodo_hasta = $data['periodo_hasta'] ?? null;
    if ($plan_id && !$periodo_hasta) {
        $planStmt = $db->prepare("SELECT duracion_dias FROM gym_planes WHERE id=?");
        $planStmt->execute([$plan_id]);
        $plan = $planStmt->fetch(PDO::FETCH_ASSOC);
        if ($plan) $periodo_hasta = date('Y-m-d', strtotime($periodo_desde . ' +' . $plan['duracion_dias'] . ' days'));
    }

    $stmt = $db->prepare("INSERT INTO gym_pagos (negocio_id,socio_id,plan_id,monto,fecha,metodo,periodo_desde,periodo_hasta,notas) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$negocio_id,$socio_id,$plan_id,$monto,$fecha,$metodo,$periodo_desde,$periodo_hasta,$notas]);

    // Actualizar estado y vencimiento del socio
    if ($plan_id && $periodo_hasta) {
        $db->prepare("UPDATE gym_socios SET estado='activo', plan_id=?, fecha_inicio=?, fecha_vencimiento=? WHERE id=? AND negocio_id=?")
           ->execute([$plan_id, $periodo_desde, $periodo_hasta, $socio_id, $negocio_id]);
    }

    Response::success('Pago registrado', ['id' => $db->lastInsertId()]);
}
