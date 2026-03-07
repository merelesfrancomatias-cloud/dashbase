<?php
namespace App\Controllers;

use App\Models\ProductoModel;
use App\Paginator;
use App\Validator;
use App\AuditLog;
use PlanGuard;
use Response;
use Database;

class ProductoController extends Controller
{
    private ProductoModel $model;
    private \PDO $db;

    public function __construct(int $negocioId, int $usuarioId)
    {
        parent::__construct($negocioId, $usuarioId);
        $this->db    = (new Database())->getConnection();
        $this->model = new ProductoModel($this->db, $negocioId);
    }

    protected function index(): void
    {
        // GET /api/productos — un producto, por código, o listado
        if (isset($_GET['id'])) {
            $producto = $this->model->findById((int)$_GET['id']);
            if (!$producto) {
                Response::error('Producto no encontrado', 404);
            }
            Response::success('Producto encontrado', $producto);
        }

        if (isset($_GET['codigo_barras'])) {
            $producto = $this->model->findByCodigoBarras($_GET['codigo_barras']);
            if (!$producto) {
                Response::error('Producto no encontrado', 404);
            }
            Response::success('Producto encontrado', $producto);
        }

        $productos = $this->model->list(
            $_GET['search']    ?? '',
            (int)($_GET['categoria'] ?? 0),
            isset($_GET['stock_bajo']) && filter_var($_GET['stock_bajo'], FILTER_VALIDATE_BOOLEAN)
        );

        // Paginación opcional: si no se pasa ?page, devuelve todo (modo legacy)
        if (isset($_GET['page'])) {
            $page    = Paginator::page();
            $perPage = Paginator::perPage();
            $total   = count($productos);
            $sliced  = array_slice($productos, ($page - 1) * $perPage, $perPage);
            Response::success('Productos obtenidos', [
                'productos'    => $sliced,
                'estadisticas' => $this->model->estadisticas($productos),
                'pagination'   => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => max(1, (int)ceil($total / $perPage)),
                ],
            ]);
        }

        Response::success('Productos obtenidos', [
            'productos'    => $productos,
            'estadisticas' => $this->model->estadisticas($productos),
        ]);
    }

    protected function store(): void
    {
        PlanGuard::checkLimit('productos', $this->negocioId, $this->db);

        $d = $this->jsonBody();

        $v = (new Validator($d))
            ->required('nombre')
            ->required('precio_venta')
            ->maxLength('nombre', 255)
            ->numeric('precio_venta', min: 0)
            ->numeric('precio_costo', min: 0)
            ->integer('stock', min: 0)
            ->integer('stock_minimo', min: 0);

        if ($v->fails()) {
            Response::error($v->firstError(), 400);
        }

        if (!empty($d['codigo_barras']) && $this->model->existsCodigoBarras($d['codigo_barras'])) {
            Response::error('El código de barras ya existe', 400);
        }

        $id = $this->model->create($d);
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::CREATE, 'productos', $id, null, $d);
        Response::success('Producto creado exitosamente', ['id' => $id], 201);
    }

    protected function update(): void
    {
        $d = $this->jsonBody();

        $v = (new Validator($d))
            ->required('id')
            ->required('nombre')
            ->required('precio_venta')
            ->integer('id', min: 1)
            ->maxLength('nombre', 255)
            ->numeric('precio_venta', min: 0)
            ->numeric('precio_costo', min: 0);

        if ($v->fails()) {
            Response::error($v->firstError(), 400);
        }

        $id = (int)$d['id'];

        if (!empty($d['codigo_barras']) && $this->model->existsCodigoBarras($d['codigo_barras'], $id)) {
            Response::error('El código de barras ya existe en otro producto', 400);
        }

        $antes = $this->model->findById($id);
        if (!$this->model->update($id, $d)) {
            Response::error('Producto no encontrado o sin cambios', 404);
        }
        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::UPDATE, 'productos', $id, $antes, $d);
        Response::success('Producto actualizado exitosamente');
    }

    protected function destroy(): void
    {
        $d = $this->jsonBody();
        $this->requireFields($d, ['id']);

        $producto = $this->model->delete((int)$d['id']);

        if (!$producto) {
            Response::error('Producto no encontrado', 404);
        }

        // Eliminar imagen física si existe
        if (!empty($producto['foto'])) {
            $path = dirname(__DIR__, 2) . '/public/uploads/productos/' . $producto['foto'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::DELETE, 'productos', (int)$d['id'], $producto);
        Response::success('Producto eliminado exitosamente');
    }
}
