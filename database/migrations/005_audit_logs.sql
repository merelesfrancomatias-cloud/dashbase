-- ============================================================
-- MIGRACIÓN 005 — Tabla de auditoría
-- Fecha: 2026-02-27
-- Descripción: Registra todas las acciones importantes
--              (CREATE, UPDATE, DELETE) por tenant/usuario.
-- ============================================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    negocio_id  INT         NOT NULL,
    usuario_id  INT,
    accion      VARCHAR(20) NOT NULL,   -- 'create' | 'update' | 'delete' | 'login' | 'logout'
    tabla       VARCHAR(60) NOT NULL,   -- 'productos', 'ventas', etc.
    registro_id INT,                    -- ID del registro afectado (NULL para login/logout)
    datos_antes JSON,                   -- snapshot anterior (para UPDATE/DELETE)
    datos_nuevos JSON,                  -- snapshot nuevo    (para CREATE/UPDATE)
    ip          VARCHAR(45),
    user_agent  VARCHAR(255),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio   (negocio_id),
    INDEX idx_usuario   (usuario_id),
    INDEX idx_tabla     (tabla),
    INDEX idx_created   (created_at),
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
