<?php
namespace App;

/**
 * Paginator — Paginación estándar para todos los listados.
 *
 * Uso en un Controller:
 *   $page     = Paginator::page();
 *   $perPage  = Paginator::perPage();
 *   $result   = Paginator::paginate($conn, $sql, $params, $page, $perPage);
 *   Response::success('Items', $result);
 *
 * La respuesta incluye:
 *   data       → array de items de la página actual
 *   pagination → { total, per_page, current_page, last_page, from, to }
 */
class Paginator
{
    public const DEFAULT_PER_PAGE = 20;
    public const MAX_PER_PAGE     = 100;

    /**
     * Devuelve el número de página actual desde $_GET['page'] (mínimo 1).
     */
    public static function page(): int
    {
        return max(1, (int)($_GET['page'] ?? 1));
    }

    /**
     * Devuelve el tamaño de página desde $_GET['per_page'], con límite máximo.
     */
    public static function perPage(): int
    {
        $perPage = (int)($_GET['per_page'] ?? self::DEFAULT_PER_PAGE);
        return min(max(1, $perPage), self::MAX_PER_PAGE);
    }

    /**
     * Ejecuta la query con paginación y devuelve datos + metadatos.
     *
     * @param \PDO   $conn
     * @param string $sql      Query SIN LIMIT/OFFSET
     * @param array  $params   Parámetros de la query
     * @param int    $page
     * @param int    $perPage
     * @return array           ['data' => [...], 'pagination' => [...]]
     */
    public static function paginate(
        \PDO   $conn,
        string $sql,
        array  $params  = [],
        int    $page    = 1,
        int    $perPage = self::DEFAULT_PER_PAGE
    ): array {
        // Contar total de registros
        $countSql  = "SELECT COUNT(*) FROM ({$sql}) AS _count_query";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Calcular offset
        $offset   = ($page - 1) * $perPage;
        $lastPage = $total > 0 ? (int)ceil($total / $perPage) : 1;

        // Obtener items de la página
        $stmt = $conn->prepare($sql . " LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data'       => $data,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'from'         => $total > 0 ? $offset + 1 : 0,
                'to'           => min($offset + $perPage, $total),
            ],
        ];
    }
}
