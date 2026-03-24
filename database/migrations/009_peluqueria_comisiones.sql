-- Migration 009: Comisiones por servicio en peluquería
-- 2026-03-24

ALTER TABLE servicios
    ADD COLUMN comision_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0
    AFTER precio;
