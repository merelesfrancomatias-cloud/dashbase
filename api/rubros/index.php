<?php
/**
 * GET /api/rubros/index.php          — Lista todos los rubros activos
 * GET /api/rubros/index.php?id=3     — Detalle de un rubro + sus categorías por defecto
 */
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
Middleware::method('GET');

$db = (new Database())->getConnection();

try {
    if (isset($_GET['id'])) {
        // Detalle de rubro + categorías por defecto
        $stmt = $db->prepare("SELECT * FROM rubros WHERE id = ? AND activo = 1");
        $stmt->execute([(int)$_GET['id']]);
        $rubro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rubro) {
            Response::error('Rubro no encontrado', 404);
            exit;
        }

        $stmt = $db->prepare("
            SELECT id, nombre, color, orden
            FROM rubro_categorias_default
            WHERE rubro_id = ?
            ORDER BY orden ASC
        ");
        $stmt->execute([$rubro['id']]);
        $rubro['categorias_default'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success('Rubro encontrado', $rubro);
    } else {
        // Listar todos
        $stmt = $db->query("
            SELECT r.id, r.slug, r.nombre, r.descripcion, r.icono, r.color, r.orden,
                   COUNT(rc.id) as total_categorias
            FROM rubros r
            LEFT JOIN rubro_categorias_default rc ON rc.rubro_id = r.id
            WHERE r.activo = 1
            GROUP BY r.id
            ORDER BY r.orden ASC
        ");
        Response::success('Rubros obtenidos', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage(), 500);
}
