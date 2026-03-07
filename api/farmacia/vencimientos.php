<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET','PUT']);
[$negocioId] = Middleware::auth();

$method = $_SERVER['REQUEST_METHOD'];
$db     = (new Database())->getConnection();

if ($method === 'GET') {
    $dias  = (int)($_GET['dias'] ?? 90);   // alerta productos que vencen en X días
    $solo_vencidos = isset($_GET['vencidos']);
    $q     = trim($_GET['q'] ?? '');
    $catId = (int)($_GET['categoria'] ?? 0);

    $hoy  = date('Y-m-d');
    $lim  = date('Y-m-d', strtotime("+{$dias} days"));

    $sql = "SELECT p.*, c.nombre AS categoria_nombre, c.color AS categoria_color,
                CASE
                    WHEN p.fecha_vencimiento < ? THEN 'vencido'
                    WHEN p.fecha_vencimiento <= ? THEN 'proximo'
                    ELSE 'ok'
                END AS estado_vencimiento,
                DATEDIFF(p.fecha_vencimiento, ?) AS dias_para_vencer
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.negocio_id = ? AND p.activo = 1 AND p.fecha_vencimiento IS NOT NULL";
    $params = [$hoy, $lim, $hoy, $negocioId];

    if ($solo_vencidos) {
        $sql .= " AND p.fecha_vencimiento < ?";
        $params[] = $hoy;
    } else {
        $sql .= " AND p.fecha_vencimiento <= ?";
        $params[] = $lim;
    }

    if ($q)     { $sql .= " AND (p.nombre LIKE ? OR p.codigo_barras LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
    if ($catId) { $sql .= " AND p.categoria_id = ?"; $params[] = $catId; }
    $sql .= " ORDER BY p.fecha_vencimiento ASC";

    $s = $db->prepare($sql);
    $s->execute($params);
    $rows = $s->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $stats = ['vencidos' => 0, 'proximos' => 0, 'ok' => 0, 'total' => count($rows)];
    foreach ($rows as $r) $stats[$r['estado_vencimiento'] === 'proximo' ? 'proximos' : $r['estado_vencimiento']]++;

    Response::success('OK', ['productos' => $rows, 'stats' => $stats]);
}

// Actualizar fecha de vencimiento de un producto
if ($method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['id'])) Response::error('ID requerido', 422);
    $db->prepare("UPDATE productos SET fecha_vencimiento=? WHERE id=? AND negocio_id=?")
       ->execute([$d['fecha_vencimiento'] ?: null, (int)$d['id'], $negocioId]);
    Response::success('Fecha actualizada');
}
