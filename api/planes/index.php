<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
Middleware::method('GET');

[$negocioId] = Middleware::auth();

try {
    $db   = new Database();
    $conn = $db->getConnection();

    $info = PlanGuard::getPlanInfo($negocioId, $conn);

    Response::success('Plan obtenido correctamente', $info);

} catch (Exception $e) {
    Response::error('Error al obtener el plan: ' . $e->getMessage(), 500);
}
