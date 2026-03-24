<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$_guardDb = (new Database())->getConnection();
PlanGuard::requireActive($negocioId, $_guardDb);

(new App\Controllers\VentaController($negocioId, $usuarioId))->dispatch();
