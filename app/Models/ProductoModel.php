<?php
namespace App\Models;

/**
 * ProductoModel — queries de la tabla productos.
 */
class ProductoModel extends Model
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne("
            SELECT p.*, c.nombre AS categoria_nombre, c.color AS categoria_color
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.id = :id AND p.negocio_id = :negocio_id
        ", [':id' => $id, ':negocio_id' => $this->negocioId]);
    }

    public function findByCodigoBarras(string $codigo): ?array
    {
        return $this->fetchOne("
            SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.codigo_barras = :codigo AND p.negocio_id = :negocio_id
        ", [':codigo' => $codigo, ':negocio_id' => $this->negocioId]);
    }

    public function list(string $search = '', int $categoriaId = 0, bool $soloStockBajo = false): array
    {
        $sql = "
            SELECT p.*, c.nombre AS categoria_nombre, c.color AS categoria_color,
                   CASE WHEN p.stock <= p.stock_minimo THEN 1 ELSE 0 END AS stock_bajo,
                   prov.nombre AS proveedor_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN proveedores prov ON prov.id = p.proveedor_id
            WHERE p.negocio_id = :negocio_id
        ";
        $params = [':negocio_id' => $this->negocioId];

        if (!empty($search)) {
            $sql .= " AND (p.nombre LIKE :search OR p.codigo_barras LIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($categoriaId > 0) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoriaId;
        }
        if ($soloStockBajo) {
            $sql .= " AND p.stock <= p.stock_minimo";
        }

        $sql .= " ORDER BY p.nombre ASC";

        return $this->fetchAll($sql, $params);
    }

    public function codigoBarrasExiste(string $codigo, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM productos
                WHERE codigo_barras = :codigo AND negocio_id = :negocio_id";
        $params = [':codigo' => $codigo, ':negocio_id' => $this->negocioId];

        if ($excludeId > 0) {
            $sql    .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        return (int)$this->db->prepare($sql) && (function () use ($sql, $params): bool {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        })();
    }

    public function existsCodigoBarras(string $codigo, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM productos
                WHERE codigo_barras = :codigo AND negocio_id = :negocio_id";
        $params = [':codigo' => $codigo, ':negocio_id' => $this->negocioId];
        if ($excludeId > 0) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $d): int
    {
        $this->execute("
            INSERT INTO productos
                (negocio_id, categoria_id, nombre, descripcion, codigo_barras,
                 precio_costo, precio_venta, stock, stock_minimo, unidad_medida, foto)
            VALUES
                (:negocio_id, :categoria_id, :nombre, :descripcion, :codigo_barras,
                 :precio_costo, :precio_venta, :stock, :stock_minimo, :unidad_medida, :foto)
        ", [
            ':negocio_id'    => $this->negocioId,
            ':categoria_id'  => $d['categoria_id']  ?? null,
            ':nombre'        => $d['nombre'],
            ':descripcion'   => $d['descripcion']   ?? null,
            ':codigo_barras' => $d['codigo_barras']  ?? null,
            ':precio_costo'  => $d['precio_costo']  ?? 0,
            ':precio_venta'  => $d['precio_venta'],
            ':stock'         => $d['stock']         ?? 0,
            ':stock_minimo'  => $d['stock_minimo']  ?? 0,
            ':unidad_medida' => $d['unidad_medida'] ?? 'unidad',
            ':foto'          => $d['foto']          ?? null,
        ]);

        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        return $this->execute("
            UPDATE productos SET
                categoria_id  = :categoria_id,
                nombre        = :nombre,
                descripcion   = :descripcion,
                codigo_barras = :codigo_barras,
                precio_costo  = :precio_costo,
                precio_venta  = :precio_venta,
                stock         = :stock,
                stock_minimo  = :stock_minimo,
                unidad_medida = :unidad_medida,
                foto          = :foto
            WHERE id = :id AND negocio_id = :negocio_id
        ", [
            ':id'            => $id,
            ':negocio_id'    => $this->negocioId,
            ':categoria_id'  => $d['categoria_id']  ?? null,
            ':nombre'        => $d['nombre'],
            ':descripcion'   => $d['descripcion']   ?? null,
            ':codigo_barras' => $d['codigo_barras']  ?? null,
            ':precio_costo'  => $d['precio_costo']  ?? 0,
            ':precio_venta'  => $d['precio_venta'],
            ':stock'         => $d['stock']         ?? 0,
            ':stock_minimo'  => $d['stock_minimo']  ?? 0,
            ':unidad_medida' => $d['unidad_medida'] ?? 'unidad',
            ':foto'          => $d['foto']          ?? null,
        ]) > 0;
    }

    public function delete(int $id): ?array
    {
        $producto = $this->findById($id);
        if (!$producto) {
            return null;
        }
        $this->execute(
            "DELETE FROM productos WHERE id = :id AND negocio_id = :negocio_id",
            [':id' => $id, ':negocio_id' => $this->negocioId]
        );
        return $producto;
    }

    /** Estadísticas rápidas para el listado */
    public function estadisticas(array $productos): array
    {
        $stockBajo = $stockValorizado = 0;
        foreach ($productos as $p) {
            if ($p['stock'] <= $p['stock_minimo']) {
                $stockBajo++;
            }
            if ($p['stock'] > 0) {
                $stockValorizado += $p['stock'] * $p['precio_venta'];
            }
        }
        return [
            'total'            => count($productos),
            'stock_bajo'       => $stockBajo,
            'stock_valorizado' => round($stockValorizado, 2),
        ];
    }
}
