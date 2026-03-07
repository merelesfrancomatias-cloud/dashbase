-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 30-10-2025 a las 21:58:11
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
CREATE DATABASE IF NOT EXISTS `dash4` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `dash4`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `monto_inicial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_ventas` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_gastos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_final` decimal(10,2) DEFAULT NULL,
  `monto_real` decimal(10,2) DEFAULT NULL,
  `diferencia` decimal(10,2) DEFAULT NULL,
  `estado` enum('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  `observaciones` text DEFAULT NULL,
  `fecha_apertura` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cajas`
--

INSERT INTO `cajas` (`id`, `negocio_id`, `usuario_id`, `monto_inicial`, `monto_ventas`, `monto_gastos`, `monto_final`, `monto_real`, `diferencia`, `estado`, `observaciones`, `fecha_apertura`, `fecha_cierre`) VALUES
(1, 1, 1, 1200.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2025-10-18 04:44:24', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
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
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `caja_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `fecha_gasto` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `precio_costo` decimal(10,2) DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_mayorista` decimal(10,2) DEFAULT NULL,
  `iva` decimal(5,2) DEFAULT 21.00,
  `cantidad_mayorista` int(11) DEFAULT 6,
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT NULL,
  `stock_maximo` int(11) DEFAULT NULL,
  `unidad_medida` varchar(50) DEFAULT 'unidad',
  `usa_lotes` tinyint(1) DEFAULT 0,
  `es_pesable` tinyint(1) DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL,
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
(1, 1, 2, NULL, 'Coca Cola', 'Coca Cola 2.5L', '12312317939821', 2500.00, 3000.00, 2700.00, 21.00, 6, 11, 5, 300, 'unidad', 0, 0, 'Góndola 1', NULL, 1, '2025-10-30 16:36:55', '2025-10-30 19:26:18', NULL),
(2, 1, 1, NULL, 'Fibron', 'Azul Fibron', '12312313', 1500.00, 2900.00, NULL, 21.00, 6, 58, 30, 1000, 'unidad', 0, 0, 'Mostrador', NULL, 1, '2025-10-30 16:40:02', '2025-10-30 19:28:27', NULL),
(3, 1, 1, NULL, 'DashFibron', '', '12414212411', 100.00, 200.00, 150.00, 21.00, 6, 2, 2, NULL, 'unidad', 0, 0, 'Mostrador', NULL, 1, '2025-10-30 16:42:21', '2025-10-30 19:28:27', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor','empleado') DEFAULT 'vendedor',
  `telefono` varchar(20) DEFAULT NULL,
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
(1, 1, 'Admin', 'Sistema', 'admin', NULL, '$2y$10$ECeDPL0VQN6NPePsOYCcMuq8hjsEui5akvmngJXv9DmgaEmxBYS5W', 'admin', NULL, NULL, 1, '2025-10-30 18:49:56', '2025-10-18 04:15:25', '2025-10-30 18:49:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `caja_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `iva` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta','transferencia','mixto') DEFAULT 'efectivo',
  `estado` enum('completada','pendiente','cancelada') DEFAULT 'completada',
  `notas` text DEFAULT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `negocio_id`, `usuario_id`, `caja_id`, `cliente_id`, `subtotal`, `descuento`, `iva`, `total`, `metodo_pago`, `estado`, `notas`, `fecha_venta`) VALUES
(1, 1, 1, 1, NULL, 5900.00, 0.00, 0.00, 5900.00, 'efectivo', 'completada', NULL, '2025-10-30 19:26:18'),
(2, 1, 1, 1, NULL, 3200.00, 0.00, 0.00, 3200.00, 'efectivo', 'completada', NULL, '2025-10-30 19:27:02'),
(3, 1, 1, 1, NULL, 3100.00, 0.00, 0.00, 3100.00, 'efectivo', 'completada', NULL, '2025-10-30 19:28:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL,
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
(6, 3, 3, 1.00, 200.00, 200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios_old`
--

CREATE TABLE `negocios_old` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
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
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ver_productos` tinyint(1) DEFAULT 1,
  `crear_productos` tinyint(1) DEFAULT 0,
  `editar_productos` tinyint(1) DEFAULT 0,
  `eliminar_productos` tinyint(1) DEFAULT 0,
  `ver_ventas` tinyint(1) DEFAULT 1,
  `crear_ventas` tinyint(1) DEFAULT 1,
  `cancelar_ventas` tinyint(1) DEFAULT 0,
  `ver_pedidos` tinyint(1) DEFAULT 0,
  `gestionar_pedidos` tinyint(1) DEFAULT 0,
  `ver_gastos` tinyint(1) DEFAULT 0,
  `crear_gastos` tinyint(1) DEFAULT 0,
  `ver_empleados` tinyint(1) DEFAULT 0,
  `crear_empleados` tinyint(1) DEFAULT 0,
  `ver_reportes` tinyint(1) DEFAULT 0,
  `gestionar_caja` tinyint(1) DEFAULT 0,
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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`),
  ADD KEY `caja_id` (`caja_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `codigo_barras` (`codigo_barras`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `negocio_id` (`negocio_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `caja_id` (`caja_id`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `negocios_old`
--
ALTER TABLE `negocios_old`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `negocios_old`
--
ALTER TABLE `negocios_old`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
