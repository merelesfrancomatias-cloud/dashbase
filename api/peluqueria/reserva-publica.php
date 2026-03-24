<?php
/**
 * Reserva pública de turnos – no requiere sesión.
 * Parámetros:
 *   GET  ?negocio_id=N               → info del negocio + servicios
 *   GET  ?negocio_id=N&fecha=YYYY-MM-DD&servicio_id=S → slots disponibles
 *   POST {negocio_id,servicio_id,fecha,hora_inicio,cliente_nombre,cliente_telefono}
 */
require_once __DIR__ . '/../../api/bootstrap.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── Horario comercial configurable ──────────────────────────────────────────
const HORA_INICIO = '09:00';
const HORA_FIN    = '20:00';
const SLOT_MIN    = 30;     // intervalo de slots en minutos

// ── GET ──────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $negocioId = (int)($_GET['negocio_id'] ?? 0);
    if (!$negocioId) { Response::error('negocio_id requerido', 400); exit; }

    // Verificar que el negocio existe y el plan está activo
    $stNeg = $pdo->prepare("SELECT id, nombre, rubro, logo, imagen_portada, telefono, whatsapp, instagram, facebook, direccion, ciudad, provincia FROM negocios WHERE id=? AND activo=1");
    $stNeg->execute([$negocioId]);
    $negocio = $stNeg->fetch(PDO::FETCH_ASSOC);
    if (!$negocio) { Response::error('Negocio no encontrado', 404); exit; }

    // Solo info básica + servicios
    if (!isset($_GET['fecha'])) {
        $stServs = $pdo->prepare("SELECT id, nombre, duracion_min, precio, categoria, color FROM servicios WHERE negocio_id=? AND activo=1 ORDER BY categoria, nombre");
        $stServs->execute([$negocioId]);
        $servicios = $stServs->fetchAll(PDO::FETCH_ASSOC);
        Response::success('ok', ['negocio' => $negocio, 'servicios' => $servicios]);
        exit;
    }

    // Slots disponibles para fecha + servicio
    $fecha       = $_GET['fecha'];
    $servicioId  = (int)($_GET['servicio_id'] ?? 0);

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { Response::error('Fecha inválida', 400); exit; }
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) { Response::error('Fecha pasada', 400); exit; }

    $durMin = 30;
    if ($servicioId) {
        $stServ = $pdo->prepare("SELECT duracion_min FROM servicios WHERE id=? AND negocio_id=?");
        $stServ->execute([$servicioId, $negocioId]);
        $row = $stServ->fetch(PDO::FETCH_ASSOC);
        if ($row) $durMin = (int)$row['duracion_min'];
    }

    // Turnos ocupados del día (pendiente, confirmado, en_curso)
    $stOcupados = $pdo->prepare("SELECT hora_inicio, hora_fin FROM turnos WHERE negocio_id=? AND fecha=? AND estado NOT IN ('cancelado','no_show')");
    $stOcupados->execute([$negocioId, $fecha]);
    $ocupados = $stOcupados->fetchAll(PDO::FETCH_ASSOC);

    // Pre-computar rangos ocupados una vez (evita strtotime() repetido en el bucle interno)
    $rangosOcupados = array_map(fn($o) => [
        'ini' => strtotime("$fecha " . substr($o['hora_inicio'], 0, 5)),
        'fin' => strtotime("$fecha " . substr($o['hora_fin'],   0, 5)),
    ], $ocupados);

    // Generar slots
    $slots  = [];
    $cursor = strtotime("$fecha " . HORA_INICIO);
    $limite = strtotime("$fecha " . HORA_FIN) - ($durMin * 60);
    $durSeg = $durMin * 60;

    while ($cursor <= $limite) {
        $libre = true;
        foreach ($rangosOcupados as $o) {
            if ($cursor < $o['fin'] && ($cursor + $durSeg) > $o['ini']) {
                $libre = false;
                break;
            }
        }
        if ($libre) $slots[] = ['hora' => date('H:i', $cursor), 'hora_fin' => date('H:i', $cursor + $durSeg)];
        $cursor += SLOT_MIN * 60;
    }

    Response::success('ok', ['slots' => $slots, 'fecha' => $fecha, 'duracion_min' => $durMin]);
    exit;
}

// ── POST – crear reserva ─────────────────────────────────────────────────────
if ($method === 'POST') {
    $d          = json_decode(file_get_contents('php://input'), true) ?: [];
    $negocioId  = (int)($d['negocio_id'] ?? 0);
    $servicioId = (int)($d['servicio_id'] ?? 0);
    $fecha      = $d['fecha'] ?? '';
    $hora       = $d['hora_inicio'] ?? '';
    $nombre     = trim($d['cliente_nombre'] ?? '');
    $telefono   = trim($d['cliente_telefono'] ?? '');

    if (!$negocioId || !$fecha || !$hora || !$nombre) {
        Response::error('Datos incompletos', 400); exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { Response::error('Fecha inválida', 400); exit; }
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) { Response::error('No se puede reservar en fechas pasadas', 400); exit; }

    // Verificar negocio
    $stNeg = $pdo->prepare("SELECT id, nombre, whatsapp FROM negocios WHERE id=? AND activo=1");
    $stNeg->execute([$negocioId]);
    if (!$stNeg->fetch()) { Response::error('Negocio no encontrado', 404); exit; }

    // Obtener servicio
    $servicioNombre = null; $durMin = 30; $precio = 0;
    if ($servicioId) {
        $stServ = $pdo->prepare("SELECT nombre, duracion_min, precio FROM servicios WHERE id=? AND negocio_id=?");
        $stServ->execute([$servicioId, $negocioId]);
        $serv = $stServ->fetch(PDO::FETCH_ASSOC);
        if ($serv) { $servicioNombre = $serv['nombre']; $durMin = (int)$serv['duracion_min']; $precio = (float)$serv['precio']; }
    }

    $horaFin = date('H:i', strtotime("$fecha $hora") + $durMin * 60);

    // Verificar que el slot sigue libre
    $stCheck = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE negocio_id=? AND fecha=? AND estado NOT IN ('cancelado','no_show') AND hora_inicio < ? AND hora_fin > ?");
    $stCheck->execute([$negocioId, $fecha, $horaFin, $hora]);
    if ($stCheck->fetchColumn() > 0) {
        Response::error('El horario ya no está disponible. Por favor elegí otro.', 409); exit;
    }

    // Crear turno pendiente
    $st = $pdo->prepare("INSERT INTO turnos (negocio_id,cliente_nombre,cliente_telefono,servicio_id,servicio_nombre,fecha,hora_inicio,hora_fin,duracion_min,precio,estado) VALUES (?,?,?,?,?,?,?,?,?,?,'pendiente')");
    $st->execute([$negocioId, $nombre, $telefono ?: null, $servicioId ?: null, $servicioNombre, $fecha, $hora, $horaFin, $durMin, $precio]);

    $negRow = $stNeg->fetch(PDO::FETCH_ASSOC);
    Response::success('Turno reservado', [
        'id'          => $pdo->lastInsertId(),
        'hora_inicio' => $hora,
        'hora_fin'    => $horaFin,
        'negocio_wa'  => $negRow['whatsapp'] ?? null,
    ]);
    exit;
}

Response::error('Método no permitido', 405);
