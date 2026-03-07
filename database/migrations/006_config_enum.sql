-- ============================================================
-- 006_config_enum.sql
-- Tabla de valores configurables por negocio (ENUMs dinámicos)
-- Reemplaza los arrays hardcodeados en el frontend/backend
-- ============================================================

CREATE TABLE IF NOT EXISTS config_enum (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id  INT              NOT NULL,
    grupo       VARCHAR(60)      NOT NULL COMMENT 'ej: metodos_pago, unidades_medida, categorias_gasto',
    valor       VARCHAR(100)     NOT NULL,
    etiqueta    VARCHAR(150)     NOT NULL COMMENT 'Texto visible al usuario',
    orden       SMALLINT         NOT NULL DEFAULT 0,
    activo      TINYINT(1)       NOT NULL DEFAULT 1,
    es_sistema  TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 = valor por defecto, no se puede borrar',
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_negocio_grupo_valor (negocio_id, grupo, valor),
    INDEX idx_negocio_grupo (negocio_id, grupo),

    CONSTRAINT fk_ce_negocio FOREIGN KEY (negocio_id) REFERENCES negocios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
