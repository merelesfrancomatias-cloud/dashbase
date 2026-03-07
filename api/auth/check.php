<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
Middleware::method('GET');

try {
    Auth::check();

    Response::success('Sesión activa', [
        'user_id'    => Auth::getUserId(),
        'nombre'     => Auth::getNombre(),
        'rol'        => Auth::getRol(),
        'negocio_id' => Auth::getNegocioId(),
        'plan'       => Auth::getPlan(),
    ]);
} catch (Exception $e) {
    Response::error('Sesión no válida', 401);
}
