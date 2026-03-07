-- MÓDULO DE CLIENTES - DASH CRM
-- Fecha: 18 de octubre de 2025

-- Tabla: clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    codigo_cliente VARCHAR(50) UNIQUE,
    tipo ENUM('persona', 'empresa') DEFAULT 'persona',
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255),
    razon_social VARCHAR(255),
    documento VARCHAR(50),
    email VARCHAR(255),
    telefono VARCHAR(50),
    celular VARCHAR(50),
    fecha_nacimiento DATE,
    direccion TEXT,
    ciudad VARCHAR(100),
    provincia VARCHAR(100),
    codigo_postal VARCHAR(10),
    pais VARCHAR(100) DEFAULT 'México',
    notas TEXT,
    categoria ENUM('regular', 'frecuente', 'vip', 'mayorista') DEFAULT 'regular',
    descuento_especial DECIMAL(5,2) DEFAULT 0,
    limite_credito DECIMAL(10,2) DEFAULT 0,
    saldo_actual DECIMAL(10,2) DEFAULT 0,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_ultima_compra TIMESTAMP NULL,
    total_compras DECIMAL(10,2) DEFAULT 0,
    cantidad_compras INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (negocio_id) REFERENCES negocios(id) ON DELETE CASCADE,
    INDEX idx_negocio (negocio_id),
    INDEX idx_codigo (codigo_cliente),
    INDEX idx_documento (documento),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actualizar tabla ventas para vincular con clientes
ALTER TABLE ventas 
ADD COLUMN cliente_id INT DEFAULT NULL AFTER caja_id,
ADD FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL;

-- Generar códigos de cliente automáticamente
DELIMITER //
CREATE TRIGGER before_insert_cliente
BEFORE INSERT ON clientes
FOR EACH ROW
BEGIN
    IF NEW.codigo_cliente IS NULL OR NEW.codigo_cliente = '' THEN
        SET NEW.codigo_cliente = CONCAT('CLI-', LPAD(LAST_INSERT_ID() + 1, 6, '0'));
    END IF;
END//
DELIMITER ;
