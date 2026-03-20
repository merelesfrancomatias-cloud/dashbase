<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
Middleware::method($_SERVER['REQUEST_METHOD']);

$method = $_SERVER['REQUEST_METHOD'];

try {
    [$negocio_id, $userId] = Middleware::auth();
    
    $database = new Database();
    $pdo = $database->getConnection();

    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT * FROM canchas WHERE negocio_id = ? ORDER BY nombre ASC");
        $stmt->execute([$negocio_id]);
        Response::success('Canchas', $stmt->fetchAll(PDO::FETCH_ASSOC));

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $nombre  = trim($data['nombre'] ?? '');
        $deporte = trim($data['deporte'] ?? '');
        if (!$nombre) Response::error('El nombre es requerido', 400);

        $stmt = $pdo->prepare("INSERT INTO canchas (negocio_id, nombre, deporte, descripcion, precio_hora, capacidad, activo) VALUES (?,?,?,?,?,?,1)");
        $stmt->execute([
            $negocio_id, $nombre, $deporte,
            trim($data['descripcion'] ?? ''),
            floatval($data['precio_hora'] ?? 0),
            intval($data['capacidad'] ?? 0),
        ]);
        $id = $pdo->lastInsertId();
        $row = $pdo->prepare("SELECT * FROM canchas WHERE id = ?");
        $row->execute([$id]);
        Response::success('Cancha creada', $row->fetch(PDO::FETCH_ASSOC), 201);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) Response::error('ID requerido', 400);

        $stmt = $pdo->prepare("UPDATE canchas SET nombre=?, deporte=?, descripcion=?, precio_hora=?, capacidad=?, activo=? WHERE id=? AND negocio_id=?");
        $stmt->execute([
            trim($data['nombre'] ?? ''),
            trim($data['deporte'] ?? ''),
            trim($data['descripcion'] ?? ''),
            floatval($data['precio_hora'] ?? 0),
            intval($data['capacidad'] ?? 0),
            intval($data['activo'] ?? 1),
            $id, $negocio_id
        ]);
        Response::success('Cancha actualizada');

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) Response::error('ID requerido', 400);
        $stmt = $pdo->prepare("UPDATE canchas SET activo=0 WHERE id=? AND negocio_id=?");
        $stmt->execute([$id, $negocio_id]);
        Response::success('Cancha eliminada');
    }

} catch (Exception $e) {
    Response::error('Error del servidor: ' . $e->getMessage(), 500);
}
