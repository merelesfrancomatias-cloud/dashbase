-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-03-2026 a las 01:21:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dashbase_local`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(20) NOT NULL,
  `tabla` varchar(60) NOT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `datos_antes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_antes`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `negocio_id`, `usuario_id`, `accion`, `tabla`, `registro_id`, `datos_antes`, `datos_nuevos`, `ip`, `user_agent`, `created_at`) VALUES
(1, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 03:55:06'),
(2, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 03:58:48'),
(3, 1, 1, 'create', 'productos', 1, NULL, '{\"nombre\":\"Coca cola 500ML\",\"descripcion\":\"\",\"categoria_id\":null,\"codigo_barras\":\"241321411\",\"precio_costo\":1500,\"precio_venta\":2000,\"stock\":3,\"stock_minimo\":1,\"unidad_medida\":\"unidad\",\"foto\":\"prod_69a1173509b2e_1772164917.png\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 04:02:29'),
(4, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 04:13:38'),
(5, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 04:13:50'),
(6, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 04:23:15'),
(7, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 04:41:27'),
(8, 1, 1, 'create', 'ventas', 1, NULL, '{\"metodo_pago\":\"efectivo\",\"total_items\":1}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 04:50:58'),
(9, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 05:01:59'),
(10, 1, 1, 'create', 'categorias', 1, NULL, '{\"nombre\":\"Bebidas\",\"descripcion\":\"\",\"color\":\"#00BCD4\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 05:05:16'),
(11, 1, 1, 'create', 'ventas', 2, NULL, '{\"metodo_pago\":\"transferencia\",\"total_items\":1}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 05:13:09'),
(12, 1, 1, 'create', 'gastos', 1, NULL, '{\"descripcion\":\"Pago de alquiler del local\",\"monto\":15000,\"fecha_gasto\":\"2026-02-27\",\"categoria\":\"alquiler\",\"metodo_pago\":\"transferencia\",\"comprobante\":\"REC-001\"}', '::1', 'curl/8.7.1', '2026-02-27 05:30:50'),
(13, 1, 1, 'update', 'gastos', 1, '{\"id\":1,\"negocio_id\":1,\"usuario_id\":1,\"caja_id\":null,\"categoria\":\"alquiler\",\"descripcion\":\"Pago de alquiler del local\",\"monto\":\"15000.00\",\"metodo_pago\":\"transferencia\",\"fecha_gasto\":\"2026-02-27\",\"comprobante\":\"REC-001\",\"fecha_creacion\":\"2026-02-27 02:30:50\"}', '{\"id\":1,\"descripcion\":\"Alquiler local - Febrero 2026\",\"monto\":16000,\"fecha_gasto\":\"2026-02-27\",\"categoria\":\"alquiler\",\"metodo_pago\":\"transferencia\",\"comprobante\":\"REC-002\"}', '::1', 'curl/8.7.1', '2026-02-27 05:31:20'),
(14, 1, 1, 'logout', 'usuarios', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 12:56:34'),
(15, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 12:57:50'),
(16, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 13:13:27'),
(17, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 13:16:54'),
(18, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 13:20:01'),
(19, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 13:30:23'),
(20, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 13:33:47'),
(21, 3, 4, 'logout', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 13:37:18'),
(22, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 17:02:57'),
(23, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 17:26:08'),
(24, 3, 4, 'logout', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-27 17:36:12'),
(25, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-27 17:38:16'),
(26, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 17:40:14'),
(27, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 17:49:53'),
(28, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 17:59:01'),
(29, 4, 5, 'logout', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 18:38:35'),
(30, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 18:40:44'),
(31, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 18:55:28'),
(32, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 19:14:03'),
(33, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 19:17:35'),
(34, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 19:19:14'),
(35, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 19:19:21'),
(36, 4, 5, 'logout', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 19:21:53'),
(37, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 19:22:57'),
(38, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'curl/8.7.1', '2026-02-27 19:33:37'),
(39, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 19:34:18'),
(40, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 19:38:54'),
(41, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 20:59:50'),
(42, 9, 9, 'login', 'usuarios', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 21:08:41'),
(43, 9, 9, 'logout', 'usuarios', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 21:25:48'),
(44, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:05:26'),
(45, 4, 5, 'logout', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:05:37'),
(46, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:07:45'),
(47, 4, 5, 'logout', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:08:47'),
(48, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:09:04'),
(49, 3, 4, 'logout', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:10:03'),
(50, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:10:10'),
(51, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:10:42'),
(52, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:11:53'),
(53, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:16:05'),
(54, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:18:37'),
(55, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 23:18:44'),
(56, 8, 8, 'login', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 00:56:20'),
(57, 11, 11, 'login', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 01:20:43'),
(58, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 03:12:17'),
(59, 8, 8, 'login', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 20:48:27'),
(60, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 21:23:18'),
(61, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 21:23:50'),
(62, 11, 11, 'login', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 21:58:50'),
(63, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 22:00:31'),
(64, 4, 5, 'logout', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 22:13:15'),
(65, 12, 12, 'login', 'usuarios', 12, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 22:14:15'),
(66, 8, 8, 'login', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', '2026-02-28 22:33:41'),
(67, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 23:39:40'),
(68, 3, 4, 'logout', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 03:20:36'),
(69, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 03:24:57'),
(70, 8, 8, 'login', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 03:26:03'),
(71, 11, 11, 'login', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 03:26:48'),
(72, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 03:27:12'),
(73, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 03:30:10'),
(74, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 19:08:30'),
(75, 13, 13, 'login', 'usuarios', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 19:09:34'),
(76, 13, 13, 'logout', 'usuarios', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 19:44:36'),
(77, 14, 14, 'login', 'usuarios', 14, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-01 19:45:33'),
(78, 14, 14, 'login', 'usuarios', 14, NULL, NULL, '::1', 'curl/8.7.1', '2026-03-01 20:13:55'),
(79, 13, 13, 'login', 'usuarios', 13, NULL, NULL, '::1', 'curl/8.7.1', '2026-03-02 14:30:22'),
(80, 14, 14, 'logout', 'usuarios', 14, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-02 14:54:43'),
(81, 15, 15, 'login', 'usuarios', 15, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-02 14:55:45'),
(82, 15, 15, 'logout', 'usuarios', 15, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-02 15:04:20'),
(83, 15, 15, 'login', 'usuarios', 15, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-02 15:04:34'),
(84, 15, 15, 'logout', 'usuarios', 15, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-02 15:06:01'),
(85, 16, 16, 'login', 'usuarios', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-02 15:21:42'),
(86, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-02 19:54:22'),
(87, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '192.168.100.193', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-03 00:09:25'),
(88, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '192.168.100.193', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-03 00:13:34'),
(89, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '192.168.100.193', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-03 00:15:12'),
(90, 6, 6, 'create', 'productos', 67, NULL, '{\"nombre\":\"Cebolla\",\"descripcion\":\"negra\",\"categoria_id\":\"61\",\"codigo_barras\":\"321331321321\",\"precio_costo\":1000,\"precio_venta\":2000,\"stock\":3,\"stock_minimo\":1,\"unidad_medida\":\"kg\",\"foto\":\"prod_69a6294b02052_1772497227.jpg\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 00:20:29'),
(91, 6, 6, 'create', 'ventas', 3, NULL, '{\"metodo_pago\":\"tarjeta_debito\",\"total_items\":2}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 00:20:53'),
(92, 6, 6, 'create', 'gastos', 2, NULL, '{\"descripcion\":\"Luz\",\"monto\":23000,\"fecha_gasto\":\"2026-03-03\",\"categoria\":\"impuestos\",\"metodo_pago\":\"tarjeta\",\"comprobante\":\"202392033933\"}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 00:28:48'),
(93, 6, 6, 'create', 'gastos', 3, NULL, '{\"descripcion\":\"Aghua\",\"monto\":30000,\"fecha_gasto\":\"2026-03-03\",\"categoria\":\"impuestos\",\"metodo_pago\":\"tarjeta\",\"comprobante\":null,\"caja_id\":5}', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 00:41:43'),
(94, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 22:53:48'),
(95, 17, 17, 'login', 'usuarios', 17, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 22:55:02'),
(96, 17, 17, 'logout', 'usuarios', 17, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 22:59:37'),
(97, 8, 8, 'login', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 22:59:44'),
(98, 8, 8, 'logout', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:00:28'),
(99, 11, 11, 'login', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:00:44'),
(100, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:01:19'),
(101, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:02:49'),
(102, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:06:15'),
(103, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:26:09'),
(104, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:26:57'),
(105, 8, 8, 'login', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:27:09'),
(106, 11, 11, 'login', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:28:00'),
(107, 11, 11, 'logout', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-03 23:28:18'),
(108, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-04 17:42:24'),
(109, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-04 17:43:17'),
(110, 1, 1, 'logout', 'usuarios', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 02:47:55'),
(111, 18, 18, 'login', 'usuarios', 18, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 02:49:33'),
(112, 18, 18, 'logout', 'usuarios', 18, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 02:50:03'),
(113, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 02:50:10'),
(114, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 18:44:11'),
(115, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 19:12:11'),
(116, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 19:12:46'),
(117, 3, 4, 'logout', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 19:14:12'),
(118, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 19:14:24'),
(119, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 19:15:24'),
(120, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 19:16:44'),
(121, 4, 5, 'login', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 20:13:23'),
(122, 4, 5, 'create', 'productos', 68, NULL, '{\"nombre\":\"prueba prueba prueba\",\"descripcion\":\"\",\"categoria_id\":\"50\",\"codigo_barras\":\"123112233\",\"precio_costo\":1000,\"precio_venta\":2000,\"stock\":0,\"stock_minimo\":0,\"unidad_medida\":\"unidad\",\"foto\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 20:31:06'),
(123, 4, 5, 'update', 'productos', 68, '{\"id\":68,\"negocio_id\":4,\"categoria_id\":50,\"nombre\":\"prueba prueba prueba\",\"descripcion\":\"\",\"codigo_barras\":\"123112233\",\"precio_costo\":\"1000.00\",\"precio_venta\":\"2000.00\",\"stock\":0,\"stock_minimo\":0,\"unidad_medida\":\"unidad\",\"foto\":null,\"activo\":1,\"fecha_creacion\":\"2026-03-07 17:31:06\",\"fecha_actualizacion\":\"2026-03-07 17:31:06\",\"fecha_vencimiento\":null,\"proveedor_id\":null,\"ubicacion\":null,\"categoria_nombre\":\"Electricidad\",\"categoria_color\":\"#eab308\"}', '{\"nombre\":\"prueba prueba\",\"descripcion\":\"\",\"categoria_id\":\"50\",\"codigo_barras\":\"123112233\",\"precio_costo\":1000,\"precio_venta\":2000,\"stock\":0,\"stock_minimo\":0,\"unidad_medida\":\"unidad\",\"foto\":null,\"id\":\"68\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 20:31:19'),
(124, 4, 5, 'delete', 'productos', 68, '{\"id\":68,\"negocio_id\":4,\"categoria_id\":50,\"nombre\":\"prueba prueba\",\"descripcion\":\"\",\"codigo_barras\":\"123112233\",\"precio_costo\":\"1000.00\",\"precio_venta\":\"2000.00\",\"stock\":0,\"stock_minimo\":0,\"unidad_medida\":\"unidad\",\"foto\":null,\"activo\":1,\"fecha_creacion\":\"2026-03-07 17:31:06\",\"fecha_actualizacion\":\"2026-03-07 17:31:19\",\"fecha_vencimiento\":null,\"proveedor_id\":null,\"ubicacion\":null,\"categoria_nombre\":\"Electricidad\",\"categoria_color\":\"#eab308\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 20:31:33'),
(125, 4, 5, 'create', 'categorias', 112, NULL, '{\"nombre\":\"azul\",\"descripcion\":\"azul prueba\",\"color\":\"#2196F3\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 20:32:24'),
(126, 4, 5, 'update', 'categorias', 112, '{\"id\":112,\"negocio_id\":4,\"nombre\":\"azul\",\"descripcion\":\"azul prueba\",\"color\":\"#2196F3\",\"activo\":1,\"fecha_creacion\":\"2026-03-07 17:32:24\"}', '{\"nombre\":\"azul11111\",\"descripcion\":\"azul prueba\",\"color\":\"#2196F3\",\"id\":\"112\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 20:32:31'),
(127, 4, 5, 'delete', 'categorias', 112, '{\"id\":112,\"negocio_id\":4,\"nombre\":\"azul11111\",\"descripcion\":\"azul prueba\",\"color\":\"#2196F3\",\"activo\":1,\"fecha_creacion\":\"2026-03-07 17:32:24\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 20:32:34'),
(128, 4, 5, 'create', 'ventas', 4, NULL, '{\"metodo_pago\":\"transferencia\",\"total_items\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 21:51:07'),
(129, 4, 5, 'logout', 'usuarios', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-07 22:50:22'),
(130, 3, 4, 'login', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 19:31:31'),
(131, 3, 4, 'logout', 'usuarios', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 20:38:38'),
(132, 6, 6, 'login', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 20:39:18'),
(133, 6, 6, 'update', 'productos', 41, '{\"id\":41,\"negocio_id\":6,\"categoria_id\":57,\"nombre\":\"Aceite Girasol 1.5L\",\"descripcion\":\"Aceite de girasol primera prensada\",\"codigo_barras\":\"7791234500001\",\"precio_costo\":\"780.00\",\"precio_venta\":\"1250.00\",\"stock\":48,\"stock_minimo\":10,\"unidad_medida\":\"unidad\",\"foto\":null,\"activo\":1,\"fecha_creacion\":\"2026-02-27 15:43:47\",\"fecha_actualizacion\":\"2026-02-27 15:43:47\",\"fecha_vencimiento\":null,\"proveedor_id\":1,\"ubicacion\":\"Pasillo A-1\",\"categoria_nombre\":\"Almacén\",\"categoria_color\":\"#f59e0b\"}', '{\"nombre\":\"Aceite Girasol 1.5L\",\"descripcion\":\"Aceite de girasol primera prensada\",\"categoria_id\":\"57\",\"codigo_barras\":\"7791234500001\",\"precio_costo\":780,\"precio_venta\":1250,\"stock\":48,\"stock_minimo\":10,\"unidad_medida\":\"unidad\",\"foto\":null,\"id\":\"41\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 20:40:15'),
(134, 6, 6, 'create', 'ventas', 14, NULL, '{\"metodo_pago\":\"efectivo\",\"total_items\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 20:40:33'),
(135, 6, 6, 'delete', 'ventas', 3, NULL, '{\"anulada\":true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 20:46:56'),
(136, 6, 6, 'logout', 'usuarios', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-08 20:49:52'),
(137, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:55:28'),
(138, 7, 7, 'create', 'productos', 69, NULL, '{\"nombre\":\"corte de cabello\",\"descripcion\":\"\",\"categoria_id\":null,\"codigo_barras\":null,\"precio_costo\":6000,\"precio_venta\":12000,\"stock\":0,\"stock_minimo\":0,\"unidad_medida\":\"unidad\",\"foto\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:56:02'),
(139, 7, 7, 'create', 'categorias', 113, NULL, '{\"nombre\":\"corte hombre\",\"descripcion\":\"\",\"color\":\"#FF5252\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:56:14'),
(140, 7, 7, 'update', 'productos', 69, '{\"id\":69,\"negocio_id\":7,\"categoria_id\":null,\"nombre\":\"corte de cabello\",\"descripcion\":\"\",\"codigo_barras\":null,\"precio_costo\":\"6000.00\",\"precio_venta\":\"12000.00\",\"stock\":0,\"stock_minimo\":0,\"unidad_medida\":\"unidad\",\"foto\":null,\"activo\":1,\"fecha_creacion\":\"2026-03-09 18:56:02\",\"fecha_actualizacion\":\"2026-03-09 18:56:02\",\"fecha_vencimiento\":null,\"proveedor_id\":null,\"ubicacion\":null,\"categoria_nombre\":null,\"categoria_color\":null}', '{\"nombre\":\"corte de cabello\",\"descripcion\":\"\",\"categoria_id\":\"113\",\"codigo_barras\":null,\"precio_costo\":6000,\"precio_venta\":12000,\"stock\":0,\"stock_minimo\":0,\"unidad_medida\":\"unidad\",\"foto\":null,\"id\":\"69\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:56:23'),
(141, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:07:57'),
(142, 7, 7, 'login', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:08:14'),
(143, 7, 7, 'logout', 'usuarios', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:08:19'),
(144, 1, 1, 'login', 'usuarios', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:08:28'),
(145, 1, 1, 'logout', 'usuarios', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:08:38'),
(146, 8, 8, 'login', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 12:47:40'),
(147, 8, 8, 'logout', 'usuarios', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:00:45'),
(148, 11, 11, 'login', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:00:57'),
(149, 11, 11, 'logout', 'usuarios', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 13:02:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
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
(1, 1, 1, 2000.00, 4000.00, 0.00, 6000.00, 6000.00, 0.00, 'cerrada', '', '2026-02-27 04:02:42', '2026-02-27 12:17:27'),
(2, 1, 1, 0.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2026-02-27 12:18:14', NULL),
(3, 3, 4, 2000.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2026-02-27 13:37:06', NULL),
(4, 4, 5, 2000.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2026-02-27 19:19:52', NULL),
(5, 6, 6, 1700.00, 2820.00, 30000.00, -25480.00, 25000.00, 50480.00, 'cerrada', 'Quedo en caja', '2026-03-01 03:29:32', '2026-03-03 00:42:28'),
(6, 6, 6, 480.00, 0.00, 0.00, 480.00, 230.00, -250.00, 'cerrada', 'nos euqe paso', '2026-03-03 00:42:36', '2026-03-03 00:46:08'),
(7, 6, 6, 2000.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2026-03-03 00:46:38', NULL),
(8, 8, 8, 20000.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2026-03-03 23:00:17', NULL),
(9, 7, 7, 10000.00, 0.00, 0.00, NULL, NULL, NULL, 'abierta', NULL, '2026-03-09 21:56:37', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
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
(1, 1, 'Bebidas', '', '#00BCD4', 1, '2026-02-27 05:05:16'),
(2, 2, 'Bebidas', NULL, '#3b82f6', 1, '2026-02-27 12:40:57'),
(3, 2, 'Lácteos', NULL, '#f59e0b', 1, '2026-02-27 12:40:57'),
(4, 2, 'Panificados', NULL, '#d97706', 1, '2026-02-27 12:40:57'),
(5, 2, 'Limpieza', NULL, '#10b981', 1, '2026-02-27 12:40:57'),
(6, 2, 'Golosinas', NULL, '#ec4899', 1, '2026-02-27 12:40:57'),
(7, 2, 'Fiambrería', NULL, '#ef4444', 1, '2026-02-27 12:40:57'),
(8, 3, 'Entradas', NULL, '#10b981', 1, '2026-02-27 12:57:40'),
(9, 3, 'Platos Principales', NULL, '#ef4444', 1, '2026-02-27 12:57:40'),
(10, 3, 'Postres', NULL, '#f97316', 1, '2026-02-27 12:57:40'),
(11, 3, 'Bebidas', NULL, '#3b82f6', 1, '2026-02-27 12:57:40'),
(12, 3, 'Combos', NULL, '#8b5cf6', 1, '2026-02-27 12:57:40'),
(13, 3, 'Ensaladas', NULL, '#22c55e', 1, '2026-02-27 13:00:41'),
(14, 3, 'Sopas y Caldos', NULL, '#f59e0b', 1, '2026-02-27 13:00:41'),
(15, 3, 'Pastas', NULL, '#d97706', 1, '2026-02-27 13:00:41'),
(16, 3, 'Carnes', NULL, '#ef4444', 1, '2026-02-27 13:00:41'),
(17, 3, 'Aves', NULL, '#f97316', 1, '2026-02-27 13:00:41'),
(18, 3, 'Pescados y Mariscos', NULL, '#0ea5e9', 1, '2026-02-27 13:00:41'),
(19, 3, 'Pizzas', NULL, '#dc2626', 1, '2026-02-27 13:00:41'),
(20, 3, 'Sandwiches', NULL, '#84cc16', 1, '2026-02-27 13:00:41'),
(21, 3, 'Minutas', NULL, '#a3e635', 1, '2026-02-27 13:00:41'),
(22, 3, 'Guarniciones', NULL, '#65a30d', 1, '2026-02-27 13:00:41'),
(23, 3, 'Bebidas Sin Alcohol', NULL, '#3b82f6', 1, '2026-02-27 13:00:41'),
(24, 3, 'Bebidas Con Alcohol', NULL, '#7c3aed', 1, '2026-02-27 13:00:41'),
(25, 3, 'Cafetería', NULL, '#92400e', 1, '2026-02-27 13:00:41'),
(26, 3, 'Menú del Día', NULL, '#0891b2', 1, '2026-02-27 13:00:41'),
(27, 3, 'Combos y Promos', NULL, '#8b5cf6', 1, '2026-02-27 13:00:41'),
(28, 3, 'Para Llevar', NULL, '#64748b', 1, '2026-02-27 13:00:41'),
(49, 4, 'Herramientas Manuales', 'Martillos, destornilladores, llaves', '#f59e0b', 1, '2026-02-27 17:42:00'),
(50, 4, 'Electricidad', 'Cables, llaves, tomacorrientes, tableros', '#eab308', 1, '2026-02-27 17:42:00'),
(51, 4, 'Plomería', 'Caños, codos, llaves de paso, griferías', '#06b6d4', 1, '2026-02-27 17:42:00'),
(52, 4, 'Pinturas y Revestimientos', 'Pinturas, barnices, rodillos, pinceles', '#ef4444', 1, '2026-02-27 17:42:00'),
(53, 4, 'Fijaciones y Herrajes', 'Tornillos, clavos, bulones, tarugos', '#64748b', 1, '2026-02-27 17:42:00'),
(54, 4, 'Construcción', 'Cemento, arena, cal, yeso, membranas', '#78716c', 1, '2026-02-27 17:42:00'),
(55, 4, 'Herramientas Eléctricas', 'Taladros, amoladoras, sierras', '#8b5cf6', 1, '2026-02-27 17:42:00'),
(56, 4, 'Seguridad Industrial', 'Guantes, cascos, antiparras, botines', '#dc2626', 1, '2026-02-27 17:42:00'),
(57, 6, 'Almacén', 'Aceites, conservas, pastas, arroz', '#f59e0b', 1, '2026-02-27 18:41:50'),
(58, 6, 'Bebidas', 'Gaseosas, aguas, jugos, vinos', '#3b82f6', 1, '2026-02-27 18:41:50'),
(59, 6, 'Lácteos', 'Leche, quesos, yogures, manteca', '#e2e8f0', 1, '2026-02-27 18:41:50'),
(60, 6, 'Carnicería', 'Cortes, achuras, embutidos', '#ef4444', 1, '2026-02-27 18:41:50'),
(61, 6, 'Verdulería', 'Frutas y verduras frescas', '#22c55e', 1, '2026-02-27 18:41:50'),
(62, 6, 'Panadería', 'Pan, facturas, galletitas', '#d97706', 1, '2026-02-27 18:41:50'),
(63, 6, 'Limpieza', 'Detergentes, lavandina, desinfectantes', '#8b5cf6', 1, '2026-02-27 18:41:50'),
(64, 6, 'Perfumería', 'Higiene personal, cosméticos', '#ec4899', 1, '2026-02-27 18:41:50'),
(65, 6, 'Congelados', 'Helados, pizzas, precocidos', '#0ea5e9', 1, '2026-02-27 18:41:50'),
(66, 6, 'Fiambrería', 'Fiambres y quesos en trozo', '#f97316', 1, '2026-02-27 18:41:50'),
(67, 9, 'General', NULL, '#667eea', 1, '2026-02-27 21:08:01'),
(68, 9, 'Servicios', NULL, '#10b981', 1, '2026-02-27 21:08:01'),
(69, 9, 'Productos', NULL, '#3b82f6', 1, '2026-02-27 21:08:01'),
(70, 10, 'Herramientas', NULL, '#f59e0b', 1, '2026-02-27 23:07:13'),
(71, 10, 'Electricidad', NULL, '#eab308', 1, '2026-02-27 23:07:13'),
(72, 10, 'Plomería', NULL, '#06b6d4', 1, '2026-02-27 23:07:13'),
(73, 10, 'Pinturas', NULL, '#ef4444', 1, '2026-02-27 23:07:13'),
(74, 10, 'Fijaciones', NULL, '#64748b', 1, '2026-02-27 23:07:13'),
(75, 12, 'General', NULL, '#667eea', 1, '2026-02-28 22:14:06'),
(76, 12, 'Servicios', NULL, '#10b981', 1, '2026-02-28 22:14:06'),
(77, 12, 'Productos', NULL, '#3b82f6', 1, '2026-02-28 22:14:06'),
(78, 13, 'General', NULL, '#667eea', 1, '2026-03-01 19:09:22'),
(79, 13, 'Servicios', NULL, '#10b981', 1, '2026-03-01 19:09:22'),
(80, 13, 'Productos', NULL, '#3b82f6', 1, '2026-03-01 19:09:22'),
(81, 14, 'General', NULL, '#667eea', 1, '2026-03-01 19:45:18'),
(82, 14, 'Servicios', NULL, '#10b981', 1, '2026-03-01 19:45:18'),
(83, 14, 'Productos', NULL, '#3b82f6', 1, '2026-03-01 19:45:18'),
(84, 15, 'General', NULL, '#667eea', 1, '2026-03-02 14:55:35'),
(85, 15, 'Servicios', NULL, '#10b981', 1, '2026-03-02 14:55:35'),
(86, 15, 'Productos', NULL, '#3b82f6', 1, '2026-03-02 14:55:35'),
(87, 16, 'Celulares', NULL, '#3b82f6', 1, '2026-03-02 15:21:34'),
(88, 16, 'Accesorios', NULL, '#6366f1', 1, '2026-03-02 15:21:34'),
(89, 16, 'Computadoras', NULL, '#64748b', 1, '2026-03-02 15:21:34'),
(90, 16, 'Servicios', NULL, '#10b981', 1, '2026-03-02 15:21:34'),
(91, 17, 'Entradas', NULL, '#10b981', 1, '2026-03-03 22:54:52'),
(92, 17, 'Ensaladas', NULL, '#22c55e', 1, '2026-03-03 22:54:52'),
(93, 17, 'Sopas y Caldos', NULL, '#f59e0b', 1, '2026-03-03 22:54:52'),
(94, 17, 'Pastas', NULL, '#d97706', 1, '2026-03-03 22:54:52'),
(95, 17, 'Carnes', NULL, '#ef4444', 1, '2026-03-03 22:54:52'),
(96, 17, 'Aves', NULL, '#f97316', 1, '2026-03-03 22:54:52'),
(97, 17, 'Pescados y Mariscos', NULL, '#0ea5e9', 1, '2026-03-03 22:54:52'),
(98, 17, 'Pizzas', NULL, '#dc2626', 1, '2026-03-03 22:54:52'),
(99, 17, 'Sandwiches', NULL, '#84cc16', 1, '2026-03-03 22:54:52'),
(100, 17, 'Minutas', NULL, '#a3e635', 1, '2026-03-03 22:54:52'),
(101, 17, 'Guarniciones', NULL, '#65a30d', 1, '2026-03-03 22:54:52'),
(102, 17, 'Postres', NULL, '#ec4899', 1, '2026-03-03 22:54:52'),
(103, 17, 'Bebidas Sin Alcohol', NULL, '#3b82f6', 1, '2026-03-03 22:54:52'),
(104, 17, 'Bebidas Con Alcohol', NULL, '#7c3aed', 1, '2026-03-03 22:54:52'),
(105, 17, 'Cafetería', NULL, '#92400e', 1, '2026-03-03 22:54:52'),
(106, 17, 'Menú del Día', NULL, '#0891b2', 1, '2026-03-03 22:54:52'),
(107, 17, 'Combos y Promos', NULL, '#8b5cf6', 1, '2026-03-03 22:54:52'),
(108, 17, 'Para Llevar', NULL, '#64748b', 1, '2026-03-03 22:54:52'),
(109, 18, 'General', NULL, '#667eea', 1, '2026-03-07 02:49:24'),
(110, 18, 'Servicios', NULL, '#10b981', 1, '2026-03-07 02:49:24'),
(111, 18, 'Productos', NULL, '#3b82f6', 1, '2026-03-07 02:49:24'),
(113, 7, 'corte hombre', '', '#FF5252', 1, '2026-03-09 21:56:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `codigo_cliente` varchar(50) DEFAULT NULL,
  `tipo` enum('persona','empresa') DEFAULT 'persona',
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `celular` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'Argentina',
  `notas` text DEFAULT NULL,
  `categoria` enum('regular','frecuente','vip','mayorista') DEFAULT 'regular',
  `descuento_especial` decimal(5,2) DEFAULT 0.00,
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `saldo_actual` decimal(10,2) DEFAULT 0.00,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_ultima_compra` timestamp NULL DEFAULT NULL,
  `total_compras` decimal(10,2) DEFAULT 0.00,
  `cantidad_compras` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `negocio_id`, `codigo_cliente`, `tipo`, `nombre`, `apellido`, `razon_social`, `documento`, `telefono`, `celular`, `fecha_nacimiento`, `email`, `direccion`, `ciudad`, `provincia`, `codigo_postal`, `pais`, `notas`, `categoria`, `descuento_especial`, `limite_credito`, `saldo_actual`, `estado`, `fecha_ultima_compra`, `total_compras`, `cantidad_compras`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 4, NULL, 'persona', 'Roberto', 'Construcciones SA', NULL, NULL, '2215551234', NULL, NULL, 'robert@constsa.com', 'Av. Ituzaingó 1500', NULL, NULL, NULL, 'Argentina', NULL, 'regular', 0.00, 0.00, 0.00, 'activo', NULL, 0.00, 0, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39'),
