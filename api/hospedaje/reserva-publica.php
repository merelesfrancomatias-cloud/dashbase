<?php
/**
 * Reserva pública de hospedaje — sin autenticación.
 * GET  ?negocio_id=N                                           → info del negocio + habitaciones activas
 * GET  ?negocio_id=N&checkin=YYYY-MM-DD&checkout=YYYY-MM-DD   → habitaciones disponibles en ese rango
 * POST {negocio_id, habitacion_id, huesped_nombre, huesped_telefono, huesped_email,
 *       checkin_fecha, checkin_hora, checkout_fecha, checkout_hora, personas, tipo_estadia}
 */
require_once __DIR__ . '/../../api/bootstrap.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $negocioId = (int)($_GET['negocio_id'] ?? 0);
    if (!$negocioId) { Response::error('negocio_id requerido', 400); exit; }

    $stN = $pdo->prepare("SELECT id, nombre, rubro, logo, imagen_portada, telefono, whatsapp, instagram, facebook, direccion, ciudad, provincia FROM negocios WHERE id=? AND activo=1");
    $stN->execute([$negocioId]);
    $negocio = $stN->fetch(PDO::FETCH_ASSOC);
    if (!$negocio) { Response::error('Negocio no encontrado', 404); exit; }

    $checkin  = $_GET['checkin']  ?? '';
    $checkout = $_GET['checkout'] ?? '';

    // Solo info + todas las habitaciones
    if (!$checkin || !$checkout) {
        $stH = $pdo->prepare("SELECT id, numero, nombre, tipo, piso, capacidad, precio_noche, precio_hora, descripcion, amenities FROM hospedaje_habitaciones WHERE negocio_id=? AND activo=1 ORDER BY tipo, numero");
        $stH->execute([$negocioId]);
        $habs = $stH->fetchAll(PDO::FETCH_ASSOC);
        foreach ($habs as &$h) {
            $h['amenities'] = json_decode($h['amenities'] ?? '[]', true) ?: [];
        }
        unset($h);
        Response::success('ok', ['negocio' => $negocio, 'habitaciones' => $habs]);
        exit;
    }

    // Validar fechas
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkin) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkout)) {
        Response::error('Fechas inválidas', 400); exit;
    }
    if ($checkin >= $checkout) { Response::error('Check-out debe ser posterior al check-in', 400); exit; }
    if ($checkin < date('Y-m-d')) { Response::error('No se puede reservar en fechas pasadas', 400); exit; }

    // Habitaciones ocupadas en ese rango
    $stOc = $pdo->prepare("SELECT DISTINCT habitacion_id FROM hospedaje_reservas
        WHERE negocio_id=? AND estado NOT IN ('cancelada')
        AND checkin_fecha < ? AND checkout_fecha > ?");
    $stOc->execute([$negocioId, $checkout, $checkin]);
    $ocupadas = array_column($stOc->fetchAll(PDO::FETCH_ASSOC), 'habitacion_id');

    // Habitaciones activas del negocio
    $stH = $pdo->prepare("SELECT id, numero, nombre, tipo, piso, capacidad, precio_noche, precio_hora, descripcion, amenities FROM hospedaje_habitaciones WHERE negocio_id=? AND activo=1 ORDER BY tipo, numero");
    $stH->execute([$negocioId]);
    $habs = $stH->fetchAll(PDO::FETCH_ASSOC);

    // Calcular noches y marcar disponibilidad
    $noches = max(1, (int)((strtotime($checkout) - strtotime($checkin)) / 86400));
    foreach ($habs as &$h) {
        $h['amenities']  = json_decode($h['amenities'] ?? '[]', true) ?: [];
        $h['disponible'] = !in_array($h['id'], $ocupadas);
        $h['total']      = round((float)$h['precio_noche'] * $noches, 2);
        $h['noches']     = $noches;
    }
    unset($h);

    Response::success('ok', ['negocio' => $negocio, 'habitaciones' => $habs, 'noches' => $noches, 'checkin' => $checkin, 'checkout' => $checkout]);
    exit;
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d           = json_decode(file_get_contents('php://input'), true) ?: [];
    $negocioId   = (int)($d['negocio_id']   ?? 0);
    $habId       = (int)($d['habitacion_id'] ?? 0);
    $nombre      = trim($d['huesped_nombre']    ?? '');
    $telefono    = trim($d['huesped_telefono']  ?? '');
    $email       = trim($d['huesped_email']     ?? '');
    $checkin     = $d['checkin_fecha']  ?? '';
    $horaIn      = $d['checkin_hora']   ?? '14:00';
    $checkout    = $d['checkout_fecha'] ?? '';
    $horaOut     = $d['checkout_hora']  ?? '10:00';
    $personas    = max(1, (int)($d['personas'] ?? 1));
    $tipo        = in_array($d['tipo_estadia'] ?? '', ['noche','hora','semana']) ? $d['tipo_estadia'] : 'noche';

    if (!$negocioId || !$habId || !$nombre || !$checkin || !$checkout) {
        Response::error('Datos incompletos', 400); exit;
    }
    if ($checkin >= $checkout) { Response::error('Check-out debe ser posterior al check-in', 400); exit; }
    if ($checkin < date('Y-m-d')) { Response::error('No se puede reservar en fechas pasadas', 400); exit; }

    // Validar habitación
    $stH = $pdo->prepare("SELECT id, precio_noche, precio_hora FROM hospedaje_habitaciones WHERE id=? AND negocio_id=? AND activo=1");
    $stH->execute([$habId, $negocioId]);
    $hab = $stH->fetch(PDO::FETCH_ASSOC);
    if (!$hab) { Response::error('Habitación no encontrada', 404); exit; }

    // Verificar disponibilidad
    $stCheck = $pdo->prepare("SELECT COUNT(*) FROM hospedaje_reservas WHERE habitacion_id=? AND estado NOT IN ('cancelada') AND checkin_fecha < ? AND checkout_fecha > ?");
    $stCheck->execute([$habId, $checkout, $checkin]);
    if ($stCheck->fetchColumn() > 0) { Response::error('La habitación ya no está disponible para esas fechas.', 409); exit; }

    // Negocio para WA
    $stN = $pdo->prepare("SELECT nombre, whatsapp FROM negocios WHERE id=? AND activo=1");
    $stN->execute([$negocioId]);
    $neg = $stN->fetch(PDO::FETCH_ASSOC);

    // Calcular total
    $noches       = max(1, (int)((strtotime($checkout) - strtotime($checkin)) / 86400));
    $precioUnit   = $tipo === 'hora' ? (float)$hab['precio_hora'] : (float)$hab['precio_noche'];
    $unidades     = $tipo === 'semana' ? max(1, (int)ceil($noches/7)) : $noches;
    $total        = round($precioUnit * $unidades, 2);

    $st = $pdo->prepare("INSERT INTO hospedaje_reservas
        (negocio_id, habitacion_id, huesped_nombre, huesped_telefono, huesped_email,
         tipo_estadia, checkin_fecha, checkin_hora, checkout_fecha, checkout_hora,
         noches, personas, precio_unitario, total, seña, estado)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,'reservada')");
    $st->execute([$negocioId, $habId, $nombre, $telefono ?: null, $email ?: null,
        $tipo, $checkin, $horaIn, $checkout, $horaOut, $unidades, $personas, $precioUnit, $total]);

    Response::success('Reserva creada', [
        'id'         => $pdo->lastInsertId(),
        'checkin'    => $checkin,
        'checkout'   => $checkout,
        'noches'     => $unidades,
        'total'      => $total,
        'negocio_wa' => $neg['whatsapp'] ?? null,
        'negocio'    => $neg['nombre']   ?? '',
    ]);
    exit;
}

Response::error('Método no permitido', 405);
