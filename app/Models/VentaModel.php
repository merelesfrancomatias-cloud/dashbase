<?php
namespace App\Models;

use PDO;

/**
 * VentaModel — queries de ventas + detalle_ventas.
 */
class VentaModel extends Model
{
    public function findById(int $id, bool $soloDelUsuario = false, int $usuarioId = 0): ?array
    {
        $sql = "SELECT v.*, u.nombre AS usuario_nombre
                FROM ventas v
                INNER JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.id = :id AND v.negocio_id = :negocio_id";

        $params = [':id' => $id, ':negocio_id' => $this->negocioId];

        if ($soloDelUsuario && $usuarioId > 0) {
            $sql .= " AND v.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }

        $venta = $this->fetchOne($sql, $params);
        if (!$venta) {
            return null;
        }

        $venta['items'] = $this->fetchAll("
            SELECT dv.*, p.nombre AS producto_nombre
            FROM detalle_ventas dv
            INNER JOIN productos p ON dv.producto_id = p.id
            WHERE dv.venta_id = :venta_id
        ", [':venta_id' => $id]);

        return $venta;
    }

    public function list(array $filtros = [], bool $soloDelUsuario = false, int $usuarioId = 0): array
    {
        $sql = "SELECT v.*, u.nombre AS usuario_nombre,
                (SELECT COUNT(*) FROM detalle_ventas WHERE venta_id = v.id) AS items_count
                FROM ventas v
                INNER JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.negocio_id = :negocio_id";

        $params = [':negocio_id' => $this->negocioId];

        if ($soloDelUsuario && $usuarioId > 0) {
            $sql .= " AND v.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(v.fecha_venta) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(v.fecha_venta) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['metodo_pago'])) {
            $sql .= " AND v.metodo_pago = :metodo_pago";
            $params[':metodo_pago'] = $filtros['metodo_pago'];
        }

        $sql .= " ORDER BY v.fecha_venta DESC";

        return $this->fetchAll($sql, $params);
    }

    public function cajaAbierta(int $usuarioId): ?array
    {
        return $this->fetchOne(
            "SELECT id FROM cajas WHERE usuario_id = :usuario_id AND estado = 'abierta' LIMIT 1",
            [':usuario_id' => $usuarioId]
        );
    }

    public function productoStock(int $productoId): ?array
    {
        return $this->fetchOne(
            "SELECT stock FROM productos WHERE id = :id AND negocio_id = :negocio_id",
            [':id' => $productoId, ':negocio_id' => $this->negocioId]
        );
    }

    /**
     * Inserta la venta completa en una transacción.
     *
     * @param  array $ventaData   Datos de cabecera
     * @param  array $items       Array de ['producto_id', 'cantidad', 'precio_unitario']
     * @return array              ['venta_id' => int, 'total' => float]
     * @throws \Exception         Si hay error en stock o en DB
     */
    public function crear(array $ventaData, array $items): array
    {
        $this->db->beginTransaction();

        try {
            // Calcular totales
            $subtotal = array_sum(array_map(
                fn($i) => $i['cantidad'] * $i['precio_unitario'],
                $items
            ));
            $descuento = (float)($ventaData['descuento'] ?? 0);
            $total     = $subtotal - $descuento;

            // Insertar cabecera
            $this->execute("
                INSERT INTO ventas
                    (negocio_id, usuario_id, caja_id, cliente_nombre, cliente_telefono,
                     subtotal, descuento, total, metodo_pago, observaciones)
                VALUES
                    (:negocio_id, :usuario_id, :caja_id, :cliente_nombre, :cliente_telefono,
                     :subtotal, :descuento, :total, :metodo_pago, :observaciones)
            ", [
                ':negocio_id'       => $this->negocioId,
                ':usuario_id'       => $ventaData['usuario_id'],
                ':caja_id'          => $ventaData['caja_id'],
                ':cliente_nombre'   => $ventaData['cliente_nombre']   ?? null,
                ':cliente_telefono' => $ventaData['cliente_telefono'] ?? null,
                ':subtotal'         => $subtotal,
                ':descuento'        => $descuento,
                ':total'            => $total,
                ':metodo_pago'      => $ventaData['metodo_pago'],
                ':observaciones'    => $ventaData['observaciones']    ?? null,
            ]);

            $ventaId = (int)$this->lastInsertId();

            // Insertar detalle y descontar stock
            foreach ($items as $item) {
                if (!$this->productoStock((int)$item['producto_id'])) {
                    throw new \Exception("Producto ID {$item['producto_id']} no encontrado");
                }

                $detalleSubtotal = $item['cantidad'] * $item['precio_unitario'];

                $this->execute("
                    INSERT INTO detalle_ventas
                        (venta_id, negocio_id, producto_id, cantidad, precio_unitario, subtotal)
                    VALUES
                        (:venta_id, :negocio_id, :producto_id, :cantidad, :precio_unitario, :subtotal)
                ", [
                    ':venta_id'        => $ventaId,
                    ':negocio_id'      => $this->negocioId,
                    ':producto_id'     => $item['producto_id'],
                    ':cantidad'        => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario'],
                    ':subtotal'        => $detalleSubtotal,
                ]);

                $this->execute(
                    "UPDATE productos SET stock = stock - :cantidad WHERE id = :id",
                    [':cantidad' => $item['cantidad'], ':id' => $item['producto_id']]
                );
            }

            $this->db->commit();

            return ['venta_id' => $ventaId, 'total' => $total];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Devuelve los productos más vendidos del negocio.
     * Si no hay historial de ventas, devuelve los de mayor stock.
     *
     * @param int $limit  Cantidad de resultados
     * @param int $dias   Ventana temporal (0 = todo el historial)
     */
    public function topProductos(int $limit = 12, int $dias = 30): array
    {
        $params = [
            ':negocio_id1' => $this->negocioId,
            ':negocio_id2' => $this->negocioId,
        ];

        $fechaCondicion = '';
        if ($dias > 0) {
            $fechaCondicion = "AND v.fecha_venta >= DATE_SUB(NOW(), INTERVAL :dias DAY)";
            $params[':dias'] = $dias;
        }

        // Intentar por historial de ventas
        $sql = "SELECT
                    p.id, p.nombre, p.precio_venta, p.stock, p.foto, p.unidad_medida,
                    COALESCE(SUM(dv.cantidad), 0) AS total_vendido
                FROM productos p
                LEFT JOIN detalle_ventas dv ON dv.producto_id = p.id
                LEFT JOIN ventas v ON v.id = dv.venta_id
                    AND v.negocio_id = :negocio_id1
                    AND v.estado = 'completada'
                    $fechaCondicion
                WHERE p.negocio_id = :negocio_id2 AND p.activo = 1
                GROUP BY p.id
                ORDER BY total_vendido DESC, p.stock DESC
                LIMIT :limit";

        $params[':limit'] = $limit;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            if ($key === ':limit') {
                $stmt->bindValue($key, $val, \PDO::PARAM_INT);
            } elseif ($key === ':dias') {
                $stmt->bindValue($key, $val, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
