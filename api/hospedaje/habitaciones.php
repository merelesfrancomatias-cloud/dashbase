<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT h.*,
                   r.id AS reserva_id, r.huesped_nombre, r.huesped_telefono,
                   r.checkin_fecha, r.checkin_hora, r.checkout_fecha, r.checkout_hora,
                   r.noches, r.personas, r.total, r.estado AS reserva_estado, r.tipo_estadia
            FROM hospedaje_habitaciones h
            LEFT JOIN hospedaje_reservas r
                ON r.habitacion_id = h.id AND r.negocio_id = h.negocio_id
                AND r.estado = 'checkin'
            WHERE h.id = :id AND h.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $hab = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hab) Response::error('Habitación no encontrada', 404);
        Response::success('OK', $hab);
    }

    // Listado completo con reserva activa
    $stmt = $pdo->prepare("
        SELECT h.*,
               r.id            AS reserva_id,
               r.huesped_nombre,
               r.huesped_telefono,
               r.checkin_fecha,
               r.checkin_hora,
               r.checkout_fecha,
               r.checkout_hora,
               r.noches,
               r.personas,
               r.total,
               r.tipo_estadia,
               r.estado        AS reserva_estado
        FROM hospedaje_habitaciones h
        LEFT JOIN hospedaje_reservas r
            ON r.habitacion_id = h.id AND r.negocio_id = h.negocio_id
            AND r.estado = 'checkin'
        WHERE h.negocio_id = :nid AND h.activo = 1
        ORDER BY h.piso, h.numero + 0, h.numero
    ");
    $stmt->execute([':nid' => $negocioId]);
    $habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $stats = ['total' => 0, 'libre' => 0, 'ocupada' => 0, 'limpieza' => 0, 'mantenimiento' => 0, 'bloqueada' => 0];
    foreach ($habitaciones as $h) {
        $stats['total']++;
        $stats[$h['estado']] = ($stats[$h['estado']] ?? 0) + 1;
    }

    Response::success('OK', ['habitaciones' => $habitaciones, 'stats' => $stats]);
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['numero'])) Response::error('El número de habitación es requerido', 400);
    if (empty($d['precio_noche']) && empty($d['precio_hora'])) Response::error('Ingresá al menos un precio', 400);

    $stmt = $pdo->prepare("
        INSERT INTO hospedaje_habitaciones
            (negocio_id, numero, nombre, tipo, piso, capacidad, precio_noche, precio_hora, descripcion, amenities, estado)
        VALUES
            (:nid, :numero, :nombre, :tipo, :piso, :cap, :pnoche, :phora, :desc, :amen, 'libre')
    ");
    $stmt->execute([
        ':nid'    => $negocioId,
        ':numero' => trim($d['numero']),
        ':nombre' => $d['nombre'] ?? null,
        ':tipo'   => $d['tipo']   ?? 'doble',
        ':piso'   => $d['piso']   ?? null,
        ':cap'    => (int)($d['capacidad'] ?? 2),
        ':pnoche' => (float)($d['precio_noche'] ?? 0),
        ':phora'  => !empty($d['precio_hora']) ? (float)$d['precio_hora'] : null,
        ':desc'   => $d['descripcion'] ?? null,
        ':amen'   => !empty($d['amenities']) ? json_encode($d['amenities']) : null,
    ]);
    Response::success('Habitación creada', ['id' => $pdo->lastInsertId()], 201);
}

if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];

    // Solo actualizar estado
    if (isset($d['estado']) && count($d) === 1) {
        $stmt = $pdo->prepare("UPDATE hospedaje_habitaciones SET estado = :estado WHERE id = :id AND negocio_id = :nid");
        $stmt->execute([':estado' => $d['estado'], ':id' => $id, ':nid' => $negocioId]);
        Response::success('Estado actualizado');
    }

    // Actualizar datos completos
    $stmt = $pdo->prepare("
        UPDATE hospedaje_habitaciones SET
            numero       = :numero,
            nombre       = :nombre,
            tipo         = :tipo,
            piso         = :piso,
            capacidad    = :cap,
            precio_noche = :pnoche,
            precio_hora  = :phora,
            descripcion  = :desc,
            amenities    = :amen,
            estado       = :estado
        WHERE id = :id AND negocio_id = :nid
    ");
    $stmt->execute([
        ':numero' => trim($d['numero'] ?? ''),
        ':nombre' => $d['nombre'] ?? null,
        ':tipo'   => $d['tipo']   ?? 'doble',
        ':piso'   => $d['piso']   ?? null,
        ':cap'    => (int)($d['capacidad'] ?? 2),
        ':pnoche' => (float)($d['precio_noche'] ?? 0),
        ':phora'  => !empty($d['precio_hora']) ? (float)$d['precio_hora'] : null,
        ':desc'   => $d['descripcion'] ?? null,
        ':amen'   => !empty($d['amenities']) ? json_encode($d['amenities']) : null,
        ':estado' => $d['estado'] ?? 'libre',
        ':id'     => $id,
        ':nid'    => $negocioId,
    ]);
    Response::success('Habitación actualizada');
}

if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $stmt = $pdo->prepare("UPDATE hospedaje_habitaciones SET activo = 0 WHERE id = :id AND negocio_id = :nid");
    $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Habitación eliminada');
}
