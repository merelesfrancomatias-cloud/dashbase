-- ============================================================
-- MIGRACIÓN 003 — Sistema de planes y suscripciones
-- Fecha: 2026-02-26
-- Descripción: Crea la tabla `planes` y agrega las columnas
--              de suscripción a `negocios`.
--              Esto convierte el sistema en un SaaS real.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Tabla: planes (catálogo de planes — administrado por super-admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS planes (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(50)     NOT NULL UNIQUE,   -- 'free', 'basic', 'pro', 'enterprise'
    nombre_display      VARCHAR(100)    NOT NULL,          -- 'Plan Gratuito', 'Plan Básico', etc.
    descripcion         TEXT,
    precio_mensual      DECIMAL(10,2)   DEFAULT 0,
    precio_anual        DECIMAL(10,2)   DEFAULT 0,
    -- Límites funcionales (NULL = ilimitado)
    max_usuarios        SMALLINT        DEFAULT 1,
    max_productos       SMALLINT        DEFAULT 50,
    max_ventas_mes      INT             DEFAULT 100,
    -- Feature flags (qué módulos habilita este plan)
    tiene_reportes      TINYINT(1)      DEFAULT 0,
    tiene_empleados     TINYINT(1)      DEFAULT 0,
    tiene_clientes      TINYINT(1)      DEFAULT 0,
    tiene_api_publica   TINYINT(1)      DEFAULT 0,
    tiene_tienda_online TINYINT(1)      DEFAULT 0,
    activo              TINYINT(1)      DEFAULT 1,
    created_at          TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Planes iniciales
-- ------------------------------------------------------------
INSERT IGNORE INTO planes
    (nombre, nombre_display, descripcion, precio_mensual, precio_anual,
     max_usuarios, max_productos, max_ventas_mes,
     tiene_reportes, tiene_empleados, tiene_clientes, tiene_api_publica, tiene_tienda_online)
VALUES
    ('free',
     'Plan Gratuito',
     'Para probar el sistema. Límites básicos, sin soporte.',
     0, 0,
     1, 50, 100,
     0, 0, 0, 0, 0),

    ('basic',
     'Plan Básico',
     'Para pequeños negocios. Incluye reportes y empleados.',
     9.99, 99.00,
     3, 500, 1000,
     1, 1, 0, 0, 0),

    ('pro',
     'Plan Profesional',
     'Para negocios en crecimiento. Todo incluido.',
     24.99, 249.00,
     10, 5000, 10000,
     1, 1, 1, 0, 1),

    ('enterprise',
     'Plan Enterprise',
     'Sin límites. Soporte prioritario y API pública.',
     NULL, NULL,
     NULL, NULL, NULL,
     1, 1, 1, 1, 1);

-- ------------------------------------------------------------
-- Agregar columnas de suscripción a la tabla negocios
-- (Para BD nueva: ya incluidas en la definición)
-- (Para BD existente: ejecutar solo el bloque ALTER)
-- ------------------------------------------------------------

-- [A] Si negocios ya existe → agregar columnas nuevas
ALTER TABLE negocios
    ADD COLUMN IF NOT EXISTS plan_id             INT             DEFAULT 1         AFTER activo,
    ADD COLUMN IF NOT EXISTS fecha_vencimiento   DATE            DEFAULT NULL      AFTER plan_id,
    ADD COLUMN IF NOT EXISTS estado_suscripcion  ENUM('activa', 'vencida', 'cancelada', 'trial')
                                                                 DEFAULT 'trial'   AFTER fecha_vencimiento,
    ADD COLUMN IF NOT EXISTS trial_hasta         DATE            DEFAULT NULL      AFTER estado_suscripcion;

-- Relacionar plan_id con la tabla planes (solo si no existe ya)
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'negocios'
      AND CONSTRAINT_NAME = 'fk_negocios_plan'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE negocios ADD CONSTRAINT fk_negocios_plan FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Índice para consultas de suscripción (solo si no existe)
SET @idx1 = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'negocios' AND INDEX_NAME = 'idx_plan'
);
SET @sql2 = IF(@idx1 = 0, 'ALTER TABLE negocios ADD INDEX idx_plan (plan_id)', 'SELECT 1');
PREPARE stmt FROM @sql2; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx2 = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'negocios' AND INDEX_NAME = 'idx_estado_suscripcion'
);
SET @sql3 = IF(@idx2 = 0, 'ALTER TABLE negocios ADD INDEX idx_estado_suscripcion (estado_suscripcion)', 'SELECT 1');
PREPARE stmt FROM @sql3; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Asignar plan 'free' a todos los negocios existentes que no tengan plan
UPDATE negocios
SET plan_id = (SELECT id FROM planes WHERE nombre = 'free' LIMIT 1),
    estado_suscripcion = 'trial',
    trial_hasta = DATE_ADD(CURDATE(), INTERVAL 30 DAY)
WHERE plan_id IS NULL;

SET FOREIGN_KEY_CHECKS = 1;
