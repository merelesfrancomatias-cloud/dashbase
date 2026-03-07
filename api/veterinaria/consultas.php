<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    // Una consulta específica
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT c.*, p.nombre AS pac_nombre, p.especie, p.duenio_nombre, p.duenio_telefono
            FROM vet_consultas c
            JOIN vet_pacientes p ON p.id = c.paciente_id
            WHERE c.id = :id AND c.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$c) Response::error('Consulta no encontrada', 404);
        Response::success('OK', $c);
    }

    // Agenda del día / filtros
    $where  = "c.negocio_id = :nid";
    $params = [':nid' => $negocioId];

    if (!empty($_GET['fecha'])) {
        $where .= " AND c.fecha = :fecha";
        $params[':fecha'] = $_GET['fecha'];
    }
    if (!empty($_GET['paciente_id'])) {
        $where .= " AND c.paciente_id = :pid";
        $params[':pid'] = (int)$_GET['paciente_id'];
    }
    if (!empty($_GET['estado'])) {
        $where .= " AND c.estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    if (!empty($_GET['desde'])) {
        $where .= " AND c.fecha >= :desde";
        $params[':desde'] = $_GET['desde'];
    }
    if (!empty($_GET['hasta'])) {
        $where .= " AND c.fecha <= :hasta";
        $params[':hasta'] = $_GET['hasta'];
    }

    $stmt = $pdo->prepare("
        SELECT c.*,
               p.nombre AS pac_nombre, p.especie, p.raza, p.foto_url,
               p.duenio_nombre, p.duenio_telefono
        FROM vet_consultas c
        JOIN vet_pacientes p ON p.id = c.paciente_id
        WHERE {$where}
        ORDER BY c.fecha DESC, c.hora ASC
        LIMIT 200
    ");
    $stmt->execute($params);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats del día
    $hoy = date('Y-m-d');
    $statsStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_hoy,
            SUM(estado='pendiente')  AS pendientes,
            SUM(estado='atendido')   AS atendidos,
            SUM(estado='cancelado')  AS cancelados,
            COALESCE(SUM(CASE WHEN estado='atendido' THEN monto ELSE 0 END), 0) AS facturado_hoy
        FROM vet_consultas
        WHERE negocio_id = :nid AND fecha = :hoy
    ");
    $statsStmt->execute([':nid' => $negocioId, ':hoy' => $hoy]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', ['consultas' => $consultas, 'stats' => $stats]);
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['paciente_id'])) Response::error('Paciente requerido', 400);
    if (empty($d['fecha']))        Response::error('Fecha requerida', 400);

    // Verificar que el paciente pertenece al negocio
    $chk = $pdo->prepare("SELECT id FROM vet_pacientes WHERE id = :id AND negocio_id = :nid AND activo = 1");
    $chk->execute([':id' => (int)$d['paciente_id'], ':nid' => $negocioId]);
    if (!$chk->fetch()) Response::error('Paciente no encontrado', 404);

    $stmt = $pdo->prepare("
        INSERT INTO vet_consultas
            (negocio_id, paciente_id, usuario_id, fecha, hora, tipo, motivo,
             diagnostico, tratamiento, medicamentos, peso_consulta, temperatura,
             proximo_turno, monto, metodo_pago, estado, observaciones)
        VALUES
            (:nid, :pid, :uid, :fecha, :hora, :tipo, :motivo,
             :diag, :trat, :meds, :peso, :temp,
             :prox, :monto, :mp, :estado, :obs)
    ");
    $monto = (float)($d['monto'] ?? 0);
    $stmt->execute([
        ':nid'   => $negocioId,
        ':pid'   => (int)$d['paciente_id'],
        ':uid'   => $usuarioId,
        ':fecha' => $d['fecha'],
        ':hora'  => $d['hora']          ?? '09:00',
        ':tipo'  => $d['tipo']          ?? 'consulta',
        ':motivo'=> $d['motivo']        ?? null,
        ':diag'  => $d['diagnostico']   ?? null,
        ':trat'  => $d['tratamiento']   ?? null,
        ':meds'  => $d['medicamentos']  ?? null,
        ':peso'  => !empty($d['peso_consulta']) ? (float)$d['peso_consulta'] : null,
        ':temp'  => !empty($d['temperatura'])   ? (float)$d['temperatura']   : null,
        ':prox'  => !empty($d['proximo_turno'])  ? $d['proximo_turno']        : null,
        ':monto' => $monto,
        ':mp'    => $d['metodo_pago']   ?? 'efectivo',
        ':estado'=> $d['estado']        ?? 'atendido',
        ':obs'   => $d['observaciones'] ?? null,
    ]);
    $consultaId = $pdo->lastInsertId();

    // Actualizar peso del paciente si se informó
    if (!empty($d['peso_consulta'])) {
        $pdo->prepare("UPDATE vet_pacientes SET peso = :peso WHERE id = :id AND negocio_id = :nid")
            ->execute([':peso' => (float)$d['peso_consulta'], ':id' => (int)$d['paciente_id'], ':nid' => $negocioId]);
    }

    // Registrar en caja si hay monto
    if ($monto > 0) {
        $cajaStmt = $pdo->prepare("SELECT id FROM cajas WHERE usuario_id = :uid AND estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
        $cajaStmt->execute([':uid' => $usuarioId]);
        $cajaRow = $cajaStmt->fetch(PDO::FETCH_ASSOC);

        $pacStmt = $pdo->prepare("SELECT nombre, duenio_nombre FROM vet_pacientes WHERE id = :id");
        $pacStmt->execute([':id' => (int)$d['paciente_id']]);
        $pac = $pacStmt->fetch(PDO::FETCH_ASSOC);

        $tipoLabel = [
            'consulta'  => 'Consulta',
            'vacuna'    => 'Vacuna',
            'cirugia'   => 'Cirugía',
            'baño'      => 'Baño/Grooming',
            'grooming'  => 'Grooming',
            'control'   => 'Control',
            'urgencia'  => 'Urgencia',
        ][$d['tipo'] ?? 'consulta'] ?? ucfirst($d['tipo'] ?? 'Consulta');

        $desc = "Veterinaria — {$tipoLabel}: " . ($pac['nombre'] ?? '') . " (due: " . ($pac['duenio_nombre'] ?? '') . ")";

        $pdo->prepare("
            INSERT INTO ventas (negocio_id, usuario_id, caja_id, cliente_nombre, subtotal, descuento, total, metodo_pago, observaciones, estado)
            VALUES (:nid, :uid, :caj, :cn, :sub, 0, :tot, :mp, :obs, 'completada')
        ")->execute([
            ':nid' => $negocioId,
            ':uid' => $usuarioId,
            ':caj' => $cajaRow ? $cajaRow['id'] : null,
            ':cn'  => $pac['duenio_nombre'] ?? 'Cliente',
            ':sub' => $monto,
            ':tot' => $monto,
            ':mp'  => $d['metodo_pago'] ?? 'efectivo',
            ':obs' => $desc,
        ]);
    }

    Response::success('Consulta registrada', ['id' => $consultaId], 201);
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];

    $curr = $pdo->prepare("SELECT * FROM vet_consultas WHERE id = :id AND negocio_id = :nid");
    $curr->execute([':id' => $id, ':nid' => $negocioId]);
    $consulta = $curr->fetch(PDO::FETCH_ASSOC);
    if (!$consulta) Response::error('Consulta no encontrada', 404);

    $stmt = $pdo->prepare("
        UPDATE vet_consultas SET
            fecha          = :fecha,
            hora           = :hora,
            tipo           = :tipo,
            motivo         = :motivo,
            diagnostico    = :diag,
            tratamiento    = :trat,
            medicamentos   = :meds,
            peso_consulta  = :peso,
            temperatura    = :temp,
            proximo_turno  = :prox,
            monto          = :monto,
            metodo_pago    = :mp,
            estado         = :estado,
            observaciones  = :obs
        WHERE id = :id AND negocio_id = :nid
    ");
    $stmt->execute([
        ':fecha' => $d['fecha']         ?? $consulta['fecha'],
        ':hora'  => $d['hora']          ?? $consulta['hora'],
        ':tipo'  => $d['tipo']          ?? $consulta['tipo'],
        ':motivo'=> $d['motivo']        ?? $consulta['motivo'],
        ':diag'  => $d['diagnostico']   ?? $consulta['diagnostico'],
        ':trat'  => $d['tratamiento']   ?? $consulta['tratamiento'],
        ':meds'  => $d['medicamentos']  ?? $consulta['medicamentos'],
        ':peso'  => !empty($d['peso_consulta']) ? (float)$d['peso_consulta'] : $consulta['peso_consulta'],
        ':temp'  => !empty($d['temperatura'])   ? (float)$d['temperatura']   : $consulta['temperatura'],
        ':prox'  => !empty($d['proximo_turno'])  ? $d['proximo_turno'] : $consulta['proximo_turno'],
        ':monto' => (float)($d['monto']       ?? $consulta['monto']),
        ':mp'    => $d['metodo_pago']   ?? $consulta['metodo_pago'],
        ':estado'=> $d['estado']        ?? $consulta['estado'],
        ':obs'   => $d['observaciones'] ?? $consulta['observaciones'],
        ':id'    => $id,
        ':nid'   => $negocioId,
    ]);
    Response::success('Consulta actualizada');
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $pdo->prepare("DELETE FROM vet_consultas WHERE id = :id AND negocio_id = :nid")
        ->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Consulta eliminada');
}
