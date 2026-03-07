<?php
namespace App\Controllers;

use App\Models\GastoModel;
use App\AuditLog;
use App\Paginator;
use App\Validator;
use Response;
use Database;

class GastoController extends Controller
{
    private GastoModel $model;
    private \PDO $db;

    public function __construct(int $negocioId, int $usuarioId)
    {
        parent::__construct($negocioId, $usuarioId);
        $this->db    = (new Database())->getConnection();
        $this->model = new GastoModel($this->db, $negocioId);
    }

    protected function index(): void
    {
        if (isset($_GET['id'])) {
            $gasto = $this->model->findById((int)$_GET['id']);
            if (!$gasto) {
                Response::error('Gasto no encontrado', 404);
            }
            Response::success('Gasto encontrado', $gasto);
        }

        $filtros = array_filter([
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin'    => $_GET['fecha_fin']    ?? null,
            'categoria'    => $_GET['categoria']    ?? null,
        ]);

        $gastos = $this->model->list($filtros);

        // Paginación opcional
        if (isset($_GET['page'])) {
            $page    = Paginator::page();
            $perPage = Paginator::perPage();
            $total   = count($gastos);
            Response::success('Gastos obtenidos', [
                'gastos'     => array_slice($gastos, ($page - 1) * $perPage, $perPage),
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => max(1, (int)ceil($total / $perPage)),
                ],
            ]);
        }

        Response::success('Gastos obtenidos', $gastos);
    }

    protected function store(): void
    {
        $d = $this->jsonBody();

        $v = (new Validator($d))
            ->required('monto')
            ->required('fecha_gasto')
            ->numeric('monto', min: 0)
            ->date('fecha_gasto');

        if ($v->fails()) {
            Response::error($v->firstError(), 400);
        }

        $id = $this->model->create($d, $this->usuarioId);
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::CREATE, 'gastos', $id, null, $d);
        Response::success('Gasto registrado exitosamente', ['id' => $id], 201);
    }

    protected function update(): void
    {
        $d = $this->jsonBody();

        $v = (new Validator($d))
            ->required('id')
            ->required('monto')
            ->required('fecha_gasto')
            ->integer('id', min: 1)
            ->numeric('monto', min: 0)
            ->date('fecha_gasto');

        if ($v->fails()) {
            Response::error($v->firstError(), 400);
        }

        $antes = $this->model->findById((int)$d['id']);
        if (!$this->model->update((int)$d['id'], $d)) {
            Response::error('Gasto no encontrado', 404);
        }
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::UPDATE, 'gastos', (int)$d['id'], $antes, $d);
        Response::success('Gasto actualizado exitosamente');
    }

    protected function destroy(): void
    {
        $d = $this->jsonBody();
        $this->requireFields($d, ['id']);

        $gasto = $this->model->findById((int)$d['id']);
        if (!$this->model->delete((int)$d['id'])) {
            Response::error('Gasto no encontrado', 404);
        }
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::DELETE, 'gastos', (int)$d['id'], $gasto);
        Response::success('Gasto eliminado exitosamente');
    }
}
