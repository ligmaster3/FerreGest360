<?php
require_once '../config/connection.php';
include 'partials/head.php';

// Inicializar variables
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$cliente_busqueda = $_GET['cliente'] ?? '';
$tipo_reporte = $_GET['tipo_reporte'] ?? 'general';
$producto_busqueda = $_GET['producto'] ?? '';

// Construcción de la consulta base
$sql_base = "
    FROM facturas_venta f
    JOIN clientes c ON f.cliente_id = c.id
    LEFT JOIN detalle_facturas_venta dfv ON f.id = dfv.factura_id
    WHERE f.fecha_factura BETWEEN :fecha_inicio AND :fecha_fin
";
$params = [
    ':fecha_inicio' => $fecha_inicio,
    ':fecha_fin' => $fecha_fin
];

if (!empty($cliente_busqueda)) {
    $sql_base .= " AND c.nombre LIKE :cliente";
    $params[':cliente'] = '%' . $cliente_busqueda . '%';
}

// 1. Estadísticas rápidas
$sql_stats = "SELECT
    COUNT(f.id) as num_ventas,
    SUM(f.total) as ventas_totales,
    AVG(f.total) as venta_promedio,
    COUNT(DISTINCT f.fecha_factura) as dias_con_ventas,
    COUNT(DISTINCT f.cliente_id) as clientes_unicos,
    MAX(f.total) as venta_mas_alta,
    MIN(f.total) as venta_mas_baja
" . str_replace("LEFT JOIN detalle_facturas_venta dfv ON f.id = dfv.factura_id", "", $sql_base);
$stmt_stats = ejecutarConsulta($sql_stats, $params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);


$sql_stats_mes = "SELECT
    COUNT(f.id) as num_ventas,
    SUM(f.total) as ventas_totales,
    AVG(f.total) as venta_promedio,
    COUNT(DISTINCT f.fecha_factura) as dias_con_ventas,
    COUNT(DISTINCT f.cliente_id) as clientes_unicos,
    MAX(f.total) as venta_mas_alta,
    MIN(f.total) as venta_mas_baja
FROM facturas_venta f
JOIN clientes c ON f.cliente_id = c.id
WHERE f.fecha_factura BETWEEN :fecha_inicio AND :fecha_fin";

$stmt_mes = ejecutarConsulta($sql_stats_mes, [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$stats_mes = $stmt_mes->fetch(PDO::FETCH_ASSOC);


// 2. Estadísticas generales (todos los datos)
$sql_stats_general = "SELECT
    COUNT(f.id) as num_ventas,
    SUM(f.total) as ventas_totales,
    AVG(f.total) as venta_promedio,
    COUNT(DISTINCT f.fecha_factura) as dias_con_ventas,
    COUNT(DISTINCT f.cliente_id) as clientes_unicos,
    MAX(f.total) as venta_mas_alta,
    MIN(f.total) as venta_mas_baja
FROM facturas_venta f
JOIN clientes c ON f.cliente_id = c.id";

$stmt_general = ejecutarConsulta($sql_stats_general);
$stats_general = $stmt_general->fetch(PDO::FETCH_ASSOC);


// 3. Top 5 productos más vendidos
$sql_top_productos = "SELECT 
    p.nombre as producto,
    p.codigo,
    SUM(dfv.cantidad) as cantidad_vendida,
    SUM(dfv.subtotal) as total_vendido
FROM detalle_facturas_venta dfv
JOIN productos p ON dfv.producto_id = p.id
JOIN facturas_venta f ON dfv.factura_id = f.id
GROUP BY p.id
ORDER BY cantidad_vendida DESC
LIMIT 5";


// 4. Top 5 clientes
$sql_top_clientes = "SELECT 
    c.nombre as cliente,
    c.tipo_cliente,
    COUNT(f.id) as num_compras,
    SUM(f.total) as total_gastado
FROM facturas_venta f
JOIN clientes c ON f.cliente_id = c.id
GROUP BY c.id
ORDER BY total_gastado DESC
LIMIT 5";



// 5. Estados de ventas
$sql_estados = "SELECT 
    estado,
    COUNT(*) as cantidad,
    SUM(total) as total_estado
FROM facturas_venta
GROUP BY estado
ORDER BY cantidad DESC";

$stmt_estados = ejecutarConsulta($sql_estados);
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);


// 6. Ventas por categoría
$sql_categorias = "SELECT 
    cat.nombre as categoria, 
    SUM(dfv.subtotal) as total_venta, 
    COUNT(DISTINCT f.id) as num_ventas
FROM detalle_facturas_venta dfv
JOIN productos p ON dfv.producto_id = p.id
JOIN categorias cat ON p.categoria_id = cat.id
JOIN facturas_venta f ON dfv.factura_id = f.id
GROUP BY cat.nombre
ORDER BY total_venta DESC";

$stmt_categorias = ejecutarConsulta($sql_categorias);
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);



