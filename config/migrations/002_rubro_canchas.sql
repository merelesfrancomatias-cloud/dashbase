-- ============================================================
-- Migración 002: Rubro "Alquiler de Canchas Deportivas"
-- ============================================================

-- 1. Insertar el rubro
INSERT INTO rubros (slug, nombre, descripcion, icono, color, orden)
VALUES (
    'canchas',
    'Alquiler de Canchas',
    'Alquiler de canchas de fútbol, pádel, vóley, tenis, básquet y otros deportes',
    'fa-futbol',
    '#16a34a',
    16
)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), descripcion=VALUES(descripcion), icono=VALUES(icono), color=VALUES(color);

-- 2. Categorías por defecto para canchas
-- (limpiar las de este rubro si ya existían)
DELETE FROM rubro_categorias_default WHERE rubro_id = (SELECT id FROM rubros WHERE slug='canchas');

INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Fútbol 5',      '#16a34a', 1 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Fútbol 7',      '#15803d', 2 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Fútbol 11',     '#14532d', 3 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Pádel',         '#0ea5e9', 4 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Tenis',         '#f59e0b', 5 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Vóley',         '#8b5cf6', 6 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Básquet',       '#ef4444', 7 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden)
SELECT id, 'Otros Deportes','#64748b', 8 FROM rubros WHERE slug='canchas';

-- 3. Registrar migración
INSERT INTO migrations (filename)
VALUES ('002_rubro_canchas.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
