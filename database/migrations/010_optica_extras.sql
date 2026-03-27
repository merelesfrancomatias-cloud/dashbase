-- ============================================================
-- Migración 010: Extras para módulo Óptica
-- Tablas: optica_seguimiento, optica_stock, optica_stock_mov
-- ============================================================

-- Historial de cambios de estado de pedidos
CREATE TABLE IF NOT EXISTS `optica_seguimiento` (
  `id`              int(11)     NOT NULL AUTO_INCREMENT,
  `negocio_id`      int(11)     NOT NULL,
  `pedido_id`       int(11)     NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo`    varchar(30) NOT NULL,
  `notas`           text        DEFAULT NULL,
  `usuario_id`      int(11)     DEFAULT NULL,
  `created_at`      timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio`  (`negocio_id`),
  KEY `idx_pedido`   (`pedido_id`),
  KEY `idx_created`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Trazabilidad de cambios de estado de pedidos óptica';

-- Inventario de artículos ópticos (monturas, lentes, accesorios)
CREATE TABLE IF NOT EXISTS `optica_stock` (
  `id`            int(11)     NOT NULL AUTO_INCREMENT,
  `negocio_id`    int(11)     NOT NULL,
  `tipo`          enum('montura','lente','contacto','accesorio','otro') NOT NULL DEFAULT 'montura',
  `nombre`        varchar(150) NOT NULL,
  `marca`         varchar(100) DEFAULT NULL,
  `modelo`        varchar(100) DEFAULT NULL,
  `color`         varchar(60)  DEFAULT NULL,
  `material`      varchar(80)  DEFAULT NULL,
  `descripcion`   text         DEFAULT NULL,
  `codigo`        varchar(60)  DEFAULT NULL,
  `precio_costo`  decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_venta`  decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_actual`  int(11)      NOT NULL DEFAULT 0,
  `stock_minimo`  int(11)      NOT NULL DEFAULT 2,
  `activo`        tinyint(1)   NOT NULL DEFAULT 1,
  `created_at`    timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`    timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio` (`negocio_id`),
  KEY `idx_tipo`    (`tipo`),
  KEY `idx_activo`  (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Stock de artículos ópticos por negocio';

-- Movimientos de stock óptico
CREATE TABLE IF NOT EXISTS `optica_stock_mov` (
  `id`              int(11)    NOT NULL AUTO_INCREMENT,
  `negocio_id`      int(11)    NOT NULL,
  `item_id`         int(11)    NOT NULL,
  `tipo`            enum('entrada','salida','ajuste') NOT NULL DEFAULT 'entrada',
  `cantidad`        int(11)    NOT NULL,
  `stock_anterior`  int(11)    NOT NULL DEFAULT 0,
  `stock_nuevo`     int(11)    NOT NULL DEFAULT 0,
  `pedido_id`       int(11)    DEFAULT NULL COMMENT 'Pedido que originó el movimiento',
  `notas`           varchar(255) DEFAULT NULL,
  `usuario_id`      int(11)    DEFAULT NULL,
  `created_at`      timestamp  NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_negocio` (`negocio_id`),
  KEY `idx_item`    (`item_id`),
  KEY `idx_pedido`  (`pedido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historial de movimientos de stock óptico';

-- Registrar migración
INSERT INTO migrations (filename)
VALUES ('010_optica_extras.sql')
ON DUPLICATE KEY UPDATE applied_at = NOW();
