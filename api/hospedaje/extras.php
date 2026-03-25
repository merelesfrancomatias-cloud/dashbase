<?php
/**
 * Cargos extra de una reserva de hospedaje
 * GET    ?reserva_id=X  → lista de cargos
 * POST   {reserva_id, descripcion, cantidad, precio_unit}
 * DELETE ?id=X
 */
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $reservaId = intval($_GET['reserva_id'] ?? 0);
        if (!$reservaId) { Response::error('reserva_id requerido', 400); exit; }

        // Verificar que la reserva pertenece al negocio
        $stV = $pdo->prepare("SELECT id FROM hospedaje_reservas WHERE id=? AND negocio_id=?");
        $stV->execute([$reservaId, $negocioId]);
        if (!$stV->fetch()) { Response::error('Reserva no encontrada', 404); exit; }

        $st = $pdo->prepare("SELECT * FROM hospedaje_cargos_extra WHERE reserva_id=? ORDER BY created_at");
        $st->execute([$reservaId]);
        $cargos = $st->fetchAll(PDO::FETCH_ASSOC);

        // Total de extras
        $totalExtras = array_sum(array_column($cargos, 'total'));

        Response::success('ok', ['cargos' => $cargos, 'total_extras' => round($totalExtras, 2)]);
        exit;
    }

    if ($method === 'POST') {
        $d          = json_decode(file_get_contents('php://input'), true) ?: [];
        $reservaId  = intval($d['reserva_id'] ?? 0);
        $desc       = trim($d['descripcion'] ?? '');
        $cantidad   = max(0.01, (float)($d['cantidad'] ?? 1));
        $precioUnit = max(0, (float)($d['precio_unit'] ?? 0));

        if (!$reservaId || !$desc) { Response::error('Datos incompletos', 400); exit; }

        // Verificar que la reserva pertenece al negocio
        $stV = $pdo->prepare("SELECT id FROM hospedaje_reservas WHERE id=? AND negocio_id=?");
        $stV->execute([$reservaId, $negocioId]);
        if (!$stV->fetch()) { Response::error('Reserva no encontrada', 404); exit; }

        $total = round($cantidad * $precioUnit, 2);

        $st = $pdo->prepare("INSERT INTO hospedaje_cargos_extra (reserva_id, negocio_id, descripcion, cantidad, precio_unit, total) VALUES (?,?,?,?,?,?)");
        $st->execute([$reservaId, $negocioId, $desc, $cantidad, $precioUnit, $total]);

        Response::success('Cargo agregado', ['id' => $pdo->lastInsertId(), 'total' => $total]);
        exit;
    }

    if ($method === 'DELETE') {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) { Response::error('id requerido', 400); exit; }

        // Verificar pertenece al negocio via JOIN
        $st = $pdo->prepare("DELETE e FROM hospedaje_cargos_extra e JOIN hospedaje_reservas r ON r.id=e.reserva_id WHERE e.id=? AND r.negocio_id=?");
        $st->execute([$id, $negocioId]);
        if ($st->rowCount() === 0) { Response::error('Cargo no encontrado', 404); exit; }

        Response::success('Cargo eliminado');
        exit;
    }

    Response::error('Método no permitido', 405);
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
