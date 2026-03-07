<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET', 'POST', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (empty($_GET['paciente_id'])) Response::error('paciente_id requerido', 400);
    $pid = (int)$_GET['paciente_id'];
    $stmt = $pdo->prepare("
        SELECT v.* FROM vet_vacunas v
        JOIN vet_pacientes p ON p.id = v.paciente_id
        WHERE v.paciente_id = :pid AND p.negocio_id = :nid
        ORDER BY v.fecha_aplicacion DESC
    ");
    $stmt->execute([':pid' => $pid, ':nid' => $negocioId]);
    Response::success('OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['paciente_id']))    Response::error('Paciente requerido', 400);
    if (empty($d['nombre']))         Response::error('Nombre de vacuna requerido', 400);
    if (empty($d['fecha_aplicacion'])) Response::error('Fecha requerida', 400);

    // Verificar que pertenece al negocio
    $chk = $pdo->prepare("SELECT id FROM vet_pacientes WHERE id = :id AND negocio_id = :nid AND activo = 1");
    $chk->execute([':id' => (int)$d['paciente_id'], ':nid' => $negocioId]);
    if (!$chk->fetch()) Response::error('Paciente no encontrado', 404);

    $stmt = $pdo->prepare("
        INSERT INTO vet_vacunas (negocio_id, paciente_id, nombre, lote, fecha_aplicacion, proxima_dosis, veterinario, observaciones)
        VALUES (:nid, :pid, :nombre, :lote, :fap, :prox, :vet, :obs)
    ");
    $stmt->execute([
        ':nid'   => $negocioId,
        ':pid'   => (int)$d['paciente_id'],
        ':nombre'=> trim($d['nombre']),
        ':lote'  => $d['lote']               ?? null,
        ':fap'   => $d['fecha_aplicacion'],
        ':prox'  => $d['proxima_dosis']       ?? null,
        ':vet'   => $d['veterinario']         ?? null,
        ':obs'   => $d['observaciones']       ?? null,
    ]);
    Response::success('Vacuna registrada', ['id' => $pdo->lastInsertId()], 201);
}

if ($method === 'DELETE') {
    if (!isset($_GET['id'])) Response::error('ID requerido', 400);
    $pdo->prepare("
        DELETE v FROM vet_vacunas v
        JOIN vet_pacientes p ON p.id = v.paciente_id
        WHERE v.id = :id AND p.negocio_id = :nid
    ")->execute([':id' => (int)$_GET['id'], ':nid' => $negocioId]);
    Response::success('Vacuna eliminada');
}
