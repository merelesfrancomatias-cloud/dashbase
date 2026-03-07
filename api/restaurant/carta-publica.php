<?php
/**
 * API pública — Carta digital del restaurant
 * No requiere autenticación.
 * GET /api/restaurant/carta-publica.php?negocio_id=X
 */
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$negocioId = (int)($_GET['negocio_id'] ?? 0);
if ($negocioId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'negocio_id requerido']);
    exit;
}

$pdo = (new Database())->getConnection();

// Datos del negocio (nombre, logo, color)
$stmtN = $pdo->prepare("SELECT nombre, logo, color_primario, slogan, horario_inicio, horario_cierre FROM negocios WHERE id = :id AND activo = 1");
$stmtN->execute([':id' => $negocioId]);
$negocio = $stmtN->fetch(PDO::FETCH_ASSOC);
if (!$negocio) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Negocio no encontrado']);
    exit;
}

// Categorías con platos activos
$stmtC = $pdo->prepare("
    SELECT c.id, c.nombre, c.color, c.icono,
           COUNT(p.id) AS total_platos
    FROM categorias c
    INNER JOIN productos p ON p.categoria_id = c.id
        AND p.negocio_id = :nid
        AND p.activo = 1
    WHERE c.negocio_id = :nid2
    GROUP BY c.id
    ORDER BY c.nombre ASC
");
$stmtC->execute([':nid' => $negocioId, ':nid2' => $negocioId]);
$categorias = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// Platos activos con categoría
$stmtP = $pdo->prepare("
    SELECT p.id, p.nombre, p.descripcion, p.precio_venta, p.foto,
           p.categoria_id, c.nombre AS categoria_nombre, c.color AS categoria_color
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.negocio_id = :nid AND p.activo = 1
    ORDER BY c.nombre ASC, p.nombre ASC
");
$stmtP->execute([':nid' => $negocioId]);
$platos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success'    => true,
    'negocio'    => $negocio,
    'categorias' => $categorias,
    'platos'     => $platos,
]);
