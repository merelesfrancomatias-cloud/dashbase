<?php
namespace App\Controllers;

use App\Models\CategoriaModel;
use App\AuditLog;
use Response;
use Database;

class CategoriaController extends Controller
{
    private CategoriaModel $model;
    private \PDO $db;

    public function __construct(int $negocioId, int $usuarioId)
    {
        parent::__construct($negocioId, $usuarioId);
        $this->db    = (new Database())->getConnection();
        $this->model = new CategoriaModel($this->db, $negocioId);
    }

    protected function index(): void
    {
        if (isset($_GET['id'])) {
            $cat = $this->model->findById((int)$_GET['id']);
            if (!$cat) {
                Response::error('Categoría no encontrada', 404);
            }
            Response::success('Categoría encontrada', $cat);
        }
        Response::success('Categorías obtenidas', $this->model->list());
    }

    protected function store(): void
    {
        $d = $this->jsonBody();
        $this->requireFields($d, ['nombre']);

        $id = $this->model->create(
            $d['nombre'],
            $d['descripcion'] ?? null,
            $d['color']       ?? '#007AFF'
        );
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::CREATE, 'categorias', $id, null, $d);
        Response::success('Categoría creada exitosamente', ['id' => $id], 201);
    }

    protected function update(): void
    {
        $d = $this->jsonBody();
        $this->requireFields($d, ['id', 'nombre']);

        $antes = $this->model->findById((int)$d['id']);
        if (!$this->model->update(
            (int)$d['id'],
            $d['nombre'],
            $d['descripcion'] ?? null,
            $d['color']       ?? '#007AFF'
        )) {
            Response::error('Categoría no encontrada', 404);
        }
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::UPDATE, 'categorias', (int)$d['id'], $antes, $d);
        Response::success('Categoría actualizada exitosamente');
    }

    protected function destroy(): void
    {
        $d = $this->jsonBody();
        $this->requireFields($d, ['id']);

        $cat = $this->model->findById((int)$d['id']);
        if (!$this->model->delete((int)$d['id'])) {
            Response::error('Categoría no encontrada', 404);
        }
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::DELETE, 'categorias', (int)$d['id'], $cat);
        Response::success('Categoría eliminada exitosamente');
    }
}
