<?php
namespace App\Models;

/**
 * ConfigEnumModel — Gestiona los valores configurables (ENUMs) por negocio.
 *
 * Grupos disponibles:
 *   - metodos_pago
 *   - unidades_medida
 *   - categorias_gasto
 *
 * El frontend lee estos valores vía GET /api/config → no hay hardcodeo en JS.
 */
class ConfigEnumModel extends Model
{
    public function __construct(\PDO $db, private int $negocioId)
    {
        parent::__construct($db);
    }

    /**
     * Devuelve todos los valores activos de un grupo.
     * Si $soloValores = true, retorna un array plano ['valor1', 'valor2', ...].
     */
    public function porGrupo(string $grupo, bool $soloValores = false): array
    {
        $rows = $this->fetchAll(
            "SELECT valor, etiqueta, orden, es_sistema
             FROM config_enum
             WHERE negocio_id = :negocio_id AND grupo = :grupo AND activo = 1
             ORDER BY orden ASC, etiqueta ASC",
            [':negocio_id' => $this->negocioId, ':grupo' => $grupo]
        );

        if ($soloValores) {
            return array_column($rows, 'valor');
        }

        return $rows;
    }

    /**
     * Devuelve todos los grupos con sus valores (para el endpoint GET /api/config).
     */
    public function todos(): array
    {
        $rows = $this->fetchAll(
            "SELECT grupo, valor, etiqueta, orden, es_sistema
             FROM config_enum
             WHERE negocio_id = :negocio_id AND activo = 1
             ORDER BY grupo ASC, orden ASC",
            [':negocio_id' => $this->negocioId]
        );

        // Agrupar por grupo
        $result = [];
        foreach ($rows as $row) {
            $result[$row['grupo']][] = [
                'valor'      => $row['valor'],
                'etiqueta'   => $row['etiqueta'],
                'orden'      => (int)$row['orden'],
                'es_sistema' => (bool)$row['es_sistema'],
            ];
        }

        return $result;
    }

    /**
     * Verifica que un valor pertenezca a un grupo para el negocio actual.
     * Útil para validación en Controllers/Validator.
     */
    public function esValido(string $grupo, string $valor): bool
    {
        $row = $this->fetchOne(
            "SELECT id FROM config_enum
             WHERE negocio_id = :negocio_id AND grupo = :grupo AND valor = :valor AND activo = 1",
            [':negocio_id' => $this->negocioId, ':grupo' => $grupo, ':valor' => $valor]
        );

        return $row !== false;
    }

    /**
     * Crea un nuevo valor en un grupo.
     */
    public function create(string $grupo, string $valor, string $etiqueta, int $orden = 99): int
    {
        return $this->execute(
            "INSERT INTO config_enum (negocio_id, grupo, valor, etiqueta, orden)
             VALUES (:negocio_id, :grupo, :valor, :etiqueta, :orden)",
            [
                ':negocio_id' => $this->negocioId,
                ':grupo'      => $grupo,
                ':valor'      => $valor,
                ':etiqueta'   => $etiqueta,
                ':orden'      => $orden,
            ]
        );
    }

    /**
     * Actualiza etiqueta/orden de un valor. No se puede modificar grupo ni valor.
     */
    public function update(int $id, string $etiqueta, int $orden): bool
    {
        return $this->execute(
            "UPDATE config_enum
             SET etiqueta = :etiqueta, orden = :orden
             WHERE id = :id AND negocio_id = :negocio_id",
            [
                ':id'         => $id,
                ':negocio_id' => $this->negocioId,
                ':etiqueta'   => $etiqueta,
                ':orden'      => $orden,
            ]
        ) > 0;
    }

    /**
     * Desactiva (soft delete) un valor que no sea de sistema.
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "UPDATE config_enum
             SET activo = 0
             WHERE id = :id AND negocio_id = :negocio_id AND es_sistema = 0",
            [':id' => $id, ':negocio_id' => $this->negocioId]
        ) > 0;
    }

    /**
     * Inicializa los valores por defecto para un negocio nuevo.
     * Se llama desde el registro de un nuevo negocio.
     */
    public function seedDefaults(): void
    {
        $defaults = [
            ['metodos_pago', 'efectivo',         'Efectivo',            1, 1],
            ['metodos_pago', 'tarjeta_debito',   'Tarjeta Débito',      2, 1],
            ['metodos_pago', 'tarjeta_credito',  'Tarjeta Crédito',     3, 1],
            ['metodos_pago', 'transferencia',    'Transferencia',       4, 1],
            ['metodos_pago', 'mercado_pago',     'Mercado Pago',        5, 0],
            ['metodos_pago', 'cheque',           'Cheque',              6, 0],

            ['unidades_medida', 'unidad',   'Unidad',     1, 1],
            ['unidades_medida', 'kg',       'Kilogramo',  2, 1],
            ['unidades_medida', 'g',        'Gramo',      3, 1],
            ['unidades_medida', 'lt',       'Litro',      4, 1],
            ['unidades_medida', 'ml',       'Mililitro',  5, 1],
            ['unidades_medida', 'caja',     'Caja',       6, 0],
            ['unidades_medida', 'paquete',  'Paquete',    7, 0],
            ['unidades_medida', 'metro',    'Metro',      8, 0],
            ['unidades_medida', 'cm',       'Centímetro', 9, 0],

            ['categorias_gasto', 'proveedor',     'Proveedor / Compra', 1, 1],
            ['categorias_gasto', 'servicios',     'Servicios',          2, 1],
            ['categorias_gasto', 'alquiler',      'Alquiler',           3, 1],
            ['categorias_gasto', 'sueldos',       'Sueldos',            4, 1],
            ['categorias_gasto', 'marketing',     'Marketing',          5, 0],
            ['categorias_gasto', 'mantenimiento', 'Mantenimiento',      6, 0],
            ['categorias_gasto', 'impuestos',     'Impuestos',          7, 0],
            ['categorias_gasto', 'otros',         'Otros',              8, 1],
        ];

        $sql = "INSERT IGNORE INTO config_enum (negocio_id, grupo, valor, etiqueta, orden, es_sistema)
                VALUES (:negocio_id, :grupo, :valor, :etiqueta, :orden, :es_sistema)";

        foreach ($defaults as [$grupo, $valor, $etiqueta, $orden, $esSistema]) {
            $this->execute($sql, [
                ':negocio_id' => $this->negocioId,
                ':grupo'      => $grupo,
                ':valor'      => $valor,
                ':etiqueta'   => $etiqueta,
                ':orden'      => $orden,
                ':es_sistema' => $esSistema,
            ]);
        }
    }
}
