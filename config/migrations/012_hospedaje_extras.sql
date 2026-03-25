-- ============================================================
-- Migración 012: Cargos extra en reservas de hospedaje
-- Minibar, room service, parking, lavandería, etc.
-- ============================================================

CREATE TABLE IF NOT EXISTS `hospedaje_cargos_extra` (
  `id`           int(11)       NOT NULL AUTO_INCREMENT,
  `reserva_id`   int(11)       NOT NULL,
  `negocio_id`   int(11)       NOT NULL,
  `descripcion`  varchar(200)  NOT NULL,
  `cantidad`     decimal(8,2)  DEFAULT 1,
  `precio_unit`  decimal(10,2) NOT NULL,
  `total`        decimal(10,2) NOT NULL,
  `created_at`   timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reserva_id` (`reserva_id`),
  KEY `negocio_id` (`negocio_id`),
  FOREIGN KEY (`reserva_id`) REFERENCES `hospedaje_reservas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO migrations (filename)
VALUES ('012_hospedaje_extras.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
