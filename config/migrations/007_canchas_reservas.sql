-- ============================================================
-- Migración 007: Tablas para Módulo de Canchas
-- ============================================================

-- Tabla de canchas
CREATE TABLE IF NOT EXISTS `canchas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `deporte` varchar(100),
  `descripcion` text,
  `precio_hora` decimal(10, 2) DEFAULT 0.00,
  `capacidad` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `negocio_id` (`negocio_id`),
  FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de reservas de canchas
CREATE TABLE IF NOT EXISTS `reservas_canchas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cancha_id` int(11) NOT NULL,
  `cliente_nombre` varchar(150),
  `cliente_telefono` varchar(40),
  `cliente_email` varchar(150),
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `duracion_horas` decimal(3, 1) DEFAULT 1.0,
  `monto` decimal(10, 2) DEFAULT 0.00,
  `estado` varchar(50) DEFAULT 'pendiente' COMMENT 'pendiente, confirmada, cancelada, completada',
  `metodo_pago` varchar(50),
  `notas` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cancha_id` (`cancha_id`),
  KEY `fecha` (`fecha`),
  KEY `estado` (`estado`),
  FOREIGN KEY (`cancha_id`) REFERENCES `canchas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fix clientes_canchas: agregar PRIMARY KEY si no tiene
ALTER TABLE `clientes_canchas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Registrar migración
INSERT INTO migrations (filename)
VALUES ('007_canchas_reservas.sql')
ON DUPLICATE KEY UPDATE applied_at=NOW();
