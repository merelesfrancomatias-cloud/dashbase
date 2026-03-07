<?php
namespace App\Models;

/**
 * CategoriaModel — queries de la tabla categorias.
 */
class CategoriaModel extends Model
{
    public function list(): array
    {
        return $this->fetchAll("
            SELECT c.*,
                   COUNT(p.id) AS total_productos
            FROM categorias c
            LEFT JOIN productos p ON p.categoria_id = c.id AND p.negocio_id = c.negocio_id
            WHERE c.negocio_id = :negocio_id
            GROUP BY c.id
            ORDER BY c.nombre ASC
        ", [':negocio_id' => $this->negocioId]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM categorias WHERE id = :id AND negocio_id = :negocio_id",
            [':id' => $id, ':negocio_id' => $this->negocioId]
        );
    }

    public function create(string $nombre, ?string $descripcion, string $color): int
    {
        $this->execute("
            INSERT INTO categorias (negocio_id, nombre, descripcion, color)
            VALUES (:negocio_id, :nombre, :descripcion, :color)
        ", [
            ':negocio_id'  => $this->negocioId,
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':color'       => $color,
        ]);
        return (int)$this->lastInsertId();
    }

    public function update(int $id, string $nombre, ?string $descripcion, string $color): bool
    {
        return $this->execute("
            UPDATE categorias
            SET nombre = :nombre, descripcion = :descripcion, color = :color
            WHERE id = :id AND negocio_id = :negocio_id
        ", [
            ':id'          => $id,
            ':negocio_id'  => $this->negocioId,
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':color'       => $color,
        ]) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM categorias WHERE id = :id AND negocio_id = :negocio_id",
            [':id' => $id, ':negocio_id' => $this->negocioId]
        ) > 0;
    }
}
