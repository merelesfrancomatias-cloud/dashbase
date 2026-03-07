-- ============================================================
-- MIGRACIÓN 004 — Fusión de negocios + perfil_negocio
-- Fecha: 2026-02-27
-- Descripción: Mueve todas las columnas de perfil_negocio
--              hacia negocios, convirtiendo negocios en la
--              única fuente de verdad del tenant.
--              Elimina la tabla perfil_negocio.
--
-- IMPORTANTE: Ejecutar DESPUÉS de 001, 002 y 003.
-- Haz un backup ANTES de ejecutar en producción:
--   mysqldump -u user -p db_name > backup_pre_004.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- PASO 1: Agregar columnas extendidas a negocios
--         (las que estaban solo en perfil_negocio)
-- ------------------------------------------------------------
ALTER TABLE negocios
    ADD COLUMN IF NOT EXISTS razon_social         VARCHAR(255)   AFTER nombre,
    ADD COLUMN IF NOT EXISTS cuit                 VARCHAR(20)    AFTER razon_social,
    ADD COLUMN IF NOT EXISTS condicion_iva        VARCHAR(50)    AFTER cuit,
    ADD COLUMN IF NOT EXISTS rubro                VARCHAR(100)   AFTER condicion_iva,
    ADD COLUMN IF NOT EXISTS sitio_web            VARCHAR(255)   AFTER email,
    ADD COLUMN IF NOT EXISTS instagram            VARCHAR(100)   AFTER sitio_web,
    ADD COLUMN IF NOT EXISTS facebook             VARCHAR(255)   AFTER instagram,
    ADD COLUMN IF NOT EXISTS mensaje_ticket       TEXT           AFTER logo,
    ADD COLUMN IF NOT EXISTS mostrar_logo_ticket        TINYINT(1) DEFAULT 1  AFTER mensaje_ticket,
    ADD COLUMN IF NOT EXISTS mostrar_direccion_ticket   TINYINT(1) DEFAULT 1  AFTER mostrar_logo_ticket,
    ADD COLUMN IF NOT EXISTS mostrar_cuit_ticket        TINYINT(1) DEFAULT 1  AFTER mostrar_direccion_ticket,
    ADD COLUMN IF NOT EXISTS horarios             JSON           AFTER mostrar_cuit_ticket,
    -- plan / suscripción (ya agregadas en 003, se incluyen aquí solo como referencia)
    -- plan_id, estado_suscripcion, fecha_vencimiento, trial_hasta → ya existen
    ADD COLUMN IF NOT EXISTS fecha_actualizacion  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER fecha_registro;

-- Renombrar columna 'nombre' para que quede como 'nombre_negocio' (alias semántico)
-- MySQL no soporta RENAME COLUMN antes de 8.0 — usamos lo existente con un alias en queries.
-- Si tu versión es MySQL 8.0+, puedes descomentar:
-- ALTER TABLE negocios RENAME COLUMN nombre TO nombre_negocio;

-- ------------------------------------------------------------
-- PASO 2: Copiar datos de perfil_negocio → negocios
--         Este bloque solo aplica si perfil_negocio existe.
--         El runner de migrate.php lo ejecuta condicionalmente.
-- [MERGE_IF_EXISTS:perfil_negocio]
-- ------------------------------------------------------------
UPDATE negocios n
INNER JOIN perfil_negocio p ON p.id = n.id
SET
    n.nombre                    = COALESCE(p.nombre_negocio, n.nombre),
    n.razon_social              = COALESCE(p.razon_social,   n.razon_social),
    n.cuit                      = COALESCE(p.cuit,           n.cuit),
    n.condicion_iva             = p.condicion_iva,
    n.rubro                     = p.rubro,
    n.telefono                  = COALESCE(p.telefono,       n.telefono),
    n.whatsapp                  = COALESCE(p.whatsapp,       n.whatsapp),
    n.email                     = COALESCE(p.email,          n.email),
    n.sitio_web                 = p.sitio_web,
    n.instagram                 = p.instagram,
    n.facebook                  = p.facebook,
    n.direccion                 = COALESCE(p.direccion,      n.direccion),
    n.ciudad                    = COALESCE(p.ciudad,         n.ciudad),
    n.codigo_postal             = COALESCE(p.codigo_postal,  n.codigo_postal),
    n.logo                      = COALESCE(p.logo,           n.logo),
    n.mensaje_ticket            = p.mensaje_ticket,
    n.mostrar_logo_ticket       = p.mostrar_logo_ticket,
    n.mostrar_direccion_ticket  = p.mostrar_direccion_ticket,
    n.mostrar_cuit_ticket       = p.mostrar_cuit_ticket,
    n.horarios                  = p.horarios;
-- [/MERGE_IF_EXISTS]

-- ------------------------------------------------------------
-- PASO 3: Eliminar tabla perfil_negocio (ya no se necesita)
--         Solo ejecutar DESPUÉS de validar que los datos
--         se copiaron correctamente (paso 2).
-- ------------------------------------------------------------
-- PRECAUCIÓN: Descomenta la línea de abajo solo cuando estés seguro.
-- DROP TABLE IF EXISTS perfil_negocio;

SET FOREIGN_KEY_CHECKS = 1;
