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
                    <div class="page-header-left">
                        <h1>Gestión de Proveedores</h1>
                        <p>Administra proveedores y pedidos asociados a productos</p>
                    </div>
                    <div class="page-header-right">
                        <button class="btn btn-primary" onclick="showAddProviderModal()">
                            <i class="fas fa-plus"></i> Nuevo Proveedor
                        </button>
                    </div>
                </div>

                <!-- Registrar proveedor por producto -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Registrar Proveedor por Producto</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="#" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="producto_id" class="form-label">Producto</label>
                                <select id="producto_id" name="producto_id" class="form-select">
                                    <option selected disabled value="">Seleccione un producto</option>
                                    <!-- Aquí deberías cargar los productos desde la base de datos -->
                                    <option value="1">Martillo</option>
                                    <option value="2">Taladro</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="proveedor_id" class="form-label">Proveedor</label>
                                <select id="proveedor_id" name="proveedor_id" class="form-select">
                                    <option selected disabled value="">Seleccione un proveedor</option>
                                    <!-- Aquí deberías cargar los proveedores desde la base de datos -->
                                    <option value="1">Proveedor A</option>
                                    <option value="2">Proveedor B</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Registrar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Gestión de pedidos pendientes y tiempos estimados de llegada -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Pedidos Pendientes</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Proveedor</th>
                                        <th>Cantidad</th>
                                        <th>Fecha Pedido</th>
                                        <th>Llegada Estimada</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Ejemplo de fila, deberías cargar los pedidos desde la base de datos -->
                                    <tr>
                                        <td>Martillo</td>
                                        <td>Proveedor A</td>
                                        <td>50</td>
                                        <td>2024-06-10</td>
                                        <td>2024-06-20</td>
                                        <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pencil-alt"></i></button>
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Taladro</td>
                                        <td>Proveedor B</td>
                                        <td>20</td>
                                        <td>2024-06-12</td>
                                        <td>2024-06-25</td>
                                        <td><span class="badge bg-info text-dark">En camino</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pencil-alt"></i></button>
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
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