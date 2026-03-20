<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT']);
Middleware::method($_SERVER['REQUEST_METHOD']);

$method = $_SERVER['REQUEST_METHOD'];

try {
    [$negocio_id, $userId] = Middleware::auth();
    
    $database = new Database();
    $pdo = $database->getConnection();

    if ($method === 'GET') {
        // ?cancha_id=X&fecha=YYYY-MM-DD  → reservas de esa cancha en esa fecha
        // ?fecha=YYYY-MM-DD              → todas las reservas del día
        // ?id=X                         → una reserva
        $canchaId = intval($_GET['cancha_id'] ?? 0);
        $fecha    = $_GET['fecha'] ?? '';
        $id       = intval($_GET['id'] ?? 0);

        if ($id) {
            $stmt = $pdo->prepare("
                SELECT r.*, c.nombre AS cancha_nombre, c.deporte, c.precio_hora
                FROM reservas_canchas r
                JOIN canchas c ON c.id = r.cancha_id
                WHERE r.id = ? AND c.negocio_id = ?");
            $stmt->execute([$id, $negocio_id]);
            Response::success('Reserva', $stmt->fetch(PDO::FETCH_ASSOC));
        } elseif ($canchaId && $fecha) {
            $stmt = $pdo->prepare("
                SELECT r.*, c.nombre AS cancha_nombre
                FROM reservas_canchas r
                JOIN canchas c ON c.id = r.cancha_id
                WHERE r.cancha_id = ? AND r.fecha = ? AND c.negocio_id = ? AND r.estado != 'cancelada'
                ORDER BY r.hora_inicio");
            $stmt->execute([$canchaId, $fecha, $negocio_id]);
            Response::success('Reservas por cancha', [
                'reservas' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'stats' => ['total' => 0, 'confirmadas' => 0, 'pendientes' => 0, 'ingresos' => 0]
            ]);
        } else {
            // Listado con filtros opcionales
            $where   = "c.negocio_id = ?";
            $params  = [$negocio_id];
            if ($fecha) { $where .= " AND r.fecha = ?"; $params[] = $fecha; }
            if (isset($_GET['estado'])) { $where .= " AND r.estado = ?"; $params[] = $_GET['estado']; }
            $stmt = $pdo->prepare("
                SELECT r.*, c.nombre AS cancha_nombre, c.deporte, c.precio_hora
                FROM reservas_canchas r
                JOIN canchas c ON c.id = r.cancha_id
                WHERE {$where}
                ORDER BY r.fecha DESC, r.hora_inicio ASC
                LIMIT 200");
            $stmt->execute($params);

            // Stats del día actual
            $hoy = date('Y-m-d');
            $stHoy = $pdo->prepare("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN estado='confirmada' THEN 1 ELSE 0 END) as confirmadas,
                       SUM(CASE WHEN estado='pendiente'  THEN 1 ELSE 0 END) as pendientes,
                       SUM(CASE WHEN estado='confirmada' THEN monto ELSE 0 END) as ingresos
                FROM reservas_canchas r JOIN canchas c ON c.id=r.cancha_id
                WHERE c.negocio_id=? AND r.fecha=?");
            $stHoy->execute([$negocio_id, $hoy]);
            $stats = $stHoy->fetch(PDO::FETCH_ASSOC);

            Response::success('Reservas', [
                'reservas' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'stats'    => $stats,
            ]);
        }

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $cancha_id   = intval($data['cancha_id'] ?? 0);
        $fecha       = trim($data['fecha'] ?? '');
        $hora_inicio = trim($data['hora_inicio'] ?? '');
        $hora_fin    = trim($data['hora_fin'] ?? '');
        if (!$cancha_id || !$fecha || !$hora_inicio || !$hora_fin)
            Response::error('cancha_id, fecha, hora_inicio y hora_fin son requeridos', 400);

        // Verificar que la cancha pertenece al negocio
        $chk = $pdo->prepare("SELECT id, precio_hora FROM canchas WHERE id=? AND negocio_id=? AND activo=1");
        $chk->execute([$cancha_id, $negocio_id]);
        $cancha = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$cancha) Response::error('Cancha no encontrada', 404);

        // Verificar solapamiento
        $solapo = $pdo->prepare("
            SELECT id FROM reservas_canchas
            WHERE cancha_id=? AND fecha=? AND estado != 'cancelada'
              AND hora_inicio < ? AND hora_fin > ?");
        $solapo->execute([$cancha_id, $fecha, $hora_fin, $hora_inicio]);
        if ($solapo->fetch()) Response::error('Ese horario ya está reservado', 409);

        // Calcular monto si no viene
        $monto = floatval($data['monto'] ?? 0);
        if ($monto <= 0 && $cancha['precio_hora'] > 0) {
            // horas = diferencia entre hora_fin y hora_inicio
            $diff = (strtotime($hora_fin) - strtotime($hora_inicio)) / 3600;
            $monto = round($diff * $cancha['precio_hora'], 2);
        }

            $stmt = $pdo->prepare("INSERT INTO reservas_canchas
                (cancha_id, cliente_id, fecha, hora_inicio, hora_fin, cliente_nombre, cliente_telefono,
                 monto, metodo_pago, estado, notas, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?, NOW())");
            $stmt->execute([
                $cancha_id,
                intval($data['cliente_id'] ?? 0) ?: null,
                $fecha, $hora_inicio, $hora_fin,
                trim($data['cliente_nombre'] ?? ''),
                trim($data['cliente_telefono'] ?? ''),
                $monto,
                $data['metodo_pago'] ?? 'efectivo',
                $data['estado'] ?? 'confirmada',
                trim($data['notas'] ?? ''),
            ]);
        Response::success('Reserva creada', ['id' => $pdo->lastInsertId(), 'monto' => $monto], 201);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) Response::error('ID requerido', 400);

        // Validar que pertenece al negocio
        $chk = $pdo->prepare("SELECT r.id FROM reservas_canchas r JOIN canchas c ON c.id=r.cancha_id WHERE r.id=? AND c.negocio_id=?");
        $chk->execute([$id, $negocio_id]);
        if (!$chk->fetch()) Response::error('Reserva no encontrada', 404);

        $fields = [];
        $params = [];
            $allowed = ['estado', 'cliente_id', 'cliente_nombre', 'cliente_telefono', 'monto', 'metodo_pago', 'notas'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) { $fields[] = "{$f}=?"; $params[] = $data[$f]; }
        }
        if (!$fields) Response::error('Nada que actualizar', 400);
        $params[] = $id;
        $pdo->prepare("UPDATE reservas_canchas SET " . implode(',', $fields) . " WHERE id=?")->execute($params);
        Response::success('Reserva actualizada');

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = intval($data['id'] ?? 0);
        if (!$id) Response::error('ID requerido', 400);
        $stmt = $pdo->prepare("UPDATE reservas_canchas r JOIN canchas c ON c.id=r.cancha_id SET r.estado='cancelada' WHERE r.id=? AND c.negocio_id=?");
        $stmt->execute([$id, $negocio_id]);
        Response::success('Reserva cancelada');
    }

} catch (Exception $e) {
    Response::error('Error del servidor: ' . $e->getMessage(), 500);
}