(2, 4, NULL, 'persona', 'Carlos', 'Domínguez', NULL, NULL, '2215552345', NULL, NULL, 'carlos.dom@gmail.com', 'Belgrano 850', NULL, NULL, NULL, 'Argentina', NULL, 'regular', 0.00, 0.00, 0.00, 'activo', NULL, 0.00, 0, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39'),
(3, 4, NULL, 'persona', 'Obra', 'Villa Norte', NULL, NULL, '2215553456', NULL, NULL, 'obra.villnorte@mail.com', 'Calle 44 N°1200', NULL, NULL, NULL, 'Argentina', NULL, 'regular', 0.00, 0.00, 0.00, 'activo', NULL, 0.00, 0, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39'),
(4, 4, NULL, 'persona', 'Laura', 'Pérez', NULL, NULL, '2215554567', NULL, NULL, 'lperez@gmail.com', 'San Martín 320', NULL, NULL, NULL, 'Argentina', NULL, 'regular', 0.00, 0.00, 0.00, 'activo', NULL, 0.00, 0, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39'),
(5, 4, NULL, 'persona', 'Plomería', 'González e Hijos', NULL, NULL, '2215555678', NULL, NULL, 'plomgonzalez@gmail.com', 'Corrientes 780', NULL, NULL, NULL, 'Argentina', NULL, 'regular', 0.00, 0.00, 0.00, 'activo', NULL, 0.00, 0, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_canchas`
--

CREATE TABLE `clientes_canchas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `telefono` varchar(40) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_peluqueria`
--

CREATE TABLE `clientes_peluqueria` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `telefono` varchar(40) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes_peluqueria`
--

INSERT INTO `clientes_peluqueria` (`id`, `negocio_id`, `nombre`, `telefono`, `email`, `notas`, `created_at`) VALUES
(1, 7, 'pancho', '3718563124', '', '', '2026-03-09 21:59:19'),
(2, 7, 'abi', '378181554', '', '', '2026-03-09 22:00:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_enum`
--

CREATE TABLE `config_enum` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `grupo` varchar(60) NOT NULL COMMENT 'ej: metodos_pago, unidades_medida, categorias_gasto',
  `valor` varchar(100) NOT NULL,
  `etiqueta` varchar(150) NOT NULL COMMENT 'Texto visible al usuario',
  `orden` smallint(6) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `es_sistema` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = valor por defecto, no se puede borrar',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `config_enum`
--

INSERT INTO `config_enum` (`id`, `negocio_id`, `grupo`, `valor`, `etiqueta`, `orden`, `activo`, `es_sistema`, `created_at`, `updated_at`) VALUES
(1, 1, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(2, 1, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(3, 1, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(4, 1, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(5, 1, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(6, 1, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(7, 1, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(8, 1, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(9, 1, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(10, 1, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(11, 1, 'unidades_medida', 'caja', 'Caja', 6, 1, 0, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(12, 1, 'categorias_gasto', 'proveedor', 'Proveedor / Compra', 1, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(13, 1, 'categorias_gasto', 'servicios', 'Servicios', 2, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(14, 1, 'categorias_gasto', 'alquiler', 'Alquiler', 3, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(15, 1, 'categorias_gasto', 'sueldos', 'Sueldos', 4, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(16, 1, 'categorias_gasto', 'otros', 'Otros', 8, 1, 1, '2026-02-27 03:53:59', '2026-02-27 03:53:59'),
(33, 2, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(34, 2, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(35, 2, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(36, 2, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(37, 2, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(38, 2, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(39, 2, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(40, 2, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(41, 2, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(42, 2, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(43, 2, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(44, 2, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(45, 2, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(46, 3, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(47, 3, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(48, 3, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(49, 3, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(50, 3, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(51, 3, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(52, 3, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(53, 3, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(54, 3, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(55, 3, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(56, 3, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(57, 3, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(58, 3, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-02-27 12:57:40', '2026-02-27 12:57:40'),
(59, 3, 'unidad_medida', 'porcion', 'Porción', 10, 1, 1, '2026-02-27 13:00:41', '2026-02-27 13:00:41'),
(60, 3, 'unidad_medida', 'plato', 'Plato', 11, 1, 1, '2026-02-27 13:00:41', '2026-02-27 13:00:41'),
(61, 3, 'unidad_medida', 'media', 'Media Porción', 12, 1, 1, '2026-02-27 13:00:41', '2026-02-27 13:00:41'),
(62, 3, 'unidad_medida', 'docena', 'Docena', 13, 1, 1, '2026-02-27 13:00:41', '2026-02-27 13:00:41'),
(63, 3, 'unidad_medida', 'litro_jarra', 'Jarra (1L)', 14, 1, 1, '2026-02-27 13:00:41', '2026-02-27 13:00:41'),
(64, 3, 'unidad_medida', 'copa', 'Copa', 15, 1, 1, '2026-02-27 13:00:41', '2026-02-27 13:00:41'),
(65, 3, 'unidad_medida', 'taza', 'Taza', 16, 1, 1, '2026-02-27 13:00:41', '2026-02-27 13:00:41'),
(66, 3, 'restaurant', 'ultimo_numero_comanda', '0', 1, 1, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(67, 4, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(68, 4, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(69, 4, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(70, 4, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(71, 4, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(72, 4, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(73, 4, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(74, 4, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(75, 4, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(76, 4, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(77, 4, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(78, 4, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(79, 4, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-02-27 17:38:05', '2026-02-27 17:38:05'),
(80, 9, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(81, 9, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(82, 9, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(83, 9, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(84, 9, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(85, 9, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(86, 9, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(87, 9, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(88, 9, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(89, 9, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(90, 9, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(91, 9, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(92, 9, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-02-27 21:08:01', '2026-02-27 21:08:01'),
(93, 10, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(94, 10, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(95, 10, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(96, 10, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(97, 10, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(98, 10, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(99, 10, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(100, 10, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(101, 10, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(102, 10, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(103, 10, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(104, 10, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(105, 10, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(106, 12, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(107, 12, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(108, 12, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(109, 12, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(110, 12, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(111, 12, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(112, 12, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(113, 12, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(114, 12, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(115, 12, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(116, 12, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(117, 12, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(118, 12, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-02-28 22:14:06', '2026-02-28 22:14:06'),
(119, 13, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(120, 13, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(121, 13, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(122, 13, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(123, 13, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(124, 13, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(125, 13, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(126, 13, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(127, 13, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(128, 13, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(129, 13, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(130, 13, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(131, 13, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-03-01 19:09:22', '2026-03-01 19:09:22'),
(132, 14, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(133, 14, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(134, 14, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(135, 14, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(136, 14, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(137, 14, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(138, 14, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(139, 14, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(140, 14, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(141, 14, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(142, 14, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(143, 14, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(144, 14, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-03-01 19:45:18', '2026-03-01 19:45:18'),
(145, 15, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(146, 15, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(147, 15, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(148, 15, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(149, 15, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(150, 15, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(151, 15, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(152, 15, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(153, 15, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(154, 15, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(155, 15, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(156, 15, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(157, 15, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-03-02 14:55:35', '2026-03-02 14:55:35'),
(158, 16, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(159, 16, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(160, 16, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(161, 16, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(162, 16, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(163, 16, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(164, 16, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(165, 16, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(166, 16, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(167, 16, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(168, 16, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(169, 16, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(170, 16, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-03-02 15:21:34', '2026-03-02 15:21:34'),
(171, 17, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(172, 17, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(173, 17, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(174, 17, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(175, 17, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(176, 17, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(177, 17, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(178, 17, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(179, 17, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(180, 17, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(181, 17, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(182, 17, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(183, 17, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-03-03 22:54:52', '2026-03-03 22:54:52'),
(184, 18, 'metodos_pago', 'efectivo', 'Efectivo', 1, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(185, 18, 'metodos_pago', 'tarjeta_debito', 'Tarjeta Débito', 2, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(186, 18, 'metodos_pago', 'tarjeta_credito', 'Tarjeta Crédito', 3, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(187, 18, 'metodos_pago', 'transferencia', 'Transferencia', 4, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(188, 18, 'metodos_pago', 'mercado_pago', 'Mercado Pago', 5, 1, 0, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(189, 18, 'unidades_medida', 'unidad', 'Unidad', 1, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(190, 18, 'unidades_medida', 'kg', 'Kilogramo', 2, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(191, 18, 'unidades_medida', 'g', 'Gramo', 3, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(192, 18, 'unidades_medida', 'lt', 'Litro', 4, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(193, 18, 'unidades_medida', 'ml', 'Mililitro', 5, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(194, 18, 'unidades_medida', 'caja', 'Caja', 6, 1, 1, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(195, 18, 'unidades_medida', 'par', 'Par', 7, 1, 0, '2026-03-07 02:49:24', '2026-03-07 02:49:24'),
(196, 18, 'unidades_medida', 'metro', 'Metro', 8, 1, 0, '2026-03-07 02:49:24', '2026-03-07 02:49:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id`, `venta_id`, `negocio_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 1, 1.00, 2000.00, 2000.00),
(2, 2, 1, 1, 1.00, 2000.00, 2000.00),
(3, 3, 6, 67, 1.00, 2000.00, 2000.00),
(4, 3, 6, 51, 1.00, 820.00, 820.00),
(5, 4, 4, 23, 1.00, 380.00, 380.00),
(6, 4, 4, 18, 1.00, 1300.00, 1300.00),
(7, 13, 3, 7, 1.00, 350.00, 350.00),
(8, 13, 3, 11, 2.00, 1400.00, 2800.00),
(9, 14, 6, 49, 1.00, 350.00, 350.00),
(10, 14, 6, 42, 1.00, 620.00, 620.00),
(11, 14, 6, 67, 1.00, 2000.00, 2000.00),
(12, 14, 6, 51, 1.00, 820.00, 820.00),
(13, 14, 6, 43, 1.00, 480.00, 480.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `negocio_id`, `nombre`, `apellido`, `email`, `telefono`, `cargo`, `activo`, `fecha_creacion`) VALUES
(1, 7, 'Laura', 'González', NULL, NULL, 'Estilista', 1, '2026-02-27 19:37:38'),
(2, 7, 'Marcela', 'Rodríguez', NULL, NULL, 'Colorista', 1, '2026-02-27 19:37:38'),
(3, 7, 'Sofía', 'Pérez', NULL, NULL, 'Manicurista', 1, '2026-02-27 19:37:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `farmacia_laboratorios`
--

CREATE TABLE `farmacia_laboratorios` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `contacto` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `condicion_pago` varchar(100) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `farmacia_recetas`
--

CREATE TABLE `farmacia_recetas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `numero_receta` varchar(50) DEFAULT NULL,
  `medico` varchar(200) DEFAULT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `paciente` varchar(200) DEFAULT NULL,
  `dni_paciente` varchar(20) DEFAULT NULL,
  `obra_social` varchar(150) DEFAULT NULL,
  `nro_afiliado` varchar(80) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('pendiente','despachada','vencida','anulada') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `farmacia_receta_items`
--

CREATE TABLE `farmacia_receta_items` (
  `id` int(11) NOT NULL,
  `receta_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `medicamento` varchar(255) NOT NULL,
  `presentacion` varchar(100) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 1,
  `indicaciones` text DEFAULT NULL,
  `dispensado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `caja_id` int(11) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT 'otros',
  `descripcion` text DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `fecha_gasto` date NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `negocio_id`, `usuario_id`, `caja_id`, `categoria`, `descripcion`, `monto`, `metodo_pago`, `fecha_gasto`, `comprobante`, `fecha_creacion`) VALUES
(1, 1, 1, NULL, 'alquiler', 'Alquiler local - Febrero 2026', 16000.00, 'transferencia', '2026-02-27', 'REC-002', '2026-02-27 05:30:50'),
(2, 6, 6, NULL, 'impuestos', 'Luz', 23000.00, 'tarjeta', '2026-03-03', '202392033933', '2026-03-03 00:28:48'),
(3, 6, 6, 5, 'impuestos', 'Aghua', 30000.00, 'tarjeta', '2026-03-03', NULL, '2026-03-03 00:41:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gym_asistencias`
--

CREATE TABLE `gym_asistencias` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gym_asistencias`
--

INSERT INTO `gym_asistencias` (`id`, `negocio_id`, `socio_id`, `fecha`, `hora`, `created_at`) VALUES
(1, 8, 1, '2026-02-27', '07:00:00', '2026-02-28 00:56:04'),
(2, 8, 2, '2026-02-27', '08:00:00', '2026-02-28 00:56:04'),
(3, 8, 3, '2026-02-27', '09:00:00', '2026-02-28 00:56:04'),
(4, 8, 5, '2026-02-27', '10:00:00', '2026-02-28 00:56:04'),
(5, 8, 6, '2026-02-27', '11:00:00', '2026-02-28 00:56:04'),
(6, 8, 7, '2026-02-27', '12:00:00', '2026-02-28 00:56:04'),
(7, 8, 3, '2026-02-28', '22:07:00', '2026-02-28 01:07:17'),
(8, 8, 7, '2026-02-28', '22:08:00', '2026-02-28 01:08:33'),
(9, 8, 1, '2026-02-28', '22:08:00', '2026-02-28 01:08:37'),
(10, 8, 3, '2026-03-10', '09:49:00', '2026-03-10 12:49:47'),
(11, 8, 7, '2026-03-10', '09:51:00', '2026-03-10 12:51:47'),
(12, 8, 2, '2026-03-10', '09:58:00', '2026-03-10 12:58:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gym_clases`
--

CREATE TABLE `gym_clases` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `dia_semana` tinyint(4) DEFAULT 0,
  `hora_inicio` time DEFAULT '09:00:00',
  `duracion_min` int(11) DEFAULT 60,
  `capacidad` int(11) DEFAULT 20,
  `color` varchar(20) DEFAULT '#f97316',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gym_clases`
--

INSERT INTO `gym_clases` (`id`, `negocio_id`, `nombre`, `instructor`, `dia_semana`, `hora_inicio`, `duracion_min`, `capacidad`, `color`, `activo`, `created_at`) VALUES
(1, 8, 'Spinning', 'Prof. Marcos', 0, '08:00:00', 50, 15, '#ef4444', 1, '2026-02-28 00:56:04'),
(2, 8, 'Yoga', 'Prof. Lucía', 0, '10:00:00', 60, 12, '#10b981', 1, '2026-02-28 00:56:04'),
(3, 8, 'CrossFit', 'Prof. Diego', 1, '07:00:00', 60, 20, '#f97316', 1, '2026-02-28 00:56:04'),
(4, 8, 'Zumba', 'Prof. Carla', 2, '19:00:00', 60, 25, '#ec4899', 1, '2026-02-28 00:56:04'),
(5, 8, 'Pilates', 'Prof. Lucía', 3, '10:00:00', 55, 10, '#8b5cf6', 1, '2026-02-28 00:56:04'),
(6, 8, 'BoxFit', 'Prof. Marcos', 4, '18:00:00', 60, 15, '#ef4444', 1, '2026-02-28 00:56:04'),
(7, 8, 'Funcional', 'Prof. Diego', 5, '09:00:00', 60, 20, '#3b82f6', 1, '2026-02-28 00:56:04'),
(8, 8, 'Stretching', 'Prof. Lucía', 6, '10:00:00', 45, 15, '#06b6d4', 1, '2026-02-28 00:56:04'),
(9, 8, 'Premium 1', 'pancho', 0, '09:48:00', 60, 20, '#f97316', 1, '2026-03-10 12:49:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gym_pagos`
--

CREATE TABLE `gym_pagos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha` date NOT NULL,
  `metodo` enum('efectivo','transferencia','tarjeta','otro') DEFAULT 'efectivo',
  `periodo_desde` date DEFAULT NULL,
  `periodo_hasta` date DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gym_pagos`
--

INSERT INTO `gym_pagos` (`id`, `negocio_id`, `socio_id`, `plan_id`, `monto`, `fecha`, `metodo`, `periodo_desde`, `periodo_hasta`, `notas`, `created_at`) VALUES
(1, 8, 3, 5, 2500.00, '2026-03-10', 'efectivo', '2026-03-10', '2026-03-17', '', '2026-03-10 13:00:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gym_planes`
--

CREATE TABLE `gym_planes` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT 0.00,
  `duracion_dias` int(11) DEFAULT 30,
  `clases_semana` int(11) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#f97316',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gym_planes`
--

INSERT INTO `gym_planes` (`id`, `negocio_id`, `nombre`, `descripcion`, `precio`, `duracion_dias`, `clases_semana`, `color`, `activo`, `created_at`) VALUES
(1, 8, 'Mensual Básico', 'Acceso libre horario regular', 8000.00, 30, 3, '#3b82f6', 1, '2026-02-28 00:56:04'),
(2, 8, 'Mensual Full', 'Acceso ilimitado + clases grupales', 12000.00, 30, NULL, '#f97316', 1, '2026-02-28 00:56:04'),
(3, 8, 'Trimestral', '3 meses con descuento especial', 30000.00, 90, NULL, '#10b981', 1, '2026-02-28 00:56:04'),
(4, 8, 'Anual VIP', 'Plan anual todo incluido', 90000.00, 365, NULL, '#8b5cf6', 1, '2026-02-28 00:56:04'),
(5, 8, 'Semanal Prueba', 'Semana de prueba para nuevos', 2500.00, 7, NULL, '#64748b', 1, '2026-02-28 00:56:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gym_socios`
--

CREATE TABLE `gym_socios` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('activo','vencido','suspendido','inactivo') DEFAULT 'activo',
  `foto` varchar(255) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gym_socios`
--

INSERT INTO `gym_socios` (`id`, `negocio_id`, `nombre`, `apellido`, `email`, `telefono`, `fecha_nacimiento`, `plan_id`, `fecha_inicio`, `fecha_vencimiento`, `estado`, `foto`, `notas`, `created_at`) VALUES
(1, 8, 'Carlos', 'Rodríguez', 'carlos@mail.com', '351-4001122', NULL, 2, '2026-02-23', '2026-03-25', 'activo', NULL, NULL, '2026-02-28 00:56:04'),
(2, 8, 'María', 'González', 'maria@mail.com', '351-4003344', NULL, 1, '2026-02-08', '2026-03-10', 'activo', NULL, NULL, '2026-02-28 00:56:04'),
(3, 8, 'Lucas', 'Fernández', 'lucas@mail.com', '351-4005566', NULL, 5, '2026-03-10', '2026-03-17', 'activo', NULL, NULL, '2026-02-28 00:56:04'),
(4, 8, 'Sofía', 'Martínez', 'sofia@mail.com', '351-4007788', NULL, 2, '2026-01-24', '2026-02-23', 'vencido', NULL, NULL, '2026-02-28 00:56:04'),
(5, 8, 'Diego', 'López', 'diego@mail.com', '351-4009900', NULL, 1, '2026-02-13', '2026-03-15', 'activo', NULL, NULL, '2026-02-28 00:56:04'),
(6, 8, 'Valentina', 'Pérez', 'valentina@mail.com', '351-4112233', NULL, 2, '2026-02-18', '2026-03-20', 'activo', NULL, NULL, '2026-02-28 00:56:04'),
(7, 8, 'Matías', 'García', 'matias@mail.com', '351-4114455', NULL, 4, '2025-12-10', '2026-12-10', 'activo', NULL, NULL, '2026-02-28 00:56:04'),
(8, 8, 'Laura', 'Silva', 'laura@mail.com', '351-4116677', NULL, 1, '2026-01-14', '2026-02-13', 'vencido', NULL, NULL, '2026-02-28 00:56:04'),
(9, 8, 'Agustín', 'Romero', 'agustin@mail.com', '351-4118899', NULL, 2, '2026-02-26', '2026-03-28', 'activo', NULL, NULL, '2026-02-28 00:56:04'),
(10, 8, 'Camila', 'Torres', 'camila@mail.com', '351-4220011', NULL, 3, '2026-01-09', '2026-04-09', 'activo', NULL, NULL, '2026-02-28 00:56:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hospedaje_habitaciones`
--

CREATE TABLE `hospedaje_habitaciones` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `tipo` enum('simple','doble','triple','suite','cabaña','otro') NOT NULL DEFAULT 'doble',
  `piso` varchar(10) DEFAULT NULL,
  `capacidad` tinyint(4) NOT NULL DEFAULT 2,
  `precio_noche` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_hora` decimal(10,2) DEFAULT NULL COMMENT 'Para moteles — precio por hora',
  `descripcion` text DEFAULT NULL,
  `amenities` text DEFAULT NULL COMMENT 'JSON array: ["wifi","tv","aire","jacuzzi"]',
  `estado` enum('libre','ocupada','limpieza','mantenimiento','bloqueada') NOT NULL DEFAULT 'libre',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `hospedaje_habitaciones`
--

INSERT INTO `hospedaje_habitaciones` (`id`, `negocio_id`, `numero`, `nombre`, `tipo`, `piso`, `capacidad`, `precio_noche`, `precio_hora`, `descripcion`, `amenities`, `estado`, `activo`, `creado_at`) VALUES
(1, 13, '101', 'Suite Premiun', 'triple', '18', 3, 40000.00, 12000.00, NULL, '[\"wifi\",\"tv\",\"aire\",\"jacuzzi\",\"minibar\",\"desayuno\"]', 'libre', 1, '2026-03-01 16:13:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hospedaje_reservas`
--

CREATE TABLE `hospedaje_reservas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `habitacion_id` int(11) NOT NULL,
  `huesped_nombre` varchar(120) NOT NULL,
  `huesped_dni` varchar(20) DEFAULT NULL,
  `huesped_telefono` varchar(30) DEFAULT NULL,
  `huesped_email` varchar(100) DEFAULT NULL,
  `tipo_estadia` enum('noche','hora','semana') NOT NULL DEFAULT 'noche',
  `checkin_fecha` date NOT NULL,
  `checkin_hora` time NOT NULL DEFAULT '14:00:00',
  `checkout_fecha` date NOT NULL,
  `checkout_hora` time NOT NULL DEFAULT '10:00:00',
  `noches` smallint(6) NOT NULL DEFAULT 1,
  `personas` tinyint(4) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `seña` decimal(10,2) DEFAULT 0.00,
  `estado` enum('reservada','checkin','checkout','cancelada') NOT NULL DEFAULT 'reservada',
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `creado_at` datetime DEFAULT current_timestamp(),
  `actualizado_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_actividad`
--

CREATE TABLE `logs_actividad` (
  `id` bigint(20) NOT NULL,
  `negocio_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(120) NOT NULL,
  `detalle` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logs_actividad`
--

INSERT INTO `logs_actividad` (`id`, `negocio_id`, `usuario_id`, `accion`, `detalle`, `ip`, `user_agent`, `created_at`) VALUES
(1, NULL, 1, 'login_superadmin', 'Login exitoso desde ::1', '::1', 'Mozilla/5.0 (iPad; CPU OS 11_0 like Mac OS X) AppleWebKit/604.1.34 (KHTML, like Gecko) Version/11.0 Mobile/15A5341f Safari/604.1', '2026-02-28 23:36:33'),
(2, NULL, 1, 'logout_superadmin', 'Logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', '2026-02-28 23:54:03'),
(3, NULL, 1, 'login_superadmin', 'Login exitoso desde ::1', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', '2026-02-28 23:54:53'),
(4, NULL, 1, 'usuario_desactivado', 'Usuario ID 12', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', '2026-03-01 03:28:27'),
(5, NULL, 1, 'login_superadmin', 'Login exitoso desde ::1', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-04 14:35:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `filename`, `applied_at`) VALUES
(1, '001_schema_base.sql', '2026-02-27 03:54:16'),
(2, '002_perfil_negocio_tenant.sql', '2026-02-27 03:54:16'),
(3, '003_planes_suscripciones.sql', '2026-02-27 03:54:16'),
(4, '004_merge_negocios_perfil.sql', '2026-02-27 03:54:16'),
(5, '005_audit_logs.sql', '2026-02-27 03:54:16'),
(6, '006_config_enum.sql', '2026-02-27 03:54:16'),
(7, '001_rubros.sql', '2026-02-27 12:34:47'),
(8, '002_rubro_canchas.sql', '2026-02-27 12:51:55'),
(9, '003_gastronomia_completo.sql', '2026-02-27 13:00:41'),
(10, '004_restaurant_mesas_reservas.sql', '2026-02-27 13:03:00'),
(11, '003_veterinaria.sql', '2026-03-01 19:43:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios`
--

CREATE TABLE `negocios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `condicion_iva` varchar(50) DEFAULT NULL,
  `rubro` varchar(100) DEFAULT NULL,
  `rubro_id` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `carta_token` varchar(64) DEFAULT NULL,
  `carta_activa` tinyint(1) NOT NULL DEFAULT 1,
  `mensaje_ticket` text DEFAULT NULL,
  `mostrar_logo_ticket` tinyint(1) DEFAULT 1,
  `mostrar_direccion_ticket` tinyint(1) DEFAULT 1,
  `mostrar_cuit_ticket` tinyint(1) DEFAULT 1,
  `horarios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`horarios`)),
  `activo` tinyint(1) DEFAULT 1,
  `plan_id` int(11) DEFAULT 1,
  `fecha_vencimiento` date DEFAULT NULL,
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `bloqueado_motivo` varchar(255) DEFAULT NULL,
  `notas_admin` text DEFAULT NULL,
  `estado_suscripcion` enum('activa','vencida','cancelada','trial') DEFAULT 'trial',
  `trial_hasta` date DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_alta` date DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `negocios`
--

INSERT INTO `negocios` (`id`, `nombre`, `razon_social`, `cuit`, `condicion_iva`, `rubro`, `rubro_id`, `direccion`, `ciudad`, `provincia`, `codigo_postal`, `telefono`, `email`, `sitio_web`, `instagram`, `facebook`, `whatsapp`, `logo`, `imagen_portada`, `carta_token`, `carta_activa`, `mensaje_ticket`, `mostrar_logo_ticket`, `mostrar_direccion_ticket`, `mostrar_cuit_ticket`, `horarios`, `activo`, `plan_id`, `fecha_vencimiento`, `bloqueado`, `bloqueado_motivo`, `notas_admin`, `estado_suscripcion`, `trial_hasta`, `fecha_registro`, `fecha_alta`, `fecha_actualizacion`) VALUES
(1, 'Mi Negocio Demo', NULL, NULL, NULL, 'Comercio minorista', NULL, NULL, NULL, NULL, NULL, '+54 11 1234-5678', 'demo@dashbase.local', NULL, NULL, NULL, NULL, NULL, NULL, 'c1d49245d8204a4b8b60d1c7be563a405ccf92bc204f2880d27ef59b692b283d', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', NULL, '2026-02-27 03:53:59', NULL, '2026-03-01 00:38:21'),
(2, 'Almacén Don Juan', NULL, NULL, NULL, 'Almacén / Kiosco', 1, NULL, NULL, NULL, NULL, NULL, 'juan@test.com', NULL, NULL, NULL, NULL, NULL, NULL, 'fed67b9dccc9c40213b289aba59fd8362e094ab913898e7d748e27adaff03eb7', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-13', '2026-02-27 12:40:57', NULL, '2026-03-01 00:38:21'),
(3, 'CicloClub', NULL, NULL, NULL, 'Gastronomía / Bar', 4, NULL, NULL, NULL, NULL, NULL, 'MartinezdeOz@gmail.com', NULL, NULL, NULL, NULL, 'logo_69adde3f589c8_1773002303.png', NULL, '0d399a21bda6f020fd9c306f450b9a5dbad581ed66182c3d644d9dddb190a10a', 1, NULL, 1, 1, 1, '{\"lunes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"martes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"miercoles\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"jueves\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"viernes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"sabado\":{\"activo\":false,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"domingo\":{\"activo\":false,\"desde\":\"09:00\",\"hasta\":\"18:00\"}}', 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-13', '2026-02-27 12:57:40', NULL, '2026-03-08 20:38:25'),
(4, 'ferretex', NULL, NULL, NULL, 'Ferretería', 3, NULL, NULL, NULL, NULL, NULL, 'san@martin.cmo', NULL, NULL, NULL, NULL, 'logo_69ac9e51cad48_1772920401.png', NULL, '3b85b643f2c4f0aa43adf8acd5ae32de88f28416abf9074e9770e4c845d9e40a', 1, NULL, 1, 1, 1, '{\"lunes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"martes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"miercoles\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"jueves\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"viernes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"sabado\":{\"activo\":false,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"domingo\":{\"activo\":false,\"desde\":\"09:00\",\"hasta\":\"18:00\"}}', 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-13', '2026-02-27 17:38:05', NULL, '2026-03-07 21:53:23'),
(5, 'SuperDemo', NULL, NULL, NULL, 'Supermercado', 18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'd4231bba2b8415e513baa1ab33d6e184b872abe218451ccf777026aa092236b9', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', NULL, '2026-02-27 18:37:30', NULL, '2026-03-01 00:38:21'),
(6, 'SuperDemo', NULL, NULL, NULL, 'Supermercado', 18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9762181b722a8614ea9b87178dea171226951b67d597fa36ccacd3bb0baef18d', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', NULL, '2026-02-27 18:37:54', NULL, '2026-03-01 00:38:21'),
(7, 'Salón Glam', NULL, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'logo_69af436f36768_1773093743.png', NULL, '374ee253de73520bad6397fc887c768a64bf9e148a3225c9d692d346bea4ff6d', 1, NULL, 1, 1, 1, '{\"lunes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"martes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"miercoles\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"jueves\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"viernes\":{\"activo\":true,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"sabado\":{\"activo\":false,\"desde\":\"09:00\",\"hasta\":\"18:00\"},\"domingo\":{\"activo\":false,\"desde\":\"09:00\",\"hasta\":\"18:00\"}}', 1, 1, NULL, 0, NULL, NULL, 'trial', NULL, '2026-02-27 19:05:28', NULL, '2026-03-09 22:02:26'),
(8, 'FitZone Gym', NULL, NULL, NULL, NULL, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '363de21d0441a049fe877a3a9c3587d26297ebe2c25578dbcaccc881ff3d0de4', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', NULL, '2026-02-27 19:41:56', NULL, '2026-03-01 00:38:21'),
(9, 'gymdemo1', NULL, NULL, NULL, 'Gimnasio / Fitness', 19, NULL, NULL, NULL, NULL, NULL, 'miami@gym.com', NULL, NULL, NULL, NULL, NULL, NULL, '6fb4188b95417d521cd4654a6d5382db09f25e5771d309df66c9c9ddd00021cf', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-13', '2026-02-27 21:08:01', NULL, '2026-03-01 00:38:21'),
(10, 'Ferreteria Casia', NULL, NULL, NULL, 'Ferretería', 3, NULL, NULL, NULL, NULL, NULL, 'nuenz@casia.com', NULL, NULL, NULL, NULL, NULL, NULL, '42357fd1ed688328e6778f9d6a0c344455fbbd56a7105bfc5b321729ebeea9a4', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-13', '2026-02-27 23:07:13', NULL, '2026-03-01 00:38:21'),
(11, 'La Cancha Sportiva', NULL, NULL, NULL, NULL, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3a43bd82f9bc128bdb1262f80ce4b3b7c7bc532a3652c78fd0ceb3b32560227e', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', NULL, '2026-02-28 01:18:43', NULL, '2026-03-01 00:38:21'),
(12, 'Farmacris', NULL, NULL, NULL, 'Farmacia / Perfumería', 5, NULL, NULL, NULL, NULL, NULL, 'algo@nunez.com', NULL, NULL, NULL, NULL, NULL, NULL, '84ef64e03045d9f7d03f53b2be9b5493aa60c8528aa53ffb6dcd4dc014ecc0cb', 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-14', '2026-02-28 22:14:06', NULL, '2026-03-01 00:38:21'),
(13, 'Hotel Las Lenas', NULL, NULL, NULL, 'Hospedaje / Hotel', 20, NULL, NULL, NULL, NULL, NULL, 'hotel@clorinda.cm', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-15', '2026-03-01 19:09:22', NULL, '2026-03-01 19:09:22'),
(14, 'Mascoton', NULL, NULL, NULL, 'Veterinaria / Mascotas', 9, NULL, NULL, NULL, NULL, NULL, 'vetetuya@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-15', '2026-03-01 19:45:18', NULL, '2026-03-01 19:45:18'),
(15, 'OjosSanos', NULL, NULL, NULL, 'Óptica', 10, NULL, NULL, NULL, NULL, NULL, 'ea@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-16', '2026-03-02 14:55:35', NULL, '2026-03-02 14:55:35'),
(16, 'neotec', NULL, NULL, NULL, 'Tecnología / Electrónica', 6, NULL, NULL, NULL, NULL, NULL, 'neo@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-16', '2026-03-02 15:21:34', NULL, '2026-03-02 15:21:34'),
(17, 'RODRIGOREST', NULL, NULL, NULL, 'Gastronomía / Restaurant', 4, NULL, NULL, NULL, NULL, NULL, 'moqndow@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-17', '2026-03-03 22:54:52', NULL, '2026-03-03 22:54:52'),
(18, 'OscarGym', NULL, NULL, NULL, 'Gimnasio / Fitness', 19, NULL, NULL, NULL, NULL, NULL, 'wlnw@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, 1, NULL, 1, 1, NULL, 0, NULL, NULL, 'trial', '2026-03-20', '2026-03-07 02:49:23', NULL, '2026-03-07 02:49:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `optica_clientes`
--

CREATE TABLE `optica_clientes` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `apellido` varchar(120) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `obra_social` varchar(100) DEFAULT NULL,
  `nro_afiliado` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `optica_pedidos`
--

CREATE TABLE `optica_pedidos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `receta_id` int(11) DEFAULT NULL,
  `armazon` varchar(120) DEFAULT NULL COMMENT 'Marca y modelo del armazón',
  `armazon_color` varchar(60) DEFAULT NULL,
  `armazon_precio` decimal(12,2) DEFAULT 0.00,
  `lente_tipo` enum('monofocal','bifocal','progresivo','solar','contacto','sin_lente') DEFAULT 'monofocal',
  `lente_material` varchar(80) DEFAULT NULL COMMENT 'CR-39, Policarbonato, Trivex...',
  `lente_tratamiento` varchar(120) DEFAULT NULL COMMENT 'Antirreflejo, Fotocromático, UV400...',
  `lente_precio` decimal(12,2) DEFAULT 0.00,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `descuento` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `seña` decimal(12,2) DEFAULT 0.00,
  `saldo` decimal(12,2) DEFAULT 0.00,
  `metodo_pago` varchar(30) DEFAULT 'efectivo',
  `estado` enum('presupuesto','pendiente','laboratorio','listo','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `laboratorio` varchar(100) DEFAULT NULL,
  `fecha_envio_lab` date DEFAULT NULL,
  `fecha_entrega_est` date DEFAULT NULL COMMENT 'Fecha estimada de entrega',
  `fecha_entrega` date DEFAULT NULL COMMENT 'Fecha real de entrega',
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `optica_recetas`
--

CREATE TABLE `optica_recetas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `od_esfera` decimal(5,2) DEFAULT NULL COMMENT 'Esfera OD',
  `od_cilindro` decimal(5,2) DEFAULT NULL COMMENT 'Cilindro OD',
  `od_eje` smallint(6) DEFAULT NULL COMMENT 'Eje OD (0-180)',
  `od_adicion` decimal(5,2) DEFAULT NULL COMMENT 'Adición OD',
  `od_av` varchar(10) DEFAULT NULL COMMENT 'Agudeza visual OD',
  `oi_esfera` decimal(5,2) DEFAULT NULL COMMENT 'Esfera OI',
  `oi_cilindro` decimal(5,2) DEFAULT NULL COMMENT 'Cilindro OI',
  `oi_eje` smallint(6) DEFAULT NULL COMMENT 'Eje OI (0-180)',
  `oi_adicion` decimal(5,2) DEFAULT NULL COMMENT 'Adición OI',
  `oi_av` varchar(10) DEFAULT NULL COMMENT 'Agudeza visual OI',
  `dnp_od` decimal(4,1) DEFAULT NULL COMMENT 'Distancia nasopupilar OD',
  `dnp_oi` decimal(4,1) DEFAULT NULL COMMENT 'Distancia nasopupilar OI',
  `altura` decimal(4,1) DEFAULT NULL COMMENT 'Altura montaje',
  `tipo` enum('lejos','cerca','progresivo','solar','contacto') NOT NULL DEFAULT 'lejos',
  `medico` varchar(120) DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_compra`
--

CREATE TABLE `ordenes_compra` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `fecha_entrega_esperada` date DEFAULT NULL,
  `estado` enum('borrador','enviada','recibida','cancelada') DEFAULT 'borrador',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ordenes_compra`
--

INSERT INTO `ordenes_compra` (`id`, `negocio_id`, `proveedor_id`, `numero`, `fecha`, `fecha_entrega_esperada`, `estado`, `subtotal`, `total`, `notas`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 'OC-0001', '2026-03-03', '2026-03-04', 'enviada', 690.00, 690.00, 'Lo mas rapido posible', 6, '2026-03-03 00:31:45', '2026-03-03 00:31:50'),
(2, 6, 3, 'OC-0002', '2026-03-08', NULL, 'recibida', 2530.00, 2530.00, 'asdasd', 6, '2026-03-08 20:49:24', '2026-03-08 20:49:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_compra_items`
--

CREATE TABLE `ordenes_compra_items` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `recibido` decimal(10,3) DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ordenes_compra_items`
--

INSERT INTO `ordenes_compra_items` (`id`, `orden_id`, `producto_id`, `descripcion`, `cantidad`, `precio_unitario`, `subtotal`, `recibido`) VALUES
(1, 1, 56, 'Manteca 200g', 1.000, 360.00, 360.00, 0.000),
(2, 1, 44, 'Azúcar Común 1kg', 1.000, 330.00, 330.00, 0.000),
(3, 2, 43, 'Fideos Tallarín 500g', 8.000, 290.00, 2320.00, 1.000),
(4, 2, 47, 'Tomate en Lata 400g', 1.000, 210.00, 210.00, 1.000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `moneda` varchar(10) DEFAULT 'ARS',
  `metodo_pago` enum('efectivo','transferencia','mercadopago','otro') DEFAULT 'transferencia',
  `referencia` varchar(255) DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `notas` text DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_negocio`
--

CREATE TABLE `perfil_negocio` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
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
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ver_productos` tinyint(1) DEFAULT 1,
  `crear_productos` tinyint(1) DEFAULT 0,
  `editar_productos` tinyint(1) DEFAULT 0,
  `eliminar_productos` tinyint(1) DEFAULT 0,
  `ver_ventas` tinyint(1) DEFAULT 1,
  `crear_ventas` tinyint(1) DEFAULT 1,
  `cancelar_ventas` tinyint(1) DEFAULT 0,
  `ver_gastos` tinyint(1) DEFAULT 1,
  `crear_gastos` tinyint(1) DEFAULT 1,
  `ver_empleados` tinyint(1) DEFAULT 0,
  `crear_empleados` tinyint(1) DEFAULT 0,
  `ver_reportes` tinyint(1) DEFAULT 0,
  `gestionar_caja` tinyint(1) DEFAULT 1,
  `ver_mesas` tinyint(1) DEFAULT 0,
  `gestionar_mesas` tinyint(1) DEFAULT 0,
  `ver_reservas` tinyint(1) DEFAULT 0,
  `gestionar_reservas` tinyint(1) DEFAULT 0,
  `ver_cocina` tinyint(1) DEFAULT 0,
  `gestionar_cocina` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `usuario_id`, `ver_productos`, `crear_productos`, `editar_productos`, `eliminar_productos`, `ver_ventas`, `crear_ventas`, `cancelar_ventas`, `ver_gastos`, `crear_gastos`, `ver_empleados`, `crear_empleados`, `ver_reportes`, `gestionar_caja`, `ver_mesas`, `gestionar_mesas`, `ver_reservas`, `gestionar_reservas`, `ver_cocina`, `gestionar_cocina`) VALUES
(2, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(3, 4, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(5, 5, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(6, 9, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(7, 10, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(8, 12, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(9, 13, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(10, 14, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(11, 15, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(12, 16, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(13, 17, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0),
(14, 18, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `nombre_display` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_mensual` decimal(10,2) DEFAULT 0.00,
  `precio_anual` decimal(10,2) DEFAULT 0.00,
  `max_usuarios` smallint(6) DEFAULT 1,
  `max_productos` smallint(6) DEFAULT 50,
  `max_ventas_mes` int(11) DEFAULT 100,
  `tiene_reportes` tinyint(1) DEFAULT 0,
  `tiene_empleados` tinyint(1) DEFAULT 0,
  `tiene_clientes` tinyint(1) DEFAULT 0,
  `tiene_api_publica` tinyint(1) DEFAULT 0,
  `tiene_tienda_online` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `dias_gratis` int(11) NOT NULL DEFAULT 0,
  `color` varchar(20) DEFAULT '#0FD186',
  `icono` varchar(50) DEFAULT 'fa-star',
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `nombre`, `nombre_display`, `descripcion`, `precio_mensual`, `precio_anual`, `max_usuarios`, `max_productos`, `max_ventas_mes`, `tiene_reportes`, `tiene_empleados`, `tiene_clientes`, `tiene_api_publica`, `tiene_tienda_online`, `activo`, `created_at`, `updated_at`, `dias_gratis`, `color`, `icono`, `orden`) VALUES
(1, 'free', 'Plan Gratuito', 'Para probar el sistema. Límites básicos, sin soporte.', 0.00, 0.00, 1, 50, 100, 0, 0, 0, 0, 0, 1, '2026-02-27 03:51:27', NULL, 0, '#0FD186', 'fa-star', 0),
(2, 'basic', 'Plan Básico', 'Para pequeños negocios. Incluye reportes y empleados.', 9.99, 99.00, 3, 500, 1000, 1, 1, 0, 0, 0, 1, '2026-02-27 03:51:27', NULL, 0, '#0FD186', 'fa-star', 0),
(3, 'pro', 'Plan Profesional', 'Para negocios en crecimiento. Todo incluido.', 24.99, 249.00, 10, 5000, 10000, 1, 1, 1, 0, 1, 1, '2026-02-27 03:51:27', NULL, 0, '#0FD186', 'fa-star', 0),
(4, 'enterprise', 'Plan Enterprise', 'Sin límites. Soporte prioritario y API pública.', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, '2026-02-27 03:51:27', NULL, 0, '#0FD186', 'fa-star', 0),
(6, 'gratis', 'Gratis 15 días', 'Prueba gratuita con acceso completo', 0.00, 0.00, 1, 50, 100, 0, 0, 0, 0, 0, 1, '2026-02-28 23:10:26', NULL, 15, '#64748b', 'fa-gift', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(255) DEFAULT NULL,
  `cliente_tel` varchar(50) DEFAULT NULL,
  `fecha` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `descuento` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `estado` enum('borrador','enviado','aprobado','rechazado','vencido') DEFAULT 'borrador',
  `notas` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuestos`
--

INSERT INTO `presupuestos` (`id`, `negocio_id`, `numero`, `cliente_id`, `cliente_nombre`, `cliente_tel`, `fecha`, `fecha_vencimiento`, `subtotal`, `descuento`, `total`, `estado`, `notas`, `creado_por`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 4, 'PRES-0001', NULL, 'Obra Villa Norte', '221 555-3456', '2026-02-27', '2026-03-13', 163360.00, 500.00, 162860.00, 'borrador', 'Materiales para 2do piso', 5, '2026-02-27 17:57:50', '2026-02-27 17:57:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_items`
--

CREATE TABLE `presupuesto_items` (
  `id` int(11) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `descripcion` varchar(500) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT 1.00,
  `precio_unit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento_item` decimal(5,2) DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_items`
--

INSERT INTO `presupuesto_items` (`id`, `presupuesto_id`, `producto_id`, `descripcion`, `cantidad`, `precio_unit`, `descuento_item`, `subtotal`) VALUES
(1, 1, NULL, 'Cemento Portland 50kg', 10.00, 12000.00, 5.00, 114000.00),
(2, 1, NULL, 'Adhesivo cerámico 30kg', 5.00, 9500.00, 0.00, 47500.00),
(3, 1, NULL, 'Tarugo plástico 8mm c/50u', 3.00, 620.00, 0.00, 1860.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `precio_costo` decimal(10,2) DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0,
  `unidad_medida` varchar(50) DEFAULT 'unidad',
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL COMMENT 'Gondola/Pasillo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `negocio_id`, `categoria_id`, `nombre`, `descripcion`, `codigo_barras`, `precio_costo`, `precio_venta`, `stock`, `stock_minimo`, `unidad_medida`, `foto`, `activo`, `fecha_creacion`, `fecha_actualizacion`, `fecha_vencimiento`, `proveedor_id`, `ubicacion`) VALUES
(1, 1, NULL, 'Coca cola 500ML', '', '241321411', 1500.00, 2000.00, 1, 1, 'unidad', 'prod_69a1173509b2e_1772164917.png', 1, '2026-02-27 04:02:29', '2026-02-27 05:13:09', NULL, NULL, NULL),
(2, 3, 9, 'Milanesa Napolitana', 'Milanesa de ternera con salsa, jamón y queso gratinado', NULL, 850.00, 1850.00, 50, 5, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(3, 3, 9, 'Bife de Chorizo 300g', 'Bife de chorizo a la plancha con guarnición', NULL, 1200.00, 2800.00, 30, 3, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(4, 3, 8, 'Empanadas (x6)', 'Empanadas de carne cortada a cuchillo. Horneadas', NULL, 420.00, 950.00, 80, 10, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(5, 3, 15, 'Spaghetti Bolognesa', 'Pasta spaghetti con salsa bolognesa casera', NULL, 380.00, 980.00, 40, 5, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(6, 3, 10, 'Brownie con helado', 'Brownie tibio con 2 bochas de helado de vainilla', NULL, 280.00, 750.00, 25, 5, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(7, 3, 11, 'Coca-Cola 500ml', 'Botella de Coca-Cola 500ml fría', NULL, 80.00, 350.00, 120, 15, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(8, 3, 24, 'Cerveza Heineken 330ml', 'Lata Heineken 330ml bien fría', NULL, 150.00, 450.00, 96, 12, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(9, 3, 8, 'Tabla de Picada', 'Quesos, fiambres, aceitunas y pan tostado para 2', NULL, 650.00, 1600.00, 20, 3, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(10, 3, 13, 'Ensalada César', 'Lechuga romana, pollo grillado, croutones y aderezo César', NULL, 420.00, 1100.00, 30, 5, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(11, 3, 26, 'Menú del Día', 'Plato principal + bebida + postre del día', NULL, 600.00, 1400.00, 40, 5, 'unidad', NULL, 1, '2026-02-27 17:15:34', '2026-02-27 17:15:34', NULL, NULL, NULL),
(12, 4, 49, 'Martillo carpintero 500g', 'Mango de madera, cabeza de acero forjado', '7790001001', 1800.00, 3500.00, 25, 5, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(13, 4, 49, 'Juego destornilladores 8pzs', 'Phillips y plano, mango ergonómico', '7790001002', 2200.00, 4200.00, 18, 3, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(14, 4, 49, 'Llave inglesa 10\"', 'Acero cromo vanadio, apertura hasta 28mm', '7790001003', 1600.00, 3200.00, 12, 3, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(15, 4, 49, 'Cinta métrica 5m', 'Carcasa ABS, traba automática', '7790001004', 700.00, 1400.00, 40, 10, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(16, 4, 49, 'Nivel de burbuja 60cm', 'Aluminio extruido, 3 burbujas', '7790001005', 1400.00, 2800.00, 15, 4, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(17, 4, 50, 'Cable unipolar 2.5mm x 100m', 'Rollo IRAM, color rojo', '7790002001', 18000.00, 32000.00, 8, 2, 'rollo', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(18, 4, 50, 'Llave de luz simple', 'Ticino Magic blanco 10A', '7790002002', 650.00, 1300.00, 49, 10, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-03-07 21:51:07', NULL, NULL, NULL),
(19, 4, 50, 'Tomacorriente doble 10A', 'Con polo a tierra, blanco', '7790002003', 950.00, 1900.00, 35, 8, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(20, 4, 50, 'Disyuntor termomagnético 16A', 'Riel DIN, marca Schneider', '7790002004', 4500.00, 8500.00, 20, 4, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(21, 4, 50, 'Cinta aisladora 19mm x 20m', 'Scotch 33+, negro', '7790002005', 280.00, 580.00, 80, 20, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(22, 4, 51, 'Caño PVC 110mm x 3m', 'Sanitario, color gris', '7790003001', 2800.00, 5200.00, 15, 3, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(23, 4, 51, 'Codo PVC 90° 50mm', 'Para agua fría/caliente', '7790003002', 180.00, 380.00, 59, 15, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-03-07 21:51:07', NULL, NULL, NULL),
(24, 4, 51, 'Llave de paso esfera 1/2\"', 'Bronce cromado, palanca', '7790003003', 1200.00, 2400.00, 25, 5, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(25, 4, 51, 'Teflón 19mm x 10m', 'Rollo blanco estándar', '7790003004', 120.00, 280.00, 100, 20, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(26, 4, 51, 'Flexo acero inox 1/2\" x 30cm', 'Para conexión de inodoro/canilla', '7790003005', 480.00, 980.00, 30, 8, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(27, 4, 52, 'Látex interior blanco 20L', 'Sinteplast Revear, mate', '7790004001', 28000.00, 48000.00, 10, 2, 'balde', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(28, 4, 52, 'Esmalte sintético blanco 4L', 'Alba, satinado', '7790004002', 12000.00, 21000.00, 8, 2, 'lata', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(29, 4, 52, 'Rodillo lana 23cm con mango', 'Para látex, pelo corto', '7790004003', 800.00, 1600.00, 20, 5, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(30, 4, 52, 'Pincel de cerda 2\"', 'Para esmaltes y aceites', '7790004004', 350.00, 750.00, 30, 8, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(31, 4, 53, 'Tornillo madera 5x60mm c/100u', 'Bugle fosfatado, cabeza Phillips', '7790005001', 350.00, 750.00, 45, 10, 'caja', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(32, 4, 53, 'Tarugo plástico 8mm c/50u', 'Con tornillo incluido', '7790005002', 280.00, 620.00, 50, 15, 'bolsa', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(33, 4, 53, 'Bulón hexagonal M10x50 c/tuerca', 'Zincado, con tuerca y arandela', '7790005003', 180.00, 420.00, 35, 10, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(34, 4, 54, 'Cemento Portland 50kg', 'Loma Negra, resistencia normal', '7790006001', 7500.00, 12000.00, 20, 4, 'bolsa', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(35, 4, 54, 'Adhesivo cerámico 30kg', 'Klaukol, uso interior/exterior', '7790006002', 5500.00, 9500.00, 12, 3, 'bolsa', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(36, 4, 55, 'Taladro percutor 650W', 'Bosch GSB 650, 13mm mandril', '7790007001', 18000.00, 32000.00, 6, 1, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(37, 4, 55, 'Amoladora angular 115mm 720W', 'DeWalt, incluye disco diamantado', '7790007002', 22000.00, 38000.00, 4, 1, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(38, 4, 56, 'Guantes de cuero vaqueta T9', 'Palma reforzada, dorso tela', '7790008001', 800.00, 1800.00, 30, 8, 'par', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(39, 4, 56, 'Casco de seguridad blanco', 'Clase A, con ajuste de rueda', '7790008002', 1200.00, 2500.00, 20, 5, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(40, 4, 56, 'Antiparras de seguridad', 'Policarbonato, ventilación lateral', '7790008003', 600.00, 1400.00, 25, 6, 'unidad', NULL, 1, '2026-02-27 17:43:39', '2026-02-27 17:43:39', NULL, NULL, NULL),
(41, 6, 57, 'Aceite Girasol 1.5L', 'Aceite de girasol primera prensada', '7791234500001', 780.00, 1250.00, 48, 10, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 1, 'Pasillo A-1'),
(42, 6, 57, 'Arroz Largo Fino 1kg', 'Arroz largo fino tipo 00', '7791234500002', 380.00, 620.00, 119, 20, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-03-08 20:40:33', NULL, 1, 'Pasillo A-1'),
(43, 6, 57, 'Fideos Tallarín 500g', 'Fideos de sémola de trigo', '7791234500003', 290.00, 480.00, 92, 15, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-03-08 20:49:36', NULL, 1, 'Pasillo A-2'),
(44, 6, 57, 'Azúcar Común 1kg', 'Azúcar blanca refinada', '7791234500004', 330.00, 550.00, 60, 15, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 1, 'Pasillo A-2'),
(45, 6, 57, 'Harina 000 1kg', 'Harina de trigo triple cero', '7791234500005', 250.00, 420.00, 70, 15, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 1, 'Pasillo A-3'),
(46, 6, 57, 'Sal Fina 1kg', 'Sal fina de mesa yodada', '7791234500006', 95.00, 180.00, 90, 20, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 1, 'Pasillo A-3'),
(47, 6, 57, 'Tomate en Lata 400g', 'Tomate perita triturado', '7791234500007', 210.00, 380.00, 56, 10, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-03-08 20:49:36', NULL, 1, 'Pasillo A-4'),
(48, 6, 58, 'Coca-Cola 2.25L', 'Gaseosa cola', '7791234500008', 620.00, 950.00, 72, 15, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 3, 'Gondola B-1'),
(49, 6, 58, 'Agua Mineral 1.5L', 'Agua mineral sin gas', '7791234500009', 190.00, 350.00, 143, 30, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-03-08 20:40:33', NULL, 3, 'Gondola B-1'),
(50, 6, 58, 'Jugo Cepita 1L', 'Jugo de naranja listo para tomar', '7791234500010', 400.00, 680.00, 36, 10, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 3, 'Gondola B-2'),
(51, 6, 58, 'Cerveza Quilmes 1L', 'Cerveza rubia retornable', '7791234500011', 520.00, 820.00, 47, 12, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-03-08 20:46:56', NULL, 3, 'Gondola B-3'),
(52, 6, 58, 'Vino Trapiche 750ml', 'Vino tinto Malbec', '7791234500012', 1100.00, 1800.00, 24, 6, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 3, 'Gondola B-4'),
(53, 6, 59, 'Leche Entera 1L', 'Leche larga vida entera', '7791234500013', 280.00, 420.00, 96, 20, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 2, 'Gondola C-1'),
(54, 6, 59, 'Yogur Natural 190g', 'Yogur natural cremoso', '7791234500014', 190.00, 320.00, 48, 10, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 2, 'Heladera 1'),
(55, 6, 59, 'Queso Cremoso 300g', 'Queso cremoso por fraccion', '7791234500015', 780.00, 1250.00, 20, 5, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 2, 'Heladera 1'),
(56, 6, 59, 'Manteca 200g', 'Manteca sin sal en paquete', '7791234500016', 360.00, 580.00, 30, 8, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 2, 'Heladera 1'),
(57, 6, 63, 'Detergente Magistral 750ml', 'Detergente lavavajillas', '7791234500017', 400.00, 680.00, 40, 10, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 4, 'Pasillo D-1'),
(58, 6, 63, 'Lavandina 2L', 'Lavandina concentrada', '7791234500018', 260.00, 450.00, 35, 8, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 4, 'Pasillo D-1'),
(59, 6, 63, 'Papel Higienico x4', 'Papel higienico doble hoja', '7791234500019', 480.00, 780.00, 60, 15, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 4, 'Pasillo D-2'),
(60, 6, 63, 'Jabon en Polvo 1kg', 'Detergente ropa en polvo', '7791234500020', 670.00, 1100.00, 28, 8, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 4, 'Pasillo D-2'),
(61, 6, 65, 'Helado Chocolate 1L', 'Helado de chocolate', '7791234500021', 860.00, 1400.00, 15, 4, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 5, 'Freezer 1'),
(62, 6, 65, 'Pizza Congelada', 'Pizza congelada muzarela', '7791234500022', 1100.00, 1850.00, 12, 3, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 5, 'Freezer 1'),
(63, 6, 65, 'Milanesa de Pollo x4', 'Milanesas de pollo rebozadas', '7791234500023', 980.00, 1650.00, 10, 3, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 5, 'Freezer 2'),
(64, 6, 64, 'Shampoo Sedal 350ml', 'Shampoo para cabello', '7791234500024', 580.00, 950.00, 20, 5, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 4, 'Pasillo E-1'),
(65, 6, 64, 'Jabon Palmolive x3', 'Jabon de tocador pack x3', '7791234500025', 400.00, 680.00, 25, 6, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 4, 'Pasillo E-1'),
(66, 6, 64, 'Desodorante Axe 150ml', 'Desodorante en aerosol', '7791234500026', 720.00, 1200.00, 18, 5, 'unidad', NULL, 1, '2026-02-27 18:43:47', '2026-02-27 18:43:47', NULL, 4, 'Pasillo E-2'),
(67, 6, 61, 'Cebolla', 'negra', '321331321321', 1000.00, 2000.00, 2, 1, 'kg', 'prod_69a6294b02052_1772497227.jpg', 1, '2026-03-03 00:20:29', '2026-03-08 20:46:56', NULL, NULL, NULL),
(69, 7, 113, 'corte de cabello', '', NULL, 6000.00, 12000.00, 0, 0, 'unidad', NULL, 1, '2026-03-09 21:56:02', '2026-03-09 21:56:23', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `contacto` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `negocio_id`, `nombre`, `razon_social`, `cuit`, `contacto`, `telefono`, `email`, `direccion`, `notas`, `activo`, `created_at`) VALUES
(1, 6, 'Distribuidora Norte', 'Distribuidora Norte S.A.', '30-71234567-8', 'Carlos Méndez', '011-4523-7890', 'ventas@distnorte.com', NULL, NULL, 1, '2026-02-27 18:41:50'),
(2, 6, 'Lácteos del Valle', 'Lácteos del Valle S.R.L.', '30-68901234-5', 'Ana Rodríguez', '011-4412-5566', 'pedidos@lacteosdelvalle.com', NULL, NULL, 1, '2026-02-27 18:41:50'),
(3, 6, 'Bebidas Premium 1', 'Bebidas Premium S.A.', '30-70456789-2', 'Roberto Silva', '011-4789-1122', 'comercial@bebidaspremium.com', '', '', 1, '2026-02-27 18:41:50'),
(4, 6, 'Limpieza Total', 'Limpieza Total Dist.', '20-30123456-7', 'Marta López', '011-4654-3322', 'info@limpiezatotal.com', NULL, NULL, 1, '2026-02-27 18:41:50'),
(5, 6, 'Frío Express', 'Frío Express Logística', '30-72345678-9', 'Diego Ferrara', '011-4901-7788', 'despacho@frioexpress.com', NULL, NULL, 1, '2026-02-27 18:41:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_cocina_sectores`
--

CREATE TABLE `restaurant_cocina_sectores` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#ef4444',
  `activo` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `restaurant_cocina_sectores`
--

INSERT INTO `restaurant_cocina_sectores` (`id`, `negocio_id`, `nombre`, `slug`, `color`, `activo`, `orden`, `created_at`) VALUES
(1, 3, 'Cocina Principal', 'principal', '#ef4444', 1, 1, '2026-02-27 13:03:00'),
(2, 3, 'Parrilla', 'parrilla', '#f97316', 1, 2, '2026-02-27 13:03:00'),
(3, 3, 'Barra / Bebidas', 'barra', '#3b82f6', 1, 3, '2026-02-27 13:03:00'),
(4, 3, 'Postres / Fría', 'fria', '#ec4899', 1, 4, '2026-02-27 13:03:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_comandas`
--

CREATE TABLE `restaurant_comandas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `mesa_id` int(11) NOT NULL,
  `reserva_id` int(11) DEFAULT NULL,
  `numero` int(11) NOT NULL,
  `mozo_id` int(11) DEFAULT NULL,
  `estado` enum('abierta','en_cocina','lista','cerrada','cancelada') DEFAULT 'abierta',
  `personas` int(11) DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `abierta_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cerrada_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `restaurant_comandas`
--

INSERT INTO `restaurant_comandas` (`id`, `negocio_id`, `mesa_id`, `reserva_id`, `numero`, `mozo_id`, `estado`, `personas`, `observaciones`, `venta_id`, `subtotal`, `descuento`, `total`, `abierta_at`, `cerrada_at`, `updated_at`) VALUES
(1, 3, 1, NULL, 1, NULL, 'cerrada', 1, NULL, 13, 3150.00, 0.00, 3150.00, '2026-02-27 13:22:05', '2026-03-08 20:21:38', '2026-03-08 20:21:38'),
(2, 3, 2, NULL, 2, NULL, 'lista', 1, NULL, NULL, 0.00, 0.00, 0.00, '2026-02-27 13:30:23', NULL, '2026-03-08 20:01:38'),
(3, 3, 3, NULL, 3, NULL, 'abierta', 1, NULL, NULL, 4300.00, 0.00, 4300.00, '2026-02-27 13:33:47', NULL, '2026-03-08 20:12:35'),
(4, 3, 4, NULL, 4, NULL, 'en_cocina', 1, NULL, NULL, 3900.00, 0.00, 3900.00, '2026-02-27 17:03:33', NULL, '2026-03-08 20:12:29'),
(5, 3, 5, NULL, 5, NULL, 'en_cocina', 1, NULL, NULL, 0.00, 0.00, 0.00, '2026-02-27 17:06:04', NULL, '2026-02-27 17:06:14'),
(6, 3, 6, NULL, 6, NULL, 'en_cocina', 1, NULL, NULL, 0.00, 0.00, 0.00, '2026-02-27 17:19:37', NULL, '2026-03-08 20:07:27'),
(7, 3, 7, NULL, 7, NULL, 'abierta', 1, NULL, NULL, 0.00, 0.00, 0.00, '2026-02-27 17:19:53', NULL, '2026-02-27 17:19:53'),
(8, 3, 8, NULL, 8, NULL, 'abierta', 1, NULL, NULL, 0.00, 0.00, 0.00, '2026-02-27 17:29:19', NULL, '2026-02-27 17:29:19'),
(9, 3, 9, NULL, 9, NULL, 'abierta', 1, NULL, NULL, 0.00, 0.00, 0.00, '2026-02-27 23:09:14', NULL, '2026-02-27 23:09:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_comanda_items`
--

CREATE TABLE `restaurant_comanda_items` (
  `id` int(11) NOT NULL,
  `comanda_id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `nombre_item` varchar(255) NOT NULL,
  `precio_unit` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL,
  `estado_cocina` enum('pendiente','en_preparacion','listo','entregado','cancelado') DEFAULT 'pendiente',
  `observaciones` varchar(255) DEFAULT NULL,
  `sector_cocina` varchar(50) DEFAULT 'principal',
  `enviado_at` timestamp NULL DEFAULT NULL,
  `listo_at` timestamp NULL DEFAULT NULL,
  `entregado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `restaurant_comanda_items`
--

INSERT INTO `restaurant_comanda_items` (`id`, `comanda_id`, `negocio_id`, `producto_id`, `nombre_item`, `precio_unit`, `cantidad`, `subtotal`, `estado_cocina`, `observaciones`, `sector_cocina`, `enviado_at`, `listo_at`, `entregado_at`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 3, 'Bife de Chorizo 300g', 2800.00, 1, 2800.00, 'listo', '', 'principal', NULL, '2026-03-08 20:01:38', NULL, '2026-03-07 19:13:37', '2026-03-08 20:01:38'),
(2, 1, 3, 11, 'Menú del Día', 1400.00, 1, 1400.00, 'cancelado', '', 'principal', NULL, NULL, NULL, '2026-03-08 19:33:13', '2026-03-08 19:33:44'),
(3, 1, 3, 7, 'Coca-Cola 500ml', 350.00, 1, 350.00, 'listo', '', 'principal', NULL, '2026-03-08 20:01:38', NULL, '2026-03-08 19:33:18', '2026-03-08 20:01:38'),
(4, 1, 3, 11, 'Menú del Día', 1400.00, 2, 2800.00, 'listo', '', 'principal', NULL, '2026-03-08 20:01:39', NULL, '2026-03-08 19:33:44', '2026-03-08 20:01:39'),
(5, 3, 3, 3, 'Bife de Chorizo 300g', 2800.00, 1, 2800.00, 'pendiente', '', 'principal', NULL, NULL, NULL, '2026-03-08 20:01:56', '2026-03-08 20:01:56'),
(6, 3, 3, 6, 'Brownie con helado', 750.00, 1, 750.00, 'listo', '', 'principal', NULL, '2026-03-08 20:08:31', NULL, '2026-03-08 20:02:02', '2026-03-08 20:08:31'),
(7, 4, 3, 3, 'Bife de Chorizo 300g', 2800.00, 1, 2800.00, 'cancelado', '', 'principal', '2026-03-08 20:06:11', NULL, NULL, '2026-03-08 20:06:04', '2026-03-08 20:08:15'),
(8, 4, 3, 7, 'Coca-Cola 500ml', 350.00, 1, 350.00, 'cancelado', '', 'principal', '2026-03-08 20:07:45', NULL, NULL, '2026-03-08 20:06:38', '2026-03-08 20:08:17'),
(9, 4, 3, 6, 'Brownie con helado', 750.00, 1, 750.00, 'listo', '', 'principal', '2026-03-08 20:07:45', '2026-03-08 20:08:29', NULL, '2026-03-08 20:07:43', '2026-03-08 20:08:29'),
(10, 4, 3, 7, 'Coca-Cola 500ml', 350.00, 1, 350.00, 'listo', '', 'principal', '2026-03-08 20:08:50', '2026-03-08 20:09:04', NULL, '2026-03-08 20:08:47', '2026-03-08 20:09:04'),
(11, 4, 3, 3, 'Bife de Chorizo 300g', 2800.00, 1, 2800.00, 'pendiente', '', 'principal', '2026-03-08 20:12:29', NULL, NULL, '2026-03-08 20:12:27', '2026-03-08 20:12:29'),
(12, 3, 3, 6, 'Brownie con helado', 750.00, 1, 750.00, 'pendiente', '', 'principal', NULL, NULL, NULL, '2026-03-08 20:12:35', '2026-03-08 20:12:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_mesas`
--

CREATE TABLE `restaurant_mesas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `numero` varchar(20) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `capacidad` int(11) DEFAULT 4,
  `estado` enum('libre','ocupada','reservada','inactiva') DEFAULT 'libre',
  `pos_x` int(11) DEFAULT 0,
  `pos_y` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `restaurant_mesas`
--

INSERT INTO `restaurant_mesas` (`id`, `negocio_id`, `sector_id`, `numero`, `nombre`, `capacidad`, `estado`, `pos_x`, `pos_y`, `activo`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '1', NULL, 4, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-03-08 20:21:38'),
(2, 3, 1, '2', NULL, 4, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:30:23'),
(3, 3, 1, '3', NULL, 4, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:33:47'),
(4, 3, 1, '4', NULL, 4, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 17:03:33'),
(5, 3, 1, '5', NULL, 6, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 17:06:04'),
(6, 3, 1, '6', NULL, 6, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 17:19:37'),
(7, 3, 1, '7', NULL, 2, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 17:19:53'),
(8, 3, 1, '8', NULL, 2, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 17:29:19'),
(9, 3, 1, '9', NULL, 4, 'ocupada', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 23:09:14'),
(10, 3, 1, '10', NULL, 4, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(11, 3, 2, 'T1', NULL, 4, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(12, 3, 2, 'T2', NULL, 4, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(13, 3, 2, 'T3', NULL, 6, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(14, 3, 2, 'T4', NULL, 2, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(15, 3, 3, 'B1', NULL, 2, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(16, 3, 3, 'B2', NULL, 2, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00'),
(17, 3, 3, 'B3', NULL, 2, 'libre', 0, 0, 1, '2026-02-27 13:03:00', '2026-02-27 13:03:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_reservas`
--

CREATE TABLE `restaurant_reservas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `mesa_id` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(255) NOT NULL,
  `cliente_telefono` varchar(50) DEFAULT NULL,
  `cliente_email` varchar(255) DEFAULT NULL,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time DEFAULT NULL,
  `personas` int(11) DEFAULT 2,
  `estado` enum('pendiente','confirmada','sentada','cancelada','no_show') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `origen` enum('telefono','presencial','web','app') DEFAULT 'telefono',
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `restaurant_reservas`
--

INSERT INTO `restaurant_reservas` (`id`, `negocio_id`, `mesa_id`, `cliente_nombre`, `cliente_telefono`, `cliente_email`, `fecha_reserva`, `hora_inicio`, `hora_fin`, `personas`, `estado`, `observaciones`, `origen`, `usuario_id`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 'fran', '+543718563124', 'francisco.goonzalez99@gmail.com', '2026-03-08', '20:00:00', NULL, 2, 'cancelada', '', 'telefono', NULL, '2026-03-08 20:35:23', '2026-03-08 20:37:35'),
(2, 3, NULL, 'fran', '+543718563124', 'francisco.goonzalez99@gmail.com', '2026-03-08', '20:00:00', NULL, 2, 'cancelada', '', 'telefono', NULL, '2026-03-08 20:35:25', '2026-03-08 20:37:48'),
(3, 3, NULL, 'fran', '+543718563124', 'francisco.goonzalez99@gmail.com', '2026-03-08', '20:00:00', NULL, 2, 'cancelada', '', 'telefono', NULL, '2026-03-08 20:35:32', '2026-03-08 20:37:51'),
(4, 3, NULL, 'fran', '+543718563124', 'francisco.goonzalez99@gmail.com', '2026-03-08', '20:00:00', NULL, 2, 'cancelada', '', 'telefono', 4, '2026-03-08 20:37:28', '2026-03-08 20:37:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurant_sectores`
--

CREATE TABLE `restaurant_sectores` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#0FD186',
  `activo` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `restaurant_sectores`
--

INSERT INTO `restaurant_sectores` (`id`, `negocio_id`, `nombre`, `descripcion`, `color`, `activo`, `orden`, `created_at`) VALUES
(1, 3, 'Salón Principal', NULL, '#0FD186', 1, 1, '2026-02-27 13:03:00'),
(2, 3, 'Terraza', NULL, '#0ea5e9', 1, 2, '2026-02-27 13:03:00'),
(3, 3, 'Barra', NULL, '#f59e0b', 1, 3, '2026-02-27 13:03:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rubros`
--

CREATE TABLE `rubros` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'fa-store',
  `color` varchar(7) DEFAULT '#667eea',
  `activo` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rubros`
--

INSERT INTO `rubros` (`id`, `slug`, `nombre`, `descripcion`, `icono`, `color`, `activo`, `orden`) VALUES
(1, 'almacen', 'Almacén / Kiosco', 'Venta de abarrotes, bebidas, snacks y artículos de primera necesidad', 'fa-store', '#10b981', 1, 1),
(2, 'indumentaria', 'Indumentaria / Ropa', 'Venta de ropa, calzado y accesorios de moda', 'fa-tshirt', '#8b5cf6', 1, 2),
(3, 'ferreteria', 'Ferretería', 'Venta de herramientas, materiales de construcción y electricidad', 'fa-hammer', '#f59e0b', 1, 3),
(4, 'gastronomia', 'Gastronomía / Restaurant', 'Restaurant, bar, parrilla, cafetería, pizzería o food truck', 'fa-utensils', '#ef4444', 1, 4),
(5, 'farmacia', 'Farmacia / Perfumería', 'Medicamentos, cosméticos y artículos de higiene personal', 'fa-pills', '#06b6d4', 1, 5),
(6, 'tecnologia', 'Tecnología / Electrónica', 'Venta y reparación de celulares, computadoras y electrónica', 'fa-laptop', '#3b82f6', 1, 6),
(7, 'libreria', 'Librería / Papelería', 'Libros, útiles escolares y artículos de oficina', 'fa-book', '#f97316', 1, 7),
(8, 'peluqueria', 'Peluquería / Estética', 'Servicios de peluquería, barbería o estética', 'fa-cut', '#ec4899', 1, 8),
(9, 'veterinaria', 'Veterinaria / Mascotas', 'Atención veterinaria, venta de alimentos y accesorios para mascotas', 'fa-paw', '#84cc16', 1, 9),
(10, 'optica', 'Óptica', 'Venta y graduación de anteojos y lentes de contacto', 'fa-glasses', '#0ea5e9', 1, 10),
(11, 'jugueteria', 'Juguetería', 'Venta de juguetes y artículos para niños', 'fa-gamepad', '#a855f7', 1, 11),
(12, 'floristeria', 'Florería', 'Venta de flores, plantas y arreglos florales', 'fa-seedling', '#22c55e', 1, 12),
(13, 'panaderia', 'Panadería / Confitería', 'Panadería, confitería y productos de pastelería', 'fa-bread-slice', '#d97706', 1, 13),
(14, 'electrodomesticos', 'Electrodomésticos', 'Venta de electrodomésticos del hogar y línea blanca', 'fa-tv', '#64748b', 1, 14),
(15, 'deportes', 'Artículos Deportivos', 'Indumentaria y equipamiento para deporte y actividad física', 'fa-running', '#16a34a', 1, 15),
(16, 'otro', 'Otro', 'Otro tipo de negocio o rubro no listado', 'fa-briefcase', '#94a3b8', 1, 99),
(17, 'canchas', 'Alquiler de Canchas', 'Alquiler de canchas de fútbol, pádel, vóley, tenis, básquet y otros deportes', 'fa-futbol', '#16a34a', 1, 16),
(18, 'supermercado', 'Supermercado', NULL, 'fa-store', '#667eea', 1, 0),
(19, 'gimnasio', 'Gimnasio / Fitness', NULL, 'fa-store', '#667eea', 1, 0),
(20, 'hospedaje', 'Hospedaje / Hotel', 'Hotel, hospedaje, motel, cabañas y alojamientos turísticos', 'fa-store', '#667eea', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rubro_categorias_default`
--

CREATE TABLE `rubro_categorias_default` (
  `id` int(11) NOT NULL,
  `rubro_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `color` varchar(7) DEFAULT '#667eea',
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rubro_categorias_default`
--

INSERT INTO `rubro_categorias_default` (`id`, `rubro_id`, `nombre`, `color`, `orden`) VALUES
(1, 1, 'Bebidas', '#3b82f6', 1),
(2, 1, 'Lácteos', '#f59e0b', 2),
(3, 1, 'Panificados', '#d97706', 3),
(4, 1, 'Limpieza', '#10b981', 4),
(5, 1, 'Golosinas', '#ec4899', 5),
(6, 1, 'Fiambrería', '#ef4444', 6),
(7, 2, 'Remeras', '#8b5cf6', 1),
(8, 2, 'Pantalones', '#6366f1', 2),
(9, 2, 'Calzado', '#3b82f6', 3),
(10, 2, 'Accesorios', '#ec4899', 4),
(11, 2, 'Ropa Deportiva', '#10b981', 5),
(12, 3, 'Herramientas', '#f59e0b', 1),
(13, 3, 'Electricidad', '#eab308', 2),
(14, 3, 'Plomería', '#06b6d4', 3),
(15, 3, 'Pinturas', '#ef4444', 4),
(16, 3, 'Fijaciones', '#64748b', 5),
(22, 6, 'Celulares', '#3b82f6', 1),
(23, 6, 'Accesorios', '#6366f1', 2),
(24, 6, 'Computadoras', '#64748b', 3),
(25, 6, 'Servicios', '#10b981', 4),
(26, 8, 'Corte', '#ec4899', 1),
(27, 8, 'Coloración', '#f97316', 2),
(28, 8, 'Tratamientos', '#8b5cf6', 3),
(29, 8, 'Manicuría', '#ef4444', 4),
(30, 8, 'Productos', '#10b981', 5),
(31, 16, 'General', '#667eea', 1),
(32, 16, 'Servicios', '#10b981', 2),
(33, 16, 'Productos', '#3b82f6', 3),
(48, 17, 'Fútbol', '#16a34a', 1),
(49, 17, 'Pádel', '#0ea5e9', 2),
(50, 17, 'Vóley', '#8b5cf6', 3),
(51, 17, 'Tenis', '#f59e0b', 4),
(52, 17, 'Básquet', '#ef4444', 5),
(53, 17, 'Otros', '#64748b', 6),
(90, 4, 'Entradas', '#10b981', 1),
(91, 4, 'Ensaladas', '#22c55e', 2),
(92, 4, 'Sopas y Caldos', '#f59e0b', 3),
(93, 4, 'Pastas', '#d97706', 4),
(94, 4, 'Carnes', '#ef4444', 5),
(95, 4, 'Aves', '#f97316', 6),
(96, 4, 'Pescados y Mariscos', '#0ea5e9', 7),
(97, 4, 'Pizzas', '#dc2626', 8),
(98, 4, 'Sandwiches', '#84cc16', 9),
(99, 4, 'Minutas', '#a3e635', 10),
(100, 4, 'Guarniciones', '#65a30d', 11),
(101, 4, 'Postres', '#ec4899', 12),
(102, 4, 'Bebidas Sin Alcohol', '#3b82f6', 13),
(103, 4, 'Bebidas Con Alcohol', '#7c3aed', 14),
(104, 4, 'Cafetería', '#92400e', 15),
(105, 4, 'Menú del Día', '#0891b2', 16),
(106, 4, 'Combos y Promos', '#8b5cf6', 17),
(107, 4, 'Para Llevar', '#64748b', 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion_min` int(11) NOT NULL DEFAULT 30,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `categoria` varchar(80) DEFAULT 'General',
  `color` varchar(7) DEFAULT '#8b5cf6',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `negocio_id`, `nombre`, `descripcion`, `duracion_min`, `precio`, `categoria`, `color`, `activo`, `created_at`) VALUES
(1, 7, 'Corte cabello dama', 'Corte y secado', 45, 2800.00, 'Cabello', '#8b5cf6', 1, '2026-02-27 19:06:59'),
(2, 7, 'Corte cabello caballero', 'Corte clasico o moderno', 30, 1800.00, 'Cabello', '#8b5cf6', 1, '2026-02-27 19:06:59'),
(3, 7, 'Coloracion completa', 'Tintura + post tratamiento', 120, 8500.00, 'Color', '#ec4899', 1, '2026-02-27 19:06:59'),
(4, 7, 'Mechas/Balayage', 'Mechas clasicas o balayage', 150, 12000.00, 'Color', '#ec4899', 1, '2026-02-27 19:06:59'),
(5, 7, 'Tratamiento keratina', 'Keratina profesional', 90, 9500.00, 'Tratamiento', '#0ea5e9', 1, '2026-02-27 19:06:59'),
(6, 7, 'Hidratacion profunda', 'Mascarilla + secado', 60, 4200.00, 'Tratamiento', '#0ea5e9', 1, '2026-02-27 19:06:59'),
(7, 7, 'Peinado de novia', 'Peinado especial + prueba', 90, 15000.00, 'Peinado', '#f59e0b', 1, '2026-02-27 19:06:59'),
(8, 7, 'Depilacion cejas', 'Depilacion con hilo o cera', 20, 900.00, 'Estetica', '#ef4444', 1, '2026-02-27 19:06:59'),
(9, 7, 'Manicura', 'Limpieza + esmaltado', 50, 2500.00, 'Unias', '#f472b6', 1, '2026-02-27 19:06:59'),
(10, 7, 'Pedicura', 'Limpieza + esmaltado pies', 60, 3000.00, 'Unias', '#f472b6', 1, '2026-02-27 19:06:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `superadmin_users`
--

CREATE TABLE `superadmin_users` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `superadmin_users`
--

INSERT INTO `superadmin_users` (`id`, `nombre`, `email`, `password`, `activo`, `ultimo_login`, `created_at`) VALUES
(1, 'Super Admin', 'admin@dashbase.com', '$2y$10$sb9vTv8uEW2aCtUGIPIFte5u0ZCOsb6bfdy/Qew63ja23hDPkWUTq', 1, '2026-03-04 11:35:03', '2026-02-28 23:10:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tec_clientes`
--

CREATE TABLE `tec_clientes` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL DEFAULT '',
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tec_ordenes`
--

CREATE TABLE `tec_ordenes` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `equipo_tipo` enum('celular','tablet','notebook','pc','impresora','tv','consola','otro') NOT NULL DEFAULT 'celular',
  `equipo_marca` varchar(80) DEFAULT NULL,
  `equipo_modelo` varchar(120) DEFAULT NULL,
  `equipo_serie` varchar(80) DEFAULT NULL,
  `equipo_color` varchar(40) DEFAULT NULL,
  `falla_reportada` text NOT NULL,
  `diagnostico` text DEFAULT NULL,
  `repuestos` text DEFAULT NULL,
  `mano_obra` decimal(12,2) NOT NULL DEFAULT 0.00,
  `repuestos_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `seña` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` enum('efectivo','tarjeta_debito','tarjeta_credito','transferencia','qr') DEFAULT 'efectivo',
  `estado` enum('ingresado','diagnosticando','esperando_repuesto','en_reparacion','listo','entregado','sin_reparacion','cancelado') NOT NULL DEFAULT 'ingresado',
  `prioridad` enum('normal','urgente','vip') NOT NULL DEFAULT 'normal',
  `accesorios` varchar(200) DEFAULT NULL,
  `contrasena` varchar(60) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_promesa` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `tecnico` varchar(80) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(150) DEFAULT NULL,
  `cliente_telefono` varchar(30) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `servicio_id` int(11) DEFAULT NULL,
  `servicio_nombre` varchar(150) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `duracion_min` int(11) DEFAULT 30,
  `precio` decimal(10,2) DEFAULT 0.00,
  `estado` enum('pendiente','confirmado','en_curso','completado','cancelado','no_show') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id`, `negocio_id`, `cliente_id`, `cliente_nombre`, `cliente_telefono`, `empleado_id`, `servicio_id`, `servicio_nombre`, `fecha`, `hora_inicio`, `hora_fin`, `duracion_min`, `precio`, `estado`, `notas`, `created_at`, `updated_at`) VALUES
(1, 7, NULL, 'Maria Gonzalez', '11-4512-3456', NULL, 1, 'Corte cabello dama', '2026-02-27', '09:00:00', '09:45:00', 45, 2800.00, 'confirmado', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(2, 7, NULL, 'Laura Rodriguez', '11-6734-5678', NULL, 3, 'Coloracion completa', '2026-02-27', '10:00:00', '12:00:00', 120, 8500.00, 'en_curso', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(3, 7, NULL, 'Ana Martinez', '11-5523-1122', NULL, 6, 'Hidratacion profunda', '2026-02-27', '12:00:00', '13:00:00', 60, 4200.00, 'pendiente', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(4, 7, NULL, 'Carlos Lopez', '11-3345-6789', NULL, 2, 'Corte cabello caballero', '2026-02-27', '14:00:00', '14:30:00', 30, 1800.00, 'pendiente', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(5, 7, NULL, 'Valeria Torres', '11-7890-1234', NULL, 9, 'Manicura', '2026-02-27', '15:00:00', '15:50:00', 50, 2500.00, 'pendiente', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(6, 7, NULL, 'Florencia Paz', '11-9012-3456', NULL, 4, 'Mechas/Balayage', '2026-02-28', '10:00:00', '12:30:00', 150, 12000.00, 'pendiente', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(7, 7, NULL, 'Sofia Rios', '11-1234-5678', NULL, 7, 'Peinado de novia', '2026-03-01', '09:00:00', '10:30:00', 90, 15000.00, 'confirmado', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(8, 7, NULL, 'Marta Suarez', '11-4567-8901', NULL, 5, 'Tratamiento keratina', '2026-03-01', '11:00:00', '12:30:00', 90, 9500.00, 'pendiente', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(9, 7, NULL, 'Patricia Vega', '11-2233-4455', NULL, 1, 'Corte cabello dama', '2026-02-26', '10:00:00', '10:45:00', 45, 2800.00, 'completado', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(10, 7, NULL, 'Romina Castro', '11-5566-7788', NULL, 3, 'Coloracion completa', '2026-02-26', '14:00:00', '16:00:00', 120, 8500.00, 'completado', NULL, '2026-02-27 19:07:25', '2026-02-27 19:07:25'),
(11, 7, NULL, 'Fran', '2341221412', NULL, NULL, NULL, '2026-03-07', '20:00:00', '20:30:00', 30, 0.00, 'pendiente', '', '2026-03-07 19:15:44', '2026-03-07 19:15:44'),
(12, 7, NULL, 'Fran', '2341221412', NULL, NULL, NULL, '2026-03-07', '20:00:00', '20:30:00', 30, 0.00, 'pendiente', '', '2026-03-07 19:15:45', '2026-03-07 19:15:45'),
(13, 7, NULL, 'Fran', '2341221412', NULL, NULL, NULL, '2026-03-07', '20:00:00', '20:30:00', 30, 0.00, 'pendiente', '', '2026-03-07 19:15:48', '2026-03-07 19:15:48'),
(14, 7, NULL, 'pancho', '3718563125', NULL, NULL, 'Corte cabello caballero', '2026-03-09', '09:00:00', '09:30:00', 30, 1800.00, 'cancelado', '', '2026-03-09 21:57:46', '2026-03-09 21:59:36'),
(15, 7, NULL, 'pancho', '3718563125', NULL, NULL, 'Corte cabello caballero', '2026-03-09', '09:00:00', '09:30:00', 30, 1800.00, 'cancelado', '', '2026-03-09 21:57:47', '2026-03-09 21:59:33'),
(16, 7, NULL, 'pancho', '3718563125', NULL, NULL, 'Corte cabello caballero', '2026-03-09', '09:00:00', '09:30:00', 30, 1800.00, 'cancelado', '', '2026-03-09 21:57:48', '2026-03-09 21:59:39'),
(17, 7, NULL, 'pancho', '3718563125', NULL, NULL, 'Corte cabello caballero', '2026-03-09', '09:00:00', '09:30:00', 30, 1800.00, 'pendiente', '', '2026-03-09 21:57:48', '2026-03-09 21:57:48'),
(18, 7, NULL, 'pancho', '3718563125', NULL, NULL, 'Corte cabello caballero', '2026-03-09', '09:00:00', '09:30:00', 30, 1800.00, 'pendiente', '', '2026-03-09 21:58:54', '2026-03-09 21:58:54'),
(19, 7, NULL, 'abi', '3189295', NULL, NULL, NULL, '2026-03-09', '09:00:00', '09:30:00', 30, 0.00, 'pendiente', '', '2026-03-09 22:00:43', '2026-03-09 22:00:43'),
(20, 7, NULL, 'abi', '3189295', NULL, NULL, NULL, '2026-03-09', '09:00:00', '09:30:00', 30, 0.00, 'pendiente', '', '2026-03-09 22:00:45', '2026-03-09 22:00:45'),
(21, 7, 2, 'abi', '378181554', NULL, NULL, NULL, '2026-03-09', '09:00:00', '09:30:00', 30, 0.00, 'pendiente', '', '2026-03-09 22:07:13', '2026-03-09 22:07:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turno_servicios`
--

CREATE TABLE `turno_servicios` (
  `id` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `servicio_id` int(11) DEFAULT NULL,
  `servicio_nombre` varchar(120) NOT NULL,
  `duracion_min` int(11) DEFAULT 30,
  `precio` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `turno_servicios`
--

INSERT INTO `turno_servicios` (`id`, `turno_id`, `negocio_id`, `servicio_id`, `servicio_nombre`, `duracion_min`, `precio`) VALUES
(1, 14, 7, 2, 'Corte cabello caballero', 30, 1800.00),
(2, 15, 7, 2, 'Corte cabello caballero', 30, 1800.00),
(3, 16, 7, 2, 'Corte cabello caballero', 30, 1800.00),
(4, 17, 7, 2, 'Corte cabello caballero', 30, 1800.00),
(5, 18, 7, 2, 'Corte cabello caballero', 30, 1800.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
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
(1, 1, 'Admin', 'Demo', 'admin', NULL, '$2y$10$wT6ZsCWX3oaHZF6pGrA/GO5GyuJ3PqVqJqGKJSdNz04j1wCq1rqlG', 'admin', NULL, NULL, 1, '2026-03-09 22:08:28', '2026-02-27 03:53:59', '2026-03-09 22:08:28'),
(3, 2, 'Juan', 'García', 'juangarcia', 'juan@test.com', '$2y$10$KzCx1YG0ylXAEyAhlnXzI.l091kO8bbu935OKAd71e0znwUh.oMJy', 'admin', NULL, NULL, 1, NULL, '2026-02-27 12:40:57', '2026-02-27 12:40:57'),
(4, 3, 'Martin', 'De Oz', 'cicloclub', 'MartinezdeOz@gmail.com', '$2y$10$wT6ZsCWX3oaHZF6pGrA/GO5GyuJ3PqVqJqGKJSdNz04j1wCq1rqlG', 'admin', NULL, NULL, 1, '2026-03-08 19:31:31', '2026-02-27 12:57:40', '2026-03-08 19:31:31'),
(5, 4, 'San', 'Martin', 'ferretex', 'san@martin.cmo', '$2y$10$9TG9RDJ8gglCrQ0OcszyCOXXjPkSbCYh1.c7wMRPmANDdnxW40B2m', 'admin', NULL, NULL, 1, '2026-03-07 20:13:23', '2026-02-27 17:38:05', '2026-03-07 20:13:23'),
(6, 6, 'Admin', 'Super', 'superdemo', 'super@demo.com', '$2y$10$LXJYbhJuQfhoeu3x9jBV7eAl.JC1hzF7rRP.Whi/ODa3rfYpBu712', 'admin', NULL, NULL, 1, '2026-03-08 20:39:18', '2026-02-27 18:37:54', '2026-03-08 20:39:18'),
(7, 7, 'Admin', 'Glam', 'glamdemo', 'glam@demo.com', '$2y$10$ZJ19tUg5VXWsp.tMUe5nzeDs2EkXqEcto2dJhIpJ8DIBlYlfMmqZW', 'admin', NULL, NULL, 1, '2026-03-09 22:08:14', '2026-02-27 19:16:49', '2026-03-09 22:08:14'),
(8, 8, 'Admin', 'FitZone', 'gymdemo', 'gym@demo.com', '$2y$10$ETr/5x8NscAXu2LzbnGwb.MtEN/ktDVfDVF7qoo4k6yBz7s1cXmpS', 'admin', NULL, NULL, 1, '2026-03-10 12:47:40', '2026-02-27 19:41:56', '2026-03-10 12:47:40'),
(9, 9, 'Pedro', 'Ramon', 'gymdemo1', 'miami@gym.com', '$2y$10$Yo4U8giRnIeB3h4ECLxUhOAQxf9ZUTN1lRMeMujUHE9v9pBJ7/Ptm', 'admin', NULL, NULL, 1, '2026-02-27 21:08:41', '2026-02-27 21:08:01', '2026-02-27 21:08:41'),
(10, 10, 'Casia', 'nunez', 'nunezferreteria', 'nuenz@casia.com', '$2y$10$4lrOAAZqxTERAjQZcy/jPO1az7KrTbqNCMnXP4kkKsMzf1y6u9K26', 'admin', NULL, NULL, 1, NULL, '2026-02-27 23:07:13', '2026-02-27 23:07:13'),
(11, 11, 'Demo', 'Canchas', 'canchasdemo', 'canchasdemo@demo.com', '$2y$10$y4fg.bSGJLVCUBoyus1zJ.gXngMMAAawb2UjYtvvQd3iF.u27RPny', 'admin', NULL, NULL, 1, '2026-03-10 13:00:57', '2026-02-28 01:18:43', '2026-03-10 13:00:57'),
(12, 12, 'Casia', 'Nunez', 'farmademo', 'algo@nunez.com', '$2y$10$8d.CmXZx5I.xarSCR85r2.3GLSBFrclqMvXbtvbAIeRix.RiskV4.', 'admin', NULL, NULL, 0, '2026-02-28 22:14:15', '2026-02-28 22:14:06', '2026-03-01 03:28:27'),
(13, 13, 'Casia', 'Nunez', 'hoteldemo', 'hotel@clorinda.cm', '$2y$10$hDcHLzCY445c0CTX0Fi00.ZSBBe/79GM1ARXi6gQhlbUzL/QN3Q9u', 'admin', NULL, NULL, 1, '2026-03-02 14:30:22', '2026-03-01 19:09:22', '2026-03-02 14:30:22'),
(14, 14, 'Juan', 'De La Rua', 'mascoton', 'vetetuya@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL, 1, '2026-03-01 20:13:55', '2026-03-01 19:45:18', '2026-03-01 20:13:55'),
(15, 15, 'Castor', 'Lopez', 'optidemo', 'ea@gmail.com', '$2y$10$GcUV.7Rhau5srYP7rOhqI.8UZ3PY.t8KD9tW4S10g.wko9gSrIiPO', 'admin', NULL, NULL, 1, '2026-03-02 15:04:34', '2026-03-02 14:55:35', '2026-03-02 15:04:34'),
(16, 16, 'Matias', 'Mereles', 'neotec', 'neo@gmail.com', '$2y$10$uiA1/ex0uPcHVUQBOLjPauUmLUDrDaWNcg48ZSLGbdOFNKdrcN8f.', 'admin', NULL, NULL, 1, '2026-03-02 15:21:42', '2026-03-02 15:21:34', '2026-03-02 15:21:42'),
(17, 17, 'Rodrigo', 'Gomez de la Fuente', 'rodrirestaurant', 'moqndow@gmail.com', '$2y$10$VZKVkn/H57s9OwEWpmJU6uhvIzeJnX0tsXqRmqNg9tje0SSqd8cOq', 'admin', NULL, NULL, 1, '2026-03-03 22:55:02', '2026-03-03 22:54:52', '2026-03-03 22:55:02'),
(18, 18, 'Oscar', 'Gonza', 'oscargym', 'wlnw@gmail.com', '$2y$10$2e/WvsoC7y8Sh9hPRQMT.exOXckLjhxeeEdJdWP08tqvx3CyhRIeW', 'admin', NULL, NULL, 1, '2026-03-07 02:49:33', '2026-03-07 02:49:24', '2026-03-07 02:49:33');

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
  `cliente_nombre` varchar(255) DEFAULT NULL,
  `cliente_telefono` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `estado` enum('completada','cancelada') DEFAULT 'completada',
  `observaciones` text DEFAULT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo_cancelacion` text DEFAULT NULL,
  `cancelada_por` int(11) DEFAULT NULL,
  `fecha_cancelacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `negocio_id`, `usuario_id`, `caja_id`, `cliente_id`, `cliente_nombre`, `cliente_telefono`, `subtotal`, `descuento`, `total`, `metodo_pago`, `estado`, `observaciones`, `fecha_venta`, `motivo_cancelacion`, `cancelada_por`, `fecha_cancelacion`) VALUES
(1, 1, 1, 1, NULL, NULL, NULL, 2000.00, 0.00, 2000.00, 'efectivo', 'completada', NULL, '2026-02-27 04:50:58', NULL, NULL, NULL),
(2, 1, 1, 1, NULL, NULL, NULL, 2000.00, 0.00, 2000.00, 'transferencia', 'completada', NULL, '2026-02-27 05:13:09', NULL, NULL, NULL),
(3, 6, 6, 5, NULL, NULL, NULL, 2820.00, 0.00, 2820.00, 'tarjeta_debito', 'cancelada', NULL, '2026-03-03 00:20:53', 'error de cobro', 6, '2026-03-08 17:46:56'),
(4, 4, 5, 4, NULL, NULL, NULL, 1680.00, 0.00, 1680.00, 'transferencia', 'completada', NULL, '2026-03-07 21:51:07', NULL, NULL, NULL),
(5, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:01:04', NULL, NULL, NULL),
(6, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:01:06', NULL, NULL, NULL),
(7, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:01:13', NULL, NULL, NULL),
(8, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:02:17', NULL, NULL, NULL),
(9, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:02:18', NULL, NULL, NULL),
(10, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:02:20', NULL, NULL, NULL),
(11, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:16:00', NULL, NULL, NULL),
(12, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:16:00', NULL, NULL, NULL),
(13, 3, 4, NULL, NULL, NULL, NULL, 3150.00, 0.00, 3150.00, 'efectivo', 'completada', 'Comanda #1 - Mesa 1', '2026-03-08 20:21:38', NULL, NULL, NULL),
(14, 6, 6, 7, NULL, NULL, NULL, 4270.00, 0.00, 4270.00, 'efectivo', 'completada', NULL, '2026-03-08 20:40:33', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vet_consultas`
--

CREATE TABLE `vet_consultas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT '09:00:00',
  `tipo` varchar(50) DEFAULT 'consulta',
  `motivo` varchar(255) DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `medicamentos` text DEFAULT NULL,
  `peso_consulta` decimal(6,2) DEFAULT NULL,
  `temperatura` decimal(4,1) DEFAULT NULL,
  `proximo_turno` date DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT 0.00,
  `metodo_pago` varchar(30) DEFAULT 'efectivo',
  `estado` enum('pendiente','atendido','cancelado') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vet_pacientes`
--

CREATE TABLE `vet_pacientes` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `especie` varchar(50) NOT NULL DEFAULT 'perro',
  `raza` varchar(100) DEFAULT NULL,
  `color` varchar(80) DEFAULT NULL,
  `sexo` enum('macho','hembra','desconocido') DEFAULT 'desconocido',
  `fecha_nacimiento` date DEFAULT NULL,
  `esterilizado` tinyint(1) DEFAULT 0,
  `duenio_nombre` varchar(150) NOT NULL,
  `duenio_telefono` varchar(30) DEFAULT NULL,
  `duenio_email` varchar(150) DEFAULT NULL,
  `duenio_direccion` varchar(255) DEFAULT NULL,
  `peso` decimal(6,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `foto_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vet_vacunas`
--

CREATE TABLE `vet_vacunas` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `lote` varchar(80) DEFAULT NULL,
  `fecha_aplicacion` date NOT NULL,
  `proxima_dosis` date DEFAULT NULL,
  `veterinario` varchar(100) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_tabla` (`tabla`),
  ADD KEY `idx_created` (`created_at`);

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
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_cliente` (`codigo_cliente`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `clientes_canchas`
--
ALTER TABLE `clientes_canchas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tel_negocio` (`telefono`,`negocio_id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `clientes_peluqueria`
--
ALTER TABLE `clientes_peluqueria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tel_negocio` (`telefono`,`negocio_id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `config_enum`
--
ALTER TABLE `config_enum`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_negocio_grupo_valor` (`negocio_id`,`grupo`,`valor`),
  ADD KEY `idx_negocio_grupo` (`negocio_id`,`grupo`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta` (`venta_id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `farmacia_laboratorios`
--
ALTER TABLE `farmacia_laboratorios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `farmacia_recetas`
--
ALTER TABLE `farmacia_recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha_emision`);

--
-- Indices de la tabla `farmacia_receta_items`
--
ALTER TABLE `farmacia_receta_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receta` (`receta_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `caja_id` (`caja_id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_fecha_gasto` (`fecha_gasto`);

--
-- Indices de la tabla `gym_asistencias`
--
ALTER TABLE `gym_asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio_fecha` (`negocio_id`,`fecha`),
  ADD KEY `idx_socio` (`socio_id`);

--
-- Indices de la tabla `gym_clases`
--
ALTER TABLE `gym_clases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `gym_pagos`
--
ALTER TABLE `gym_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_socio` (`socio_id`);

--
-- Indices de la tabla `gym_planes`
--
ALTER TABLE `gym_planes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `gym_socios`
--
ALTER TABLE `gym_socios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `hospedaje_habitaciones`
--
ALTER TABLE `hospedaje_habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `hospedaje_reservas`
--
ALTER TABLE `hospedaje_reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_habitacion` (`habitacion_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fechas` (`checkin_fecha`,`checkout_fecha`);

--
-- Indices de la tabla `logs_actividad`
--
ALTER TABLE `logs_actividad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filename` (`filename`);

--
-- Indices de la tabla `negocios`
--
ALTER TABLE `negocios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_plan` (`plan_id`),
  ADD KEY `idx_estado_suscripcion` (`estado_suscripcion`),
  ADD KEY `idx_rubro_id` (`rubro_id`);

--
-- Indices de la tabla `optica_clientes`
--
ALTER TABLE `optica_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_nombre` (`nombre`,`apellido`);

--
-- Indices de la tabla `optica_pedidos`
--
ALTER TABLE `optica_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `optica_recetas`
--
ALTER TABLE `optica_recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_fecha` (`fecha_emision`);

--
-- Indices de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `ordenes_compra_items`
--
ALTER TABLE `ordenes_compra_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `perfil_negocio`
--
ALTER TABLE `perfil_negocio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `negocio_id` (`negocio_id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `presupuesto_items`
--
ALTER TABLE `presupuesto_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_presupuesto` (`presupuesto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_codigo_barras` (`codigo_barras`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `negocio_id` (`negocio_id`);

--
-- Indices de la tabla `restaurant_cocina_sectores`
--
ALTER TABLE `restaurant_cocina_sectores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_negocio_slug` (`negocio_id`,`slug`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `restaurant_comandas`
--
ALTER TABLE `restaurant_comandas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_mesa` (`mesa_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_numero` (`negocio_id`,`numero`);

--
-- Indices de la tabla `restaurant_comanda_items`
--
ALTER TABLE `restaurant_comanda_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comanda` (`comanda_id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_estado` (`estado_cocina`),
  ADD KEY `idx_producto` (`producto_id`);

--
-- Indices de la tabla `restaurant_mesas`
--
ALTER TABLE `restaurant_mesas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_sector` (`sector_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `restaurant_reservas`
--
ALTER TABLE `restaurant_reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_mesa` (`mesa_id`),
  ADD KEY `idx_fecha` (`fecha_reserva`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `restaurant_sectores`
--
ALTER TABLE `restaurant_sectores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `rubros`
--
ALTER TABLE `rubros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `rubro_categorias_default`
--
ALTER TABLE `rubro_categorias_default`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rubro` (`rubro_id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `superadmin_users`
--
ALTER TABLE `superadmin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `tec_clientes`
--
ALTER TABLE `tec_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_dni` (`dni`);

--
-- Indices de la tabla `tec_ordenes`
--
ALTER TABLE `tec_ordenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio_fecha` (`negocio_id`,`fecha`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_cliente` (`cliente_id`);

--
-- Indices de la tabla `turno_servicios`
--
ALTER TABLE `turno_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_turno` (`turno_id`),
  ADD KEY `idx_negocio` (`negocio_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `idx_usuario` (`usuario`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_activo` (`activo`);

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
  ADD KEY `idx_cliente` (`cliente_id`);

--
-- Indices de la tabla `vet_consultas`
--
ALTER TABLE `vet_consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_paciente` (`paciente_id`),
  ADD KEY `idx_fecha` (`negocio_id`,`fecha`);

--
-- Indices de la tabla `vet_pacientes`
--
ALTER TABLE `vet_pacientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_negocio` (`negocio_id`),
  ADD KEY `idx_duenio` (`negocio_id`,`duenio_nombre`);

--
-- Indices de la tabla `vet_vacunas`
--
ALTER TABLE `vet_vacunas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paciente` (`paciente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `clientes_canchas`
--
ALTER TABLE `clientes_canchas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes_peluqueria`
--
ALTER TABLE `clientes_peluqueria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `config_enum`
--
ALTER TABLE `config_enum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `farmacia_laboratorios`
--
ALTER TABLE `farmacia_laboratorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `farmacia_recetas`
--
ALTER TABLE `farmacia_recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `farmacia_receta_items`
--
ALTER TABLE `farmacia_receta_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `gym_asistencias`
--
ALTER TABLE `gym_asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `gym_clases`
--
ALTER TABLE `gym_clases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `gym_pagos`
--
ALTER TABLE `gym_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `gym_planes`
--
ALTER TABLE `gym_planes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `gym_socios`
--
ALTER TABLE `gym_socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `hospedaje_habitaciones`
--
ALTER TABLE `hospedaje_habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `hospedaje_reservas`
--
ALTER TABLE `hospedaje_reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs_actividad`
--
ALTER TABLE `logs_actividad`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `negocios`
--
ALTER TABLE `negocios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `optica_clientes`
--
ALTER TABLE `optica_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `optica_pedidos`
--
ALTER TABLE `optica_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `optica_recetas`
--
ALTER TABLE `optica_recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra_items`
--
ALTER TABLE `ordenes_compra_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `presupuesto_items`
--
ALTER TABLE `presupuesto_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `restaurant_cocina_sectores`
--
ALTER TABLE `restaurant_cocina_sectores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `restaurant_comandas`
--
ALTER TABLE `restaurant_comandas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `restaurant_comanda_items`
--
ALTER TABLE `restaurant_comanda_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `restaurant_mesas`
--
ALTER TABLE `restaurant_mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `restaurant_reservas`
--
ALTER TABLE `restaurant_reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `restaurant_sectores`
--
ALTER TABLE `restaurant_sectores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rubros`
--
ALTER TABLE `rubros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `rubro_categorias_default`
--
ALTER TABLE `rubro_categorias_default`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `superadmin_users`
--
ALTER TABLE `superadmin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tec_clientes`
--
ALTER TABLE `tec_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tec_ordenes`
--
ALTER TABLE `tec_ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `turno_servicios`
--
ALTER TABLE `turno_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `vet_consultas`
--
ALTER TABLE `vet_consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vet_pacientes`
--
ALTER TABLE `vet_pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vet_vacunas`
--
ALTER TABLE `vet_vacunas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD CONSTRAINT `cajas_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cajas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `config_enum`
--
ALTER TABLE `config_enum`
  ADD CONSTRAINT `fk_ce_negocio` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `negocios`
--
ALTER TABLE `negocios`
  ADD CONSTRAINT `fk_negocios_plan` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD CONSTRAINT `ordenes_compra_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`),
  ADD CONSTRAINT `ordenes_compra_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `ordenes_compra_items`
--
ALTER TABLE `ordenes_compra_items`
  ADD CONSTRAINT `ordenes_compra_items_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `perfil_negocio`
--
ALTER TABLE `perfil_negocio`
  ADD CONSTRAINT `perfil_negocio_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `presupuesto_items`
--
ALTER TABLE `presupuesto_items`
  ADD CONSTRAINT `presupuesto_items_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`);

--
-- Filtros para la tabla `tec_ordenes`
--
ALTER TABLE `tec_ordenes`
  ADD CONSTRAINT `fk_tec_ord_cli` FOREIGN KEY (`cliente_id`) REFERENCES `tec_clientes` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`negocio_id`) REFERENCES `negocios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vet_consultas`
--
ALTER TABLE `vet_consultas`
  ADD CONSTRAINT `vet_consultas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `vet_pacientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vet_vacunas`
--
ALTER TABLE `vet_vacunas`
  ADD CONSTRAINT `vet_vacunas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `vet_pacientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
