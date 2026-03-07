<?php
namespace App\Controllers;

use Response;
use Middleware;

/**
 * Clase base para todos los controladores.
 * Centraliza el dispatch por método HTTP.
 */
abstract class Controller
{
    protected int $negocioId;
    protected int $usuarioId;

    public function __construct(int $negocioId, int $usuarioId)
    {
        $this->negocioId = $negocioId;
        $this->usuarioId = $usuarioId;
    }

    /**
     * Despacha la acción correcta según el método HTTP.
     * Cada controlador hijo implementa los métodos que soporta.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        match ($method) {
            'GET'    => $this->index(),
            'POST'   => $this->store(),
            'PUT'    => $this->update(),
            'DELETE' => $this->destroy(),
            default  => Response::error('Método no permitido', 405),
        };
    }

    // Métodos que los controladores hijos pueden sobreescribir
    protected function index(): void
    {
        Response::error('Método GET no implementado', 405);
    }

    protected function store(): void
    {
        Response::error('Método POST no implementado', 405);
    }

    protected function update(): void
    {
        Response::error('Método PUT no implementado', 405);
    }

    protected function destroy(): void
    {
        Response::error('Método DELETE no implementado', 405);
    }

    /**
     * Decodifica y devuelve el body JSON de la request.
     */
    protected function jsonBody(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }

    /**
     * Valida campos requeridos en un array de datos.
     * Si falta alguno, responde con error 400 y termina.
     *
     * @param array $data    Datos a validar
     * @param array $fields  Nombres de campos requeridos
     */
    protected function requireFields(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                Response::error("El campo '$field' es requerido", 400);
            }
        }
    }
}
