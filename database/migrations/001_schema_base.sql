-- ============================================================
-- MIGRACIÓN 001 — Schema base canónico
-- Fecha: 2026-02-26
-- Descripción: Tablas fundacionales del sistema multi-tenant.
--              Reemplaza: database_schema.sql, database.sql,
--              dash4.sql, dash4_crm.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Tabla: negocios (un registro = un tenant)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS negocios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(255) NOT NULL,
    razon_social    VARCHAR(255),
    cuit            VARCHAR(20),
    direccion       TEXT,
    ciudad          VARCHAR(100),
    provincia       VARCHAR(100),
    codigo_postal   VARCHAR(10),
    telefono        VARCHAR(50),
    email           VARCHAR(255),
    whatsapp        VARCHAR(50),
    logo            VARCHAR(255),
    activo          TINYINT(1) DEFAULT 1,
    fecha_registro      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id  INT NOT NULL,
    nombre      VARCHAR(255) NOT NULL,
    apellido    VARCHAR(255) NOT NULL,
    usuario     VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(255),
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('admin', 'empleado') DEFAULT 'empleado',
    telefono    VARCHAR(50),
    foto        VARCHAR(255),
    activo      TINYINT(1) DEFAULT 1,
    ultimo_acceso   TIMESTAMP NULL,
    fecha_creacion      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE,
    INDEX idx_usuario  (usuario),
    INDEX idx_negocio  (negocio_id),
    INDEX idx_activo   (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: categorias
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id  INT NOT NULL,
    nombre      VARCHAR(255) NOT NULL,
    descripcion TEXT,
    color       VARCHAR(7) DEFAULT '#007AFF',
    activo      TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE,
    INDEX idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: productos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id      INT NOT NULL,
    categoria_id    INT,
    nombre          VARCHAR(255) NOT NULL,
    descripcion     TEXT,
    codigo_barras   VARCHAR(100),
    precio_costo    DECIMAL(10,2) DEFAULT 0,
    precio_venta    DECIMAL(10,2) NOT NULL,
    stock           INT DEFAULT 0,
    stock_minimo    INT DEFAULT 0,
    unidad_medida   VARCHAR(50) DEFAULT 'unidad',
    foto            VARCHAR(255),
    activo          TINYINT(1) DEFAULT 1,
    fecha_creacion      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id)   REFERENCES negocios(id)    ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)  ON DELETE SET NULL,
    INDEX idx_negocio       (negocio_id),
    INDEX idx_codigo_barras (codigo_barras),
    INDEX idx_stock         (stock),
    INDEX idx_activo        (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: cajas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cajas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id      INT NOT NULL,
    usuario_id      INT NOT NULL,
    monto_inicial   DECIMAL(10,2) NOT NULL,
    monto_ventas    DECIMAL(10,2) DEFAULT 0,
    monto_gastos    DECIMAL(10,2) DEFAULT 0,
    monto_final     DECIMAL(10,2),
    monto_real      DECIMAL(10,2),
    diferencia      DECIMAL(10,2),
    estado          ENUM('abierta', 'cerrada') DEFAULT 'abierta',
    observaciones   TEXT,
    fecha_apertura  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre    TIMESTAMP NULL,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id)  ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  ON DELETE CASCADE,
    INDEX idx_negocio (negocio_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado  (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: ventas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ventas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id          INT NOT NULL,
    usuario_id          INT NOT NULL,
    caja_id             INT,
    cliente_nombre      VARCHAR(255),
    cliente_telefono    VARCHAR(50),
    subtotal            DECIMAL(10,2) NOT NULL,
    descuento           DECIMAL(10,2) DEFAULT 0,
    total               DECIMAL(10,2) NOT NULL,
    metodo_pago         VARCHAR(50) NOT NULL,
    estado              ENUM('completada', 'cancelada') DEFAULT 'completada',
    observaciones       TEXT,
    fecha_venta         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id)  ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  ON DELETE CASCADE,
    FOREIGN KEY (caja_id)    REFERENCES cajas(id)     ON DELETE SET NULL,
    INDEX idx_negocio (negocio_id),
    INDEX idx_fecha   (fecha_venta),
    INDEX idx_estado  (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: detalle_ventas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS detalle_ventas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    venta_id        INT NOT NULL,
    producto_id     INT NOT NULL,
    cantidad        DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id)    REFERENCES ventas(id)   ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_venta   (venta_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: gastos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gastos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id  INT NOT NULL,
    usuario_id  INT NOT NULL,
    caja_id     INT,
    categoria   VARCHAR(100) DEFAULT 'otros',
    descripcion TEXT,
    monto       DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50),
    fecha_gasto DATE NOT NULL,
    comprobante VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id)  ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  ON DELETE CASCADE,
    FOREIGN KEY (caja_id)    REFERENCES cajas(id)     ON DELETE SET NULL,
    INDEX idx_negocio     (negocio_id),
    INDEX idx_fecha_gasto (fecha_gasto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: clientes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id  INT NOT NULL,
    nombre      VARCHAR(255) NOT NULL,
    apellido    VARCHAR(255),
    telefono    VARCHAR(50),
    email       VARCHAR(255),
    direccion   TEXT,
    notas       TEXT,
    activo      TINYINT(1) DEFAULT 1,
    fecha_creacion      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE,
    INDEX idx_negocio (negocio_id),
    INDEX idx_activo  (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: permisos (permisos granulares por empleado)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS permisos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT NOT NULL UNIQUE,
    ver_productos       TINYINT(1) DEFAULT 1,
    crear_productos     TINYINT(1) DEFAULT 0,
    editar_productos    TINYINT(1) DEFAULT 0,
    eliminar_productos  TINYINT(1) DEFAULT 0,
    ver_ventas          TINYINT(1) DEFAULT 1,
    crear_ventas        TINYINT(1) DEFAULT 1,
    cancelar_ventas     TINYINT(1) DEFAULT 0,
    ver_gastos          TINYINT(1) DEFAULT 1,
    crear_gastos        TINYINT(1) DEFAULT 1,
    ver_empleados       TINYINT(1) DEFAULT 0,
    crear_empleados     TINYINT(1) DEFAULT 0,
    ver_reportes        TINYINT(1) DEFAULT 0,
    gestionar_caja      TINYINT(1) DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
