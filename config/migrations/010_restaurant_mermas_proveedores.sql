-- ===================================================
-- Migración 010: Mermas y Proveedores para Restaurant
-- ===================================================

-- Tabla de proveedores
CREATE TABLE IF NOT EXISTS `restaurant_proveedores` (
  `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `negocio_id`  INT UNSIGNED     NOT NULL,
  `nombre`      VARCHAR(150)     NOT NULL,
  `contacto`    VARCHAR(120)     DEFAULT NULL,
  `telefono`    VARCHAR(40)      DEFAULT NULL,
  `email`       VARCHAR(120)     DEFAULT NULL,
  `direccion`   VARCHAR(200)     DEFAULT NULL,
  `notas`       TEXT             DEFAULT NULL,
  `activo`      TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_prov_negocio` (`negocio_id`, `activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de mermas (pérdidas de stock)
CREATE TABLE IF NOT EXISTS `restaurant_mermas` (
  `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `negocio_id`  INT UNSIGNED     NOT NULL,
  `insumo_id`   INT UNSIGNED     NOT NULL,
  `cantidad`    DECIMAL(10,3)    NOT NULL,
  `motivo`      ENUM('vencimiento','rotura','contaminacion','coccion','otro') NOT NULL DEFAULT 'otro',
  `descripcion` TEXT             DEFAULT NULL,
  `usuario_id`  INT UNSIGNED     DEFAULT NULL,
  `fecha`       DATE             NOT NULL,
  `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mermas_negocio` (`negocio_id`, `fecha`),
  KEY `idx_mermas_insumo`  (`insumo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar columna proveedor_id a restaurant_compras (si no existe)
ALTER TABLE `restaurant_compras`
  ADD COLUMN IF NOT EXISTS `proveedor_id` INT UNSIGNED DEFAULT NULL AFTER `proveedor`;
