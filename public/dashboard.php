<?php
require_once '../config/connection.php';
session_start();

// Verificar si el usuario está autenticado

// Obtener información del usuario

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
                                <p class="stat-value">$1,250.50</p>
                                <span class="stat-change positive">+12%</span>
                            </div>
                        </div>

                        <div class="stat-card products">
                            <div class="stat-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Total Productos</h3>
                                <p class="stat-value">245</p>
                                <span class="stat-change neutral">Activos</span>
                            </div>
                        </div>

                        <div class="stat-card stock">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Stock Bajo</h3>
                                <p class="stat-value">12</p>
                                <span class="stat-change negative">Crítico</span>
                            </div>
                        </div>

                        <div class="stat-card clients">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Clientes</h3>
                                <p class="stat-value">89</p>
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
                                <div class="alert-item">
                                    <span class="product-name">Martillo de garra 16oz</span>
                                    <span class="stock-info">Stock: 2 | Mínimo: 5</span>
                                    <span class="alert-badge critical">Crítico</span>
                                </div>
                                <div class="alert-item">
                                    <span class="product-name">Pintura blanca 1 galón</span>
                                    <span class="stock-info">Stock: 1 | Mínimo: 3</span>
                                    <span class="alert-badge critical">Crítico</span>
                                </div>
                                <div class="alert-item">
                                    <span class="product-name">Taladro eléctrico 1/2"</span>
                                    <span class="stock-info">Stock: 1 | Mínimo: 2</span>
                                    <span class="alert-badge critical">Crítico</span>
                                </div>
                            </div>
                        </div>

                        <div class="recent-sales">
                            <h3><i class="fas fa-shopping-cart"></i> Ventas Recientes</h3>
                            <div class="sales-list">
                                <div class="sale-item">
                                    <div class="sale-info">
                                        <span class="invoice-number">F000001</span>
                                        <span class="customer-name">Juan Pérez</span>
                                    </div>
                                    <div class="sale-amount">$125.50</div>
                                    <div class="sale-time">hace 2 horas</div>
                                </div>
                                <div class="sale-item">
                                    <div class="sale-info">
                                        <span class="invoice-number">F000002</span>
                                        <span class="customer-name">María González</span>
                                    </div>
                                    <div class="sale-amount">$89.25</div>
                                    <div class="sale-time">hace 3 horas</div>
                                </div>
                                <div class="sale-item">
                                    <div class="sale-info">
                                        <span class="invoice-number">F000003</span>
                                        <span class="customer-name">Carlos Rodríguez</span>
                                    </div>
                                    <div class="sale-amount">$267.80</div>
                                    <div class="sale-time">hace 5 horas</div>
                                </div>
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