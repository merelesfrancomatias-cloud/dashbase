<?php
namespace App\Models;

use PDO;

/**
 * Clase base para todos los modelos.
 * Encapsula la conexión y provee helpers comunes.
 */
abstract class Model
{
    protected PDO $db;
    protected int $negocioId;

    public function __construct(PDO $db, int $negocioId)
    {
        $this->db        = $db;
        $this->negocioId = $negocioId;
    }

    /**
     * Ejecuta una query preparada y devuelve todos los resultados.
     *
     * @param string $sql
     * @param array  $params
     * @return array
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ejecuta una query preparada y devuelve la primera fila.
     *
     * @param string $sql
     * @param array  $params
     * @return array|null
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ejecuta una query de escritura (INSERT/UPDATE/DELETE).
     *
     * @param string $sql
     * @param array  $params
     * @return int  Rows affected
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Devuelve el último ID insertado.
     */
    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}
