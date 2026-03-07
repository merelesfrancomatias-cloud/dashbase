<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);

[$negocioId, $usuarioId] = Middleware::auth();

(new App\Controllers\ConfigEnumController($negocioId, $usuarioId))->handle();
