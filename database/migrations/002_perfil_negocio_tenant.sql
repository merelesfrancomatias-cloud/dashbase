-- ============================================================
-- MIGRACIÓN 002 — perfil_negocio con negocio_id
-- Fecha: 2026-02-26
-- Descripción: Agrega negocio_id a perfil_negocio para
--              soportar correctamente multi-tenant.
--              Reemplaza: perfil_negocio_schema.sql
--
-- IMPORTANTE: Ejecutar DESPUÉS de 001_schema_base.sql
--
-- Para bases de datos NUEVAS: solo ejecuta el CREATE TABLE.
-- Para bases de datos EXISTENTES: ejecuta el bloque ALTER.
-- ============================================================

-- ------------------------------------------------------------
-- [A] BASE DE DATOS NUEVA — crear tabla completa
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS perfil_negocio (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    negocio_id  INT NOT NULL UNIQUE,           -- 1 perfil por negocio
    nombre_negocio      VARCHAR(255) NOT NULL,
    razon_social        VARCHAR(255),
    cuit                VARCHAR(20),
    condicion_iva       VARCHAR(50),
    rubro               VARCHAR(100),
    telefono            VARCHAR(20),
    whatsapp            VARCHAR(20),
    email               VARCHAR(100),
    sitio_web           VARCHAR(255),
    instagram           VARCHAR(100),
    facebook            VARCHAR(255),
    direccion           VARCHAR(255),
    ciudad              VARCHAR(100),
    provincia           VARCHAR(100),
    codigo_postal       VARCHAR(10),
    pais                VARCHAR(100) DEFAULT 'Argentina',
    logo                VARCHAR(255),
    mensaje_ticket      TEXT,
    mostrar_logo_ticket         TINYINT(1) DEFAULT 1,
    mostrar_direccion_ticket    TINYINT(1) DEFAULT 1,
    mostrar_cuit_ticket         TINYINT(1) DEFAULT 1,
    horarios    JSON,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE,
    INDEX idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- [B] BASE DE DATOS EXISTENTE — agregar negocio_id si no existe
--     Descomentá este bloque si la tabla ya existe en producción
-- ------------------------------------------------------------
-- ALTER TABLE perfil_negocio
--     ADD COLUMN negocio_id INT AFTER id;
--
-- -- Asignar el negocio 1 al perfil existente (ajustar según corresponda)
-- UPDATE perfil_negocio SET negocio_id = 1 WHERE negocio_id IS NULL;
--
-- ALTER TABLE perfil_negocio
--     MODIFY COLUMN negocio_id INT NOT NULL,
--     ADD CONSTRAINT uq_perfil_negocio UNIQUE (negocio_id),
--     ADD CONSTRAINT fk_perfil_negocio
--         FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE;
