-- ============================================================
-- SUPER ADMIN PANEL — Migración de base de datos
-- Ejecutar en: dashbase_local
-- ============================================================

-- 1. Planes de suscripción
CREATE TABLE IF NOT EXISTS `planes` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `nombre`          VARCHAR(80)     NOT NULL,
    `descripcion`     TEXT,
    `precio_mensual`  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `precio_anual`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `dias_gratis`     INT             NOT NULL DEFAULT 0,
    `activo`          TINYINT(1)      NOT NULL DEFAULT 1,
    `color`           VARCHAR(20)     DEFAULT '#0FD186',
    `icono`           VARCHAR(50)     DEFAULT 'fa-star',
    `orden`           INT             DEFAULT 0,
    `created_at`      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plan inicial: Gratis 15 días
INSERT INTO `planes` (`nombre`, `descripcion`, `precio_mensual`, `precio_anual`, `dias_gratis`, `activo`, `color`, `icono`, `orden`)
VALUES 
    ('Gratis',   'Prueba gratuita de 15 días con acceso completo', 0.00, 0.00, 15, 1, '#64748b', 'fa-gift', 1),
    ('Básico',   'Plan básico para negocios pequeños', 0.00, 0.00, 0, 0, '#0ea5e9', 'fa-rocket', 2),
    ('Pro',      'Plan completo para negocios en crecimiento', 0.00, 0.00, 0, 0, '#0FD186', 'fa-crown', 3),
    ('Premium',  'Plan ilimitado para negocios grandes', 0.00, 0.00, 0, 0, '#8b5cf6', 'fa-gem', 4);

-- 2. Tabla de Super Admins (solo el dueño del sistema)
CREATE TABLE IF NOT EXISTS `superadmin_users` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `nombre`       VARCHAR(120)    NOT NULL,
    `email`        VARCHAR(180)    NOT NULL UNIQUE,
    `password`     VARCHAR(255)    NOT NULL,
    `activo`       TINYINT(1)      NOT NULL DEFAULT 1,
    `ultimo_login` DATETIME        DEFAULT NULL,
    `created_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Superadmin por defecto: admin@dashbase.com / Admin1234
-- (cambiar password desde el panel)
INSERT INTO `superadmin_users` (`nombre`, `email`, `password`)
VALUES ('Super Admin', 'admin@dashbase.com', '$2y$12$Kk7vzrOEjPO0WLfKT8OMaOQY1c.GF.MFuAVQ4hVF3Y6FkJJjJg2DS');
-- password: Admin1234

-- 3. Agregar campos de suscripción a la tabla negocios
ALTER TABLE `negocios`
    ADD COLUMN IF NOT EXISTS `activo`             TINYINT(1)      NOT NULL DEFAULT 1     AFTER `rubro_id`,
    ADD COLUMN IF NOT EXISTS `plan_id`            INT             DEFAULT NULL            AFTER `activo`,
    ADD COLUMN IF NOT EXISTS `fecha_alta`         DATE            DEFAULT NULL            AFTER `plan_id`,
    ADD COLUMN IF NOT EXISTS `fecha_vencimiento`  DATE            DEFAULT NULL            AFTER `fecha_alta`,
    ADD COLUMN IF NOT EXISTS `bloqueado`          TINYINT(1)      NOT NULL DEFAULT 0      AFTER `fecha_vencimiento`,
    ADD COLUMN IF NOT EXISTS `bloqueado_motivo`   VARCHAR(255)    DEFAULT NULL            AFTER `bloqueado`,
    ADD COLUMN IF NOT EXISTS `notas_admin`        TEXT            DEFAULT NULL            AFTER `bloqueado_motivo`;

-- Actualizar negocios existentes con plan Gratis
UPDATE `negocios` SET 
    `activo` = 1,
    `plan_id` = 1,
    `fecha_alta` = CURDATE(),
    `fecha_vencimiento` = DATE_ADD(CURDATE(), INTERVAL 15 DAY)
WHERE `plan_id` IS NULL;

-- 4. Pagos registrados manualmente
CREATE TABLE IF NOT EXISTS `pagos` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `negocio_id`        INT             NOT NULL,
    `plan_id`           INT             NOT NULL,
    `monto`             DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `moneda`            VARCHAR(10)     DEFAULT 'ARS',
    `metodo_pago`       ENUM('efectivo','transferencia','mercadopago','otro') DEFAULT 'transferencia',
    `referencia`        VARCHAR(255)    DEFAULT NULL COMMENT 'Número de comprobante/transferencia',
    `fecha_pago`        DATE            NOT NULL,
    `fecha_desde`       DATE            NOT NULL,
    `fecha_hasta`       DATE            NOT NULL,
    `notas`             TEXT,
    `registrado_por`    INT             DEFAULT NULL COMMENT 'superadmin_users.id',
    `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`negocio_id`) REFERENCES `negocios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Logs de actividad
CREATE TABLE IF NOT EXISTS `logs_actividad` (
    `id`          BIGINT AUTO_INCREMENT PRIMARY KEY,
    `negocio_id`  INT             DEFAULT NULL,
    `usuario_id`  INT             DEFAULT NULL,
    `accion`      VARCHAR(120)    NOT NULL,
    `detalle`     TEXT            DEFAULT NULL,
    `ip`          VARCHAR(45)     DEFAULT NULL,
    `user_agent`  VARCHAR(255)    DEFAULT NULL,
    `created_at`  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_negocio` (`negocio_id`),
    INDEX `idx_usuario` (`usuario_id`),
    INDEX `idx_fecha`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
