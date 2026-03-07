<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT p.*,
                TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad_anios,
                (SELECT COUNT(*) FROM vet_consultas c WHERE c.paciente_id = p.id) AS total_consultas,
                (SELECT MAX(c.fecha)  FROM vet_consultas c WHERE c.paciente_id = p.id) AS ultima_consulta,
                (SELECT MIN(v.proxima_dosis) FROM vet_vacunas v WHERE v.paciente_id = p.id AND v.proxima_dosis >= CURDATE()) AS proxima_vacuna
            FROM vet_pacientes p
            WHERE p.id = :id AND p.negocio_id = :nid
        ");
        $stmt->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) Response::error('Paciente no encontrado', 404);

        // Historial consultas
        $cons = $pdo->prepare("SELECT * FROM vet_consultas WHERE paciente_id = :pid ORDER BY fecha DESC, hora DESC LIMIT 50");
        $cons->execute([':pid' => $p['id']]);
        $p['consultas'] = $cons->fetchAll(PDO::FETCH_ASSOC);

        // Vacunas
        $vacs = $pdo->prepare("SELECT * FROM vet_vacunas WHERE paciente_id = :pid ORDER BY fecha_aplicacion DESC");
        $vacs->execute([':pid' => $p['id']]);
        $p['vacunas'] = $vacs->fetchAll(PDO::FETCH_ASSOC);

        Response::success('OK', $p);
    }

    // Listado con búsqueda
    $where  = "p.negocio_id = :nid AND p.activo = 1";
    $params = [':nid' => $negocioId];

    if (!empty($_GET['q'])) {
        $where .= " AND (p.nombre LIKE :q1 OR p.duenio_nombre LIKE :q2 OR p.duenio_telefono LIKE :q3)";
        $params[':q1'] = '%' . $_GET['q'] . '%';
        $params[':q2'] = '%' . $_GET['q'] . '%';
        $params[':q3'] = '%' . $_GET['q'] . '%';
    }
    if (!empty($_GET['especie'])) {
        $where .= " AND p.especie = :especie";
        $params[':especie'] = $_GET['especie'];
    }

    $stmt = $pdo->prepare("
        SELECT p.*,
            TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad_anios,
            (SELECT MAX(c.fecha) FROM vet_consultas c WHERE c.paciente_id = p.id) AS ultima_consulta,
            (SELECT MIN(v.proxima_dosis) FROM vet_vacunas v WHERE v.paciente_id = p.id AND v.proxima_dosis >= CURDATE()) AS proxima_vacuna
        FROM vet_pacientes p
        WHERE {$where}
        ORDER BY p.nombre ASC
        LIMIT 300
    ");
    $stmt->execute($params);
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $stats = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(especie='perro') AS perros,
            SUM(especie='gato')  AS gatos,
            SUM(especie NOT IN ('perro','gato')) AS otros,
            (SELECT COUNT(*) FROM vet_consultas c WHERE c.negocio_id = :nid2 AND c.fecha = CURDATE() AND c.estado='pendiente') AS turnos_hoy
        FROM vet_pacientes WHERE negocio_id = :nid AND activo = 1
    ");
    $stats->execute([':nid' => $negocioId, ':nid2' => $negocioId]);
    $statsRow = $stats->fetch(PDO::FETCH_ASSOC);

    // Vacunas vencidas próximas (7 días)
    $prox = $pdo->prepare("
        SELECT v.*, p.nombre AS pac_nombre, p.especie, p.duenio_nombre, p.duenio_telefono
        FROM vet_vacunas v
        JOIN vet_pacientes p ON p.id = v.paciente_id
        WHERE v.negocio_id = :nid AND v.proxima_dosis BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY v.proxima_dosis ASC
        LIMIT 10
    ");
    $prox->execute([':nid' => $negocioId]);
    $proximasVacunas = $prox->fetchAll(PDO::FETCH_ASSOC);

    Response::success('OK', [
        'pacientes'       => $pacientes,
        'stats'           => $statsRow,
        'proximas_vacunas'=> $proximasVacunas,
    ]);
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['nombre']))       Response::error('El nombre de la mascota es requerido', 400);
    if (empty($d['duenio_nombre'])) Response::error('El nombre del dueño es requerido', 400);

    $stmt = $pdo->prepare("
        INSERT INTO vet_pacientes
            (negocio_id, nombre, especie, raza, color, sexo, fecha_nacimiento,
             esterilizado, duenio_nombre, duenio_telefono, duenio_email,
             duenio_direccion, peso, observaciones)
        VALUES
            (:nid, :nombre, :especie, :raza, :color, :sexo, :fnac,
             :estr, :duenio, :tel, :email, :dir, :peso, :obs)
    ");
    $stmt->execute([
        ':nid'    => $negocioId,
        ':nombre' => trim($d['nombre']),
        ':especie'=> $d['especie']    ?? 'perro',
        ':raza'   => $d['raza']       ?? null,
        ':color'  => $d['color']      ?? null,
        ':sexo'   => $d['sexo']       ?? 'desconocido',
        ':fnac'   => $d['fecha_nacimiento'] ?? null,
        ':estr'   => (int)($d['esterilizado'] ?? 0),
        ':duenio' => trim($d['duenio_nombre']),
        ':tel'    => $d['duenio_telefono']  ?? null,
        ':email'  => $d['duenio_email']     ?? null,
        ':dir'    => $d['duenio_direccion'] ?? null,
        ':peso'   => !empty($d['peso']) ? (float)$d['peso'] : null,
        ':obs'    => $d['observaciones']    ?? null,
    ]);
    Response::success('Paciente creado', ['id' => $pdo->lastInsertId()], 201);
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $id = (int)$_GET['id'];
    $d  = json_decode(file_get_contents('php://input'), true) ?? [];

    $curr = $pdo->prepare("SELECT * FROM vet_pacientes WHERE id = :id AND negocio_id = :nid");
    $curr->execute([':id' => $id, ':nid' => $negocioId]);
    if (!$curr->fetch()) Response::error('Paciente no encontrado', 404);

    $stmt = $pdo->prepare("
        UPDATE vet_pacientes SET
            nombre           = :nombre,
            especie          = :especie,
            raza             = :raza,
            color            = :color,
            sexo             = :sexo,
            fecha_nacimiento = :fnac,
            esterilizado     = :estr,
            duenio_nombre    = :duenio,
            duenio_telefono  = :tel,
            duenio_email     = :email,
            duenio_direccion = :dir,
            peso             = :peso,
            observaciones    = :obs,
            activo           = :activo
        WHERE id = :id AND negocio_id = :nid
    ");
    $stmt->execute([
        ':nombre' => trim($d['nombre']         ?? ''),
        ':especie'=> $d['especie']             ?? 'perro',
        ':raza'   => $d['raza']                ?? null,
        ':color'  => $d['color']               ?? null,
        ':sexo'   => $d['sexo']                ?? 'desconocido',
        ':fnac'   => $d['fecha_nacimiento']    ?? null,
        ':estr'   => (int)($d['esterilizado']  ?? 0),
        ':duenio' => trim($d['duenio_nombre']  ?? ''),
        ':tel'    => $d['duenio_telefono']     ?? null,
        ':email'  => $d['duenio_email']        ?? null,
        ':dir'    => $d['duenio_direccion']    ?? null,
        ':peso'   => !empty($d['peso']) ? (float)$d['peso'] : null,
        ':obs'    => $d['observaciones']       ?? null,
        ':activo' => (int)($d['activo']        ?? 1),
        ':id'     => $id,
        ':nid'    => $negocioId,
    ]);
    Response::success('Paciente actualizado');
}

// ── DELETE (soft) ─────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $pdo->prepare("UPDATE vet_pacientes SET activo = 0 WHERE id = :id AND negocio_id = :nid")
        ->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Paciente eliminado');
}
