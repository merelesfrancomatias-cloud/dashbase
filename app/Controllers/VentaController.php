<?php
namespace App\Controllers;

use App\Models\VentaModel;
use App\AuditLog;
use App\Paginator;
use App\Validator;
use PlanGuard;
use Response;
use Auth;
use Database;

class VentaController extends Controller
{
    private VentaModel $model;
    private \PDO $db;

    public function __construct(int $negocioId, int $usuarioId)
    {
        parent::__construct($negocioId, $usuarioId);
        $this->db    = (new Database())->getConnection();
        $this->model = new VentaModel($this->db, $negocioId);
    }

    protected function index(): void
    {
        $esAdmin = Auth::getRol() === 'admin';

        // GET /api/ventas/?top_productos=1  — para el punto de venta
        if (isset($_GET['top_productos'])) {
            $limit = min((int)($_GET['limit'] ?? 12), 50);
            // Aceptar 'dias' directo o 'periodo' como texto
            if (isset($_GET['dias'])) {
                $dias = (int)$_GET['dias'];
            } else {
                $periodoMap = [
                    'hoy'       => 1,
                    'semana'    => 7,
                    'mes'       => 30,
                    'trimestre' => 90,
                    'anio'      => 365,
                    'año'       => 365,
                ];
                $p    = strtolower(explode('&', $_GET['periodo'] ?? 'mes')[0]);
                $dias = $periodoMap[$p] ?? 30;
            }
            Response::success('Top productos', $this->model->topProductos($limit, $dias));
            return;
        }

        if (isset($_GET['id'])) {
            $venta = $this->model->findById(
                (int)$_GET['id'],
                !$esAdmin,
                $this->usuarioId
            );
            if (!$venta) {
                Response::error('Venta no encontrada', 404);
            }
            Response::success('Venta encontrada', $venta);
        }

        $filtros = array_filter([
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin'    => $_GET['fecha_fin']    ?? null,
            'metodo_pago'  => $_GET['metodo_pago']  ?? null,
        ]);

        $ventas = $this->model->list($filtros, !$esAdmin, $this->usuarioId);

        // Paginación opcional
        if (isset($_GET['page'])) {
            $page    = Paginator::page();
            $perPage = Paginator::perPage();
            $total   = count($ventas);
            Response::success('Ventas obtenidas', [
                'ventas'     => array_slice($ventas, ($page - 1) * $perPage, $perPage),
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => max(1, (int)ceil($total / $perPage)),
                ],
            ]);
        }

