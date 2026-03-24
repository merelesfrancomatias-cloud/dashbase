<?php
require_once __DIR__ . '/../bootstrap.php';
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $where  = ["m.negocio_id = :nid"];
    $params = [':nid' => $negocioId];
    if (!empty($_GET['insumo_id'])) { $where[] = "m.insumo_id = :iid"; $params[':iid'] = (int)$_GET['insumo_id']; }
    if (!empty($_GET['desde']))     { $where[] = "m.fecha >= :desde";   $params[':desde'] = $_GET['desde']; }
    if (!empty($_GET['hasta']))     { $where[] = "m.fecha <= :hasta";   $params[':hasta'] = $_GET['hasta']; }

    $stmt = $pdo->prepare("
        SELECT m.*, i.nombre AS insumo_nombre, i.unidad,
               ROUND(m.cantidad * i.precio_unitario, 2) AS costo_perdida,
               u.nombre AS usuario_nombre
        FROM restaurant_mermas m
        JOIN restaurant_insumos i ON i.id = m.insumo_id
        LEFT JOIN usuarios u ON u.id = m.usuario_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY m.fecha DESC, m.id DESC
        LIMIT " . (int)($_GET['limit'] ?? 200)
    );
    $stmt->execute($params);
    $mermas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $costoPerdida = array_sum(array_column($mermas, 'costo_perdida'));
    Response::success('OK', ['mermas' => $mermas, 'costo_total_perdida' => $costoPerdida]);
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    if (empty($d['insumo_id']) || empty($d['cantidad']) || empty($d['fecha']))
        Response::error('insumo_id, cantidad y fecha requeridos', 400);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO restaurant_mermas (negocio_id, insumo_id, cantidad, motivo, descripcion, usuario_id, fecha)
            VALUES (:nid, :iid, :cant, :motivo, :desc, :uid, :fecha)
        ");
        $stmt->execute([
            ':nid'    => $negocioId,
            ':iid'    => (int)$d['insumo_id'],
            ':cant'   => (float)$d['cantidad'],
            ':motivo' => $d['motivo'] ?? 'otro',
            ':desc'   => trim($d['descripcion'] ?? ''),
            ':uid'    => $usuarioId,
            ':fecha'  => $d['fecha'],
        ]);
        $mid = $pdo->lastInsertId();
        // Descontar del stock
        $pdo->prepare("
            UPDATE restaurant_insumos
            SET stock_actual = GREATEST(0, stock_actual - :cant), updated_at = NOW()
            WHERE id = :id AND negocio_id = :nid
        ")->execute([':cant' => (float)$d['cantidad'], ':id' => (int)$d['insumo_id'], ':nid' => $negocioId]);
        $pdo->commit();
        Response::success('Merma registrada', ['id' => $mid], 201);
    } catch(Exception $e) {
        $pdo->rollBack();
        Response::error('Error al registrar merma', 500);
    }
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    // Recuperar para revertir stock
    $stmt = $pdo->prepare("SELECT * FROM restaurant_mermas WHERE id=:id AND negocio_id=:nid");
    $stmt->execute([':id' => $id, ':nid' => $negocioId]);
    $m = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($m) {
        $pdo->prepare("UPDATE restaurant_insumos SET stock_actual = stock_actual + :cant WHERE id=:iid AND negocio_id=:nid")
            ->execute([':cant' => $m['cantidad'], ':iid' => $m['insumo_id'], ':nid' => $negocioId]);
        $pdo->prepare("DELETE FROM restaurant_mermas WHERE id=:id AND negocio_id=:nid")->execute([':id' => $id, ':nid' => $negocioId]);
    }
    Response::success('Merma eliminada');
}

Response::error('Método no soportado', 405);
