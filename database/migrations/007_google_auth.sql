-- ============================================================
-- 007_google_auth.sql
-- Extiende usuarios para autenticación con Google (OAuth/GSI)
-- ============================================================

ALTER TABLE usuarios
    ADD COLUMN auth_provider ENUM('local', 'google') NOT NULL DEFAULT 'local' AFTER password,
    ADD COLUMN google_sub VARCHAR(64) NULL AFTER auth_provider,
    ADD COLUMN avatar_url VARCHAR(255) NULL AFTER foto,
    MODIFY COLUMN password VARCHAR(255) NULL;

CREATE UNIQUE INDEX uk_usuarios_google_sub ON usuarios (google_sub);
