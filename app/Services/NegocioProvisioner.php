<?php
namespace App\Services;

/**
 * NegocioProvisioner — Configura un negocio recién creado.
 *
 * Al crear un negocio se ejecuta:
 *   1. Categorías por defecto según el rubro elegido
 *   2. Config_enum (métodos de pago + unidades de medida) base
 *   3. Permisos del usuario admin
 */
class NegocioProvisioner
{
    private \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Punto de entrada principal. Llama a todos los pasos en orden.
     */
    public function provision(int $negocioId, int $adminUserId, int $rubroId): void
    {
        $this->crearCategorias($negocioId, $rubroId);
        $this->crearConfigEnum($negocioId);
        $this->crearPermisosAdmin($adminUserId);
    }

    // ─── Paso 1: Categorías por defecto según rubro ────────────────────────

    private function crearCategorias(int $negocioId, int $rubroId): void
    {
        // Si el rubro no tiene categorías definidas, usar las de "otro"
        $stmt = $this->db->prepare("
            SELECT nombre, color, orden
            FROM rubro_categorias_default
            WHERE rubro_id = ?
            ORDER BY orden ASC
        ");
        $stmt->execute([$rubroId]);
        $categorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($categorias)) {
            // Fallback a "otro"
            $stmt = $this->db->prepare("
                SELECT rc.nombre, rc.color, rc.orden
                FROM rubro_categorias_default rc
                INNER JOIN rubros r ON r.id = rc.rubro_id
                WHERE r.slug = 'otro'
                ORDER BY rc.orden ASC
            ");
            $stmt->execute();
            $categorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        if (empty($categorias)) return;

        $insert = $this->db->prepare("
            INSERT INTO categorias (negocio_id, nombre, color, activo)
            VALUES (?, ?, ?, 1)
        ");

        foreach ($categorias as $cat) {
            $insert->execute([$negocioId, $cat['nombre'], $cat['color']]);
        }
    }

    // ─── Paso 2: Config enum base ──────────────────────────────────────────

    private function crearConfigEnum(int $negocioId): void
    {
        $defaults = [
            // Métodos de pago
            ['metodos_pago', 'efectivo',       'Efectivo',       1, 1],
            ['metodos_pago', 'tarjeta_debito',  'Tarjeta Débito', 2, 1],
            ['metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito',3, 1],
            ['metodos_pago', 'transferencia',   'Transferencia',  4, 1],
            ['metodos_pago', 'mercado_pago',    'Mercado Pago',   5, 0],
            // Unidades de medida
            ['unidades_medida', 'unidad', 'Unidad',    1, 1],
            ['unidades_medida', 'kg',     'Kilogramo', 2, 1],
            ['unidades_medida', 'g',      'Gramo',     3, 1],
            ['unidades_medida', 'lt',     'Litro',     4, 1],
            ['unidades_medida', 'ml',     'Mililitro', 5, 1],
            ['unidades_medida', 'caja',   'Caja',      6, 1],
            ['unidades_medida', 'par',    'Par',       7, 0],
            ['unidades_medida', 'metro',  'Metro',     8, 0],
        ];

        $stmt = $this->db->prepare("
            INSERT INTO config_enum (negocio_id, grupo, valor, etiqueta, orden, es_sistema)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($defaults as [$grupo, $valor, $etiqueta, $orden, $esSistema]) {
            $stmt->execute([$negocioId, $grupo, $valor, $etiqueta, $orden, $esSistema]);
        }
    }

    // ─── Paso 3: Permisos completos para el admin ──────────────────────────

    private function crearPermisosAdmin(int $adminUserId): void
    {
        // Verificar que no existan ya
        $check = $this->db->prepare("SELECT id FROM permisos WHERE usuario_id = ?");
        $check->execute([$adminUserId]);
        if ($check->fetch()) return;

        $stmt = $this->db->prepare("
            INSERT INTO permisos (
                usuario_id,
                ver_productos, crear_productos, editar_productos, eliminar_productos,
                ver_ventas, crear_ventas, cancelar_ventas,
                ver_gastos, crear_gastos,
                ver_empleados, crear_empleados,
                ver_reportes, gestionar_caja
            ) VALUES (?, 1,1,1,1, 1,1,1, 1,1, 1,1, 1,1)
        ");
        $stmt->execute([$adminUserId]);
    }
}
