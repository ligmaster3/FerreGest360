CREATE DATABASE IF NOT EXISTS `ferregest360` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
Use `ferregest360`;
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
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
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
(1, 'Ferretería El Martillo', 'Ferretería El Martillo S.A.', '12345678-1-123456', 'Calle 50, Ciudad de Panamá', '507-123-4567', 'info@ferreteriamartillo.com', NULL, '2025-06-10 02:14:46', 1);

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
(1, 1, 10, 0, 15.00, '2025-06-10 02:14:46'),
(2, 2, 25, 0, 3.50, '2025-06-10 02:14:46'),
(3, 3, 500, 0, 0.05, '2025-06-10 02:14:46'),
(4, 4, 5, 0, 25.00, '2025-06-10 02:14:46'),
(5, 5, 3, 0, 75.00, '2025-06-10 02:14:46');

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
(1, 1, 'MART001', NULL, 'Martillo de garra 16oz', 'Martillo de garra Stanley 16 onzas', 1, 1, 1, 15.00, 25.00, NULL, 5, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(2, 1, 'DEST001', NULL, 'Destornillador plano 6\"', 'Destornillador plano Stanley 6 pulgadas', 1, 1, 1, 3.50, 6.00, NULL, 10, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(3, 1, 'TORN001', NULL, 'Tornillo 1/4\" x 2\"', 'Tornillo galvanizado 1/4 x 2 pulgadas', 2, 7, 15, 0.05, 0.10, NULL, 100, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(4, 1, 'PINB001', NULL, 'Pintura blanca 1 galón', 'Pintura látex blanca interior/exterior', 6, 6, 11, 25.00, 45.00, NULL, 3, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(5, 1, 'TALA001', NULL, 'Taladro eléctrico 1/2\"', 'Taladro eléctrico Black & Decker 1/2 pulgada', 1, 2, 1, 75.00, 125.00, NULL, 2, 0, NULL, NULL, 1, '2025-06-10 02:14:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `razon_social` varchar(200) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contacto_nombre` varchar(100) DEFAULT NULL,
  `contacto_telefono` varchar(20) DEFAULT NULL,
  `dias_credito` int(11) DEFAULT 0,
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `empresa_id`, `codigo`, `nombre`, `razon_social`, `ruc`, `direccion`, `telefono`, `email`, `contacto_nombre`, `contacto_telefono`, `dias_credito`, `limite_credito`, `activo`, `fecha_registro`) VALUES
(1, 1, 'PROV001', 'Distribuidora Central', 'Distribuidora Central S.A.', '87654321-1-654321', 'Zona Libre de Colón', '507-444-5555', NULL, NULL, NULL, 30, 0.00, 1, '2025-06-10 02:14:46');

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
(1, 1, 'Admin', 'Sistema', 'admin@ferreteria.com', '0192023a7bbd73250516f069df18b500', 'admin', '507-123-4567', '2025-06-10 02:14:46', NULL, 1);

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
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_facturas_venta`
--
ALTER TABLE `detalle_facturas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ordenes_compra`
--
ALTER TABLE `detalle_ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;CREATE DATABASE IF NOT EXISTS `ferregest360` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
Use `ferregest360`;
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
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
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
(1, 'Ferretería El Martillo', 'Ferretería El Martillo S.A.', '12345678-1-123456', 'Calle 50, Ciudad de Panamá', '507-123-4567', 'info@ferreteriamartillo.com', NULL, '2025-06-10 02:14:46', 1);

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
(1, 1, 10, 0, 15.00, '2025-06-10 02:14:46'),
(2, 2, 25, 0, 3.50, '2025-06-10 02:14:46'),
(3, 3, 500, 0, 0.05, '2025-06-10 02:14:46'),
(4, 4, 5, 0, 25.00, '2025-06-10 02:14:46'),
(5, 5, 3, 0, 75.00, '2025-06-10 02:14:46');

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
(1, 1, 'MART001', NULL, 'Martillo de garra 16oz', 'Martillo de garra Stanley 16 onzas', 1, 1, 1, 15.00, 25.00, NULL, 5, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(2, 1, 'DEST001', NULL, 'Destornillador plano 6\"', 'Destornillador plano Stanley 6 pulgadas', 1, 1, 1, 3.50, 6.00, NULL, 10, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(3, 1, 'TORN001', NULL, 'Tornillo 1/4\" x 2\"', 'Tornillo galvanizado 1/4 x 2 pulgadas', 2, 7, 15, 0.05, 0.10, NULL, 100, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(4, 1, 'PINB001', NULL, 'Pintura blanca 1 galón', 'Pintura látex blanca interior/exterior', 6, 6, 11, 25.00, 45.00, NULL, 3, 0, NULL, NULL, 1, '2025-06-10 02:14:46'),
(5, 1, 'TALA001', NULL, 'Taladro eléctrico 1/2\"', 'Taladro eléctrico Black & Decker 1/2 pulgada', 1, 2, 1, 75.00, 125.00, NULL, 2, 0, NULL, NULL, 1, '2025-06-10 02:14:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `razon_social` varchar(200) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contacto_nombre` varchar(100) DEFAULT NULL,
  `contacto_telefono` varchar(20) DEFAULT NULL,
  `dias_credito` int(11) DEFAULT 0,
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `empresa_id`, `codigo`, `nombre`, `razon_social`, `ruc`, `direccion`, `telefono`, `email`, `contacto_nombre`, `contacto_telefono`, `dias_credito`, `limite_credito`, `activo`, `fecha_registro`) VALUES
(1, 1, 'PROV001', 'Distribuidora Central', 'Distribuidora Central S.A.', '87654321-1-654321', 'Zona Libre de Colón', '507-444-5555', NULL, NULL, NULL, 30, 0.00, 1, '2025-06-10 02:14:46');

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
(1, 1, 'Admin', 'Sistema', 'admin@ferreteria.com', '0192023a7bbd73250516f069df18b500', 'admin', '507-123-4567', '2025-06-10 02:14:46', NULL, 1);

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
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_facturas_venta`
--
ALTER TABLE `detalle_facturas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ordenes_compra`
--
ALTER TABLE `detalle_ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_facturas`
--
ALTER TABLE `pagos_facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
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



--
-- AUTO_INCREMENT de la tabla `pagos_facturas`
--
ALTER TABLE `pagos_facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
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

