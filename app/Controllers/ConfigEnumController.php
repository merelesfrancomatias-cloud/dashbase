<?php
namespace App\Controllers;

use App\Models\ConfigEnumModel;
use App\Validator;
use Response;
use Database;

/**
 * ConfigEnumController
 *
 * GET    /api/config              → todos los grupos con sus valores
 * GET    /api/config?grupo=X      → solo el grupo solicitado
 * POST   /api/config              → crear nuevo valor { grupo, valor, etiqueta, orden? }
 * PUT    /api/config              → editar etiqueta/orden { id, etiqueta, orden }
 * DELETE /api/config              → desactivar valor { id }  (no aplica a es_sistema=1)
 */
class ConfigEnumController extends Controller
{
    private ConfigEnumModel $model;

    public function __construct(int $negocioId, int $usuarioId)
    {
        parent::__construct($negocioId, $usuarioId);
        $db          = (new Database())->getConnection();
        $this->model = new ConfigEnumModel($db, $negocioId);
    }

    protected function index(): void
    {
        if (isset($_GET['grupo'])) {
            $grupo = trim($_GET['grupo']);
            Response::success('Valores obtenidos', $this->model->porGrupo($grupo));
        } else {
            Response::success('Configuración obtenida', $this->model->todos());
        }
    }

    protected function store(): void
    {
        $d = $this->jsonBody();

        $v = (new Validator($d))
            ->required('grupo')
            ->required('valor')
            ->required('etiqueta')
            ->maxLength('grupo', 60)
            ->maxLength('valor', 100)
            ->maxLength('etiqueta', 150);

        if ($v->fails()) {
            Response::error($v->firstError(), 400);
        }

        $id = $this->model->create(
            trim($d['grupo']),
            trim($d['valor']),
            trim($d['etiqueta']),
            isset($d['orden']) ? (int)$d['orden'] : 99
        );

        Response::success('Valor creado exitosamente', ['id' => $id], 201);
    }

    protected function update(): void
    {
        $d = $this->jsonBody();

        $v = (new Validator($d))
            ->required('id')
            ->required('etiqueta')
            ->integer('id', min: 1)
            ->maxLength('etiqueta', 150);

        if ($v->fails()) {
            Response::error($v->firstError(), 400);
        }

        if (!$this->model->update((int)$d['id'], trim($d['etiqueta']), (int)($d['orden'] ?? 99))) {
            Response::error('Valor no encontrado', 404);
        }

        Response::success('Valor actualizado exitosamente');
    }

    protected function destroy(): void
    {
        $d = $this->jsonBody();
        $this->requireFields($d, ['id']);

        if (!$this->model->delete((int)$d['id'])) {
            Response::error('Valor no encontrado o no se puede eliminar (es valor del sistema)', 400);
        }

        Response::success('Valor desactivado exitosamente');
    }
}
