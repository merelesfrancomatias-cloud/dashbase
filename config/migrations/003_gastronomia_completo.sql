-- ============================================================
-- Migración 003: Rubro Gastronomía completo para restaurant
-- Expande categorías de productos/carta + config_enum
-- ============================================================

-- ── 1. Actualizar descripción del rubro ─────────────────────
UPDATE rubros SET
    nombre      = 'Gastronomía / Restaurant',
    descripcion = 'Restaurant, bar, parrilla, cafetería, pizzería o food truck',
    icono       = 'fa-utensils',
    color       = '#ef4444',
    orden       = 4
WHERE slug = 'gastronomia';

-- ── 2. Reemplazar categorías por defecto ────────────────────
DELETE FROM rubro_categorias_default
WHERE rubro_id = (SELECT id FROM rubros WHERE slug='gastronomia');

-- Carta completa de restaurant
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Entradas',            '#10b981', 1  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Ensaladas',           '#22c55e', 2  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Sopas y Caldos',      '#f59e0b', 3  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Pastas',              '#d97706', 4  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Carnes',              '#ef4444', 5  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Aves',                '#f97316', 6  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Pescados y Mariscos', '#0ea5e9', 7  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Pizzas',              '#dc2626', 8  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Sandwiches',          '#84cc16', 9  FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Minutas',             '#a3e635', 10 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Guarniciones',        '#65a30d', 11 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Postres',             '#ec4899', 12 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Bebidas Sin Alcohol',  '#3b82f6', 13 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Bebidas Con Alcohol',  '#7c3aed', 14 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Cafetería',           '#92400e', 15 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Menú del Día',        '#0891b2', 16 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Combos y Promos',     '#8b5cf6', 17 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Para Llevar',         '#64748b', 18 FROM rubros WHERE slug='gastronomia';

-- ── 3. Actualizar categorías del negocio gastronomía ya registrado (id=3) ──
-- Solo agregar las que no existen, sin tocar las que el usuario haya creado
INSERT INTO categorias (negocio_id, nombre, color)
SELECT 3, d.nombre, d.color
FROM rubro_categorias_default d
JOIN rubros r ON r.id = d.rubro_id
LEFT JOIN categorias c ON c.negocio_id = 3 AND c.nombre COLLATE utf8mb4_general_ci = d.nombre COLLATE utf8mb4_general_ci
WHERE r.slug = 'gastronomia'
AND c.id IS NULL;

-- ── 4. Actualizar config_enum del negocio gastronomía ───────
-- Unidades de medida para gastronomía (si no existen ya)
INSERT INTO config_enum (negocio_id, grupo, valor, etiqueta, orden, es_sistema)
SELECT 3, 'unidad_medida', v.valor, v.etiqueta, v.orden, 1
FROM (
    SELECT 'porcion'   AS valor, 'Porción'    AS etiqueta, 10 AS orden UNION ALL
    SELECT 'plato',              'Plato',                   11         UNION ALL
    SELECT 'media',              'Media Porción',           12         UNION ALL
    SELECT 'docena',             'Docena',                  13         UNION ALL
    SELECT 'litro_jarra',        'Jarra (1L)',              14         UNION ALL
    SELECT 'copa',               'Copa',                    15         UNION ALL
    SELECT 'taza',               'Taza',                    16
) v
WHERE NOT EXISTS (
    SELECT 1 FROM config_enum
    WHERE negocio_id = 3 AND grupo = 'unidad_medida' AND valor = v.valor
);

-- ── 5. Registrar migración ───────────────────────────────────
INSERT INTO migrations (filename)
VALUES ('003_gastronomia_completo.sql')
ON DUPLICATE KEY UPDATE applied_at = NOW();
