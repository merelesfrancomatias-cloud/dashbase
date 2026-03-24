<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET']);
Middleware::method('GET');

$clientId = trim((string)($_ENV['GOOGLE_CLIENT_ID'] ?? ''));

if ($clientId === '') {
    Response::success('Google no configurado', [
        'enabled' => false,
        'client_id' => null,
    ]);
}

Response::success('Google configurado', [
    'enabled' => true,
    'client_id' => $clientId,
]);
