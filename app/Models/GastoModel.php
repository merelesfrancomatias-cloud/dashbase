<?php
namespace App\Models;

/**
 * GastoModel — queries de la tabla gastos.
 */
class GastoModel extends Model
{
    public function list(array $filtros = []): array
    {
        $sql = "SELECT g.*, u.nombre AS usuario_nombre
                FROM gastos g
                LEFT JOIN usuarios u ON g.usuario_id = u.id
                WHERE g.negocio_id = :negocio_id";

        $params = [':negocio_id' => $this->negocioId];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND g.fecha_gasto >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND g.fecha_gasto <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['categoria'])) {
            $sql .= " AND g.categoria = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }

        $sql .= " ORDER BY g.fecha_gasto DESC, g.fecha_creacion DESC";

        return $this->fetchAll($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM gastos WHERE id = :id AND negocio_id = :negocio_id",
            [':id' => $id, ':negocio_id' => $this->negocioId]
        );
    }

    public function create(array $d, int $usuarioId): int
    {
        $this->execute("
            INSERT INTO gastos
                (negocio_id, usuario_id, caja_id, categoria, descripcion,
                 monto, metodo_pago, comprobante, fecha_gasto)
            VALUES
                (:negocio_id, :usuario_id, :caja_id, :categoria, :descripcion,
                 :monto, :metodo_pago, :comprobante, :fecha_gasto)
        ", [
            ':negocio_id'  => $this->negocioId,
            ':usuario_id'  => $usuarioId,
            ':caja_id'     => $d['caja_id']     ?? null,
            ':categoria'   => $d['categoria']   ?? 'otros',
            ':descripcion' => $d['descripcion'] ?? null,
            ':monto'       => $d['monto'],
            ':metodo_pago' => $d['metodo_pago'] ?? null,
            ':comprobante' => $d['comprobante'] ?? null,
            ':fecha_gasto' => $d['fecha_gasto'],
        ]);
        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        return $this->execute("
            UPDATE gastos SET
                categoria   = :categoria,
                descripcion = :descripcion,
                monto       = :monto,
                metodo_pago = :metodo_pago,
                comprobante = :comprobante,
                fecha_gasto = :fecha_gasto
            WHERE id = :id AND negocio_id = :negocio_id
        ", [
            ':id'          => $id,
            ':negocio_id'  => $this->negocioId,
            ':categoria'   => $d['categoria']   ?? 'otros',
            ':descripcion' => $d['descripcion'] ?? null,
            ':monto'       => $d['monto'],
            ':metodo_pago' => $d['metodo_pago'] ?? null,
            ':comprobante' => $d['comprobante'] ?? null,
            ':fecha_gasto' => $d['fecha_gasto'],
        ]) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM gastos WHERE id = :id AND negocio_id = :negocio_id",
            [':id' => $id, ':negocio_id' => $this->negocioId]
        ) > 0;
    }
}
