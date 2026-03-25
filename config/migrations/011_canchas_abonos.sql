-- ============================================================
-- Migración 011: Abonos/Membresías de Canchas
-- Un abono = slot fijo semanal (ej. todos los martes 20:00-21:00)
-- Al crear/renovar genera reservas_canchas automáticamente
-- ============================================================

CREATE TABLE IF NOT EXISTS `abonos_canchas` (
  `id`              int(11)       NOT NULL AUTO_INCREMENT,
  `negocio_id`      int(11)       NOT NULL,
  `cancha_id`       int(11)       NOT NULL,
  `cliente_nombre`  varchar(150)  NOT NULL,
  `cliente_telefono` varchar(40)  DEFAULT NULL,
  `dia_semana`      tinyint(1)    NOT NULL COMMENT '0=Dom,1=Lun,...,6=Sab',
  `hora_inicio`     time          NOT NULL,
  `hora_fin`        time          NOT NULL,
  `duracion_horas`  decimal(3,1)  DEFAULT 1.0,
  `monto_mensual`   decimal(10,2) DEFAULT 0.00,
  `fecha_inicio`    date          NOT NULL,
  `fecha_fin`       date          NOT NULL,
  `estado`          varchar(30)   DEFAULT 'activo' COMMENT 'activo, pausado, vencido, cancelado',
  `notas`           text          DEFAULT NULL,
  `created_at`      timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `negocio_id` (`negocio_id`),
  KEY `cancha_id`  (`cancha_id`),
  KEY `estado`     (`estado`),
  FOREIGN KEY (`cancha_id`) REFERENCES `canchas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar abono_id a reservas_canchas para vincular reservas de abono
ALTER TABLE `reservas_canchas`
  ADD COLUMN IF NOT EXISTS `abono_id` int(11) NULL DEFAULT NULL AFTER `id`,
  ADD KEY IF NOT EXISTS `abono_id` (`abono_id`);

INSERT INTO migrations (filename)
VALUES ('011_canchas_abonos.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
