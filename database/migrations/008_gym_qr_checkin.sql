-- ============================================================
-- 008_gym_qr_checkin.sql
-- Token QR para check-in de socios del gimnasio
-- ============================================================

ALTER TABLE gym_socios
    ADD COLUMN qr_token VARCHAR(64) NULL UNIQUE AFTER estado;

-- Generar tokens para socios existentes
UPDATE gym_socios
SET qr_token = LOWER(HEX(RANDOM_BYTES(32)))
WHERE qr_token IS NULL;
