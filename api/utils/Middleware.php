<?php
/**
 * Middleware — Filtros reutilizables para los endpoints de la API.
 *
 * Uso típico al inicio de un endpoint:
 *
 *   Middleware::auth();                          // solo autenticado
 *   Middleware::admin();                         // autenticado + admin
 *   Middleware::method('POST');                  // método HTTP exacto
 *   Middleware::methods(['GET', 'POST']);         // uno de varios métodos
 *   Middleware::cors(['GET', 'POST']);            // CORS + preflight
 */
class Middleware {

    /**
     * Verifica autenticación y retorna [$negocioId, $usuarioId].
     * Detiene la ejecución con 401 si no está autenticado.
     */
    public static function auth(): array {
        Auth::check();
        return [Auth::getNegocioId(), Auth::getUserId()];
    }

    /**
     * Verifica autenticación + rol admin.
     * Detiene la ejecución con 401/403 si no cumple.
     */
    public static function admin(): array {
        Auth::requireAdmin();
        return [Auth::getNegocioId(), Auth::getUserId()];
    }

    /**
     * Verifica que el método HTTP sea exactamente el esperado.
     */
    public static function method(string $expected): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($expected)) {
            Response::error('Método no permitido', 405);
        }
    }

    /**
     * Verifica que el método HTTP sea uno de los esperados.
     */
    public static function methods(array $allowed): void {
        $allowed = array_map('strtoupper', $allowed);
        if (!in_array($_SERVER['REQUEST_METHOD'], $allowed, true)) {
            Response::error('Método no permitido', 405);
        }
    }

    /**
     * Configura headers CORS y maneja preflight OPTIONS automáticamente.
     *
     * En producción, reemplazar '*' con el dominio real del frontend.
     */
    public static function cors(array $methods = ['GET', 'POST', 'PUT', 'DELETE']): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        // En producción deberías validar el origen contra una whitelist:
        // $allowed = ['https://tuapp.com', 'https://admin.tuapp.com'];
        // if (!in_array($origin, $allowed)) { http_response_code(403); exit; }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: ' . implode(', ', $methods) . ', OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        // Responder al preflight sin procesar nada más
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * Parsea el body JSON del request y valida campos requeridos.
     *
     * @param  array  $required  Lista de campos obligatorios
     * @return array             Los datos parseados
     */
    public static function jsonBody(array $required = []): array {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if ($data === null && !empty($raw)) {
            Response::error('El cuerpo de la solicitud no es JSON válido', 400);
        }

        $data = $data ?? [];

        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            Response::error(
                'Campos requeridos faltantes: ' . implode(', ', $missing),
                400,
                ['missing' => $missing]
            );
        }

        return $data;
    }
}
