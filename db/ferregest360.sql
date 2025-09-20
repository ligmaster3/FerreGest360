-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-09-2025 a las 22:20:10
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
-- Base de datos: `ferregest360`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `empresa_id`, `nombre`, `descripcion`, `parent_id`, `activo`) VALUES
(1, 1, 'Herramientas', 'Herramientas manuales y eléctricas', NULL, 1),
(2, 1, 'Ferretería', 'Artículos de ferretería general', NULL, 1),
(3, 1, 'Plomería', 'Artículos para plomería', NULL, 1),
(4, 1, 'Electricidad', 'Artículos eléctricos', NULL, 1),
(5, 1, 'Construcción', 'Materiales de construcción', NULL, 1),
(6, 1, 'Pintura', 'Pinturas y accesorios', NULL, 1),
(7, 1, 'Jardín', 'Artículos para jardín', NULL, 1);

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `productos_proveedores`
--

CREATE TABLE `productos_proveedores` (
    `empresa_id` int(11) NOT NULL,
    `producto_id` int(11) NOT NULL,
    `proveedor_id` int(11) NOT NULL,
    `activo` tinyint(1) DEFAULT 1,
    `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`empresas_id`, `producto_id`, `proveedor_id`),
    FOREIGN KEY (`empresas`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`productos`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`proveedores`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
);
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `tipo_cliente` enum('natural','juridico') NOT NULL,
  `cedula_ruc` varchar(20) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `razon_social` varchar(200) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `dias_credito` int(11) DEFAULT 0,
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `empresa_id`, `tipo_cliente`, `cedula_ruc`, `nombre`, `razon_social`, `direccion`, `telefono`, `email`, `limite_credito`, `dias_credito`, `descuento_porcentaje`, `activo`, `fecha_registro`) VALUES
(1, 1, 'natural', '8-123-456', 'Juan Pérez', NULL, 'Calle 1, Ciudad de Panamá', '507-123-4567', 'juan.perez@email.com', 1000.00, 30, 5.00, 1, '2025-01-15 15:00:00'),
(2, 1, 'juridico', '12345678-1-123456', 'Constructora ABC', 'Constructora ABC S.A.', 'Avenida Central 123', '507-234-5678', 'info@constructoraabc.com', 5000.00, 60, 10.00, 1, '2025-01-16 16:00:00'),
(3, 1, 'natural', '8-234-567', 'María González', NULL, 'Calle 2, San Miguelito', '507-345-6789', 'maria.gonzalez@email.com', 500.00, 15, 0.00, 1, '2025-01-17 17:00:00'),
(4, 1, 'juridico', '87654321-1-654321', 'Ferretería Central', 'Ferretería Central Ltda.', 'Zona Libre de Colón', '507-456-7890', 'ventas@ferreteriacentral.com', 3000.00, 45, 8.00, 1, '2025-01-18 18:00:00'),
(5, 1, 'natural', '8-345-678', 'Carlos Rodríguez', NULL, 'Calle 3, Arraiján', '507-567-8901', 'carlos.rodriguez@email.com', 750.00, 20, 3.00, 1, '2025-01-19 19:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_facturas_venta`
--

CREATE TABLE `detalle_facturas_venta` (
  `id` int(11) NOT NULL,
  `factura_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_facturas_venta`
--

INSERT INTO `detalle_facturas_venta` (`id`, `factura_id`, `producto_id`, `cantidad`, `precio_unitario`, `descuento`, `subtotal`) VALUES
(1, 1, 1, 1, 25.00, 0.00, 25.00),
(2, 1, 2, 2, 6.00, 0.00, 12.00),
(3, 1, 3, 100, 0.10, 0.00, 10.00),
(4, 2, 4, 2, 45.00, 0.00, 90.00),
(5, 2, 5, 1, 125.00, 0.00, 125.00),
(6, 3, 1, 1, 25.00, 0.00, 25.00),
(7, 3, 2, 1, 6.00, 0.00, 6.00),
(8, 4, 3, 500, 0.10, 0.00, 50.00),
(9, 4, 4, 1, 45.00, 0.00, 45.00),
(10, 5, 4, 1, 45.00, 0.00, 45.00),
(11, 6, 2, 1, 6.00, 0.00, 6.00),
(12, 6, 3, 50, 0.10, 0.00, 5.00),
(13, 7, 5, 2, 125.00, 0.00, 250.00),
(14, 7, 1, 3, 25.00, 0.00, 75.00),
(15, 8, 4, 1, 45.00, 0.00, 45.00),
(16, 8, 2, 2, 6.00, 0.00, 12.00),
(17, 9, 3, 300, 0.10, 0.00, 30.00),
(18, 9, 1, 2, 25.00, 0.00, 50.00),
(19, 10, 2, 1, 6.00, 0.00, 6.00),
(20, 10, 3, 100, 0.10, 0.00, 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ordenes_compra`
--

CREATE TABLE `detalle_ordenes_compra` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad_ordenada` int(11) NOT NULL,
  `cantidad_recibida` int(11) DEFAULT 0,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_ordenes_compra`
--

INSERT INTO `detalle_ordenes_compra` (`id`, `orden_id`, `producto_id`, `cantidad_ordenada`, `cantidad_recibida`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 50, 0, 15.00, 750.00),
(2, 1, 3, 1500, 0, 0.05, 75.00),
(3, 2, 5, 10, 10, 75.00, 750.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(150) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `nombre`, `razon_social`, `ruc`, `direccion`, `telefono`, `email`, `logo_url`, `fecha_registro`, `activo`) VALUES
(1, 'Ferretería El Martillo', 'Ferretería El Martillo S.A.', '12345678-1-123456', 'Calle 50, Ciudad de Panamá', '507-123-4567', 'info@ferreteriamartillo.com', NULL, '2025-06-10 07:14:46', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_venta`
--

CREATE TABLE `facturas_venta` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `vendedor_id` int(11) NOT NULL,
  `fecha_factura` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `tipo_pago` enum('contado','credito','mixto') NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `itbms` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagada','anulada','vencida') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas_venta`
--

INSERT INTO `facturas_venta` (`id`, `empresa_id`, `numero_factura`, `cliente_id`, `vendedor_id`, `fecha_factura`, `fecha_vencimiento`, `tipo_pago`, `subtotal`, `descuento`, `itbms`, `total`, `estado`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 'F000001', 1, 1, '2025-01-15', '2025-02-14', 'credito', 150.00, 7.50, 14.25, 156.75, 'pagada', 'Venta de herramientas', '2025-01-15 15:30:00'),
(2, 1, 'F000002', 2, 1, '2025-01-16', '2025-03-17', 'credito', 1250.00, 125.00, 118.75, 1243.75, 'pendiente', 'Materiales de construcción', '2025-01-16 16:45:00'),
(3, 1, 'F000003', 3, 1, '2025-01-17', '2025-02-01', 'contado', 75.00, 0.00, 7.13, 82.13, 'pagada', 'Herramientas manuales', '2025-01-17 17:15:00'),
(4, 1, 'F000004', 4, 1, '2025-01-18', '2025-03-04', 'credito', 850.00, 68.00, 80.75, 862.75, 'pendiente', 'Productos de ferretería', '2025-01-18 18:20:00'),
(5, 1, 'F000005', 5, 1, '2025-01-19', '2025-02-08', 'credito', 200.00, 6.00, 19.00, 213.00, 'vencida', 'Pinturas y accesorios', '2025-01-19 19:30:00'),
(6, 1, 'F000006', 1, 1, '2025-01-20', '2025-01-20', 'contado', 45.00, 0.00, 4.28, 49.28, 'pagada', 'Repuestos varios', '2025-01-20 14:15:00'),
(7, 1, 'F000007', 2, 1, '2025-01-21', '2025-03-22', 'credito', 1800.00, 180.00, 171.00, 1791.00, 'pendiente', 'Equipos eléctricos', '2025-01-21 15:45:00'),
(8, 1, 'F000008', 3, 1, '2025-01-22', '2025-02-06', 'contado', 120.00, 0.00, 11.40, 131.40, 'pagada', 'Herramientas de jardín', '2025-01-22 16:30:00'),
(9, 1, 'F000009', 4, 1, '2025-01-23', '2025-03-09', 'credito', 650.00, 52.00, 61.75, 659.75, 'pendiente', 'Materiales de plomería', '2025-01-23 17:20:00'),
(10, 1, 'F000010', 5, 1, '2025-01-24', '2025-02-13', 'credito', 95.00, 2.85, 9.03, 101.18, 'anulada', 'Accesorios eléctricos', '2025-01-24 18:10:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `stock_reservado` int(11) DEFAULT 0,
  `costo_promedio` decimal(10,2) DEFAULT 0.00,
  `fecha_ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `producto_id`, `stock_actual`, `stock_reservado`, `costo_promedio`, `fecha_ultima_actualizacion`) VALUES
(1, 1, 10, 0, 15.00, '2025-06-10 07:14:46'),
(2, 2, 25, 0, 3.50, '2025-06-10 07:14:46'),
(3, 3, 500, 0, 0.05, '2025-06-10 07:14:46'),
(4, 4, 5, 0, 25.00, '2025-06-10 07:14:46'),
(5, 5, 3, 0, 75.00, '2025-06-10 07:14:46'),
(6, 26, 3, 0, 0.79, '2025-07-26 00:27:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id`, `empresa_id`, `nombre`, `descripcion`, `activo`) VALUES
(1, 1, 'Stanley', 'Herramientas Stanley', 1),
(2, 1, 'Black & Decker', 'Herramientas Black & Decker', 1),
(3, 1, 'Truper', 'Herramientas Truper', 1),
(4, 1, 'DeWalt', 'Herramientas profesionales DeWalt', 1),
(5, 1, 'Makita', 'Herramientas Makita', 1),
(6, 1, 'Sherwin Williams', 'Pinturas Sherwin Williams', 1),
(7, 1, 'Genérica', 'Marca genérica', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `tipo_movimiento_id` int(11) NOT NULL,
  `documento_referencia` varchar(100) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `empresa_id`, `producto_id`, `tipo_movimiento_id`, `documento_referencia`, `cantidad`, `costo_unitario`, `motivo`, `usuario_id`, `fecha_movimiento`) VALUES
(1, 1, 26, 1, NULL, 3, 0.79, 'Stock inicial', 1, '2025-07-26 00:27:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_compra`
--

CREATE TABLE `ordenes_compra` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `numero_orden` varchar(50) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_orden` date NOT NULL,
  `fecha_entrega_esperada` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `itbms` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmada','recibida','cancelada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes_compra`
--

INSERT INTO `ordenes_compra` (`id`, `empresa_id`, `numero_orden`, `proveedor_id`, `usuario_id`, `fecha_orden`, `fecha_entrega_esperada`, `subtotal`, `itbms`, `total`, `estado`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 'OC-000001', 1, 1, '2025-01-10', '2025-01-20', 825.00, 57.75, 882.75, 'pendiente', 'Pedido urgente de herramientas para proyecto', '2025-01-10 09:00:00'),
(2, 1, 'OC-000002', 1, 1, '2025-01-12', '2025-01-25', 750.00, 52.50, 802.50, 'recibida', 'Pedido de taladros para re-stock', '2025-01-12 10:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_facturas`
--

CREATE TABLE `pagos_facturas` (
  `id` int(11) NOT NULL,
  `factura_id` int(11) NOT NULL,
  `forma_pago` enum('efectivo','tarjeta','transferencia','cheque') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `referencia` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_facturas`
--

INSERT INTO `pagos_facturas` (`id`, `factura_id`, `forma_pago`, `monto`, `fecha_pago`, `referencia`, `observaciones`) VALUES
(1, 1, 'efectivo', 156.75, '2025-01-16 14:30:00', 'PAGO-001', 'Pago completo'),
(2, 3, 'tarjeta', 82.13, '2025-01-17 12:20:00', 'TARJ-001', 'Pago con tarjeta'),
(3, 6, 'efectivo', 49.28, '2025-01-20 09:20:00', 'PAGO-002', 'Pago en efectivo'),
(4, 8, 'transferencia', 131.40, '2025-01-22 11:35:00', 'TRANS-001', 'Transferencia bancaria');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `marca_id` int(11) DEFAULT NULL,
  `unidad_medida_id` int(11) DEFAULT NULL,
  `precio_compra` decimal(10,2) DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_mayoreo` decimal(10,2) DEFAULT NULL,
  `stock_minimo` int(11) DEFAULT 0,
  `stock_maximo` int(11) DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `empresa_id`, `codigo`, `codigo_barras`, `nombre`, `descripcion`, `categoria_id`, `marca_id`, `unidad_medida_id`, `precio_compra`, `precio_venta`, `precio_mayoreo`, `stock_minimo`, `stock_maximo`, `ubicacion`, `imagen_url`, `activo`, `fecha_creacion`) VALUES
(1, 1, 'MART001', NULL, 'Martillo de garra 16oz', 'Martillo de garra Stanley 16 onzas', 1, 1, 1, 15.00, 25.00, NULL, 5, 0, NULL, NULL, 1, '2025-06-10 07:14:46'),
(2, 1, 'DEST001', NULL, 'Destornillador plano 6\"', 'Destornillador plano Stanley 6 pulgadas', 1, 1, 1, 3.50, 6.00, NULL, 10, 0, NULL, NULL, 1, '2025-06-10 07:14:46'),
(3, 1, 'TORN001', NULL, 'Tornillo 1/4\" x 2\"', 'Tornillo galvanizado 1/4 x 2 pulgadas', 2, 7, 15, 0.05, 0.10, NULL, 100, 0, NULL, NULL, 1, '2025-06-10 07:14:46'),
(4, 1, 'PINB001', '14949', 'Pintura blanca 1 galón', 'Pintura látex blanca interior/exterior', 6, 6, 11, 25.00, 45.00, 24.00, 3, 50, 'san andres', 'img.png', 0, '2025-06-10 07:14:46'),
(5, 1, 'TALA001', NULL, 'Taladro eléctrico 1/2\"', 'Taladro eléctrico Black & Decker 1/2 pulgada', 1, 2, 1, 75.00, 125.00, NULL, 2, 0, NULL, NULL, 1, '2025-06-10 07:14:46'),
(6, 1, 'P001', NULL, 'Martillo de carpintero', 'Martillo con mango de fibra', 1, 1, 1, 0.00, 9.50, NULL, 5, 50, NULL, NULL, 1, '2025-06-21 23:29:23'),
(7, 1, 'P002', NULL, 'Destornillador plano', 'Destornillador de 6 pulgadas', 1, 2, 1, 0.00, 3.75, NULL, 10, 100, NULL, NULL, 1, '2025-06-21 23:29:23'),
(8, 1, 'P003', NULL, 'Bloque de concreto', 'Bloque 15x20x40 cm', 2, NULL, 1, 0.00, 1.25, NULL, 200, 2000, NULL, NULL, 1, '2025-06-21 23:29:23'),
(9, 1, 'P004', NULL, 'Saco de cemento 42.5kg', 'Cemento Portland gris', 2, 4, 3, 5.00, 7.80, 250.00, 20, 200, NULL, NULL, 1, '2025-06-21 23:29:23'),
(10, 1, 'P005', NULL, 'Galón de pintura blanca', 'Pintura base agua', 3, 5, 4, 0.00, 12.60, NULL, 10, 100, NULL, NULL, 1, '2025-06-21 23:29:23'),
(11, 1, 'P006', NULL, 'Cable eléctrico 10m', 'Cable de cobre calibre 12', 4, 4, 2, 0.00, 6.30, NULL, 10, 100, NULL, NULL, 1, '2025-06-21 23:29:23'),
(12, 1, 'P007', NULL, 'Lámpara LED 10W', 'Lámpara E27 luz blanca', 4, 5, 1, 0.00, 4.20, NULL, 20, 200, NULL, NULL, 1, '2025-06-21 23:29:23'),
(13, 1, 'P008', NULL, 'Tubo PVC 1 pulgada', 'Tubo de 3 metros', 5, NULL, 2, 0.00, 2.70, NULL, 50, 500, NULL, NULL, 1, '2025-06-21 23:29:23'),
(14, 1, 'P009', NULL, 'Llave ajustable 10\"', 'Llave inglesa de acero', 1, 2, 1, 0.00, 8.45, NULL, 10, 100, NULL, NULL, 1, '2025-06-21 23:29:23'),
(15, 1, 'P010', NULL, 'Cinta aislante', 'Rollo de cinta negra 18mm', 4, 1, 1, 0.00, 1.15, NULL, 30, 300, NULL, NULL, 1, '2025-06-21 23:29:23'),
(16, 1, 'P011', NULL, 'Taladro eléctrico', 'Taladro 650W con maletín', 1, 3, 1, 0.00, 45.90, NULL, 5, 50, NULL, NULL, 1, '2025-06-21 23:29:23'),
(17, 1, 'P012', NULL, 'Rodillo para pintura', 'Rodillo de felpa 9\"', 3, 4, 1, 0.00, 2.60, NULL, 15, 150, NULL, NULL, 1, '2025-06-21 23:29:23'),
(18, 1, 'P013', NULL, 'Llave de tubo', 'Llave tipo Stillson 14\"', 5, 1, 1, 0.00, 11.50, NULL, 8, 80, NULL, NULL, 1, '2025-06-21 23:29:23'),
(19, 1, 'P014', NULL, 'Grifo metálico', 'Grifo de lavamanos cromado', 5, NULL, 1, 0.00, 7.75, NULL, 10, 100, NULL, NULL, 1, '2025-06-21 23:29:23'),
(20, 1, 'P015', NULL, 'Cinta métrica 5m', 'Cuerpo metálico con freno', 1, 2, 1, 0.00, 3.85, NULL, 20, 100, NULL, NULL, 1, '2025-06-21 23:29:23'),
(21, 1, 'P016', NULL, 'Caja de clavos 1\"', 'Clavos galvanizados', 2, NULL, 1, 0.00, 4.00, NULL, 30, 300, NULL, NULL, 1, '2025-06-21 23:29:23'),
(22, 1, 'P017', NULL, 'Lija de agua 220', 'Hoja de lija fina', 3, NULL, 1, 0.00, 0.90, NULL, 100, 1000, NULL, NULL, 1, '2025-06-21 23:29:23'),
(23, 1, 'P018', NULL, 'Juego de brocas', 'Brocas HSS 6 piezas', 1, 3, 1, 0.00, 6.80, NULL, 10, 80, NULL, NULL, 1, '2025-06-21 23:29:23'),
(24, 1, 'P019', NULL, 'Foco incandescente 60W', 'Rosca E27 luz cálida', 4, 5, 1, 0.00, 1.10, NULL, 50, 300, NULL, NULL, 1, '2025-06-21 23:29:23'),
(25, 1, 'P020', NULL, 'Varilla corrugada', 'Varilla de acero 3/8\" x 6m', 2, NULL, 2, 0.00, 6.25, NULL, 100, 1000, NULL, NULL, 1, '2025-06-21 23:29:23'),
(26, 1, '3464578', '4444565547655', 'Sillas', '', 1, 4, NULL, 0.79, 1.03, NULL, 10, 0, '', '', 1, '2025-07-26 00:27:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_proveedores`
--

CREATE TABLE `productos_proveedores` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `codigo_proveedor` varchar(50) DEFAULT NULL,
  `precio_compra` decimal(10,2) DEFAULT 0.00,
  `tiempo_entrega` int(11) DEFAULT 7,
  `es_principal` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos_proveedores`
--

INSERT INTO `productos_proveedores` (`id`, `empresa_id`, `producto_id`, `proveedor_id`, `codigo_proveedor`, `precio_compra`, `tiempo_entrega`, `es_principal`, `activo`, `fecha_registro`) VALUES
(1, 1, 1, 1, 'MART-STAN-001', 15.00, 5, 1, 1, '2025-01-15 15:00:00'),
(2, 1, 2, 1, 'DEST-STAN-001', 3.50, 5, 1, 1, '2025-01-15 15:00:00'),
(3, 1, 3, 1, 'TORN-GEN-001', 0.05, 3, 1, 1, '2025-01-15 15:00:00'),
(4, 1, 4, 1, 'PINB-SHW-001', 25.00, 7, 1, 1, '2025-01-15 15:00:00'),
(5, 1, 5, 1, 'TALA-BKD-001', 75.00, 10, 1, 1, '2025-01-15 15:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `tipo_proveedor` enum('distribuidor','fabricante','importador','mayorista','otro') DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `razon_social` varchar(200) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono_principal` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nombre_contacto` varchar(100) DEFAULT NULL,
  `cargo_contacto` varchar(100) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `email_contacto` varchar(100) DEFAULT NULL,
  `dias_credito` int(11) DEFAULT 0,
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00,
  `tiempo_entrega` int(11) DEFAULT 7,
  `monto_minimo` decimal(10,2) DEFAULT 0.00,
  `condiciones_pago` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `horario_atencion` varchar(100) DEFAULT NULL,
  `productos_principales` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `empresa_id`, `codigo`, `tipo_proveedor`, `nombre`, `razon_social`, `ruc`, `direccion`, `telefono_principal`, `email`, `nombre_contacto`, `cargo_contacto`, `telefono_contacto`, `email_contacto`, `dias_credito`, `descuento_porcentaje`, `tiempo_entrega`, `monto_minimo`, `condiciones_pago`, `sitio_web`, `horario_atencion`, `productos_principales`, `observaciones`, `activo`, `fecha_registro`) VALUES
(1, 1, 'PROV001', 'distribuidor', 'Distribuidora Central', 'Distribuidora Central S.A.', '87654321-1-654321', 'Zona Libre de Colón, Galera 12', '507-444-5555', 'contacto@distribuidoracentral.com', 'Luis Gómez', 'Ejecutivo de Ventas', '507-6789-1234', 'lgomez@distribuidoracentral.com', 30, 10.00, 7, 500.00, 'Transferencia bancaria a 30 días', 'https://www.distribuidoracentral.com', 'Lunes a Viernes 8:00am - 5:00pm', 'Herramientas, tornillos, materiales de construcción', 'Proveedor confiable con entregas puntuales', 1, '2025-06-10 07:14:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secuencias`
--

CREATE TABLE `secuencias` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `prefijo` varchar(10) DEFAULT NULL,
  `siguiente_numero` int(11) DEFAULT 1,
  `longitud` int(11) DEFAULT 6
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `secuencias`
--

INSERT INTO `secuencias` (`id`, `empresa_id`, `tipo`, `prefijo`, `siguiente_numero`, `longitud`) VALUES
(1, 1, 'factura', 'F', 1, 6),
(2, 1, 'orden_compra', 'OC', 1, 6),
(3, 1, 'cliente', 'CLI', 2, 6),
(4, 1, 'proveedor', 'PROV', 2, 6),
(5, 1, 'producto', 'PROD', 6, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_movimiento`
--

CREATE TABLE `tipos_movimiento` (
  `id` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `tipo` enum('entrada','salida','ajuste') NOT NULL,
  `afecta_costo` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_movimiento`
--

INSERT INTO `tipos_movimiento` (`id`, `codigo`, `descripcion`, `tipo`, `afecta_costo`) VALUES
(1, 'COMPRA', 'Compra a proveedor', 'entrada', 1),
(2, 'VENTA', 'Venta a cliente', 'salida', 0),
(3, 'AJUSTE+', 'Ajuste positivo', 'entrada', 0),
(4, 'AJUSTE-', 'Ajuste negativo', 'salida', 0),
(5, 'DEVOL_V', 'Devolución de venta', 'entrada', 0),
(6, 'DEVOL_C', 'Devolución a proveedor', 'salida', 0),
(7, 'INICIAL', 'Inventario inicial', 'entrada', 1),
(8, 'DAÑO', 'Producto dañado', 'salida', 0),
(9, 'TRANSFER', 'Transferencia', 'ajuste', 0),
(10, 'MERMA', 'Merma de producto', 'salida', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades_medida`
--

CREATE TABLE `unidades_medida` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `abreviatura` varchar(10) NOT NULL,
  `tipo` enum('peso','longitud','volumen','unidad') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `unidades_medida`
--

INSERT INTO `unidades_medida` (`id`, `nombre`, `abreviatura`, `tipo`) VALUES
(1, 'Unidad', 'und', 'unidad'),
(2, 'Kilogramo', 'kg', 'peso'),
(3, 'Gramo', 'g', 'peso'),
(4, 'Metro', 'm', 'longitud'),
(5, 'Centímetro', 'cm', 'longitud'),
(6, 'Litro', 'l', 'volumen'),
(7, 'Mililitro', 'ml', 'volumen'),
(8, 'Caja', 'cja', 'unidad'),
(9, 'Paquete', 'pqt', 'unidad'),
(10, 'Rollo', 'rll', 'unidad'),
(11, 'Galón', 'gal', 'volumen'),
(12, 'Libra', 'lb', 'peso'),
(13, 'Pie', 'ft', 'longitud'),
(14, 'Pulgada', 'in', 'longitud'),
(15, 'Docena', 'doc', 'unidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor','bodeguero','contador') NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `empresa_id`, `nombre`, `apellido`, `email`, `password_hash`, `rol`, `telefono`, `fecha_creacion`, `ultimo_acceso`, `activo`) VALUES
(1, 1, 'Admin', 'Sistema', 'admin@ferreteria.com', '0192023a7bbd73250516f069df18b500', 'admin', '507-123-4567', '2025-06-10 07:14:46', NULL, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `idx_clientes_cedula` (`cedula_ruc`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_empresa_clave` (`empresa_id`,`clave`);

--
-- Indices de la tabla `detalle_facturas_venta`
--
ALTER TABLE `detalle_facturas_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factura_id` (`factura_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `detalle_ordenes_compra`
--
ALTER TABLE `detalle_ordenes_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`);

--
-- Indices de la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_empresa_factura` (`empresa_id`,`numero_factura`),
  ADD KEY `vendedor_id` (`vendedor_id`),
  ADD KEY `idx_facturas_fecha` (`fecha_factura`),
  ADD KEY `idx_facturas_cliente` (`cliente_id`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_producto` (`producto_id`),
  ADD KEY `idx_inventario_producto` (`producto_id`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `tipo_movimiento_id` (`tipo_movimiento_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_movimientos_fecha` (`fecha_movimiento`);

--
-- Indices de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_empresa_orden` (`empresa_id`,`numero_orden`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pagos_facturas`
--
ALTER TABLE `pagos_facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factura_id` (`factura_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_empresa_codigo` (`empresa_id`,`codigo`),
  ADD KEY `marca_id` (`marca_id`),
  ADD KEY `unidad_medida_id` (`unidad_medida_id`),
  ADD KEY `idx_productos_codigo` (`codigo`),
  ADD KEY `idx_productos_nombre` (`nombre`),
  ADD KEY `idx_productos_categoria` (`categoria_id`);

--
-- Indices de la tabla `productos_proveedores`
--
ALTER TABLE `productos_proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_producto_proveedor` (`producto_id`,`proveedor_id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `idx_productos_proveedores_principal` (`es_principal`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_empresa_ruc` (`empresa_id`,`ruc`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `idx_proveedores_nombre` (`nombre`),
  ADD KEY `idx_proveedores_activo` (`activo`);

--
-- Indices de la tabla `secuencias`
--
ALTER TABLE `secuencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_empresa_tipo` (`empresa_id`,`tipo`);

--
-- Indices de la tabla `tipos_movimiento`
--
ALTER TABLE `tipos_movimiento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `unidades_medida`
--
ALTER TABLE `unidades_medida`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_facturas_venta`
--
ALTER TABLE `detalle_facturas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `detalle_ordenes_compra`
--
ALTER TABLE `detalle_ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pagos_facturas`
--
ALTER TABLE `pagos_facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `productos_proveedores`
--
ALTER TABLE `productos_proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `secuencias`
--
ALTER TABLE `secuencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipos_movimiento`
--
ALTER TABLE `tipos_movimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `unidades_medida`
--
ALTER TABLE `unidades_medida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categorias_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD CONSTRAINT `configuraciones_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_facturas_venta`
--
ALTER TABLE `detalle_facturas_venta`
  ADD CONSTRAINT `detalle_facturas_venta_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas_venta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_facturas_venta_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `detalle_ordenes_compra`
--
ALTER TABLE `detalle_ordenes_compra`
  ADD CONSTRAINT `detalle_ordenes_compra_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ordenes_compra_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  ADD CONSTRAINT `facturas_venta_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `facturas_venta_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `facturas_venta_ibfk_3` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD CONSTRAINT `marcas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimientos_inventario_ibfk_3` FOREIGN KEY (`tipo_movimiento_id`) REFERENCES `tipos_movimiento` (`id`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_4` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD CONSTRAINT `ordenes_compra_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ordenes_compra_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `ordenes_compra_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pagos_facturas`
--
ALTER TABLE `pagos_facturas`
  ADD CONSTRAINT `pagos_facturas_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas_venta` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `productos_ibfk_3` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `productos_ibfk_4` FOREIGN KEY (`unidad_medida_id`) REFERENCES `unidades_medida` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos_proveedores`
--
ALTER TABLE `productos_proveedores`
  ADD CONSTRAINT `productos_proveedores_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_proveedores_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_proveedores_ibfk_3` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `secuencias`
--
ALTER TABLE `secuencias`
  ADD CONSTRAINT `secuencias_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