// 7. Resumen de inventario
$sql_inventario = "SELECT 
    COUNT(p.id) as total_productos,
    SUM(i.stock_actual) as stock_total,
    COUNT(CASE WHEN i.stock_actual <= p.stock_minimo THEN 1 END) as productos_bajo_stock,
    AVG(i.stock_actual) as stock_promedio
FROM productos p
LEFT JOIN inventario i ON p.id = i.producto_id
WHERE p.activo = 1";

$stmt_inventario = ejecutarConsulta($sql_inventario);
$inventario = $stmt_inventario->fetch(PDO::FETCH_ASSOC);



// 2. Historial de ventas para la tabla
$sql_historial = "SELECT 
    f.id, 
    f.numero_factura, 
    c.nombre as cliente_nombre,
    c.tipo_cliente,
    f.fecha_factura, 
    f.total,
    f.estado,
    COUNT(dfv.id) as num_productos
" . $sql_base . " 
GROUP BY f.id
ORDER BY f.fecha_factura DESC";
$stmt_historial = ejecutarConsulta($sql_historial, $params);
$historial_ventas = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

// 3. Ventas por Categoría (para el gráfico)
$sql_categorias = "
    SELECT cat.nombre as categoria, SUM(dfv.subtotal) as total_venta, COUNT(DISTINCT f.id) as num_ventas
    FROM detalle_facturas_venta dfv
    JOIN productos p ON dfv.producto_id = p.id
    JOIN categorias cat ON p.categoria_id = cat.id
    JOIN facturas_venta f ON dfv.factura_id = f.id
    WHERE f.fecha_factura BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY cat.nombre
    ORDER BY total_venta DESC