        Response::success('Ventas obtenidas', $ventas);
    }

    protected function store(): void
    {
        PlanGuard::checkLimit('ventas_mes', $this->negocioId, $this->db);

        $d = $this->jsonBody();

        if (empty($d['items']) || !is_array($d['items']) || count($d['items']) === 0) {
            Response::error('Debe incluir al menos un producto', 400);
        }

        $v = (new Validator($d))
            ->required('metodo_pago')
            ->numeric('descuento', min: 0);

        if ($v->fails()) {
            Response::error($v->firstError(), 400);
        }

        // Validar cada item
        foreach ($d['items'] as $i => $item) {
            $vi = (new Validator((array)$item))
                ->required('producto_id')
                ->required('cantidad')
                ->required('precio_unitario')
                ->numeric('cantidad', min: 0.01)
                ->numeric('precio_unitario', min: 0);
            if ($vi->fails()) {
                Response::error("Item #" . ($i + 1) . ": " . $vi->firstError(), 400);
            }
        }

        // Verificar caja abierta
        $caja = $this->model->cajaAbierta($this->usuarioId);
        if (!$caja) {
            Response::error('No tienes una caja abierta', 400);
        }

        // Normalizar items a array
        $items = array_map(fn($i) => (array)$i, $d['items']);

        $result = $this->model->crear(
            [
                'usuario_id'       => $this->usuarioId,
                'caja_id'          => $caja['id'],
                'cliente_nombre'   => $d['cliente_nombre']   ?? null,
                'cliente_telefono' => $d['cliente_telefono'] ?? null,
                'metodo_pago'      => $d['metodo_pago'],
                'descuento'        => $d['descuento']        ?? 0,
                'observaciones'    => $d['observaciones']    ?? null,
            ],
            $items
        );

        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::CREATE, 'ventas', $result['venta_id'] ?? null, null, ['metodo_pago' => $d['metodo_pago'], 'total_items' => count($items)]);
        Response::success('Venta registrada exitosamente', $result, 201);
    }

    protected function update(): void
    {
        $d  = $this->jsonBody();
        $id = (int)($d['id'] ?? 0);

        if ($id <= 0) {
            Response::error('ID de venta requerido', 400);
        }

        // Solo admin puede editar ventas de otros
        $esAdmin = Auth::getRol() === 'admin';
        $venta   = $this->model->findById($id, !$esAdmin, $this->usuarioId);

        if (!$venta) {
            Response::error('Venta no encontrada o sin permiso', 404);
        }

        if ($venta['estado'] === 'cancelada') {
            Response::error('No se puede editar una venta anulada', 400);
        }

        // Campos editables
        $campos  = [];
        $params  = [':id' => $id, ':negocio_id' => $this->negocioId];

        if (isset($d['metodo_pago']) && $d['metodo_pago'] !== '') {
            $campos[]              = 'metodo_pago = :metodo_pago';
            $params[':metodo_pago'] = $d['metodo_pago'];
        }
        if (isset($d['descuento'])) {
            $descuento = max(0, (float)$d['descuento']);
            $nuevoTotal = (float)$venta['subtotal'] - $descuento;
            $campos[] = 'descuento = :descuento';
            $campos[] = 'total = :total';
            $params[':descuento'] = $descuento;
            $params[':total']     = $nuevoTotal;
        }
        if (array_key_exists('observaciones', $d)) {
            $campos[]                = 'observaciones = :observaciones';
            $params[':observaciones'] = $d['observaciones'];
        }

        if (empty($campos)) {
            Response::error('No hay campos para actualizar', 400);
        }

        $sql = "UPDATE ventas SET " . implode(', ', $campos) . "
                WHERE id = :id AND negocio_id = :negocio_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::UPDATE, 'ventas', $id, null, $d);
        $ventaActualizada = $this->model->findById($id);
        Response::success('Venta actualizada', $ventaActualizada);
    }

    protected function destroy(): void
    {
        $d  = $this->jsonBody();
        $id = (int)($d['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            Response::error('ID de venta requerido', 400);
        }

        $esAdmin = Auth::getRol() === 'admin';
        $venta   = $this->model->findById($id, !$esAdmin, $this->usuarioId);

        if (!$venta) {
            Response::error('Venta no encontrada o sin permiso', 404);
        }

        if ($venta['estado'] === 'cancelada') {
            Response::error('La venta ya está anulada', 400);
        }

        $this->db->beginTransaction();
        try {
            // Anular venta (soft delete — excluye del cálculo de caja automáticamente)
            $stmt = $this->db->prepare("
                UPDATE ventas
                SET estado = 'cancelada',
                    motivo_cancelacion = :motivo,
                    cancelada_por = :cancelada_por,
                    fecha_cancelacion = NOW()
                WHERE id = :id AND negocio_id = :negocio_id
            ");
            $stmt->execute([
                ':motivo'        => $d['motivo'] ?? 'Anulada desde historial',
                ':cancelada_por' => $this->usuarioId,
                ':id'            => $id,
                ':negocio_id'    => $this->negocioId,
            ]);

            // Revertir stock de los items
            foreach ($venta['items'] as $item) {
                $this->db->prepare("
                    UPDATE productos SET stock = stock + :cantidad WHERE id = :id
                ")->execute([':cantidad' => $item['cantidad'], ':id' => $item['producto_id']]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error('Error al anular la venta: ' . $e->getMessage(), 500);
        }

        AuditLog::log($this->db, $this->negocioId, $this->usuarioId, AuditLog::DELETE, 'ventas', $id, null, ['anulada' => true]);
        Response::success('Venta anulada correctamente. El stock fue restaurado y la caja actualizada.', ['id' => $id]);
    }
}
