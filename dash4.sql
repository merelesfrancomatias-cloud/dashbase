-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 01-11-2025 a las 17:06:06
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dash4`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `monto_inicial` decimal(10,2) NOT NULL,
  `monto_ventas` decimal(10,2) DEFAULT 0.00,
  `monto_gastos` decimal(10,2) DEFAULT 0.00,
  `monto_final` decimal(10,2) DEFAULT NULL,
  `monto_real` decimal(10,2) DEFAULT NULL,
  `diferencia` decimal(10,2) DEFAULT NULL,
  `estado` enum('abierta','cerrada') DEFAULT 'abierta',
  `observaciones` text DEFAULT NULL,
  `fecha_apertura` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cajas`
--

INSERT INTO `cajas` (`id`, `negocio_id`, `usuario_id`, `monto_inicial`, `monto_ventas`, `monto_gastos`, `monto_final`, `monto_real`, `diferencia`, `estado`, `observaciones`, `fecha_apertura`, `fecha_cierre`) VALUES
(1, 1, 1, 1200.00, 18300.00, 0.00, 19500.00, 1100.00, -18400.00, 'cerrada', '', '2025-10-18 04:44:24', '2025-10-31 21:41:32'),
(2, 1, 1, 1100.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2025-10-31 21:42:09', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007AFF',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `negocio_id`, `nombre`, `descripcion`, `color`, `activo`, `fecha_creacion`) VALUES
(1, 1, 'General', NULL, '#007AFF', 1, '2025-10-18 04:15:25'),
(2, 1, 'Bebidas', NULL, '#5AC8FA', 1, '2025-10-18 04:15:25'),
(3, 1, 'Alimentos', NULL, '#34C759', 1, '2025-10-18 04:15:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL COMMENT 'Empleado asignado (opcional)',
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('pendiente','confirmada','en_progreso','completada','cancelada','no_asistio') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `precio_final` decimal(10,2) DEFAULT NULL COMMENT 'Puede diferir del precio del servicio',
  `motivo_cancelacion` text DEFAULT NULL,
  `recordatorio_24h` tinyint(1) DEFAULT 0,
  `recordatorio_2h` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL COMMENT 'Usuario que creó la cita',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro completo de todas las citas agendadas';

--
-- Disparadores `citas`
--
DELIMITER $$
CREATE TRIGGER `after_cita_completada` AFTER UPDATE ON `citas` FOR EACH ROW BEGIN
    IF NEW.estado = 'completada' AND OLD.estado != 'completada' THEN
        UPDATE clientes_citas 
        SET ultima_cita = NEW.fecha
        WHERE id = NEW.cliente_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_cita_insert` AFTER INSERT ON `citas` FOR EACH ROW BEGIN
    UPDATE clientes_citas 
    SET total_citas = total_citas + 1,
        ultima_cita = NEW.fecha
    WHERE id = NEW.cliente_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `citas_hoy`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `citas_hoy` (
`id` int(11)
,`fecha` date
,`hora_inicio` time
,`hora_fin` time
,`estado` enum('pendiente','confirmada','en_progreso','completada','cancelada','no_asistio')
,`cliente_nombre` varchar(100)
,`cliente_telefono` varchar(20)
,`servicio_nombre` varchar(100)
,`servicio_duracion` int(11)
,`servicio_color` varchar(7)
,`precio_final` decimal(10,2)
,`empleado_nombre` varchar(255)
,`notas` text
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_citas`
--

CREATE TABLE `clientes_citas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `total_citas` int(11) DEFAULT 0,
  `ultima_cita` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Base de datos de clientes para el sistema de citas';

--
-- Volcado de datos para la tabla `clientes_citas`
--

INSERT INTO `clientes_citas` (`id`, `negocio_id`, `nombre`, `telefono`, `email`, `notas`, `total_citas`, `ultima_cita`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cliente de Ejemplo', '555-1234567', 'cliente@ejemplo.com', 'Cliente de demostración para pruebas del sistema de citas.', 0, NULL, '2025-10-18 10:02:04', '2025-10-18 10:02:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_cuenta_corriente`
--

CREATE TABLE `clientes_cuenta_corriente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `limite_credito` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_actual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('activo','bloqueado','inactivo') DEFAULT 'activo',
  `notas` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes_cuenta_corriente`
--

INSERT INTO `clientes_cuenta_corriente` (`id`, `nombre`, `apellido`, `dni`, `telefono`, `email`, `direccion`, `limite_credito`, `saldo_actual`, `estado`, `notas`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Juan', 'Pérez', '12345678', '1145678901', NULL, 'Av. Siempre Viva 123', 50000.00, 0.00, 'activo', NULL, '2025-10-30 19:32:34', '2025-10-30 19:32:34'),
(2, 'María', 'González', '23456789', '1156789012', NULL, 'Calle Falsa 456', 30000.00, 0.00, 'activo', NULL, '2025-10-30 19:32:34', '2025-10-30 19:32:34'),
(3, 'Pedro', 'Rodríguez', '34567890', '1167890123', NULL, 'Av. Libertador 789', 75000.00, 0.00, 'activo', NULL, '2025-10-30 19:32:34', '2025-10-30 19:32:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `numero_compra` varchar(50) DEFAULT NULL,
  `tipo_comprobante` enum('factura_a','factura_b','factura_c','remito','presupuesto') DEFAULT 'remito',
  `numero_comprobante` varchar(50) DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `descuento` decimal(12,2) DEFAULT 0.00,
  `impuestos` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `estado` enum('pendiente','recibida','parcial','cancelada') DEFAULT 'pendiente',
  `forma_pago` enum('efectivo','transferencia','cheque','cuenta_corriente') DEFAULT 'cuenta_corriente',
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'Usuario que registró la compra',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_detalle`
--

CREATE TABLE `compras_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compra_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `lote_numero` varchar(50) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `recibido` int(11) DEFAULT 0 COMMENT 'Cantidad recibida'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_negocio`
--

CREATE TABLE `configuracion_negocio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `tema` varchar(20) DEFAULT 'light',
  `color_primario` varchar(7) DEFAULT '#007AFF',
  `color_secundario` varchar(7) DEFAULT '#00C9A7',
  `color_acento` varchar(7) DEFAULT '#FFC107',
  `nombre_empresa` varchar(200) DEFAULT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `favicon_url` varchar(500) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `sitio_web` varchar(200) DEFAULT NULL,
  `mostrar_logo_ticket` tinyint(1) DEFAULT 1,
  `mensaje_ticket` text DEFAULT 'Gracias por su compra',
  `pie_ticket` text DEFAULT NULL,
  `facebook` varchar(200) DEFAULT NULL,
  `instagram` varchar(200) DEFAULT NULL,
  `twitter` varchar(200) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `idioma` varchar(5) DEFAULT 'es',
  `moneda` varchar(3) DEFAULT 'MXN',
  `zona_horaria` varchar(50) DEFAULT 'America/Mexico_City',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_negocio`
--

INSERT INTO `configuracion_negocio` (`id`, `negocio_id`, `tema`, `color_primario`, `color_secundario`, `color_acento`, `nombre_empresa`, `logo_url`, `favicon_url`, `telefono`, `email`, `direccion`, `sitio_web`, `mostrar_logo_ticket`, `mensaje_ticket`, `pie_ticket`, `facebook`, `instagram`, `twitter`, `whatsapp`, `idioma`, `moneda`, `zona_horaria`, `created_at`, `updated_at`) VALUES
(1, 1, 'light', '#007AFF', '#00C9A7', '#FFC107', 'Mi Negocio Demo', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'Gracias por su compra', NULL, NULL, NULL, NULL, NULL, 'es', 'MXN', 'America/Mexico_City', '2025-10-18 09:01:13', '2025-10-18 09:01:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta_corriente_movimientos`
--

CREATE TABLE `cuenta_corriente_movimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `tipo` enum('venta','pago','ajuste','nota_credito','nota_debito') NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `saldo_anterior` decimal(10,2) NOT NULL,
  `saldo_nuevo` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL COMMENT 'Para pagos: efectivo, transferencia, etc',
  `descripcion` text DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 2, 1.00, 2900.00, 2900.00),
(2, 1, 1, 1.00, 3000.00, 3000.00),
(3, 2, 1, 1.00, 3000.00, 3000.00),
(4, 2, 3, 1.00, 200.00, 200.00),
(5, 3, 2, 1.00, 2900.00, 2900.00),
(6, 3, 3, 1.00, 200.00, 200.00),
(7, 4, 1, 1.00, 3000.00, 3000.00),
(8, 4, 3, 1.00, 200.00, 200.00),
(9, 5, 2, 1.00, 2900.00, 2900.00),
(10, 6, 2, 1.00, 2900.00, 2900.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dias_bloqueados`
--

CREATE TABLE `dias_bloqueados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `todo_el_dia` tinyint(1) DEFAULT 1,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Días u horarios no disponibles para citas';

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `estadisticas_servicios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `estadisticas_servicios` (
`id` int(11)
,`nombre` varchar(100)
,`categoria` varchar(50)
,`precio` decimal(10,2)
,`total_citas` bigint(21)
,`citas_completadas` decimal(22,0)
,`citas_canceladas` decimal(22,0)
,`ingresos_totales` decimal(32,2)
,`ingreso_promedio` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `caja_id` int(11) DEFAULT NULL,
  `categoria` enum('compra_mercaderia','servicios','salarios','alquiler','impuestos','otros') NOT NULL,
  `descripcion` text NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta_debito','tarjeta_credito','transferencia','otro') NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `fecha_gasto` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `negocio_id`, `usuario_id`, `caja_id`, `categoria`, `descripcion`, `monto`, `metodo_pago`, `comprobante`, `fecha_gasto`) VALUES
(1, 1, 1, 2, 'servicios', 'Luz - Pago 01/11', 20000.00, 'efectivo', '', '2025-10-31 03:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_atencion`
--

CREATE TABLE `horarios_atencion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL COMMENT '0=Domingo, 1=Lunes, 2=Martes, ..., 6=Sábado',
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  `intervalo_minutos` int(11) DEFAULT 30 COMMENT 'Duración de cada bloque de tiempo',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Configuración de horarios de trabajo por día';

--
-- Volcado de datos para la tabla `horarios_atencion`
--

INSERT INTO `horarios_atencion` (`id`, `negocio_id`, `dia_semana`, `hora_apertura`, `hora_cierre`, `intervalo_minutos`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '09:00:00', '18:00:00', 30, 1, '2025-10-18 10:02:04', '2025-10-18 10:02:04'),
(2, 1, 2, '09:00:00', '18:00:00', 30, 1, '2025-10-18 10:02:04', '2025-10-18 10:02:04'),
(3, 1, 3, '09:00:00', '18:00:00', 30, 1, '2025-10-18 10:02:04', '2025-10-18 10:02:04'),
(4, 1, 4, '09:00:00', '18:00:00', 30, 1, '2025-10-18 10:02:04', '2025-10-18 10:02:04'),
(5, 1, 5, '09:00:00', '18:00:00', 30, 1, '2025-10-18 10:02:04', '2025-10-18 10:02:04'),
(6, 1, 6, '09:00:00', '14:00:00', 30, 1, '2025-10-18 10:02:04', '2025-10-18 10:02:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_alertas`
--

CREATE TABLE `inventario_alertas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `tipo` enum('stock_minimo','stock_maximo','vencimiento','sin_movimiento') NOT NULL,
  `producto_id` int(11) NOT NULL,
  `lote_id` int(11) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `prioridad` enum('baja','media','alta','critica') DEFAULT 'media',
  `vista` tinyint(1) DEFAULT 0,
  `resuelta` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_visto` timestamp NULL DEFAULT NULL,
  `fecha_resolucion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_actividades`
--

CREATE TABLE `log_actividades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos_sistema`
--

CREATE TABLE `modulos_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'fas fa-cube',
  `es_core` tinyint(1) DEFAULT 0 COMMENT 'Módulo base que no se puede desactivar',
  `requiere_modulos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de códigos de módulos requeridos' CHECK (json_valid(`requiere_modulos`)),
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modulos_sistema`
--

INSERT INTO `modulos_sistema` (`id`, `codigo`, `nombre`, `descripcion`, `icono`, `es_core`, `requiere_modulos`, `activo`, `created_at`) VALUES
(1, 'dashboard', 'Dashboard', 'Panel principal con estadísticas', 'fas fa-home', 1, NULL, 1, '2025-10-30 14:26:37'),
(2, 'empleados', 'Empleados', 'Gestión de usuarios y empleados', 'fas fa-users', 1, NULL, 1, '2025-10-30 14:26:37'),
(3, 'caja', 'Caja', 'Arqueo y control de caja', 'fas fa-cash-register', 1, NULL, 1, '2025-10-30 14:26:37'),
(4, 'ventas', 'Ventas', 'Sistema de ventas y facturación', 'fas fa-shopping-cart', 1, NULL, 1, '2025-10-30 14:26:37'),
(5, 'productos', 'Productos', 'Gestión de inventario y productos', 'fas fa-box', 0, NULL, 1, '2025-10-30 14:26:37'),
(6, 'categorias', 'Categorías', 'Categorías de productos', 'fas fa-tags', 0, NULL, 1, '2025-10-30 14:26:37'),
(7, 'clientes', 'Clientes', 'Base de datos de clientes', 'fas fa-user-friends', 0, NULL, 1, '2025-10-30 14:26:37'),
(8, 'gastos', 'Gastos', 'Control de gastos y egresos', 'fas fa-money-bill-wave', 0, NULL, 1, '2025-10-30 14:26:37'),
(9, 'citas', 'Citas/Turnos', 'Sistema de agenda y turnos', 'fas fa-calendar-alt', 0, NULL, 1, '2025-10-30 14:26:37'),
(10, 'servicios', 'Servicios', 'Catálogo de servicios', 'fas fa-concierge-bell', 0, NULL, 1, '2025-10-30 14:26:37'),
(11, 'comisiones', 'Comisiones', 'Cálculo de comisiones por empleado', 'fas fa-percentage', 0, NULL, 1, '2025-10-30 14:26:37'),
(12, 'reservas', 'Reservas', 'Sistema de reservas por horario', 'fas fa-calendar-check', 0, NULL, 1, '2025-10-30 14:26:37'),
(13, 'canchas', 'Canchas', 'Gestión de canchas/espacios', 'fas fa-futbol', 0, NULL, 1, '2025-10-30 14:26:37'),
(14, 'torneos', 'Torneos', 'Organización de torneos y ligas', 'fas fa-trophy', 0, NULL, 1, '2025-10-30 14:26:37'),
(15, 'ordenes_trabajo', 'Órdenes de Trabajo', 'Seguimiento de reparaciones', 'fas fa-clipboard-list', 0, NULL, 1, '2025-10-30 14:26:37'),
(16, 'garantias', 'Garantías', 'Control de garantías', 'fas fa-shield-alt', 0, NULL, 1, '2025-10-30 14:26:37'),
(17, 'propiedades', 'Propiedades', 'Catálogo de propiedades', 'fas fa-home', 0, NULL, 1, '2025-10-30 14:26:37'),
(18, 'contratos', 'Contratos', 'Gestión de contratos', 'fas fa-file-contract', 0, NULL, 1, '2025-10-30 14:26:37'),
(19, 'visitas', 'Visitas', 'Agenda de visitas a propiedades', 'fas fa-eye', 0, NULL, 1, '2025-10-30 14:26:37'),
(20, 'inventario', 'Inventario Avanzado', 'Control de stock avanzado', 'fas fa-warehouse', 0, NULL, 1, '2025-10-30 14:26:37'),
(21, 'tallas', 'Tallas/Medidas', 'Gestión de tallas (boutique)', 'fas fa-ruler', 0, NULL, 1, '2025-10-30 14:26:37'),
(22, 'temporadas', 'Temporadas', 'Gestión de temporadas (moda)', 'fas fa-calendar', 0, NULL, 1, '2025-10-30 14:26:37'),
(23, 'mesas', 'Mesas', 'Gestión de mesas (restaurante)', 'fas fa-chair', 0, NULL, 1, '2025-10-30 14:26:37'),
(24, 'comandas', 'Comandas', 'Sistema de comandas (restaurante)', 'fas fa-receipt', 0, NULL, 1, '2025-10-30 14:26:37'),
(25, 'menu', 'Menú', 'Gestión del menú (restaurante)', 'fas fa-book-open', 0, NULL, 1, '2025-10-30 14:26:37'),
(26, 'cocina', 'Cocina', 'Monitor de cocina (restaurante)', 'fas fa-fire', 0, NULL, 1, '2025-10-30 14:26:37'),
(27, 'socios', 'Socios', 'Gestión de socios (gimnasio)', 'fas fa-id-card', 0, NULL, 1, '2025-10-30 14:26:37'),
(28, 'planes', 'Planes', 'Planes y membresías (gimnasio)', 'fas fa-list-alt', 0, NULL, 1, '2025-10-30 14:26:37'),
(29, 'asistencias', 'Asistencias', 'Control de asistencias (gimnasio)', 'fas fa-clipboard-check', 0, NULL, 1, '2025-10-30 14:26:37'),
(30, 'clases', 'Clases', 'Gestión de clases grupales', 'fas fa-chalkboard-teacher', 0, NULL, 1, '2025-10-30 14:26:37'),
(31, 'mascotas', 'Mascotas', 'Registro de mascotas (veterinaria)', 'fas fa-paw', 0, NULL, 1, '2025-10-30 14:26:37'),
(32, 'pacientes', 'Pacientes', 'Historias clínicas (consultorio)', 'fas fa-notes-medical', 0, NULL, 1, '2025-10-30 14:26:37'),
(33, 'historial_clinico', 'Historial Clínico', 'Historial médico completo', 'fas fa-file-medical', 0, NULL, 1, '2025-10-30 14:26:37'),
(34, 'recetas', 'Recetas', 'Gestión de recetas médicas', 'fas fa-prescription', 0, NULL, 1, '2025-10-30 14:26:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios`
--

CREATE TABLE `negocios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `tipo_negocio` enum('restaurante','retail','farmacia','salon','gimnasio','servicios') NOT NULL,
  `subtipo` varchar(100) DEFAULT NULL COMMENT 'Ej: Cafetería, Restaurante italiano, etc.',
  `slogan` varchar(300) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `logo` varchar(300) DEFAULT NULL,
  `logo_ticket_url` varchar(255) DEFAULT NULL,
  `color_primario` varchar(7) DEFAULT '#007AFF',
  `color_secundario` varchar(7) DEFAULT '#5AC8FA',
  `tema` varchar(20) DEFAULT 'light' COMMENT 'light, dark, auto',
  `configuracion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Horarios, políticas, contacto, impuestos' CHECK (json_valid(`configuracion`)),
  `terminologia` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Nombres personalizados para módulos y secciones' CHECK (json_valid(`terminologia`)),
  `campos_personalizados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Campos extra por tipo de negocio' CHECK (json_valid(`campos_personalizados`)),
  `modulos_activos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Módulos habilitados/deshabilitados' CHECK (json_valid(`modulos_activos`)),
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `pais` varchar(50) DEFAULT 'México',
  `rfc` varchar(13) DEFAULT NULL,
  `cuit_cuil` varchar(20) DEFAULT NULL,
  `regimen_fiscal` varchar(100) DEFAULT NULL,
  `condicion_iva` enum('responsable_inscripto','monotributo','exento','consumidor_final') DEFAULT 'monotributo',
  `mensaje_ticket` text DEFAULT NULL,
  `pie_ticket` text DEFAULT NULL,
  `imprimir_logo_ticket` tinyint(1) DEFAULT 1,
  `ancho_ticket` int(11) DEFAULT 80,
  `permitir_descuentos` tinyint(1) DEFAULT 1,
  `descuento_maximo` decimal(5,2) DEFAULT 20.00,
  `permitir_ventas_negativas` tinyint(1) DEFAULT 0,
  `alerta_stock_minimo` tinyint(1) DEFAULT 1,
  `habilitar_cuenta_corriente` tinyint(1) DEFAULT 1,
  `limite_credito_default` decimal(10,2) DEFAULT 10000.00,
  `dias_vencimiento_default` int(11) DEFAULT 30,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `horario_inicio` time DEFAULT '09:00:00',
  `horario_cierre` time DEFAULT '18:00:00',
  `dias_operacion` varchar(50) DEFAULT 'Lun-Sab' COMMENT 'Días que abre el negocio',
  `onboarding_completado` tinyint(1) DEFAULT 0,
  `paso_onboarding` int(11) DEFAULT 1 COMMENT 'Paso actual del wizard',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `negocios`
--

INSERT INTO `negocios` (`id`, `nombre`, `razon_social`, `tipo_negocio`, `subtipo`, `slogan`, `descripcion`, `logo`, `logo_ticket_url`, `color_primario`, `color_secundario`, `tema`, `configuracion`, `terminologia`, `campos_personalizados`, `modulos_activos`, `telefono`, `email`, `website`, `direccion`, `ciudad`, `estado`, `codigo_postal`, `pais`, `rfc`, `cuit_cuil`, `regimen_fiscal`, `condicion_iva`, `mensaje_ticket`, `pie_ticket`, `imprimir_logo_ticket`, `ancho_ticket`, `permitir_descuentos`, `descuento_maximo`, `permitir_ventas_negativas`, `alerta_stock_minimo`, `habilitar_cuenta_corriente`, `limite_credito_default`, `dias_vencimiento_default`, `facebook`, `instagram`, `whatsapp`, `horario_inicio`, `horario_cierre`, `dias_operacion`, `onboarding_completado`, `paso_onboarding`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Template Restaurante', NULL, 'restaurante', 'Restaurante', '¡Buen provecho!', NULL, NULL, NULL, '#FF6B35', '#F7931E', 'light', '{\"moneda\":\"MXN\",\"formato_moneda\":\"$#,##0.00\",\"impuesto_incluido\":false,\"porcentaje_iva\":16,\"propina_sugerida\":[10,15,20]}', '{\"productos\":\"Platillos\",\"ventas\":\"Comandas\",\"clientes\":\"Comensales\"}', '{\"productos\":[{\"nombre\":\"tiempo_preparacion\",\"etiqueta\":\"Tiempo Prep (min)\",\"tipo\":\"number\"}],\"ventas\":[{\"nombre\":\"numero_mesa\",\"etiqueta\":\"Mesa\",\"tipo\":\"number\",\"requerido\":true}]}', '{\"inventario\":true,\"citas\":false,\"empleados\":true,\"mesas\":true}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', NULL, NULL, NULL, 'monotributo', NULL, NULL, 1, 80, 1, 20.00, 0, 1, 1, 10000.00, 30, NULL, NULL, NULL, '09:00:00', '18:00:00', 'Lun-Sab', 0, 1, 1, '2025-10-18 21:32:07', '2025-10-18 21:32:07'),
(2, 'Template Retail', NULL, 'retail', 'Tienda', 'Tu mejor opción', NULL, NULL, NULL, '#007AFF', '#5AC8FA', 'light', '{\"moneda\":\"MXN\",\"porcentaje_iva\":16}', '{\"productos\":\"Productos\",\"ventas\":\"Ventas\",\"clientes\":\"Clientes\"}', '{\"productos\":[{\"nombre\":\"talla\",\"etiqueta\":\"Talla\",\"tipo\":\"text\"}]}', '{\"inventario\":true,\"empleados\":true}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', NULL, NULL, NULL, 'monotributo', NULL, NULL, 1, 80, 1, 20.00, 0, 1, 1, 10000.00, 30, NULL, NULL, NULL, '09:00:00', '18:00:00', 'Lun-Sab', 0, 1, 1, '2025-10-18 21:32:07', '2025-10-18 21:32:07'),
(3, 'Template Farmacia', NULL, 'farmacia', 'Farmacia', 'Tu salud, nuestra prioridad', NULL, NULL, NULL, '#34C759', '#30D158', 'light', '{\"moneda\":\"MXN\",\"porcentaje_iva\":16}', '{\"productos\":\"Medicamentos\",\"ventas\":\"Despachos\",\"clientes\":\"Pacientes\"}', '{\"productos\":[{\"nombre\":\"lote\",\"etiqueta\":\"Lote\",\"tipo\":\"text\",\"requerido\":true},{\"nombre\":\"fecha_vencimiento\",\"etiqueta\":\"Vencimiento\",\"tipo\":\"date\",\"requerido\":true}]}', '{\"inventario\":true,\"control_lotes\":true}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', NULL, NULL, NULL, 'monotributo', NULL, NULL, 1, 80, 1, 20.00, 0, 1, 1, 10000.00, 30, NULL, NULL, NULL, '09:00:00', '18:00:00', 'Lun-Sab', 0, 1, 1, '2025-10-18 21:32:07', '2025-10-18 21:32:07'),
(4, 'Template Salón', NULL, 'salon', 'Salón de Belleza', 'Tu belleza, nuestra pasión', NULL, NULL, NULL, '#FF2D55', '#FF375F', 'light', '{\"moneda\":\"MXN\",\"porcentaje_iva\":16}', '{\"productos\":\"Servicios\",\"ventas\":\"Servicios Realizados\",\"clientes\":\"Clientes\",\"empleados\":\"Estilistas\"}', '{\"productos\":[{\"nombre\":\"duracion_minutos\",\"etiqueta\":\"Duraci\\u00f3n (min)\",\"tipo\":\"number\",\"requerido\":true}]}', '{\"citas\":true,\"empleados\":true,\"paquetes\":true}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', NULL, NULL, NULL, 'monotributo', NULL, NULL, 1, 80, 1, 20.00, 0, 1, 1, 10000.00, 30, NULL, NULL, NULL, '09:00:00', '18:00:00', 'Lun-Sab', 0, 1, 1, '2025-10-18 21:32:07', '2025-10-18 21:32:07'),
(5, 'Template Gimnasio', NULL, 'gimnasio', 'Gimnasio', 'Tu mejor versión', NULL, NULL, NULL, '#FF9500', '#FF9F0A', 'light', '{\"moneda\":\"MXN\",\"porcentaje_iva\":16}', '{\"productos\":\"Membres\\u00edas\",\"ventas\":\"Inscripciones\",\"clientes\":\"Socios\"}', '{\"clientes\":[{\"nombre\":\"fecha_vencimiento\",\"etiqueta\":\"Vence\",\"tipo\":\"date\"}]}', '{\"membresias\":true,\"clases\":true}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', NULL, NULL, NULL, 'monotributo', NULL, NULL, 1, 80, 1, 20.00, 0, 1, 1, 10000.00, 30, NULL, NULL, NULL, '09:00:00', '18:00:00', 'Lun-Sab', 0, 1, 1, '2025-10-18 21:32:07', '2025-10-18 21:32:07'),
(6, 'Template Servicios', NULL, 'servicios', 'Servicios Profesionales', 'Expertos en soluciones', NULL, NULL, NULL, '#5856D6', '#5E5CE6', 'light', '{\"moneda\":\"MXN\",\"porcentaje_iva\":16}', '{\"productos\":\"Servicios\",\"ventas\":\"Proyectos\",\"clientes\":\"Clientes\"}', '{\"ventas\":[{\"nombre\":\"estado_proyecto\",\"etiqueta\":\"Estado\",\"tipo\":\"select\"}]}', '{\"proyectos\":true,\"cotizaciones\":true}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', NULL, NULL, NULL, 'monotributo', NULL, NULL, 1, 80, 1, 20.00, 0, 1, 1, 10000.00, 30, NULL, NULL, NULL, '09:00:00', '18:00:00', 'Lun-Sab', 0, 1, 1, '2025-10-18 21:32:07', '2025-10-18 21:32:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios_config`
--

CREATE TABLE `negocios_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo_rubro` varchar(50) NOT NULL,
  `nombre_negocio` varchar(100) NOT NULL,
  `razon_social` varchar(100) DEFAULT NULL,
  `cuit_cuil` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `provincia` varchar(50) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `color_primario` varchar(7) DEFAULT '#6366f1',
  `color_secundario` varchar(7) DEFAULT '#8b5cf6',
  `moneda` varchar(3) DEFAULT 'ARS',
  `configurado` tinyint(1) DEFAULT 0,
  `fecha_configuracion` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `negocios_config`
--

INSERT INTO `negocios_config` (`id`, `usuario_id`, `tipo_rubro`, `nombre_negocio`, `razon_social`, `cuit_cuil`, `telefono`, `email`, `direccion`, `ciudad`, `provincia`, `codigo_postal`, `logo`, `color_primario`, `color_secundario`, `moneda`, `configurado`, `fecha_configuracion`, `created_at`, `updated_at`) VALUES
(1, 1, 'canchas', 'aed', NULL, NULL, '234', '', '', '', '', '', NULL, '#10b981', '#10b981', 'ARS', 1, '2025-10-30 13:02:30', '2025-10-30 16:02:30', '2025-10-30 16:02:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios_modulos`
--

CREATE TABLE `negocios_modulos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_config_id` int(11) NOT NULL,
  `modulo_codigo` varchar(30) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `configuracion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Configuración específica del módulo para este negocio' CHECK (json_valid(`configuracion`)),
  `fecha_activacion` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `negocios_modulos`
--

INSERT INTO `negocios_modulos` (`id`, `negocio_config_id`, `modulo_codigo`, `activo`, `configuracion`, `fecha_activacion`, `created_at`) VALUES
(1, 1, 'dashboard', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(2, 1, 'empleados', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(3, 1, 'caja', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(4, 1, 'ventas', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(5, 1, 'reservas', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(6, 1, 'canchas', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(7, 1, 'clientes', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(8, 1, 'productos', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30'),
(9, 1, 'torneos', 1, NULL, '2025-10-30 13:02:30', '2025-10-30 16:02:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios_old`
--

CREATE TABLE `negocios_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `negocios_old`
--

INSERT INTO `negocios_old` (`id`, `nombre`, `razon_social`, `cuit`, `direccion`, `ciudad`, `provincia`, `codigo_postal`, `telefono`, `email`, `whatsapp`, `logo`, `activo`, `fecha_registro`, `fecha_actualizacion`) VALUES
(1, 'Mi Negocio Demo', NULL, NULL, 'Av. Principal 123', NULL, NULL, NULL, '+54 9 11 1234-5678', 'info@minegocio.com', NULL, NULL, 1, '2025-10-18 04:15:25', '2025-10-18 04:15:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `cliente_nombre` varchar(255) NOT NULL,
  `cliente_telefono` varchar(50) NOT NULL,
  `cliente_direccion` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmado','completado','cancelado') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_confirmacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_negocio`
--

CREATE TABLE `perfil_negocio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_negocio` varchar(255) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `condicion_iva` varchar(50) DEFAULT NULL,
  `rubro` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Argentina',
  `logo` varchar(255) DEFAULT NULL,
  `mensaje_ticket` text DEFAULT NULL,
  `mostrar_logo_ticket` tinyint(1) DEFAULT 1,
  `mostrar_direccion_ticket` tinyint(1) DEFAULT 1,
  `mostrar_cuit_ticket` tinyint(1) DEFAULT 1,
  `horarios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`horarios`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `ver_productos` tinyint(1) DEFAULT 1,
  `crear_productos` tinyint(1) DEFAULT 0,
  `editar_productos` tinyint(1) DEFAULT 0,
  `eliminar_productos` tinyint(1) DEFAULT 0,
  `ver_ventas` tinyint(1) DEFAULT 1,
  `crear_ventas` tinyint(1) DEFAULT 1,
  `cancelar_ventas` tinyint(1) DEFAULT 0,
  `ver_pedidos` tinyint(1) DEFAULT 1,
  `gestionar_pedidos` tinyint(1) DEFAULT 0,
  `ver_gastos` tinyint(1) DEFAULT 1,
  `crear_gastos` tinyint(1) DEFAULT 1,
  `ver_empleados` tinyint(1) DEFAULT 0,
  `crear_empleados` tinyint(1) DEFAULT 0,
  `ver_reportes` tinyint(1) DEFAULT 0,
  `gestionar_caja` tinyint(1) DEFAULT 1,
  `puede_ver_reportes` tinyint(1) DEFAULT 0,
  `puede_eliminar_ventas` tinyint(1) DEFAULT 0,
  `puede_modificar_ventas` tinyint(1) DEFAULT 0,
  `puede_ver_gastos` tinyint(1) DEFAULT 0,
  `puede_agregar_gastos` tinyint(1) DEFAULT 0,
  `puede_ver_caja` tinyint(1) DEFAULT 0,
  `puede_abrir_caja` tinyint(1) DEFAULT 0,
  `puede_cerrar_caja` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `usuario_id`, `ver_productos`, `crear_productos`, `editar_productos`, `eliminar_productos`, `ver_ventas`, `crear_ventas`, `cancelar_ventas`, `ver_pedidos`, `gestionar_pedidos`, `ver_gastos`, `crear_gastos`, `ver_empleados`, `crear_empleados`, `ver_reportes`, `gestionar_caja`, `puede_ver_reportes`, `puede_eliminar_ventas`, `puede_modificar_ventas`, `puede_ver_gastos`, `puede_agregar_gastos`, `puede_ver_caja`, `puede_abrir_caja`, `puede_cerrar_caja`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `precio_costo` decimal(10,2) DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_mayorista` decimal(10,2) DEFAULT NULL,
  `iva` decimal(5,2) DEFAULT 21.00 COMMENT 'Porcentaje de IVA',
  `cantidad_mayorista` int(11) DEFAULT 6 COMMENT 'Cantidad mínima para precio mayorista',
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0,
  `stock_maximo` int(11) DEFAULT 0,
  `unidad_medida` enum('unidad','kg','litro','metro','caja','pack') DEFAULT 'unidad',
  `usa_lotes` tinyint(1) DEFAULT 0 COMMENT 'Si el producto maneja lotes y vencimientos',
  `es_pesable` tinyint(1) DEFAULT 0 COMMENT 'Si el producto se vende por peso',
  `ubicacion` varchar(50) DEFAULT NULL COMMENT 'Góndola/pasillo donde está el producto',
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `datos_personalizados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Campos custom por tipo de negocio: lote, vencimiento, talla, etc.' CHECK (json_valid(`datos_personalizados`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `negocio_id`, `categoria_id`, `proveedor_id`, `nombre`, `descripcion`, `codigo_barras`, `precio_costo`, `precio_venta`, `precio_mayorista`, `iva`, `cantidad_mayorista`, `stock`, `stock_minimo`, `stock_maximo`, `unidad_medida`, `usa_lotes`, `es_pesable`, `ubicacion`, `foto`, `activo`, `fecha_creacion`, `fecha_actualizacion`, `datos_personalizados`) VALUES
(1, 1, 2, NULL, 'Coca Cola', 'Coca Cola 2.5L', '12312317939821', 2500.00, 3000.00, 2700.00, 21.00, 6, 10, 5, 300, 'unidad', 0, 0, 'Góndola 1', NULL, 1, '2025-10-30 16:36:55', '2025-10-31 16:09:24', NULL),
(2, 1, 1, NULL, 'Fibron', 'Azul Fibron', '12312313', 1500.00, 2900.00, NULL, 21.00, 6, 56, 30, 1000, 'unidad', 0, 0, 'Mostrador', NULL, 1, '2025-10-30 16:40:02', '2025-10-31 22:08:54', NULL),
(3, 1, 1, NULL, 'DashFibron', '', '12414212411', 100.00, 200.00, 150.00, 21.00, 6, 30, 2, NULL, 'unidad', 0, 0, 'Mostrador', NULL, 1, '2025-10-30 16:42:21', '2025-10-31 22:00:04', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_lotes`
--

CREATE TABLE `productos_lotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `numero_lote` varchar(50) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `precio_costo` decimal(10,2) DEFAULT 0.00,
  `fecha_ingreso` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `razon_social` varchar(150) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `contacto_nombre` varchar(100) DEFAULT NULL,
  `contacto_telefono` varchar(20) DEFAULT NULL,
  `dias_entrega` varchar(50) DEFAULT NULL COMMENT 'Ej: Lunes y Jueves',
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `saldo_cuenta` decimal(12,2) DEFAULT 0.00 COMMENT 'Saldo de cuenta corriente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `proximas_citas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `proximas_citas` (
`id` int(11)
,`fecha` date
,`hora_inicio` time
,`hora_fin` time
,`estado` enum('pendiente','confirmada','en_progreso','completada','cancelada','no_asistio')
,`cliente_nombre` varchar(100)
,`cliente_telefono` varchar(20)
,`servicio_nombre` varchar(100)
,`servicio_duracion` int(11)
,`servicio_color` varchar(7)
,`precio_final` decimal(10,2)
,`notas` text
,`dias_faltantes` int(7)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recordatorios`
--

CREATE TABLE `recordatorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_recordatorio` date NOT NULL,
  `hora_recordatorio` time DEFAULT NULL,
  `completado` tinyint(1) DEFAULT 0,
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recordatorios_citas`
--

CREATE TABLE `recordatorios_citas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cita_id` int(11) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL COMMENT 'email, sms, whatsapp',
  `mensaje` text DEFAULT NULL,
  `destinatario` varchar(100) DEFAULT NULL,
  `enviado_at` datetime DEFAULT NULL,
  `estado` enum('pendiente','enviado','fallido') DEFAULT 'pendiente',
  `error_mensaje` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log de recordatorios y notificaciones enviadas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rubros_disponibles`
--

CREATE TABLE `rubros_disponibles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'fas fa-store',
  `color` varchar(7) DEFAULT '#6366f1',
  `modulos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modulos_json`)),
  `activo` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rubros_disponibles`
--

INSERT INTO `rubros_disponibles` (`id`, `codigo`, `nombre`, `descripcion`, `icono`, `color`, `modulos_json`, `activo`, `orden`, `created_at`) VALUES
(1, 'peluqueria', 'Peluquería/Barbería', 'Salones de belleza, barberías y estéticas', 'fas fa-cut', '#ec4899', '[\"citas\", \"servicios\", \"clientes\", \"productos\", \"ventas\", \"empleados\", \"caja\", \"comisiones\"]', 1, 1, '2025-10-30 14:26:37'),
(2, 'canchas', 'Canchas Deportivas', 'Alquiler de canchas de fútbol, paddle, tenis, voley', 'fas fa-futbol', '#10b981', '[\"reservas\", \"canchas\", \"clientes\", \"productos\", \"ventas\", \"caja\", \"torneos\"]', 1, 2, '2025-10-30 14:26:37'),
(3, 'reparaciones', 'Servicios Técnicos', 'Reparación de celulares, computadoras, electrodomésticos', 'fas fa-tools', '#f59e0b', '[\"ordenes_trabajo\", \"clientes\", \"productos\", \"ventas\", \"empleados\", \"caja\", \"garantias\"]', 1, 3, '2025-10-30 14:26:37'),
(4, 'inmobiliaria', 'Inmobiliaria', 'Venta y alquiler de propiedades', 'fas fa-building', '#3b82f6', '[\"propiedades\", \"contratos\", \"visitas\", \"clientes\", \"empleados\", \"caja\"]', 1, 4, '2025-10-30 14:26:37'),
(5, 'supermercado', 'Supermercado/Tienda', 'Comercio minorista general', 'fas fa-shopping-cart', '#8b5cf6', '[\"productos\", \"categorias\", \"ventas\", \"clientes\", \"empleados\", \"caja\", \"gastos\", \"inventario\"]', 1, 5, '2025-10-30 14:26:37'),
(6, 'ferreteria', 'Ferretería', 'Venta de materiales de construcción y herramientas', 'fas fa-hammer', '#64748b', '[\"productos\", \"categorias\", \"ventas\", \"clientes\", \"empleados\", \"caja\", \"gastos\", \"inventario\"]', 1, 6, '2025-10-30 14:26:37'),
(7, 'boutique', 'Boutique/Moda', 'Tienda de ropa y accesorios', 'fas fa-tshirt', '#f43f5e', '[\"productos\", \"categorias\", \"ventas\", \"clientes\", \"empleados\", \"caja\", \"tallas\", \"temporadas\"]', 1, 7, '2025-10-30 14:26:37'),
(8, 'restaurante', 'Restaurante/Bar', 'Servicios de comida y bebidas', 'fas fa-utensils', '#ef4444', '[\"mesas\", \"comandas\", \"menu\", \"productos\", \"ventas\", \"clientes\", \"empleados\", \"caja\", \"cocina\"]', 1, 8, '2025-10-30 14:26:37'),
(9, 'gimnasio', 'Gimnasio/Fitness', 'Centro de entrenamiento y fitness', 'fas fa-dumbbell', '#059669', '[\"socios\", \"planes\", \"asistencias\", \"clases\", \"productos\", \"ventas\", \"empleados\", \"caja\"]', 1, 9, '2025-10-30 14:26:37'),
(10, 'veterinaria', 'Veterinaria', 'Clínica veterinaria y pet shop', 'fas fa-paw', '#06b6d4', '[\"mascotas\", \"citas\", \"historial_clinico\", \"productos\", \"ventas\", \"clientes\", \"empleados\", \"caja\"]', 1, 10, '2025-10-30 14:26:37'),
(11, 'consultorio', 'Consultorio Médico', 'Consultorios médicos y odontológicos', 'fas fa-user-md', '#14b8a6', '[\"pacientes\", \"citas\", \"historial_clinico\", \"recetas\", \"ventas\", \"empleados\", \"caja\"]', 1, 11, '2025-10-30 14:26:37'),
(12, 'otros', 'Otro Rubro', 'Personaliza tu negocio', 'fas fa-store', '#6366f1', '[\"productos\", \"ventas\", \"clientes\", \"empleados\", \"caja\", \"gastos\"]', 1, 99, '2025-10-30 14:26:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion` int(11) NOT NULL COMMENT 'Duración en minutos',
  `precio` decimal(10,2) NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `color` varchar(7) DEFAULT '#007AFF' COMMENT 'Color para el calendario',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de servicios disponibles para agendar citas';

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `negocio_id`, `nombre`, `descripcion`, `duracion`, `precio`, `categoria`, `activo`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, 'Servicio de Ejemplo', 'Este es un servicio de demostración. Puedes editarlo o crear nuevos servicios.', 60, 500.00, 'General', 1, '#007AFF', '2025-10-18 10:02:04', '2025-10-18 10:02:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock_movimientos`
--

CREATE TABLE `stock_movimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `lote_id` int(11) DEFAULT NULL,
  `tipo` enum('ingreso','egreso','ajuste','merma','transferencia') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_nuevo` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL COMMENT 'Ej: venta, compra, ajuste',
  `referencia_id` int(11) DEFAULT NULL COMMENT 'ID de la venta/compra/etc',
  `usuario_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','empleado') DEFAULT 'empleado',
  `telefono` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `negocio_id`, `nombre`, `apellido`, `usuario`, `email`, `password`, `rol`, `telefono`, `foto`, `activo`, `ultimo_acceso`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 'Admin', 'Sistema', 'admin', NULL, '$2y$10$ECeDPL0VQN6NPePsOYCcMuq8hjsEui5akvmngJXv9DmgaEmxBYS5W', 'admin', NULL, NULL, 1, '2025-11-01 15:57:58', '2025-10-18 04:15:25', '2025-11-01 15:57:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `caja_id` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(255) DEFAULT NULL,
  `cliente_telefono` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta_debito','tarjeta_credito','transferencia','mercadopago','otro') NOT NULL,
  `cliente_cuenta_id` int(11) DEFAULT NULL,
  `estado` enum('completada','cancelada') DEFAULT 'completada',
  `observaciones` text DEFAULT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo_cancelacion` text DEFAULT NULL,
  `cancelada_por` int(11) DEFAULT NULL,
  `fecha_cancelacion` datetime DEFAULT NULL,
  `datos_personalizados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Campos custom: mesa, mesero, delivery, etc.' CHECK (json_valid(`datos_personalizados`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `negocio_id`, `usuario_id`, `caja_id`, `cliente_nombre`, `cliente_telefono`, `subtotal`, `descuento`, `total`, `metodo_pago`, `cliente_cuenta_id`, `estado`, `observaciones`, `fecha_venta`, `motivo_cancelacion`, `cancelada_por`, `fecha_cancelacion`, `datos_personalizados`) VALUES
(1, 1, 1, 1, NULL, NULL, 5900.00, 0.00, 5900.00, 'efectivo', NULL, 'completada', NULL, '2025-10-30 19:25:37', NULL, NULL, NULL, NULL),
(2, 1, 1, 1, NULL, NULL, 3200.00, 0.00, 3200.00, 'efectivo', NULL, 'completada', NULL, '2025-10-30 19:26:18', NULL, NULL, NULL, NULL),
(3, 1, 1, 1, NULL, NULL, 3100.00, 0.00, 3100.00, 'efectivo', NULL, 'completada', NULL, '2025-10-30 19:28:27', NULL, NULL, NULL, NULL),
(4, 1, 1, 1, NULL, NULL, 3200.00, 0.00, 3200.00, 'efectivo', NULL, 'completada', NULL, '2025-10-31 16:09:24', NULL, NULL, NULL, NULL),
(5, 1, 1, 1, NULL, NULL, 2900.00, 0.00, 2900.00, 'tarjeta_debito', NULL, 'completada', NULL, '2025-10-31 21:36:02', NULL, NULL, NULL, NULL),
(6, 1, 1, 2, NULL, NULL, 2900.00, 0.00, 2900.00, 'transferencia', NULL, 'completada', NULL, '2025-10-31 22:08:54', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_historial`
--

CREATE TABLE `ventas_historial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_clientes_cuenta_resumen`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_clientes_cuenta_resumen` (
`id` int(11)
,`nombre` varchar(255)
,`apellido` varchar(255)
,`nombre_completo` varchar(511)
,`dni` varchar(20)
,`telefono` varchar(20)
,`email` varchar(255)
,`limite_credito` decimal(10,2)
,`saldo_actual` decimal(10,2)
,`credito_disponible` decimal(11,2)
,`estado` enum('activo','bloqueado','inactivo')
,`total_compras` bigint(21)
,`total_pagos` bigint(21)
,`ultima_compra` timestamp
,`ultimo_pago` timestamp
,`fecha_registro` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_productos_reporte`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_productos_reporte` (
`id` int(11)
,`nombre` varchar(255)
,`codigo_barras` varchar(100)
,`stock` int(11)
,`stock_minimo` int(11)
,`precio_costo` decimal(10,2)
,`precio_venta` decimal(10,2)
,`margen_unitario` decimal(11,2)
,`margen_porcentaje` decimal(20,6)
,`categoria` varchar(255)
,`total_vendido` decimal(32,2)
,`ingresos_generados` decimal(32,2)
,`ganancia_generada` decimal(43,4)
,`veces_vendido` bigint(21)
,`ultima_venta` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_rendimiento_cajeros`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_rendimiento_cajeros` (
`usuario_id` int(11)
,`cajero` varchar(255)
,`total_ventas` bigint(21)
,`total_vendido` decimal(32,2)
,`ticket_promedio` decimal(14,6)
,`venta_maxima` decimal(10,2)
,`venta_minima` decimal(10,2)
,`descuentos_aplicados` decimal(32,2)
,`dias_trabajados` bigint(21)
,`promedio_por_dia` decimal(36,6)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_resumen_diario`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_resumen_diario` (
`fecha` date
,`total_ventas` bigint(21)
,`cajeros_activos` bigint(21)
,`subtotal` decimal(32,2)
,`descuento` decimal(32,2)
,`total` decimal(32,2)
,`ticket_promedio` decimal(14,6)
,`efectivo` decimal(32,2)
,`tarjeta` decimal(32,2)
,`transferencia` decimal(32,2)
,`cuenta_corriente` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ventas_detalladas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_ventas_detalladas` (
`venta_id` int(11)
,`fecha_venta` timestamp
,`fecha` date
,`hora` int(2)
,`dia_semana` varchar(9)
,`semana` int(2)
,`mes` int(2)
,`anio` int(4)
,`subtotal` decimal(10,2)
,`descuento` decimal(10,2)
,`total` decimal(10,2)
,`metodo_pago` enum('efectivo','tarjeta_debito','tarjeta_credito','transferencia','mercadopago','otro')
,`usuario_id` int(11)
,`cajero` varchar(255)
,`producto_id` int(11)
,`producto_nombre` varchar(255)
,`categoria` varchar(255)
,`cantidad` decimal(10,2)
,`precio_unitario` decimal(10,2)
,`item_subtotal` decimal(10,2)
,`ganancia_unitaria` decimal(11,2)
,`ganancia_total` decimal(21,4)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `citas_hoy`
--
DROP TABLE IF EXISTS `citas_hoy`;

CREATE OR REPLACE VIEW `citas_hoy`  AS SELECT `c`.`id` AS `id`, `c`.`fecha` AS `fecha`, `c`.`hora_inicio` AS `hora_inicio`, `c`.`hora_fin` AS `hora_fin`, `c`.`estado` AS `estado`, `cl`.`nombre` AS `cliente_nombre`, `cl`.`telefono` AS `cliente_telefono`, `s`.`nombre` AS `servicio_nombre`, `s`.`duracion` AS `servicio_duracion`, `s`.`color` AS `servicio_color`, `c`.`precio_final` AS `precio_final`, `u`.`nombre` AS `empleado_nombre`, `c`.`notas` AS `notas` FROM (((`citas` `c` join `clientes_citas` `cl` on(`c`.`cliente_id` = `cl`.`id`)) join `servicios` `s` on(`c`.`servicio_id` = `s`.`id`)) left join `usuarios` `u` on(`c`.`empleado_id` = `u`.`id`)) WHERE `c`.`fecha` = curdate() ORDER BY `c`.`hora_inicio` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `estadisticas_servicios`
--
DROP TABLE IF EXISTS `estadisticas_servicios`;

CREATE OR REPLACE VIEW `estadisticas_servicios`  AS SELECT `s`.`id` AS `id`, `s`.`nombre` AS `nombre`, `s`.`categoria` AS `categoria`, `s`.`precio` AS `precio`, count(`c`.`id`) AS `total_citas`, sum(case when `c`.`estado` = 'completada' then 1 else 0 end) AS `citas_completadas`, sum(case when `c`.`estado` = 'cancelada' then 1 else 0 end) AS `citas_canceladas`, sum(case when `c`.`estado` = 'completada' then `c`.`precio_final` else 0 end) AS `ingresos_totales`, avg(case when `c`.`estado` = 'completada' then `c`.`precio_final` else NULL end) AS `ingreso_promedio` FROM (`servicios` `s` left join `citas` `c` on(`s`.`id` = `c`.`servicio_id`)) GROUP BY `s`.`id`, `s`.`nombre`, `s`.`categoria`, `s`.`precio` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `proximas_citas`
--
DROP TABLE IF EXISTS `proximas_citas`;

CREATE OR REPLACE VIEW `proximas_citas`  AS SELECT `c`.`id` AS `id`, `c`.`fecha` AS `fecha`, `c`.`hora_inicio` AS `hora_inicio`, `c`.`hora_fin` AS `hora_fin`, `c`.`estado` AS `estado`, `cl`.`nombre` AS `cliente_nombre`, `cl`.`telefono` AS `cliente_telefono`, `s`.`nombre` AS `servicio_nombre`, `s`.`duracion` AS `servicio_duracion`, `s`.`color` AS `servicio_color`, `c`.`precio_final` AS `precio_final`, `c`.`notas` AS `notas`, to_days(`c`.`fecha`) - to_days(curdate()) AS `dias_faltantes` FROM ((`citas` `c` join `clientes_citas` `cl` on(`c`.`cliente_id` = `cl`.`id`)) join `servicios` `s` on(`c`.`servicio_id` = `s`.`id`)) WHERE `c`.`fecha` >= curdate() AND `c`.`estado` in ('pendiente','confirmada') ORDER BY `c`.`fecha` ASC, `c`.`hora_inicio` ASC LIMIT 0, 50 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_clientes_cuenta_resumen`
--
DROP TABLE IF EXISTS `vista_clientes_cuenta_resumen`;

CREATE OR REPLACE VIEW `vista_clientes_cuenta_resumen`  AS SELECT `c`.`id` AS `id`, `c`.`nombre` AS `nombre`, `c`.`apellido` AS `apellido`, concat(`c`.`nombre`,' ',`c`.`apellido`) AS `nombre_completo`, `c`.`dni` AS `dni`, `c`.`telefono` AS `telefono`, `c`.`email` AS `email`, `c`.`limite_credito` AS `limite_credito`, `c`.`saldo_actual` AS `saldo_actual`, `c`.`limite_credito`- `c`.`saldo_actual` AS `credito_disponible`, `c`.`estado` AS `estado`, count(distinct case when `m`.`tipo` = 'venta' then `m`.`id` end) AS `total_compras`, count(distinct case when `m`.`tipo` = 'pago' then `m`.`id` end) AS `total_pagos`, max(case when `m`.`tipo` = 'venta' then `m`.`fecha_movimiento` end) AS `ultima_compra`, max(case when `m`.`tipo` = 'pago' then `m`.`fecha_movimiento` end) AS `ultimo_pago`, `c`.`fecha_registro` AS `fecha_registro` FROM (`clientes_cuenta_corriente` `c` left join `cuenta_corriente_movimientos` `m` on(`c`.`id` = `m`.`cliente_id`)) GROUP BY `c`.`id` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_productos_reporte`
--
DROP TABLE IF EXISTS `vista_productos_reporte`;

CREATE OR REPLACE VIEW `vista_productos_reporte`  AS SELECT `p`.`id` AS `id`, `p`.`nombre` AS `nombre`, `p`.`codigo_barras` AS `codigo_barras`, `p`.`stock` AS `stock`, `p`.`stock_minimo` AS `stock_minimo`, coalesce(`p`.`precio_costo`,0) AS `precio_costo`, `p`.`precio_venta` AS `precio_venta`, `p`.`precio_venta`- coalesce(`p`.`precio_costo`,0) AS `margen_unitario`, CASE WHEN coalesce(`p`.`precio_costo`,0) > 0 THEN (`p`.`precio_venta` - coalesce(`p`.`precio_costo`,0)) / `p`.`precio_costo` * 100 ELSE 0 END AS `margen_porcentaje`, `c`.`nombre` AS `categoria`, coalesce(sum(`dv`.`cantidad`),0) AS `total_vendido`, coalesce(sum(`dv`.`subtotal`),0) AS `ingresos_generados`, coalesce(sum((`dv`.`precio_unitario` - coalesce(`p`.`precio_costo`,0)) * `dv`.`cantidad`),0) AS `ganancia_generada`, count(distinct `v`.`id`) AS `veces_vendido`, max(`v`.`fecha_venta`) AS `ultima_venta` FROM (((`productos` `p` left join `categorias` `c` on(`p`.`categoria_id` = `c`.`id`)) left join `detalle_ventas` `dv` on(`p`.`id` = `dv`.`producto_id`)) left join `ventas` `v` on(`dv`.`venta_id` = `v`.`id`)) GROUP BY `p`.`id` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_rendimiento_cajeros`
--
DROP TABLE IF EXISTS `vista_rendimiento_cajeros`;

CREATE OR REPLACE VIEW `vista_rendimiento_cajeros`  AS SELECT `u`.`id` AS `usuario_id`, `u`.`nombre` AS `cajero`, count(distinct `v`.`id`) AS `total_ventas`, sum(`v`.`total`) AS `total_vendido`, avg(`v`.`total`) AS `ticket_promedio`, max(`v`.`total`) AS `venta_maxima`, min(`v`.`total`) AS `venta_minima`, sum(`v`.`descuento`) AS `descuentos_aplicados`, count(distinct cast(`v`.`fecha_venta` as date)) AS `dias_trabajados`, sum(`v`.`total`) / count(distinct cast(`v`.`fecha_venta` as date)) AS `promedio_por_dia` FROM (`usuarios` `u` left join `ventas` `v` on(`u`.`id` = `v`.`usuario_id`)) WHERE `u`.`rol` in ('admin','empleado') GROUP BY `u`.`id` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_resumen_diario`
--
DROP TABLE IF EXISTS `vista_resumen_diario`;

CREATE OR REPLACE VIEW `vista_resumen_diario`  AS SELECT cast(`v`.`fecha_venta` as date) AS `fecha`, count(distinct `v`.`id`) AS `total_ventas`, count(distinct `v`.`usuario_id`) AS `cajeros_activos`, sum(`v`.`subtotal`) AS `subtotal`, sum(`v`.`descuento`) AS `descuento`, sum(`v`.`total`) AS `total`, avg(`v`.`total`) AS `ticket_promedio`, sum(case when `v`.`metodo_pago` = 'efectivo' then `v`.`total` else 0 end) AS `efectivo`, sum(case when `v`.`metodo_pago` = 'tarjeta_debito' or `v`.`metodo_pago` = 'tarjeta_credito' then `v`.`total` else 0 end) AS `tarjeta`, sum(case when `v`.`metodo_pago` = 'transferencia' then `v`.`total` else 0 end) AS `transferencia`, sum(case when `v`.`metodo_pago` = 'cuenta_corriente' then `v`.`total` else 0 end) AS `cuenta_corriente` FROM `ventas` AS `v` GROUP BY cast(`v`.`fecha_venta` as date) ORDER BY cast(`v`.`fecha_venta` as date) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ventas_detalladas`
--
DROP TABLE IF EXISTS `vista_ventas_detalladas`;

CREATE OR REPLACE VIEW `vista_ventas_detalladas`  AS SELECT `v`.`id` AS `venta_id`, `v`.`fecha_venta` AS `fecha_venta`, cast(`v`.`fecha_venta` as date) AS `fecha`, hour(`v`.`fecha_venta`) AS `hora`, dayname(`v`.`fecha_venta`) AS `dia_semana`, week(`v`.`fecha_venta`) AS `semana`, month(`v`.`fecha_venta`) AS `mes`, year(`v`.`fecha_venta`) AS `anio`, `v`.`subtotal` AS `subtotal`, `v`.`descuento` AS `descuento`, `v`.`total` AS `total`, `v`.`metodo_pago` AS `metodo_pago`, `u`.`id` AS `usuario_id`, `u`.`nombre` AS `cajero`, `dv`.`producto_id` AS `producto_id`, `p`.`nombre` AS `producto_nombre`, `c`.`nombre` AS `categoria`, `dv`.`cantidad` AS `cantidad`, `dv`.`precio_unitario` AS `precio_unitario`, `dv`.`subtotal` AS `item_subtotal`, `dv`.`precio_unitario`- coalesce(`p`.`precio_costo`,0) AS `ganancia_unitaria`, (`dv`.`precio_unitario` - coalesce(`p`.`precio_costo`,0)) * `dv`.`cantidad` AS `ganancia_total` FROM ((((`ventas` `v` join `usuarios` `u` on(`v`.`usuario_id` = `u`.`id`)) join `detalle_ventas` `dv` on(`v`.`id` = `dv`.`venta_id`)) join `productos` `p` on(`dv`.`producto_id` = `p`.`id`)) left join `categorias` `c` on(`p`.`categoria_id` = `c`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicio_id` (`servicio_id`),
  ADD KEY `empleado_id` (`empleado_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_negocio_fecha` (`negocio_id`,`fecha`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_fecha_hora` (`fecha`,`hora_inicio`),
  ADD KEY `idx_disponibilidad` (`negocio_id`,`fecha`,`hora_inicio`,`hora_fin`,`estado`),
  ADD KEY `idx_reportes_fecha` (`negocio_id`,`fecha`,`estado`);

--
-- Indices de la tabla `clientes_citas`
--
ALTER TABLE `clientes_citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_telefono` (`telefono`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_busqueda_clientes` (`negocio_id`,`nombre`,`telefono`);

--
-- Indices de la tabla `clientes_cuenta_corriente`
--
ALTER TABLE `clientes_cuenta_corriente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `idx_dni` (`dni`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_nombre` (`nombre`,`apellido`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_compra_negocio` (`negocio_id`),
  ADD KEY `idx_compra_proveedor` (`proveedor_id`),
  ADD KEY `idx_compra_fecha` (`fecha_compra`),
  ADD KEY `idx_compra_estado` (`estado`);

--
-- Indices de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle_compra` (`compra_id`),
  ADD KEY `idx_detalle_producto` (`producto_id`);

--
-- Indices de la tabla `configuracion_negocio`
--
ALTER TABLE `configuracion_negocio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `cuenta_corriente_movimientos`
--
ALTER TABLE `cuenta_corriente_movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_fecha` (`fecha_movimiento`),
  ADD KEY `idx_venta` (`venta_id`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `idx_pedido` (`pedido_id`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta` (`venta_id`),
  ADD KEY `idx_producto` (`producto_id`);

--
-- Indices de la tabla `dias_bloqueados`
--
ALTER TABLE `dias_bloqueados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_negocio_fecha` (`negocio_id`,`fecha`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `caja_id` (`caja_id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_fecha` (`fecha_gasto`);

--
-- Indices de la tabla `horarios_atencion`
--
ALTER TABLE `horarios_atencion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_negocio_dia` (`negocio_id`,`dia_semana`),
  ADD KEY `idx_negocio_activo` (`negocio_id`,`activo`);

--
-- Indices de la tabla `inventario_alertas`
--
ALTER TABLE `inventario_alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `lote_id` (`lote_id`),
  ADD KEY `idx_alerta_negocio` (`negocio_id`),
  ADD KEY `idx_alerta_tipo` (`tipo`),
  ADD KEY `idx_alerta_vista` (`vista`),
  ADD KEY `idx_alerta_resuelta` (`resuelta`);

--
-- Indices de la tabla `log_actividades`
--
ALTER TABLE `log_actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_modulo` (`modulo`);

--
-- Indices de la tabla `modulos_sistema`
--
ALTER TABLE `modulos_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_modulo_activo` (`activo`);

--
-- Indices de la tabla `negocios`
--
ALTER TABLE `negocios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo` (`tipo_negocio`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `negocios_config`
--
ALTER TABLE `negocios_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`usuario_id`),
  ADD KEY `idx_negocio_tipo` (`tipo_rubro`),
  ADD KEY `idx_negocio_configurado` (`configurado`);

--
-- Indices de la tabla `negocios_modulos`
--
ALTER TABLE `negocios_modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_negocio_modulo` (`negocio_config_id`,`modulo_codigo`),
  ADD KEY `modulo_codigo` (`modulo_codigo`);

--
-- Indices de la tabla `negocios_old`
--
ALTER TABLE `negocios_old`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `perfil_negocio`
--
ALTER TABLE `perfil_negocio`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_codigo_barras` (`codigo_barras`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `idx_productos_activo` (`activo`),
  ADD KEY `idx_productos_stock` (`stock`),
  ADD KEY `idx_productos_proveedor` (`proveedor_id`);

--
-- Indices de la tabla `productos_lotes`
--
ALTER TABLE `productos_lotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote_producto` (`producto_id`),
  ADD KEY `idx_lote_vencimiento` (`fecha_vencimiento`),
  ADD KEY `idx_lote_activo` (`activo`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proveedor_negocio` (`negocio_id`),
  ADD KEY `idx_proveedor_activo` (`activo`),
  ADD KEY `idx_proveedor_cuit` (`cuit`);

--
-- Indices de la tabla `recordatorios`
--
ALTER TABLE `recordatorios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_fecha` (`fecha_recordatorio`),
  ADD KEY `idx_completado` (`completado`);

--
-- Indices de la tabla `recordatorios_citas`
--
ALTER TABLE `recordatorios_citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cita` (`cita_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `rubros_disponibles`
--
ALTER TABLE `rubros_disponibles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_rubro_activo` (`activo`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio_activo` (`negocio_id`,`activo`);

--
-- Indices de la tabla `stock_movimientos`
--
ALTER TABLE `stock_movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lote_id` (`lote_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_movimiento_producto` (`producto_id`),
  ADD KEY `idx_movimiento_tipo` (`tipo`),
  ADD KEY `idx_movimiento_fecha` (`fecha`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `idx_usuario` (`usuario`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `caja_id` (`caja_id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_fecha` (`fecha_venta`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `cliente_cuenta_id` (`cliente_cuenta_id`);

--
-- Indices de la tabla `ventas_historial`
--
ALTER TABLE `ventas_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_venta` (`venta_id`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes_citas`
--
ALTER TABLE `clientes_citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes_cuenta_corriente`
--
ALTER TABLE `clientes_cuenta_corriente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_negocio`
--
ALTER TABLE `configuracion_negocio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuenta_corriente_movimientos`
--
ALTER TABLE `cuenta_corriente_movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `dias_bloqueados`
--
ALTER TABLE `dias_bloqueados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `horarios_atencion`
--
ALTER TABLE `horarios_atencion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `inventario_alertas`
--
ALTER TABLE `inventario_alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `log_actividades`
--
ALTER TABLE `log_actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulos_sistema`
--
ALTER TABLE `modulos_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `negocios`
--
ALTER TABLE `negocios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `negocios_config`
--
ALTER TABLE `negocios_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `negocios_modulos`
--
ALTER TABLE `negocios_modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `negocios_old`
--
ALTER TABLE `negocios_old`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `perfil_negocio`
--
ALTER TABLE `perfil_negocio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos_lotes`
--
ALTER TABLE `productos_lotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recordatorios`
--
ALTER TABLE `recordatorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recordatorios_citas`
--
ALTER TABLE `recordatorios_citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rubros_disponibles`
--
ALTER TABLE `rubros_disponibles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `stock_movimientos`
--
ALTER TABLE `stock_movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ventas_historial`
--
ALTER TABLE `ventas_historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD CONSTRAINT `cajas_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cajas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_citas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`),
  ADD CONSTRAINT `citas_ibfk_4` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `citas_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `clientes_citas`
--
ALTER TABLE `clientes_citas`
  ADD CONSTRAINT `clientes_citas_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_config` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `compras_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD CONSTRAINT `compras_detalle_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `configuracion_negocio`
--
ALTER TABLE `configuracion_negocio`
  ADD CONSTRAINT `configuracion_negocio_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cuenta_corriente_movimientos`
--
ALTER TABLE `cuenta_corriente_movimientos`
  ADD CONSTRAINT `cuenta_corriente_movimientos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_cuenta_corriente` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cuenta_corriente_movimientos_ibfk_2` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cuenta_corriente_movimientos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dias_bloqueados`
--
ALTER TABLE `dias_bloqueados`
  ADD CONSTRAINT `dias_bloqueados_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dias_bloqueados_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `horarios_atencion`
--
ALTER TABLE `horarios_atencion`
  ADD CONSTRAINT `horarios_atencion_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventario_alertas`
--
ALTER TABLE `inventario_alertas`
  ADD CONSTRAINT `inventario_alertas_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_config` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_alertas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_alertas_ibfk_3` FOREIGN KEY (`lote_id`) REFERENCES `productos_lotes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `log_actividades`
--
ALTER TABLE `log_actividades`
  ADD CONSTRAINT `log_actividades_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `log_actividades_ibfk_2` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `negocios_config`
--
ALTER TABLE `negocios_config`
  ADD CONSTRAINT `negocios_config_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `negocios_modulos`
--
ALTER TABLE `negocios_modulos`
  ADD CONSTRAINT `negocios_modulos_ibfk_1` FOREIGN KEY (`negocio_config_id`) REFERENCES `negocios_config` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `negocios_modulos_ibfk_2` FOREIGN KEY (`modulo_codigo`) REFERENCES `modulos_sistema` (`codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos_lotes`
--
ALTER TABLE `productos_lotes`
  ADD CONSTRAINT `productos_lotes_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_config` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recordatorios`
--
ALTER TABLE `recordatorios`
  ADD CONSTRAINT `recordatorios_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recordatorios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `recordatorios_citas`
--
ALTER TABLE `recordatorios_citas`
  ADD CONSTRAINT `recordatorios_citas_ibfk_1` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `servicios_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `stock_movimientos`
--
ALTER TABLE `stock_movimientos`
  ADD CONSTRAINT `stock_movimientos_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movimientos_ibfk_2` FOREIGN KEY (`lote_id`) REFERENCES `productos_lotes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movimientos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios_old` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ventas_ibfk_4` FOREIGN KEY (`cliente_cuenta_id`) REFERENCES `clientes_cuenta_corriente` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ventas_historial`
--
ALTER TABLE `ventas_historial`
  ADD CONSTRAINT `ventas_historial_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_historial_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

