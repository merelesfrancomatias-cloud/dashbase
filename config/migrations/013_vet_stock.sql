-- ============================================================
-- Migración 013: Stock de medicamentos / insumos veterinarios
-- ============================================================

CREATE TABLE IF NOT EXISTS `vet_stock` (
  `id`            int(11)       NOT NULL AUTO_INCREMENT,
  `negocio_id`    int(11)       NOT NULL,
  `nombre`        varchar(150)  NOT NULL,
  `descripcion`   text          DEFAULT NULL,
  `categoria`     varchar(80)   DEFAULT NULL COMMENT 'antiparasitario, vacuna, antibiotico, analgesico, insumo, otro',
  `unidad`        varchar(30)   DEFAULT 'unidad' COMMENT 'unidad, caja, frasco, ampolla, comprimido',
  `stock_actual`  decimal(10,2) DEFAULT 0,
  `stock_minimo`  decimal(10,2) DEFAULT 0 COMMENT 'alerta si stock_actual <= stock_minimo',
  `precio_costo`  decimal(10,2) DEFAULT 0,
  `precio_venta`  decimal(10,2) DEFAULT 0,
  `activo`        tinyint(1)    DEFAULT 1,
  `created_at`    timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `negocio_id` (`negocio_id`),
  KEY `activo`     (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Movimientos de stock (entradas/salidas)
CREATE TABLE IF NOT EXISTS `vet_stock_movimientos` (
  `id`          int(11)       NOT NULL AUTO_INCREMENT,
  `negocio_id`  int(11)       NOT NULL,
  `item_id`     int(11)       NOT NULL,
  `tipo`        enum('entrada','salida','ajuste') NOT NULL DEFAULT 'salida',
  `cantidad`    decimal(10,2) NOT NULL,
  `motivo`      varchar(200)  DEFAULT NULL,
  `consulta_id` int(11)       DEFAULT NULL,
  `created_at`  timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `negocio_id` (`negocio_id`),
  FOREIGN KEY (`item_id`) REFERENCES `vet_stock` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO migrations (filename)
VALUES ('013_vet_stock.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
