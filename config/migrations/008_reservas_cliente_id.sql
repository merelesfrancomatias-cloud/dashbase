-- ============================================================
-- Migración 008: Agregar cliente_id a reservas_canchas
-- ============================================================

-- Agregar columna cliente_id
ALTER TABLE `reservas_canchas` 
ADD COLUMN `cliente_id` INT(11) NULL DEFAULT NULL AFTER `cancha_id`,
ADD FOREIGN KEY (`cliente_id`) REFERENCES `clientes_canchas`(`id`) ON DELETE SET NULL;

-- Registrar migración
INSERT INTO migrations (filename)
VALUES ('008_reservas_cliente_id.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