";
$stmt_categorias = ejecutarConsulta($sql_categorias, [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$ventas_por_categoria = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
$categorias_json = json_encode($ventas_por_categoria);

// 4. Tendencia de Ventas (para el gráfico)
$sql_tendencia = "
    SELECT DATE(fecha_factura) as fecha, SUM(total) as total_dia, COUNT(*) as num_ventas
    FROM facturas_venta
    WHERE fecha_factura BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY DATE(fecha_factura)
    ORDER BY fecha ASC
";
$stmt_tendencia = ejecutarConsulta($sql_tendencia, [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$tendencia_ventas = $stmt_tendencia->fetchAll(PDO::FETCH_ASSOC);
$tendencia_json = json_encode($tendencia_ventas);

// 5. Estados de Ventas
$sql_estados = "
    SELECT 
        estado,
        COUNT(*) as cantidad,
        SUM(total) as total_estado
    FROM facturas_venta
    WHERE fecha_factura BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY estado
    ORDER BY cantidad DESC
";
$stmt_estados = ejecutarConsulta($sql_estados, [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$estados_ventas = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);
$estados_json = json_encode($estados_ventas);

// 6. Top Productos Vendidos
$sql_top_productos = "
    SELECT 
        p.nombre as producto,
        p.codigo,
        SUM(dfv.cantidad) as cantidad_vendida,
        SUM(dfv.subtotal) as total_vendido,
        AVG(dfv.precio_unitario) as precio_promedio
    FROM detalle_facturas_venta dfv
    JOIN productos p ON dfv.producto_id = p.id
    JOIN facturas_venta f ON dfv.factura_id = f.id
    WHERE f.fecha_factura BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY p.id
    ORDER BY cantidad_vendida DESC
    LIMIT 10
";
$stmt_top_productos = ejecutarConsulta($sql_top_productos, [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$top_productos = $stmt_top_productos->fetchAll(PDO::FETCH_ASSOC);
$top_productos_json = json_encode($top_productos);

// 7. Top Clientes
$sql_top_clientes = "
    SELECT 
        c.nombre as cliente,
        c.tipo_cliente,
        COUNT(f.id) as num_compras,
        SUM(f.total) as total_gastado,
        AVG(f.total) as promedio_compra
    FROM facturas_venta f
    JOIN clientes c ON f.cliente_id = c.id
    WHERE f.fecha_factura BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY c.id
    ORDER BY total_gastado DESC
    LIMIT 10
";
$stmt_top_clientes = ejecutarConsulta($sql_top_clientes, [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$top_clientes = $stmt_top_clientes->fetchAll(PDO::FETCH_ASSOC);
$top_clientes_json = json_encode($top_clientes);

// 8. Ventas por Día de la Semana
$sql_dias_semana = "
    SELECT 
        DAYNAME(fecha_factura) as dia_semana,
        COUNT(*) as num_ventas,
        SUM(total) as total_dia
    FROM facturas_venta
    WHERE fecha_factura BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY DAYNAME(fecha_factura)
    ORDER BY FIELD(DAYNAME(fecha_factura), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
";
$stmt_dias_semana = ejecutarConsulta($sql_dias_semana, [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$dias_semana = $stmt_dias_semana->fetchAll(PDO::FETCH_ASSOC);
$dias_semana_json = json_encode($dias_semana);

?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<body>
    <div id="dashboardScreen" class="dashboard-container">
        <?php include 'partials/sidebar.php'; ?>
        <main class="main-content">
            <?php include 'partials/header.php'; ?>
            <div class="content-area">
                <div class="page-header">
                    <div>
                        <h1>Reportes y Análisis</h1>
                        <p>Estadísticas detalladas de ventas, inventario y clientes</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn-primary" onclick="exportarPDF()">
                            <i class="fas fa-download"></i> Exportar PDF
                        </button>
                        <button class="btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <!-- Filtros de reportes -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="reportes.php" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                                <select id="tipo_reporte" name="tipo_reporte" class="form-control">
                                    <option value="general"
                                        <?php echo $tipo_reporte === 'general' ? 'selected' : ''; ?>>General</option>
                                    <option value="ventas" <?php echo $tipo_reporte === 'ventas' ? 'selected' : ''; ?>>
                                        Ventas</option>
                                    <option value="productos"
                                        <?php echo $tipo_reporte === 'productos' ? 'selected' : ''; ?>>Productos
                                    </option>
                                    <option value="clientes"
                                        <?php echo $tipo_reporte === 'clientes' ? 'selected' : ''; ?>>Clientes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_cliente" class="form-label">Buscar por cliente</label>
                                <input id="filtro_cliente" type="text" name="cliente"
                                    placeholder="Escriba para buscar..." class="form-control"
                                    value="<?php echo htmlspecialchars($cliente_busqueda); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_producto" class="form-label">Buscar por producto</label>
                                <input id="filtro_producto" type="text" name="producto"
                                    placeholder="Escriba para buscar..." class="form-control"
                                    value="<?php echo htmlspecialchars($producto_busqueda); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_fecha_inicio" class="form-label">Fecha de inicio</label>
                                <input id="filtro_fecha_inicio" type="date" name="fecha_inicio" class="form-control"
                                    title="Fecha de inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_fecha_fin" class="form-label">Fecha de fin</label>
                                <input id="filtro_fecha_fin" type="date" name="fecha_fin" class="form-control"
                                    title="Fecha de fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                            </div>
                            <div class="col-md-1">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary w-100 me-2">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary" title="Limpiar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="stats-grid mb-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">$<?php echo number_format($stats_general['ventas_totales'] ?? 0, 2)  ?>
                            </h3>
                            <p class="stat-label">Ventas Totales</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $stats_general['num_ventas'] ?? 0; ?></h3>
                            <p class="stat-label">Número de Ventas</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">
                                $<?php echo number_format($stats_general['venta_promedio'] ?? 0, 2); ?></h3>
                            <p class="stat-label">Venta Promedio</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo ($stats_general['clientes_unicos'] ?? 0); ?></h3>
                            <p class="stat-label">Clientes Únicos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-danger">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $stats_general['dias_con_ventas'] ?? 0; ?></h3>
                            <p class="stat-label">Días con Ventas</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-secondary">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">$<?php echo number_format($stats_general['venta_mas_alta'] ?? 0, 2); ?>
                            </h3>
                            <p class="stat-label">Venta Más Alta</p>
                        </div>
                    </div>
                </div>



                <!-- Gráficos principales -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ventas por Categoría</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="ventasPorCategoriaChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Tendencia de Ventas</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="tendenciaVentasChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos secundarios -->
                <div class="row mb-4">
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Estados de Ventas</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="estadosVentasChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ventas por Día de la Semana</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="diasSemanaChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Productos</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="topProductosChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tablas de datos -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top 10 Productos Vendidos</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($top_productos, 0, 10) as $producto): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($producto['producto']); ?></td>
                                                    <td><?php echo $producto['cantidad_vendida']; ?></td>
                                                    <td>$<?php echo number_format($producto['total_vendido'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top 10 Clientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Compras</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($top_clientes, 0, 10) as $cliente): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($cliente['cliente']); ?></td>
                                                    <td><?php echo $cliente['num_compras']; ?></td>
                                                    <td>$<?php echo number_format($cliente['total_gastado'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de ventas -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Historial Detallado de Ventas</h5>
                        <div class="header-actions">
                            <button class="btn-primary" onclick="exportarHistorial()">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                            <button class="btn-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Factura</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Productos</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($historial_ventas) > 0): ?>
                                        <?php foreach ($historial_ventas as $venta): ?>
                                            <tr>
                                                <td>
                                                    <span
                                                        class="badge bg-primary"><?php echo htmlspecialchars($venta['numero_factura']); ?></span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($venta['cliente_nombre']); ?></strong>
                                                        <small class="text-muted d-block">ID:
                                                            <?php echo $venta['id']; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php echo $venta['tipo_cliente'] === 'natural' ? 'info' : 'warning'; ?>">
                                                        <?php echo ucfirst($venta['tipo_cliente']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha_factura'])); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $venta['num_productos']; ?>
                                                        items</span>
                                                </td>
                                                <td>
                                                    <strong>$<?php echo number_format($venta['total'], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php echo $venta['estado'] === 'pagado' ? 'success' : ($venta['estado'] === 'pendiente' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($venta['estado'] ?? 'pendiente'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-info" title="Ver Detalle"
                                                            onclick="verDetalleVenta(<?php echo $venta['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-primary" title="Imprimir"
                                                            onclick="imprimirFactura(<?php echo $venta['id']; ?>)">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" title="Enviar"
                                                            onclick="enviarFactura(<?php echo $venta['id']; ?>)">
                                                            <i class="fas fa-envelope"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No se encontraron ventas para el período seleccionado.
                                                </p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include 'partials/modals.php'; ?>

    <script src="js/reportes.js"></script>
    <script>
        // Variables globales para los gráficos
        let ventasCategoriaChart = null;
        let tendenciaVentasChart = null;
        let estadosVentasChart = null;
        let diasSemanaChart = null;
        let topProductosChart = null;

        // Función para destruir gráficos existentes
        function destroyCharts() {
            if (ventasCategoriaChart) {
                ventasCategoriaChart.destroy();
                ventasCategoriaChart = null;
            }
            if (tendenciaVentasChart) {
                tendenciaVentasChart.destroy();
                tendenciaVentasChart = null;
            }
            if (estadosVentasChart) {
                estadosVentasChart.destroy();
                estadosVentasChart = null;
            }
            if (diasSemanaChart) {
                diasSemanaChart.destroy();
                diasSemanaChart = null;
            }
            if (topProductosChart) {
                topProductosChart.destroy();
                topProductosChart = null;
            }
        }

        // Función para inicializar todos los gráficos
        function initializeCharts() {
            // Destruir gráficos existentes primero
            destroyCharts();

            // Datos desde PHP
            const ventasCategoriaData = <?php echo $categorias_json; ?>;
            const tendenciaVentasData = <?php echo $tendencia_json; ?>;
            const estadosVentasData = <?php echo $estados_json; ?>;
            const topProductosData = <?php echo $top_productos_json; ?>;
            const diasSemanaData = <?php echo $dias_semana_json; ?>;

            // Colores para gráficos
            const colors = [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)',
                'rgba(83, 102, 255, 0.8)'
            ];

            // Gráfico de Ventas por Categoría
            const ctxCategoria = document.getElementById('ventasPorCategoriaChart');
            if (ctxCategoria) {
                ventasCategoriaChart = new Chart(ctxCategoria, {
                    type: 'bar',
                    data: {
                        labels: ventasCategoriaData.map(item => item.categoria),
                        datasets: [{
                            label: 'Ventas por Categoría',
                            data: ventasCategoriaData.map(item => item.total_venta),
                            backgroundColor: colors,
                            borderColor: colors.map(color => color.replace('0.8', '1')),
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de Tendencia de Ventas
            const ctxTendencia = document.getElementById('tendenciaVentasChart');
            if (ctxTendencia) {
                tendenciaVentasChart = new Chart(ctxTendencia, {
                    type: 'line',
                    data: {
                        labels: tendenciaVentasData.map(item => new Date(item.fecha).toLocaleDateString()),
                        datasets: [{
                            label: 'Ventas Diarias',
                            data: tendenciaVentasData.map(item => item.total_dia),
                            fill: true,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 3,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de Estados de Ventas
            const ctxEstados = document.getElementById('estadosVentasChart');
            if (ctxEstados) {
                estadosVentasChart = new Chart(ctxEstados, {
                    type: 'doughnut',
                    data: {
                        labels: estadosVentasData.map(item => item.estado),
                        datasets: [{
                            data: estadosVentasData.map(item => item.cantidad),
                            backgroundColor: colors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Gráfico de Ventas por Día de la Semana
            const ctxDias = document.getElementById('diasSemanaChart');
            if (ctxDias) {
                diasSemanaChart = new Chart(ctxDias, {
                    type: 'radar',
                    data: {
                        labels: diasSemanaData.map(item => item.dia_semana),
                        datasets: [{
                            label: 'Ventas',
                            data: diasSemanaData.map(item => item.total_dia),
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de Top Productos
            const ctxProductos = document.getElementById('topProductosChart');
            if (ctxProductos) {
                topProductosChart = new Chart(ctxProductos, {
                    type: 'bar',
                    data: {
                        labels: topProductosData.slice(0, 5).map(item => item.producto.substring(0, 20) + '...'),
                        datasets: [{
                            label: 'Cantidad Vendida',
                            data: topProductosData.slice(0, 5).map(item => item.cantidad_vendida),
                            backgroundColor: colors,
                            borderColor: colors.map(color => color.replace('0.8', '1')),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar gráficos cuando se carga la página
            initializeCharts();
        });

        // Función para actualizar gráficos (puede ser llamada desde otros scripts)
        function updateCharts() {
            initializeCharts();
        }

        window.ventasPorCategoriaData = <?php echo $categorias_json; ?>;

        window.exportarPDF = function() {
            showLoadingModal('Generando reporte PDF...');

            setTimeout(() => {
                hideLoadingModal();

                // Usar jsPDF UMD
                const {
                    jsPDF
                } = window.jspdf;
                var doc = new jsPDF();

                // Si tienes logo, pon la ruta aquí. Si no, déjalo vacío.
                var logoUrl = ''; // Ejemplo: '../src/assets/img/logoUnachi.jpg'

                function generarPDF() {
                    doc.setFontSize(16);
                    doc.setTextColor(44, 62, 80);
                    doc.text('FerreGest360 - Reporte de Ventas', 45, 20);

                    doc.setFontSize(10);
                    doc.setTextColor(44, 62, 80);
                    doc.text('Fecha: ' + new Date().toLocaleString(), 45, 28);

                    var userName = window.userName || 'Administrador';
                    doc.setFontSize(10);
                    doc.text('Generado por: ' + userName, 45, 36);

                    // Datos de ejemplo: Ventas por Categoría
                    var headers = [
                        ['Categoría', 'Total Ventas', 'N° Ventas']
                    ];
                    // Los datos los pasas desde PHP a JS en reportes.php:
                    var data = (window.ventasPorCategoriaData || []).map(item => [
                        item.categoria,
                        '$' + Number(item.total_venta).toLocaleString(),
                        item.num_ventas
                    ]);

                    doc.autoTable({
                        head: headers,
                        body: data,
                        startY: 55,
                        theme: 'grid',
                        headStyles: {
                            fillColor: [52, 152, 219],
                            textColor: 255,
                            fontSize: 10,
                            fontStyle: 'bold'
                        },
                        bodyStyles: {
                            fontSize: 9
                        },
                        alternateRowStyles: {
                            fillColor: [245, 245, 245]
                        },
                        styles: {
                            cellPadding: 3,
                            lineColor: [200, 200, 200],
                            lineWidth: 0.1
                        },
                        margin: {
                            top: 55
                        }
                    });

                    doc.setFontSize(8);
                    doc.text('FerreGest360 - Sistema de Gestión', 14, doc.internal.pageSize.height - 10);

                    doc.save('reporte_ventas_' + new Date().toISOString().split('T')[0] + '.pdf');
                    showSuccessModal('Reporte PDF generado y descargado correctamente');
                }

                if (logoUrl) {
                    var img = new window.Image();
                    img.src = logoUrl;
                    img.onload = function() {
                        doc.addImage(img, 'JPEG', 14, 10, 25, 25);
                        generarPDF();
                    };
                    img.onerror = function() {
                        generarPDF();
                    };
                } else {
                    generarPDF();
                }
            }, 1000);
        };
    </script>
</body>

</html>