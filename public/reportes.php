<?php
require_once '../config/connection.php';
include 'partials/head.php';
?>

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
                </div>
               
               

                <!-- Filtros de reportes -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="#" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="filtro_cliente" class="form-label">Buscar por cliente</label>
                                <input id="filtro_cliente" type="text" name="cliente" placeholder="Escriba para buscar..." class="form-control" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_fecha_inicio" class="form-label">Fecha de inicio</label>
                                <input id="filtro_fecha_inicio" type="date" name="fecha_inicio" class="form-control" title="Fecha de inicio">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_fecha_fin" class="form-label">Fecha de fin</label>
                                <input id="filtro_fecha_fin" type="date" name="fecha_fin" class="form-control" title="Fecha de fin">
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary w-100 me-2">
                                        <i class="fas fa-search"></i><span class="d-none d-lg-inline ms-1"> Buscar</span>
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
                <div class=" rounded-lg  p-4 stats-grid">
                    <div class="invecard">
                        <div class="stat-content">
                            <p class="stat-value text-info text-center fw-bold">$0.00</p>
                            <h3 class="text-primary text-center">Ventas Totales</h3>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <p class="stat-value text-success text-center fw-bold">0</p>
                            <h3 class="text-success text-center">Numeros de ventas</h3>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <p class="stat-value text-warning text-center fw-bold">1</p>
                            <h3 class="text-warning text-center">Ventas Promedio</h3>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <p class="stat-value text-danger text-center fw-bold">0</p>
                            <h3 class="text-danger text-center">Dias con Ventas</h3>
                        </div>
                    </div>
                </div>
                <!-- Ventas por Categoría -->
                <div class="bg-white rounded-lg shadow p-6 mt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Ventas por Categoría</h2>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Monto Total</th>
                                    <th>Nº Ventas</th>
                                    <th>% del Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Herramientas Manuales</td>
                                    <td class="text-success">$450.75</td>
                                    <td>15</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 35%;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">35%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Herramientas Eléctricas</td>
                                    <td class="text-success">$320.00</td>
                                    <td>8</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Fontanería</td>
                                    <td class="text-success">$250.50</td>
                                    <td>22</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">20%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Electricidad</td>
                                    <td class="text-success">$180.25</td>
                                    <td>18</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 15%;" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">15%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Construcción</td>
                                    <td class="text-success">$80.50</td>
                                    <td>5</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 5%;" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">5%</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Historial de ventas -->
                <div class="bg-white rounded-lg shadow p-6 mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Historial de Ventas</h2>
                        <div class="flex gap-2">
                            <button class="btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                            <button class="btn-secondary" onclick="enviarFacturaDigital()"><i class="fas fa-envelope"></i> Enviar Digital</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Venta</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Aquí irían los datos de la base de datos -->
                                    <tr>
                                        <td>1</td>
                                        <td>Juan Pérez</td>
                                        <td>2024-07-20</td>
                                        <td>$150.00</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-info" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>María Gómez</td>
                                        <td>2024-07-19</td>
                                        <td>$85.50</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-info" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include 'partials/modals.php'; ?>
</body>

</html>