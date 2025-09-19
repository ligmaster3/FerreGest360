<?php
require_once '../config/connection.php';
session_start();

// Consultas para las tarjetas de estadísticas
// 1. Ventas de Hoy
$stmt_ventas_hoy = ejecutarConsulta("SELECT SUM(total) as total FROM facturas_venta WHERE DATE(fecha_factura) = CURDATE()");
$ventas_hoy = $stmt_ventas_hoy->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 2. Total de Productos
$stmt_total_productos = ejecutarConsulta("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$total_productos = $stmt_total_productos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 3. Productos con Stock Bajo
$stmt_stock_bajo = ejecutarConsulta("SELECT COUNT(*) as total FROM productos p JOIN inventario i ON p.id = i.producto_id WHERE i.stock_actual <= p.stock_minimo AND p.activo = 1");
$total_stock_bajo = $stmt_stock_bajo->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 4. Total de Clientes
$stmt_total_clientes = ejecutarConsulta("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
$total_clientes = $stmt_total_clientes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Consultas para las secciones de actividad reciente
// 5. Lista de Productos con Stock Bajo
$stmt_productos_stock_bajo = ejecutarConsulta("SELECT p.nombre, i.stock_actual as stock, p.stock_minimo FROM productos p JOIN inventario i ON p.id = i.producto_id WHERE i.stock_actual <= p.stock_minimo AND p.activo = 1 ORDER BY (i.stock_actual - p.stock_minimo) ASC LIMIT 5");
$productos_stock_bajo = $stmt_productos_stock_bajo->fetchAll(PDO::FETCH_ASSOC);

// 6. Ventas Recientes
$stmt_ventas_recientes = ejecutarConsulta("SELECT f.numero_factura, c.nombre as cliente_nombre, f.total, f.fecha_factura 
                           FROM facturas_venta f 
                           JOIN clientes c ON f.cliente_id = c.id 
                           ORDER BY f.fecha_factura DESC 
                           LIMIT 5");
$ventas_recientes = $stmt_ventas_recientes->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include 'partials/head.php'; ?>

<body>

    <!-- Main Dashboard -->
    <div id="dashboardScreen" class="dashboard-container">
        <?php include 'partials/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <?php include 'partials/header.php'; ?>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Dashboard Section -->
                <section id="dashboard-content" class="content-section active">
                    <div class="page-header">
                        <h1>Dashboard</h1>
                        <p>Resumen general de tu ferretería</p>
                    </div>

                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card revenue">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Ventas Hoy</h3>
                                <p class="stat-value">$<?php echo number_format($ventas_hoy, 2); ?></p>
                                <span class="stat-change positive">+12%</span>
                            </div>
                        </div>

                        <div class="stat-card products">
                            <div class="stat-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Total Productos</h3>
                                <p class="stat-value"><?php echo $total_productos; ?></p>
                                <span class="stat-change neutral">Activos</span>
                            </div>
                        </div>

                        <div class="stat-card stock">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Stock Bajo</h3>
                                <p class="stat-value"><?php echo $total_stock_bajo; ?></p>
                                <span class="stat-change negative">Crítico</span>
                            </div>
                        </div>

                        <div class="stat-card clients">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Clientes</h3>
                                <p class="stat-value"><?php echo $total_clientes; ?></p>
                                <span class="stat-change positive">+5 nuevos</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h2>Acciones Rápidas</h2>
                        <div class="actions-grid">
                            <button class="action-btn primary" onclick="showSection('ventas')">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Nueva Venta</span>
                            </button>
                            <button class="action-btn secondary" onclick="showAddProductModal()">
                                <i class="fas fa-plus"></i>
                                <span>Agregar Producto</span>
                            </button>
                            <button class="action-btn secondary" onclick="showAddClientModal()">
                                <i class="fas fa-user-plus"></i>
                                <span>Nuevo Cliente</span>
                            </button>
                            <button class="action-btn secondary" onclick="showAddProviderModal()">
                                <i class="fas fa-truck"></i>
                                <span>Nuevo Proveedor</span>
                            </button>
                            <button class="action-btn secondary" onclick="showSection('reportes')">
                                <i class="fas fa-chart-line"></i>
                                <span>Ver Reportes</span>
                            </button>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="activity-section">
                        <div class="low-stock-alert">
                            <h3><i class="fas fa-exclamation-triangle"></i> Productos con Stock Bajo</h3>
                            <div class="alert-list">
                                <?php if (count($productos_stock_bajo) > 0): ?>
                                    <?php foreach ($productos_stock_bajo as $producto): ?>
                                        <div class="alert-item">
                                            <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                            <span class="stock-info">Stock: <?php echo $producto['stock']; ?> | Mínimo: <?php echo $producto['stock_minimo']; ?></span>
                                            <span class="alert-badge critical">Crítico</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No hay productos con stock bajo.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="recent-sales">
                            <h3><i class="fas fa-shopping-cart"></i> Ventas Recientes</h3>
                            <div class="sales-list">
                                <?php if (count($ventas_recientes) > 0): ?>
                                    <?php foreach ($ventas_recientes as $venta): ?>
                                        <div class="sale-item">
                                            <div class="sale-info">
                                                <span class="invoice-number"><?php echo htmlspecialchars($venta['numero_factura']); ?></span>
                                                <span class="customer-name"><?php echo htmlspecialchars($venta['cliente_nombre']); ?></span>
                                            </div>
                                            <div class="sale-amount">$<?php echo number_format($venta['total'], 2); ?></div>
                                            <div class="sale-time"><?php echo date('h:i A', strtotime($venta['fecha_factura'])); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No hay ventas recientes hoy.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <!-- Modals -->
    <?php include 'partials/modals.php'; ?>

</body>

</html>