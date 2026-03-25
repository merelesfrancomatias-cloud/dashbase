<?php
/**
 * Reserva pública de canchas — no requiere sesión.
 * GET  ?negocio_id=N                              → info del negocio + canchas activas
 * GET  ?negocio_id=N&cancha_id=C&fecha=YYYY-MM-DD → slots disponibles para esa cancha/fecha
 * POST {negocio_id,cancha_id,fecha,hora_inicio,duracion_horas,cliente_nombre,cliente_telefono}
 */
require_once __DIR__ . '/../../api/bootstrap.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

const HORA_INICIO = '08:00';
const HORA_FIN    = '23:00';

// ── GET ──────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $negocioId = (int)($_GET['negocio_id'] ?? 0);
    if (!$negocioId) { Response::error('negocio_id requerido', 400); exit; }

    $stNeg = $pdo->prepare("SELECT id, nombre, rubro, logo, imagen_portada, telefono, whatsapp, instagram, facebook, direccion, ciudad, provincia FROM negocios WHERE id=? AND activo=1");
    $stNeg->execute([$negocioId]);
    $negocio = $stNeg->fetch(PDO::FETCH_ASSOC);
    if (!$negocio) { Response::error('Negocio no encontrado', 404); exit; }

    // Solo info + canchas
    if (!isset($_GET['fecha'])) {
        $stC = $pdo->prepare("SELECT id, nombre, deporte, descripcion, precio_hora, capacidad FROM canchas WHERE negocio_id=? AND activo=1 ORDER BY nombre");
        $stC->execute([$negocioId]);
        $canchas = $stC->fetchAll(PDO::FETCH_ASSOC);
        Response::success('ok', ['negocio' => $negocio, 'canchas' => $canchas]);
        exit;
    }

    // Slots disponibles para cancha + fecha
    $fecha     = $_GET['fecha'];
    $canchaId  = (int)($_GET['cancha_id'] ?? 0);
    $durHoras  = max(1, (int)($_GET['duracion_horas'] ?? 1));

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { Response::error('Fecha inválida', 400); exit; }
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) { Response::error('Fecha pasada', 400); exit; }
    if (!$canchaId) { Response::error('cancha_id requerido', 400); exit; }

    // Validar cancha pertenece al negocio
    $stC = $pdo->prepare("SELECT precio_hora FROM canchas WHERE id=? AND negocio_id=? AND activo=1");
    $stC->execute([$canchaId, $negocioId]);
    $cancha = $stC->fetch(PDO::FETCH_ASSOC);
    if (!$cancha) { Response::error('Cancha no encontrada', 404); exit; }

    // Reservas ocupadas del día
    $stOc = $pdo->prepare("SELECT hora_inicio, hora_fin FROM reservas_canchas WHERE cancha_id=? AND fecha=? AND estado NOT IN ('cancelada')");
    $stOc->execute([$canchaId, $fecha]);
    $ocupadas = $stOc->fetchAll(PDO::FETCH_ASSOC);

    $rangos = array_map(fn($o) => [
        'ini' => strtotime("$fecha " . substr($o['hora_inicio'], 0, 5)),
        'fin' => strtotime("$fecha " . substr($o['hora_fin'],   0, 5)),
    ], $ocupadas);

    $durSeg  = $durHoras * 3600;
    $cursor  = strtotime("$fecha " . HORA_INICIO);
    $limite  = strtotime("$fecha " . HORA_FIN) - $durSeg;
    $slots   = [];

    while ($cursor <= $limite) {
        $libre = true;
        foreach ($rangos as $r) {
            if ($cursor < $r['fin'] && ($cursor + $durSeg) > $r['ini']) {
                $libre = false;
                break;
            }
        }
        if ($libre) {
            $horaFin = date('H:i', $cursor + $durSeg);
            $slots[] = [
                'hora_inicio' => date('H:i', $cursor),
                'hora_fin'    => $horaFin,
                'monto'       => round((float)$cancha['precio_hora'] * $durHoras, 2),
            ];
        }
        $cursor += 3600; // slots cada 1 hora
    }

    Response::success('ok', ['slots' => $slots, 'fecha' => $fecha, 'duracion_horas' => $durHoras, 'precio_hora' => (float)$cancha['precio_hora']]);
    exit;
}

// ── POST ─────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d          = json_decode(file_get_contents('php://input'), true) ?: [];
    $negocioId  = (int)($d['negocio_id']  ?? 0);
    $canchaId   = (int)($d['cancha_id']   ?? 0);
    $fecha      = $d['fecha']             ?? '';
    $hora       = $d['hora_inicio']       ?? '';
    $durHoras   = max(1, (int)($d['duracion_horas'] ?? 1));
    $nombre     = trim($d['cliente_nombre']    ?? '');
    $telefono   = trim($d['cliente_telefono']  ?? '');

    if (!$negocioId || !$canchaId || !$fecha || !$hora || !$nombre) {
        Response::error('Datos incompletos', 400); exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { Response::error('Fecha inválida', 400); exit; }
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) { Response::error('No se puede reservar en fechas pasadas', 400); exit; }

    // Validar negocio y cancha
    $stNeg = $pdo->prepare("SELECT id, nombre, whatsapp FROM negocios WHERE id=? AND activo=1");
    $stNeg->execute([$negocioId]);
    $negRow = $stNeg->fetch(PDO::FETCH_ASSOC);
    if (!$negRow) { Response::error('Negocio no encontrado', 404); exit; }

    $stC = $pdo->prepare("SELECT id, nombre, precio_hora FROM canchas WHERE id=? AND negocio_id=? AND activo=1");
    $stC->execute([$canchaId, $negocioId]);
    $canchaRow = $stC->fetch(PDO::FETCH_ASSOC);
    if (!$canchaRow) { Response::error('Cancha no encontrada', 404); exit; }

    $horaFin = date('H:i', strtotime("$fecha $hora") + $durHoras * 3600);
    $monto   = round((float)$canchaRow['precio_hora'] * $durHoras, 2);

    // Verificar disponibilidad
    $stCheck = $pdo->prepare("SELECT COUNT(*) FROM reservas_canchas WHERE cancha_id=? AND fecha=? AND estado NOT IN ('cancelada') AND hora_inicio < ? AND hora_fin > ?");
    $stCheck->execute([$canchaId, $fecha, $horaFin, $hora]);
    if ($stCheck->fetchColumn() > 0) {
        Response::error('El horario ya no está disponible. Por favor elegí otro.', 409); exit;
    }

    $st = $pdo->prepare("INSERT INTO reservas_canchas (cancha_id, cliente_nombre, cliente_telefono, fecha, hora_inicio, hora_fin, duracion_horas, monto, estado) VALUES (?,?,?,?,?,?,?,?,'pendiente')");
    $st->execute([$canchaId, $nombre, $telefono ?: null, $fecha, $hora, $horaFin, $durHoras, $monto]);

    Response::success('Reserva creada', [
        'id'          => $pdo->lastInsertId(),
        'hora_inicio' => $hora,
        'hora_fin'    => $horaFin,
        'monto'       => $monto,
        'cancha'      => $canchaRow['nombre'],
        'negocio_wa'  => $negRow['whatsapp'] ?? null,
    ]);
    exit;
}

Response::error('Método no permitido', 405);
