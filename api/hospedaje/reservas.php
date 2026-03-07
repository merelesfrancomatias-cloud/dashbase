<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT r.*, h.numero AS hab_numero, h.nombre AS hab_nombre, h.tipo AS hab_tipo
            FROM hospedaje_reservas r
            JOIN hospedaje_habitaciones h ON h.id = r.habitacion_id
            WHERE r.id = :id AND r.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) Response::error('Reserva no encontrada', 404);
        Response::success('OK', $r);
    }

    // Filtros: ?estado=, ?desde=, ?hasta=, ?habitacion_id=
    $where = "r.negocio_id = :nid";
    $params = [':nid' => $negocioId];

    if (!empty($_GET['estado'])) {
        $where .= " AND r.estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    if (!empty($_GET['desde'])) {
        $where .= " AND r.checkin_fecha >= :desde";
        $params[':desde'] = $_GET['desde'];
    }
    if (!empty($_GET['hasta'])) {
        $where .= " AND r.checkout_fecha <= :hasta";
        $params[':hasta'] = $_GET['hasta'];
    }
    if (!empty($_GET['habitacion_id'])) {
        $where .= " AND r.habitacion_id = :habid";
        $params[':habid'] = (int)$_GET['habitacion_id'];
    }

    $stmt = $pdo->prepare("
        SELECT r.*,
               h.numero AS hab_numero, h.nombre AS hab_nombre, h.tipo AS hab_tipo, h.piso
        FROM hospedaje_reservas r
        JOIN hospedaje_habitaciones h ON h.id = r.habitacion_id
        WHERE {$where}
        ORDER BY r.checkin_fecha DESC, r.id DESC
        LIMIT 200
    ");
    $stmt->execute($params);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];

    // Validaciones
    if (empty($d['habitacion_id']))   Response::error('Seleccioná una habitación', 400);
    if (empty($d['huesped_nombre']))  Response::error('El nombre del huésped es requerido', 400);
    if (empty($d['checkin_fecha']))   Response::error('Fecha de check-in requerida', 400);
    if (empty($d['checkout_fecha']))  Response::error('Fecha de check-out requerida', 400);

    $habId = (int)$d['habitacion_id'];

    // Verificar que la habitación no esté ocupada en esas fechas
    $conflict = $pdo->prepare("
        SELECT id FROM hospedaje_reservas
        WHERE habitacion_id = :hid AND negocio_id = :nid
          AND estado IN ('reservada','checkin')
          AND NOT (checkout_fecha <= :ci OR checkin_fecha >= :co)
    ");
    $conflict->execute([
        ':hid' => $habId,
        ':nid' => $negocioId,
        ':ci'  => $d['checkin_fecha'],
        ':co'  => $d['checkout_fecha'],
    ]);
    if ($conflict->fetch()) Response::error('La habitación ya está reservada en esas fechas', 409);

    // Calcular noches / precio
    $tipo = $d['tipo_estadia'] ?? 'noche';
    $ci   = new DateTime($d['checkin_fecha']);
    $co   = new DateTime($d['checkout_fecha']);
    $diff = $ci->diff($co)->days;
    $noches = max(1, $diff);

    // Obtener precio de la habitación
    $habStmt = $pdo->prepare("SELECT precio_noche, precio_hora FROM hospedaje_habitaciones WHERE id = :id AND negocio_id = :nid");
    $habStmt->execute([':id' => $habId, ':nid' => $negocioId]);
    $hab = $habStmt->fetch(PDO::FETCH_ASSOC);
    if (!$hab) Response::error('Habitación no encontrada', 404);

    $precioUnit = $tipo === 'hora' ? (float)($hab['precio_hora'] ?? 0) : (float)$hab['precio_noche'];
    $unidades   = $tipo === 'semana' ? ceil($noches / 7) : ($tipo === 'hora' ? (int)($d['horas'] ?? 1) : $noches);
    $total      = isset($d['total']) ? (float)$d['total'] : $precioUnit * $unidades;

    $stmt = $pdo->prepare("
        INSERT INTO hospedaje_reservas
            (negocio_id, habitacion_id, huesped_nombre, huesped_dni, huesped_telefono, huesped_email,
             tipo_estadia, checkin_fecha, checkin_hora, checkout_fecha, checkout_hora,
             noches, personas, precio_unitario, total, seña, estado, observaciones, usuario_id)
        VALUES
            (:nid, :habid, :nombre, :dni, :tel, :email,
             :tipo, :ci_f, :ci_h, :co_f, :co_h,
             :noches, :personas, :precio, :total, :sena, :estado, :obs, :uid)
    ");
    $stmt->execute([
        ':nid'     => $negocioId,
        ':habid'   => $habId,
        ':nombre'  => trim($d['huesped_nombre']),
        ':dni'     => $d['huesped_dni']      ?? null,
        ':tel'     => $d['huesped_telefono'] ?? null,
        ':email'   => $d['huesped_email']    ?? null,
        ':tipo'    => $tipo,
        ':ci_f'    => $d['checkin_fecha'],
        ':ci_h'    => $d['checkin_hora']  ?? '14:00:00',
        ':co_f'    => $d['checkout_fecha'],
        ':co_h'    => $d['checkout_hora'] ?? '10:00:00',
        ':noches'  => $noches,
        ':personas'=> (int)($d['personas'] ?? 1),
        ':precio'  => $precioUnit,
        ':total'   => $total,
        ':sena'    => (float)($d['seña'] ?? 0),
        ':estado'  => $d['estado'] ?? 'reservada',
        ':obs'     => $d['observaciones'] ?? null,
        ':uid'     => $usuarioId,
    ]);
    $reservaId = $pdo->lastInsertId();

    // Si es check-in directo, actualizar estado de la habitación
    if (($d['estado'] ?? '') === 'checkin') {
        $pdo->prepare("UPDATE hospedaje_habitaciones SET estado = 'ocupada' WHERE id = :id AND negocio_id = :nid")
            ->execute([':id' => $habId, ':nid' => $negocioId]);
    }

    // ── Registrar en caja si hay seña o es check-in con total ────────────────
    $sena      = (float)($d['seña'] ?? 0);
    $metodo    = $d['metodo_pago'] ?? 'efectivo';
    $habNumero = $hab ? "Hab. {$habId}" : "Hab. #$habId";

    // Obtener número de habitación para descripción
    $habNumStmt = $pdo->prepare("SELECT numero FROM hospedaje_habitaciones WHERE id = :id");
    $habNumStmt->execute([':id' => $habId]);
    $habNumRow  = $habNumStmt->fetch(PDO::FETCH_ASSOC);
    $habNum     = $habNumRow ? $habNumStmt->fetchColumn() : "#{$habId}";
    $habNumero  = $habNumRow['numero'] ?? "#{$habId}";

    // Obtener caja activa del usuario
    $cajaStmt = $pdo->prepare("SELECT id FROM cajas WHERE usuario_id = :uid AND estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
    $cajaStmt->execute([':uid' => $usuarioId]);
    $cajaRow  = $cajaStmt->fetch(PDO::FETCH_ASSOC);
    $cajaId   = $cajaRow ? $cajaRow['id'] : null;

    $montoARegistrar = 0;
    $descripcionVenta = '';

    if (($d['estado'] ?? '') === 'checkin' && $sena <= 0) {
        // Check-in sin seña → registrar total completo
        $montoARegistrar  = $total;
        $descripcionVenta = "Hospedaje Hab.{$habNumero} — " . trim($d['huesped_nombre']) . " ({$noches} " . ($tipo === 'hora' ? 'hs' : ($tipo === 'semana' ? 'sem' : 'noches')) . ")";
    } elseif ($sena > 0) {
        // Hay seña → registrar solo la seña
        $montoARegistrar  = $sena;
        $descripcionVenta = "Seña Hospedaje Hab.{$habNumero} — " . trim($d['huesped_nombre']);
    }

    if ($montoARegistrar > 0) {
        $pdo->prepare("
            INSERT INTO ventas
                (negocio_id, usuario_id, caja_id, cliente_nombre, subtotal, descuento, total, metodo_pago, observaciones, estado)
            VALUES
                (:nid, :uid, :caj, :cn, :sub, 0, :tot, :mp, :obs, 'completada')
        ")->execute([
            ':nid' => $negocioId,
            ':uid' => $usuarioId,
            ':caj' => $cajaId,
            ':cn'  => trim($d['huesped_nombre']),
            ':sub' => $montoARegistrar,
            ':tot' => $montoARegistrar,
            ':mp'  => $metodo,
            ':obs' => $descripcionVenta,
        ]);
    }
    // ─────────────────────────────────────────────────────────────────────────

    Response::success('Reserva creada', ['id' => $reservaId], 201);
}

if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];

    // Obtener reserva actual
    $curr = $pdo->prepare("SELECT * FROM hospedaje_reservas WHERE id = :id AND negocio_id = :nid");
    $curr->execute([':id' => $id, ':nid' => $negocioId]);
    $reserva = $curr->fetch(PDO::FETCH_ASSOC);
    if (!$reserva) Response::error('Reserva no encontrada', 404);

    $nuevoEstado = $d['estado'] ?? $reserva['estado'];
    $habId = (int)$reserva['habitacion_id'];

    // Cambiar estado habitación según flujo
    if ($nuevoEstado === 'checkin' && $reserva['estado'] !== 'checkin') {
        $pdo->prepare("UPDATE hospedaje_habitaciones SET estado = 'ocupada' WHERE id = :id AND negocio_id = :nid")
            ->execute([':id' => $habId, ':nid' => $negocioId]);
    } elseif (in_array($nuevoEstado, ['checkout', 'cancelada']) && $reserva['estado'] === 'checkin') {
        $pdo->prepare("UPDATE hospedaje_habitaciones SET estado = 'limpieza' WHERE id = :id AND negocio_id = :nid")
            ->execute([':id' => $habId, ':nid' => $negocioId]);
    }

    // ── Al hacer checkout, registrar saldo restante en caja ──────────────────
    if ($nuevoEstado === 'checkout' && $reserva['estado'] !== 'checkout') {
        $totalRes  = (float)$reserva['total'];
        $senaRes   = (float)($reserva['seña'] ?? 0);
        $saldo     = $totalRes - $senaRes;
        $metodo    = $d['metodo_pago'] ?? 'efectivo';

        // Obtener número habitación
        $habNumStmt2 = $pdo->prepare("SELECT numero FROM hospedaje_habitaciones WHERE id = :id");
        $habNumStmt2->execute([':id' => $habId]);
        $habNumRow2  = $habNumStmt2->fetch(PDO::FETCH_ASSOC);
        $habNumero2  = $habNumRow2['numero'] ?? "#{$habId}";

        // Caja activa
        $cajaStmt2 = $pdo->prepare("SELECT id FROM cajas WHERE usuario_id = :uid AND estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
        $cajaStmt2->execute([':uid' => $usuarioId]);
        $cajaRow2  = $cajaStmt2->fetch(PDO::FETCH_ASSOC);
        $cajaId2   = $cajaRow2 ? $cajaRow2['id'] : null;

        if ($saldo > 0) {
            $desc = "Saldo Hospedaje Hab.{$habNumero2} — {$reserva['huesped_nombre']}";
            $pdo->prepare("
                INSERT INTO ventas
                    (negocio_id, usuario_id, caja_id, cliente_nombre, subtotal, descuento, total, metodo_pago, observaciones, estado)
                VALUES
                    (:nid, :uid, :caj, :cn, :sub, 0, :tot, :mp, :obs, 'completada')
            ")->execute([
                ':nid' => $negocioId,
                ':uid' => $usuarioId,
                ':caj' => $cajaId2,
                ':cn'  => $reserva['huesped_nombre'],
                ':sub' => $saldo,
                ':tot' => $saldo,
                ':mp'  => $metodo,
                ':obs' => $desc,
            ]);
        } elseif ($saldo == 0 && $senaRes == 0 && $totalRes > 0) {
            // No hubo seña y tampoco se registró al hacer check-in (edge case)
            $desc = "Hospedaje Hab.{$habNumero2} — {$reserva['huesped_nombre']}";
            $pdo->prepare("
                INSERT INTO ventas
                    (negocio_id, usuario_id, caja_id, cliente_nombre, subtotal, descuento, total, metodo_pago, observaciones, estado)
                VALUES
                    (:nid, :uid, :caj, :cn, :sub, 0, :tot, :mp, :obs, 'completada')
            ")->execute([
                ':nid' => $negocioId,
                ':uid' => $usuarioId,
                ':caj' => $cajaId2,
                ':cn'  => $reserva['huesped_nombre'],
                ':sub' => $totalRes,
                ':tot' => $totalRes,
                ':mp'  => $metodo,
                ':obs' => $desc,
            ]);
        }
    }
    // ─────────────────────────────────────────────────────────────────────────

    $stmt = $pdo->prepare("
        UPDATE hospedaje_reservas SET
            huesped_nombre    = :nombre,
            huesped_dni       = :dni,
            huesped_telefono  = :tel,
            huesped_email     = :email,
            checkin_fecha     = :ci_f,
            checkin_hora      = :ci_h,
            checkout_fecha    = :co_f,
            checkout_hora     = :co_h,
            noches            = :noches,
            personas          = :personas,
            precio_unitario   = :precio,
            total             = :total,
            seña              = :sena,
            estado            = :estado,
            observaciones     = :obs
        WHERE id = :id AND negocio_id = :nid
    ");
    $stmt->execute([
        ':nombre'  => $d['huesped_nombre']    ?? $reserva['huesped_nombre'],
        ':dni'     => $d['huesped_dni']       ?? $reserva['huesped_dni'],
        ':tel'     => $d['huesped_telefono']  ?? $reserva['huesped_telefono'],
        ':email'   => $d['huesped_email']     ?? $reserva['huesped_email'],
        ':ci_f'    => $d['checkin_fecha']     ?? $reserva['checkin_fecha'],
        ':ci_h'    => $d['checkin_hora']      ?? $reserva['checkin_hora'],
        ':co_f'    => $d['checkout_fecha']    ?? $reserva['checkout_fecha'],
        ':co_h'    => $d['checkout_hora']     ?? $reserva['checkout_hora'],
        ':noches'  => $d['noches']            ?? $reserva['noches'],
        ':personas'=> $d['personas']          ?? $reserva['personas'],
        ':precio'  => $d['precio_unitario']   ?? $reserva['precio_unitario'],
        ':total'   => $d['total']             ?? $reserva['total'],
        ':sena'    => $d['seña']              ?? $reserva['seña'],
        ':estado'  => $nuevoEstado,
        ':obs'     => $d['observaciones']     ?? $reserva['observaciones'],
        ':id'      => $id,
        ':nid'     => $negocioId,
    ]);
    Response::success('Reserva actualizada');
}

if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $stmt = $pdo->prepare("UPDATE hospedaje_reservas SET estado = 'cancelada' WHERE id = :id AND negocio_id = :nid");
    $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Reserva cancelada');
}
