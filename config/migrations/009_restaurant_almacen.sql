-- ============================================================
-- Migración 009: Módulo Almacén Restaurant
-- ============================================================

-- Insumos / ingredientes del almacén
CREATE TABLE IF NOT EXISTS `restaurant_insumos` (
  `id`               int(11)        NOT NULL AUTO_INCREMENT,
  `negocio_id`       int(11)        NOT NULL,
  `nombre`           varchar(150)   NOT NULL,
  `categoria`        varchar(100)   DEFAULT NULL,
  `unidad`           varchar(30)    NOT NULL DEFAULT 'unidad' COMMENT 'kg,g,litro,ml,unidad,docena,etc',
  `precio_unitario`  decimal(10,2)  DEFAULT 0.00,
  `stock_actual`     decimal(10,3)  DEFAULT 0.000,
  `stock_minimo`     decimal(10,3)  DEFAULT 0.000,
  `activo`           tinyint(1)     DEFAULT 1,
  `created_at`       timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `negocio_id` (`negocio_id`),
  FOREIGN KEY (`negocio_id`) REFERENCES `negocios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de compras
CREATE TABLE IF NOT EXISTS `restaurant_compras` (
  `id`               int(11)        NOT NULL AUTO_INCREMENT,
  `negocio_id`       int(11)        NOT NULL,
  `insumo_id`        int(11)        NOT NULL,
  `cantidad`         decimal(10,3)  NOT NULL,
  `precio_unitario`  decimal(10,2)  NOT NULL,
  `total`            decimal(10,2)  NOT NULL,
  `proveedor`        varchar(150)   DEFAULT NULL,
  `fecha`            date           NOT NULL,
  `notas`            text           DEFAULT NULL,
  `usuario_id`       int(11)        DEFAULT NULL,
  `created_at`       timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `negocio_id` (`negocio_id`),
  KEY `insumo_id`  (`insumo_id`),
  KEY `fecha`      (`fecha`),
  FOREIGN KEY (`negocio_id`) REFERENCES `negocios`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`insumo_id`)  REFERENCES `restaurant_insumos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recetas: qué insumos usa cada plato
CREATE TABLE IF NOT EXISTS `restaurant_recetas` (
  `id`               int(11)        NOT NULL AUTO_INCREMENT,
  `negocio_id`       int(11)        NOT NULL,
  `producto_id`      int(11)        NOT NULL,
  `insumo_id`        int(11)        NOT NULL,
  `cantidad_porcion` decimal(10,4)  NOT NULL COMMENT 'cantidad de este insumo por porcion del plato',
  `created_at`       timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_insumo` (`producto_id`,`insumo_id`),
  KEY `negocio_id`   (`negocio_id`),
  KEY `producto_id`  (`producto_id`),
  KEY `insumo_id`    (`insumo_id`),
  FOREIGN KEY (`negocio_id`)  REFERENCES `negocios`(`id`)           ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)          ON DELETE CASCADE,
  FOREIGN KEY (`insumo_id`)   REFERENCES `restaurant_insumos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar migración
INSERT INTO migrations (filename) VALUES ('009_restaurant_almacen.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
