DELETE FROM rubro_categorias_default WHERE rubro_id = (SELECT id FROM rubros WHERE slug='canchas');

INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Fútbol',  '#16a34a', 1 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Pádel',   '#0ea5e9', 2 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Vóley',   '#8b5cf6', 3 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Tenis',   '#f59e0b', 4 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Básquet', '#ef4444', 5 FROM rubros WHERE slug='canchas';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Otros',   '#64748b', 6 FROM rubros WHERE slug='canchas';

UPDATE rubros SET 
    nombre      = 'Alquiler de Canchas',
    descripcion = 'Alquiler de canchas de fútbol, pádel, vóley, tenis, básquet y otros deportes',
    icono       = 'fa-futbol',
    color       = '#16a34a',
    orden       = 16
WHERE slug = 'canchas';
