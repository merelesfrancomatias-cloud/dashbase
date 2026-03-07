-- ============================================================
-- Migración 004: Módulos Restaurant
-- Mesas, Reservas, Comandas, Cocina
-- ============================================================

-- ── 1. SECTORES (zonas del salón) ────────────────────────────
CREATE TABLE IF NOT EXISTS restaurant_sectores (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    negocio_id  INT(11)      NOT NULL,
    nombre      VARCHAR(100) NOT NULL,          -- "Salón Principal", "Terraza", "VIP", "Barra"
    descripcion VARCHAR(255) DEFAULT NULL,
    color       VARCHAR(7)   DEFAULT '#0FD186',
    activo      TINYINT(1)   DEFAULT 1,
    orden       INT(11)      DEFAULT 0,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. MESAS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS restaurant_mesas (
    id           INT(11)      NOT NULL AUTO_INCREMENT,
    negocio_id   INT(11)      NOT NULL,
    sector_id    INT(11)      DEFAULT NULL,
    numero       VARCHAR(20)  NOT NULL,         -- "1", "A1", "VIP-3"
    nombre       VARCHAR(100) DEFAULT NULL,     -- alias opcional "Ventana Sur"
    capacidad    INT(11)      DEFAULT 4,
    estado       ENUM('libre','ocupada','reservada','inactiva') DEFAULT 'libre',
    pos_x        INT(11)      DEFAULT 0,        -- posición en el mapa (futuro)
    pos_y        INT(11)      DEFAULT 0,
    activo       TINYINT(1)   DEFAULT 1,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_negocio  (negocio_id),
    KEY idx_sector   (sector_id),
    KEY idx_estado   (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. RESERVAS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS restaurant_reservas (
    id               INT(11)      NOT NULL AUTO_INCREMENT,
    negocio_id       INT(11)      NOT NULL,
    mesa_id          INT(11)      DEFAULT NULL,
    cliente_nombre   VARCHAR(255) NOT NULL,
    cliente_telefono VARCHAR(50)  DEFAULT NULL,
    cliente_email    VARCHAR(255) DEFAULT NULL,
    fecha_reserva    DATE         NOT NULL,
    hora_inicio      TIME         NOT NULL,
    hora_fin         TIME         DEFAULT NULL,
    personas         INT(11)      DEFAULT 2,
    estado           ENUM('pendiente','confirmada','sentada','cancelada','no_show') DEFAULT 'pendiente',
    observaciones    TEXT         DEFAULT NULL,
    origen           ENUM('telefono','presencial','web','app') DEFAULT 'telefono',
    usuario_id       INT(11)      DEFAULT NULL,  -- quién tomó la reserva
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_negocio      (negocio_id),
    KEY idx_mesa         (mesa_id),
    KEY idx_fecha        (fecha_reserva),
    KEY idx_estado       (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. COMANDAS (pedido por mesa) ────────────────────────────
CREATE TABLE IF NOT EXISTS restaurant_comandas (
    id            INT(11)      NOT NULL AUTO_INCREMENT,
    negocio_id    INT(11)      NOT NULL,
    mesa_id       INT(11)      NOT NULL,
    reserva_id    INT(11)      DEFAULT NULL,
    numero        INT(11)      NOT NULL,         -- número correlativo por negocio
    mozo_id       INT(11)      DEFAULT NULL,     -- usuario que atiende
    estado        ENUM('abierta','en_cocina','lista','cerrada','cancelada') DEFAULT 'abierta',
    personas      INT(11)      DEFAULT 1,
    observaciones TEXT         DEFAULT NULL,
    venta_id      INT(11)      DEFAULT NULL,     -- se asigna al cerrar/cobrar
    subtotal      DECIMAL(10,2) DEFAULT 0.00,
    descuento     DECIMAL(10,2) DEFAULT 0.00,
    total         DECIMAL(10,2) DEFAULT 0.00,
    abierta_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    cerrada_at    TIMESTAMP    NULL DEFAULT NULL,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_negocio  (negocio_id),
    KEY idx_mesa     (mesa_id),
    KEY idx_estado   (estado),
    KEY idx_numero   (negocio_id, numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. DETALLE DE COMANDA (ítems pedidos) ────────────────────
CREATE TABLE IF NOT EXISTS restaurant_comanda_items (
    id            INT(11)       NOT NULL AUTO_INCREMENT,
    comanda_id    INT(11)       NOT NULL,
    negocio_id    INT(11)       NOT NULL,
    producto_id   INT(11)       NOT NULL,
    nombre_item   VARCHAR(255)  NOT NULL,        -- snapshot del nombre
    precio_unit   DECIMAL(10,2) NOT NULL,
    cantidad      INT(11)       NOT NULL DEFAULT 1,
    subtotal      DECIMAL(10,2) NOT NULL,
    estado_cocina ENUM('pendiente','en_preparacion','listo','entregado','cancelado') DEFAULT 'pendiente',
    observaciones VARCHAR(255)  DEFAULT NULL,    -- "sin cebolla", "término jugoso"
    sector_cocina VARCHAR(50)   DEFAULT 'principal', -- "parrilla","pizzeria","fria","barra"
    enviado_at    TIMESTAMP     NULL DEFAULT NULL,
    listo_at      TIMESTAMP     NULL DEFAULT NULL,
    entregado_at  TIMESTAMP     NULL DEFAULT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comanda  (comanda_id),
    KEY idx_negocio  (negocio_id),
    KEY idx_estado   (estado_cocina),
    KEY idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. SECTORES DE COCINA ────────────────────────────────────
CREATE TABLE IF NOT EXISTS restaurant_cocina_sectores (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    negocio_id  INT(11)      NOT NULL,
    nombre      VARCHAR(100) NOT NULL,           -- "Parrilla", "Pizzería", "Barra", "Fría"
    slug        VARCHAR(50)  NOT NULL,
    color       VARCHAR(7)   DEFAULT '#ef4444',
    activo      TINYINT(1)   DEFAULT 1,
    orden       INT(11)      DEFAULT 0,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_negocio (negocio_id),
    UNIQUE KEY uk_negocio_slug (negocio_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 7. Sectores y mesas por defecto para negocio id=3 ────────
-- Sectores de salón
INSERT INTO restaurant_sectores (negocio_id, nombre, color, orden) VALUES
(3, 'Salón Principal', '#0FD186', 1),
(3, 'Terraza',         '#0ea5e9', 2),
(3, 'Barra',           '#f59e0b', 3);

-- Mesas salón principal (sector 1)
INSERT INTO restaurant_mesas (negocio_id, sector_id, numero, capacidad) VALUES
(3, 1, '1',  4), (3, 1, '2',  4), (3, 1, '3',  4), (3, 1, '4',  4),
(3, 1, '5',  6), (3, 1, '6',  6), (3, 1, '7',  2), (3, 1, '8',  2),
(3, 1, '9',  4), (3, 1, '10', 4);

-- Mesas terraza (sector 2)
INSERT INTO restaurant_mesas (negocio_id, sector_id, numero, capacidad) VALUES
(3, 2, 'T1', 4), (3, 2, 'T2', 4), (3, 2, 'T3', 6), (3, 2, 'T4', 2);

-- Mesas barra (sector 3)
INSERT INTO restaurant_mesas (negocio_id, sector_id, numero, capacidad) VALUES
(3, 3, 'B1', 2), (3, 3, 'B2', 2), (3, 3, 'B3', 2);

-- Sectores de cocina para negocio id=3
INSERT INTO restaurant_cocina_sectores (negocio_id, nombre, slug, color, orden) VALUES
(3, 'Cocina Principal', 'principal', '#ef4444', 1),
(3, 'Parrilla',         'parrilla',  '#f97316', 2),
(3, 'Barra / Bebidas',  'barra',     '#3b82f6', 3),
(3, 'Postres / Fría',   'fria',      '#ec4899', 4);

-- Contador de comandas por negocio (en config_enum)
INSERT INTO config_enum (negocio_id, grupo, valor, etiqueta, orden, es_sistema)
VALUES (3, 'restaurant', 'ultimo_numero_comanda', '0', 1, 1)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- ── 8. Registrar migración ───────────────────────────────────
INSERT INTO migrations (filename)
VALUES ('004_restaurant_mesas_reservas.sql')
ON DUPLICATE KEY UPDATE applied_at = NOW();
