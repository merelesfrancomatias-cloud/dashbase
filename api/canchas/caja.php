<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Response.php';

session_start();
Auth::check();
$negocioId = (int)$_SESSION['negocio_id'];

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$fecha  = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// ── GET: resumen de caja del día ─────────────────────────────────────────────
if ($method === 'GET') {

    // Totales del día
    $stmtTotales = $db->prepare("
        SELECT
            COUNT(rc.id)                                                        AS total_reservas,
            SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END)      AS ingresos_total,
            SUM(CASE WHEN rc.estado='confirmada' AND rc.metodo_pago='efectivo'      THEN rc.monto ELSE 0 END) AS efectivo,
            SUM(CASE WHEN rc.estado='confirmada' AND rc.metodo_pago='transferencia' THEN rc.monto ELSE 0 END) AS transferencia,
            SUM(CASE WHEN rc.estado='confirmada' AND rc.metodo_pago='tarjeta'       THEN rc.monto ELSE 0 END) AS tarjeta,
            COUNT(CASE WHEN rc.estado='confirmada'  THEN 1 END)                 AS confirmadas,
            COUNT(CASE WHEN rc.estado='pendiente'   THEN 1 END)                 AS pendientes,
            COUNT(CASE WHEN rc.estado='cancelada'   THEN 1 END)                 AS canceladas
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.fecha = ?
    ");
    $stmtTotales->execute([$negocioId, $fecha]);
    $totales = $stmtTotales->fetch(PDO::FETCH_ASSOC);

    // Desglose por cancha
    $stmtPorCancha = $db->prepare("
        SELECT
            c.nombre as cancha_nombre,
            c.deporte,
            COUNT(rc.id) as reservas,
            SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END) as ingresos
        FROM canchas c
        LEFT JOIN reservas_canchas rc ON rc.cancha_id = c.id AND rc.fecha = ?
        WHERE c.negocio_id = ? AND c.activo = 1
        GROUP BY c.id
        ORDER BY ingresos DESC
    ");
    $stmtPorCancha->execute([$fecha, $negocioId]);
    $porCancha = $stmtPorCancha->fetchAll(PDO::FETCH_ASSOC);

    // Detalle de reservas confirmadas del día
    $stmtDetalle = $db->prepare("
        SELECT rc.*, c.nombre as cancha_nombre, c.deporte
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.fecha = ? AND rc.estado = 'confirmada'
        ORDER BY rc.hora_inicio ASC
    ");
    $stmtDetalle->execute([$negocioId, $fecha]);
    $detalle = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

    // Comparativo semana (últimos 7 días)
    $stmtSemana = $db->prepare("
        SELECT rc.fecha,
               SUM(CASE WHEN rc.estado='confirmada' THEN rc.monto ELSE 0 END) as ingresos,
               COUNT(CASE WHEN rc.estado='confirmada' THEN 1 END) as reservas
        FROM reservas_canchas rc
        JOIN canchas c ON c.id = rc.cancha_id
        WHERE c.negocio_id = ? AND rc.fecha BETWEEN DATE_SUB(?, INTERVAL 6 DAY) AND ?
        GROUP BY rc.fecha
        ORDER BY rc.fecha ASC
    ");
    $stmtSemana->execute([$negocioId, $fecha, $fecha]);
    $semana = $stmtSemana->fetchAll(PDO::FETCH_ASSOC);

    Response::success([
        'fecha'     => $fecha,
        'totales'   => $totales,
        'porCancha' => $porCancha,
        'detalle'   => $detalle,
        'semana'    => $semana,
    ]);
}
