-- ============================================================
-- Migración 001: Tabla rubros + seed de rubros comunes
-- Categorías por defecto y config_enum por rubro
-- ============================================================

-- 1. Tabla de rubros del sistema
CREATE TABLE IF NOT EXISTS rubros (
    id            INT(11)      NOT NULL AUTO_INCREMENT,
    slug          VARCHAR(50)  NOT NULL UNIQUE,
    nombre        VARCHAR(100) NOT NULL,
    descripcion   VARCHAR(255) DEFAULT NULL,
    icono         VARCHAR(50)  DEFAULT 'fa-store',   -- icono FontAwesome
    color         VARCHAR(7)   DEFAULT '#667eea',    -- color hex
    activo        TINYINT(1)   DEFAULT 1,
    orden         INT(11)      DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Seed de rubros comunes
INSERT INTO rubros (slug, nombre, descripcion, icono, color, orden) VALUES
('almacen',        'Almacén / Kiosco',        'Venta de abarrotes, bebidas, snacks y artículos de primera necesidad',  'fa-store',             '#10b981', 1),
('indumentaria',   'Indumentaria / Ropa',     'Venta de ropa, calzado y accesorios de moda',                           'fa-tshirt',            '#8b5cf6', 2),
('ferreteria',     'Ferretería',              'Venta de herramientas, materiales de construcción y electricidad',       'fa-hammer',            '#f59e0b', 3),
('gastronomia',    'Gastronomía / Bar',       'Restaurante, bar, cafetería o food truck',                              'fa-utensils',          '#ef4444', 4),
('farmacia',       'Farmacia / Perfumería',   'Medicamentos, cosméticos y artículos de higiene personal',              'fa-pills',             '#06b6d4', 5),
('tecnologia',     'Tecnología / Electrónica','Venta y reparación de celulares, computadoras y electrónica',           'fa-laptop',            '#3b82f6', 6),
('libreria',       'Librería / Papelería',    'Libros, útiles escolares y artículos de oficina',                       'fa-book',              '#f97316', 7),
('peluqueria',     'Peluquería / Estética',   'Servicios de peluquería, barbería o estética',                          'fa-cut',               '#ec4899', 8),
('veterinaria',    'Veterinaria / Mascotas',  'Atención veterinaria, venta de alimentos y accesorios para mascotas',   'fa-paw',               '#84cc16', 9),
('optica',         'Óptica',                  'Venta y graduación de anteojos y lentes de contacto',                   'fa-glasses',           '#0ea5e9', 10),
('jugueteria',     'Juguetería',              'Venta de juguetes y artículos para niños',                              'fa-gamepad',           '#a855f7', 11),
('floristeria',    'Florería',                'Venta de flores, plantas y arreglos florales',                          'fa-seedling',          '#22c55e', 12),
('panaderia',      'Panadería / Confitería',  'Panadería, confitería y productos de pastelería',                       'fa-bread-slice',       '#d97706', 13),
('electrodomesticos','Electrodomésticos',     'Venta de electrodomésticos del hogar y línea blanca',                   'fa-tv',                '#64748b', 14),
('deportes',       'Artículos Deportivos',    'Indumentaria y equipamiento para deporte y actividad física',           'fa-running',           '#16a34a', 15),
('otro',           'Otro',                    'Otro tipo de negocio o rubro no listado',                               'fa-briefcase',         '#94a3b8', 99)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), descripcion=VALUES(descripcion);

-- 3. Tabla de categorías por defecto por rubro
CREATE TABLE IF NOT EXISTS rubro_categorias_default (
    id        INT(11)      NOT NULL AUTO_INCREMENT,
    rubro_id  INT(11)      NOT NULL,
    nombre    VARCHAR(255) NOT NULL,
    color     VARCHAR(7)   DEFAULT '#667eea',
    orden     INT(11)      DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_rubro (rubro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Limpiar y re-insertar para idempotencia
DELETE FROM rubro_categorias_default;

-- Almacén
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Bebidas',          '#3b82f6', 1 FROM rubros WHERE slug='almacen';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Lácteos',          '#f59e0b', 2 FROM rubros WHERE slug='almacen';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Panificados',      '#d97706', 3 FROM rubros WHERE slug='almacen';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Limpieza',         '#10b981', 4 FROM rubros WHERE slug='almacen';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Golosinas',        '#ec4899', 5 FROM rubros WHERE slug='almacen';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Fiambrería',       '#ef4444', 6 FROM rubros WHERE slug='almacen';

-- Indumentaria
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Remeras',          '#8b5cf6', 1 FROM rubros WHERE slug='indumentaria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Pantalones',       '#6366f1', 2 FROM rubros WHERE slug='indumentaria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Calzado',          '#3b82f6', 3 FROM rubros WHERE slug='indumentaria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Accesorios',       '#ec4899', 4 FROM rubros WHERE slug='indumentaria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Ropa Deportiva',   '#10b981', 5 FROM rubros WHERE slug='indumentaria';

-- Ferretería
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Herramientas',     '#f59e0b', 1 FROM rubros WHERE slug='ferreteria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Electricidad',     '#eab308', 2 FROM rubros WHERE slug='ferreteria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Plomería',         '#06b6d4', 3 FROM rubros WHERE slug='ferreteria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Pinturas',         '#ef4444', 4 FROM rubros WHERE slug='ferreteria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Fijaciones',       '#64748b', 5 FROM rubros WHERE slug='ferreteria';

-- Gastronomía
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Entradas',         '#10b981', 1 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Platos Principales','#ef4444', 2 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Postres',          '#f97316', 3 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Bebidas',          '#3b82f6', 4 FROM rubros WHERE slug='gastronomia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Combos',           '#8b5cf6', 5 FROM rubros WHERE slug='gastronomia';

-- Tecnología
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Celulares',        '#3b82f6', 1 FROM rubros WHERE slug='tecnologia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Accesorios',       '#6366f1', 2 FROM rubros WHERE slug='tecnologia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Computadoras',     '#64748b', 3 FROM rubros WHERE slug='tecnologia';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Servicios',        '#10b981', 4 FROM rubros WHERE slug='tecnologia';

-- Peluquería (servicios)
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Corte',            '#ec4899', 1 FROM rubros WHERE slug='peluqueria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Coloración',       '#f97316', 2 FROM rubros WHERE slug='peluqueria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Tratamientos',     '#8b5cf6', 3 FROM rubros WHERE slug='peluqueria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Manicuría',        '#ef4444', 4 FROM rubros WHERE slug='peluqueria';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Productos',        '#10b981', 5 FROM rubros WHERE slug='peluqueria';

-- Otro (genérico)
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'General',          '#667eea', 1 FROM rubros WHERE slug='otro';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Servicios',        '#10b981', 2 FROM rubros WHERE slug='otro';
INSERT INTO rubro_categorias_default (rubro_id, nombre, color, orden) SELECT id, 'Productos',        '#3b82f6', 3 FROM rubros WHERE slug='otro';

-- 4. Agregar columna rubro_id en negocios (si no existe)
ALTER TABLE negocios ADD COLUMN IF NOT EXISTS rubro_id INT(11) DEFAULT NULL AFTER rubro;
ALTER TABLE negocios ADD KEY IF NOT EXISTS idx_rubro_id (rubro_id);

-- 5. Registrar esta migración
INSERT INTO migrations (filename)
VALUES ('001_rubros.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
