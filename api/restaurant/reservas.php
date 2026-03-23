<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT r.*, m.numero AS mesa_numero, m.capacidad AS mesa_capacidad,
                   s.nombre AS sector_nombre
            FROM restaurant_reservas r
            LEFT JOIN restaurant_mesas m ON m.id = r.mesa_id
            LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
            WHERE r.id = :id AND r.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) Response::error('Reserva no encontrada', 404);
        Response::success('OK', $res);
    }

    // Filtros
    $where  = ["r.negocio_id = :nid"];
    $params = [':nid' => $negocioId];

    if (!empty($_GET['fecha']))  { $where[] = "r.fecha_reserva = :fecha";  $params[':fecha']  = $_GET['fecha']; }
    if (!empty($_GET['estado'])) { $where[] = "r.estado = :estado";        $params[':estado'] = $_GET['estado']; }
    if (!empty($_GET['mesa_id'])){ $where[] = "r.mesa_id = :mid";          $params[':mid']    = (int)$_GET['mesa_id']; }

    // Por defecto: hoy y futuros
    if (empty($_GET['fecha']) && empty($_GET['todas'])) {
        $where[]  = "r.fecha_reserva >= CURDATE()";
    }

    $stmt = $pdo->prepare("
        SELECT r.*,
               m.numero AS mesa_numero,
               s.nombre AS sector_nombre
        FROM restaurant_reservas r
        LEFT JOIN restaurant_mesas m ON m.id = r.mesa_id
        LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC
    ");
    $stmt->execute($params);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    $required = ['cliente_nombre', 'fecha_reserva', 'hora_inicio'];
    foreach ($required as $f) {
        if (empty($d[$f])) Response::error("Campo requerido: {$f}", 400);
    }

    // Verificar disponibilidad de mesa si se especificó
    if (!empty($d['mesa_id'])) {
        $stmt = $pdo->prepare("
            SELECT id FROM restaurant_reservas
            WHERE mesa_id = :mid AND fecha_reserva = :fecha
              AND estado NOT IN ('cancelada','no_show')
              AND (
                    (hora_inicio <= :hi AND (hora_fin IS NULL OR hora_fin > :hi))
                 OR (hora_inicio < :hf AND (hora_fin IS NULL OR hora_fin >= :hf))
              )
              AND id != :self
        ");
        $stmt->execute([
            ':mid'   => (int)$d['mesa_id'],
            ':fecha' => $d['fecha_reserva'],
            ':hi'    => $d['hora_inicio'],
            ':hf'    => $d['hora_fin'] ?? '23:59',
            ':self'  => 0,
        ]);
        if ($stmt->fetch()) Response::error('La mesa ya tiene una reserva en ese horario', 409);
    }

    $stmt = $pdo->prepare("
        INSERT INTO restaurant_reservas
            (negocio_id, mesa_id, cliente_nombre, cliente_telefono, cliente_email,
             fecha_reserva, hora_inicio, hora_fin, personas, estado, observaciones, origen, usuario_id)
        VALUES
            (:nid, :mid, :cn, :ct, :ce,
             :fecha, :hi, :hf, :personas, :estado, :obs, :origen, :uid)
    ");
    $stmt->execute([
        ':nid'     => $negocioId,
        ':mid'     => !empty($d['mesa_id']) ? (int)$d['mesa_id'] : null,
        ':cn'      => trim($d['cliente_nombre']),
        ':ct'      => trim($d['cliente_telefono'] ?? ''),
        ':ce'      => trim($d['cliente_email'] ?? ''),
        ':fecha'   => $d['fecha_reserva'],
        ':hi'      => $d['hora_inicio'],
        ':hf'      => $d['hora_fin'] ?? null,
        ':personas'=> (int)($d['personas'] ?? 2),
        ':estado'  => $d['estado'] ?? 'pendiente',
        ':obs'     => trim($d['observaciones'] ?? ''),
        ':origen'  => $d['origen'] ?? 'telefono',
        ':uid'     => $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null,
    ]);
    $newId = $pdo->lastInsertId();

    // Si tiene mesa y la reserva es HOY → poner mesa en reservada
    if (!empty($d['mesa_id']) && in_array($d['estado'] ?? 'pendiente', ['confirmada','pendiente'])
        && ($d['fecha_reserva'] ?? '') === date('Y-m-d')) {
        $pdo->prepare("UPDATE restaurant_mesas SET estado='reservada' WHERE id=:id AND negocio_id=:nid AND estado='libre'")
            ->execute([':id' => (int)$d['mesa_id'], ':nid' => $negocioId]);
    }

    Response::success('Reserva creada', ['id' => $newId], 201);
}

if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($_GET['id'] ?? $d['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);

    $sets   = [];
    $params = [':id' => $id, ':nid' => $negocioId];
    $allow  = ['mesa_id','cliente_nombre','cliente_telefono','cliente_email',
                'fecha_reserva','hora_inicio','hora_fin','personas','estado','observaciones','origen'];
    foreach ($allow as $f) {
        if (isset($d[$f])) {
            $sets[] = "{$f} = :{$f}";
            $params[":{$f}"] = $d[$f];
        }
    }
    if (empty($sets)) Response::error('Sin campos para actualizar', 400);
    $pdo->prepare("UPDATE restaurant_reservas SET ".implode(',',$sets)." WHERE id=:id AND negocio_id=:nid")
        ->execute($params);

    // Si se canceló → liberar mesa
    if (isset($d['estado']) && in_array($d['estado'], ['cancelada','no_show'])) {
        $r = $pdo->prepare("SELECT mesa_id FROM restaurant_reservas WHERE id=:id")->execute([':id'=>$id]);
        $row = $pdo->query("SELECT mesa_id FROM restaurant_reservas WHERE id={$id}")->fetch();
        if (!empty($row['mesa_id'])) {
            $pdo->prepare("UPDATE restaurant_mesas SET estado='libre' WHERE id=:mid AND negocio_id=:nid AND estado='reservada'")
                ->execute([':mid' => $row['mesa_id'], ':nid' => $negocioId]);
        }
    }

    Response::success(null, 'Reserva actualizada');
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE restaurant_reservas SET estado='cancelada' WHERE id=:id AND negocio_id=:nid")
        ->execute([':id' => $id, ':nid' => $negocioId]);
    Response::success(null, 'Reserva cancelada');
}
