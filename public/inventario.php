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
                    <h1>Inventario</h1>
                    <p>Gestión de productos y stock</p>
                   
                </div> 
                <!-- Aquí va el contenido de inventario -->
                <div class="filters-bar bg-light rounded-lg shadow p-6 mb-6">
                    <div class="filter-group">
                        <input type="text" placeholder="Nombre, Codigo, ID..." class="filter-input" style="flex-grow: 1;">
                        <select class="filter-select">
                            <option value="">Todas las categorías</option>
                            <option value="Herramientas Manuales">Herramientas Manuales</option>
                            <option value="Herramientas Eléctricas">Herramientas Eléctricas</option>
                            <option value="Ferretería General">Ferretería General</option>
                            <option value="Fontanería">Fontanería</option>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Construcción">Construcción</option>
                            <option value="Jardín y Exterior">Jardín y Exterior</option>
                            <option value="Seguridad y Cerrajería">Seguridad y Cerrajería</option>
                            <option value="Pintura y Acabados">Pintura y Acabados</option>
                        </select>
                        <button class="btn-secondary"><i class="fas fa-search"></i> Buscar</button>
                    </div>
                </div>


            </div>
            <!--  -->
            <div class="rounded-lg  p-4 stats-grid">
                <div class="invecard">
                    <div class="stat-content">
                        <h3 class="text-primary text-center">Total Productos</h3>
                        <p class="stat-value text-info text-center">15</p>
                    </div>
                </div>
                <div class="invecard">
                    <div class="stat-content">
                        <h3 class="text-success text-center">Stock Normal</h3>
                        <p class="stat-value text-success text-center">14</p>
                    </div>
                </div>
                <div class="invecard">
                    <div class="stat-content">
                        <h3 class="text-warning text-center">Stock Bajo</h3>
                        <p class="stat-value text-warning text-center">1</p>
                    </div>
                </div>
                <div class="invecard">
                    <div class="stat-content">
                        <h3 class="text-danger text-center">Stock Crítico</h3>
                        <p class="stat-value text-danger text-center">0</p>
                    </div>
                </div>
            </div>
            <!--  -->
            <div class="bg-white rounded-lg shadow data-table">
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Margen</th>
                                <th>Ubicación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Martillo de Carpintero 500g</div>
                                        <div class="product-desc">HM001 - 7501234567890</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Herramientas Manuales</span></td>
                                <td><span class="stock-badge normal">25 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$18.00</div>
                                    <div class="text-sm text-gray-500">Costo: $11.50</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">56%</span></td>
                                <td>A1-E2</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Destornillador Phillips PH2</div>
                                        <div class="product-desc">HM002 - 7501234567891</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Herramientas Manuales</span></td>
                                <td><span class="stock-badge normal">50 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$4.75</div>
                                    <div class="text-sm text-gray-500">Costo: $2.85</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">67%</span></td>
                                <td>A1-E3</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Taladro Eléctrico 650W</div>
                                        <div class="product-desc">HE001 - 7501234567892</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Herramientas Eléctricas</span></td>
                                <td><span class="stock-badge low">8 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$95.00</div>
                                    <div class="text-sm text-gray-500">Costo: $69.00</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">38%</span></td>
                                <td>B2-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Tornillos Autorroscantes 4x40mm</div>
                                        <div class="product-desc">FG001 - 7501234567893</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Ferretería General</span></td>
                                <td><span class="stock-badge normal">120 caja</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$9.25</div>
                                    <div class="text-sm text-gray-500">Costo: $5.65</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">61%</span></td>
                                <td>C1-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Tuercas Hexagonales M8</div>
                                        <div class="product-desc">FG002 - 7501234567894</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Ferretería General</span></td>
                                <td><span class="stock-badge normal">80 bolsa</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$4.10</div>
                                    <div class="text-sm text-gray-500">Costo: $2.30</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">78%</span></td>
                                <td>C1-E2</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Tubo PVC 110mm</div>
                                        <div class="product-desc">FO001 - 7501234567895</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Fontanería</span></td>
                                <td><span class="stock-badge normal">35 tubo</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$13.50</div>
                                    <div class="text-sm text-gray-500">Costo: $9.75</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">38%</span></td>
                                <td>D1-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Grifo Monomando Cocina</div>
                                        <div class="product-desc">FO002 - 7501234567896</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Fontanería</span></td>
                                <td><span class="stock-badge normal">12 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$48.50</div>
                                    <div class="text-sm text-gray-500">Costo: $30.50</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">59%</span></td>
                                <td>D2-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Cable THW 12 AWG</div>
                                        <div class="product-desc">EL001 - 7501234567897</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Electricidad</span></td>
                                <td><span class="stock-badge normal">500 metro</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$3.20</div>
                                    <div class="text-sm text-gray-500">Costo: $2.15</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">49%</span></td>
                                <td>E1-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Interruptor Simple Blanco</div>
                                        <div class="product-desc">EL002 - 7501234567898</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Electricidad</span></td>
                                <td><span class="stock-badge normal">60 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$3.50</div>
                                    <div class="text-sm text-gray-500">Costo: $1.95</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">79%</span></td>
                                <td>E1-E2</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Cemento Portland Tipo I</div>
                                        <div class="product-desc">CO001 - 7501234567899</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Construcción</span></td>
                                <td><span class="stock-badge normal">200 saco</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$6.75</div>
                                    <div class="text-sm text-gray-500">Costo: $4.85</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">39%</span></td>
                                <td>F1-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Bloque de Concreto 6"</div>
                                        <div class="product-desc">CO002 - 7501234567900</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Construcción</span></td>
                                <td><span class="stock-badge critical">2000 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$1.25</div>
                                    <div class="text-sm text-gray-500">Costo: $0.85</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">47%</span></td>
                                <td>F2-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Pintura Látex Interior</div>
                                        <div class="product-desc">PI001 - 7501234567901</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Pintura y Acabados</span></td>
                                <td><span class="stock-badge normal">45 galón</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$22.50</div>
                                    <div class="text-sm text-gray-500">Costo: $15.25</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">48%</span></td>
                                <td>G1-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Rodillo Anticorrosivo 9"</div>
                                        <div class="product-desc">PI002 - 7501234567902</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Pintura y Acabados</span></td>
                                <td><span class="stock-badge normal">30 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$8.95</div>
                                    <div class="text-sm text-gray-500">Costo: $5.85</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">53%</span></td>
                                <td>G1-E2</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Cerradura Embutir Doble</div>
                                        <div class="product-desc">SE001 - 7501234567903</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Seguridad y Cerrajería</span></td>
                                <td><span class="stock-badge low">18 unidad</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$28.75</div>
                                    <div class="text-sm text-gray-500">Costo: $19.50</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">47%</span></td>
                                <td>H1-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Manguera Jardín 50 pies</div>
                                        <div class="product-desc">JE001 - 7501234567904</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Jardín y Exterior</span></td>
                                <td><span class="stock-badge normal">22 rollo</span></td>
                                <td>
                                    <div class="text-sm text-gray-900">$32.00</div>
                                    <div class="text-sm text-gray-500">Costo: $22.50</div>
                                </td>
                                <td><span class="text-sm font-medium text-green-600">42%</span></td>
                                <td>I1-E1</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    </main>
    </div>
    <?php include 'partials/modals.php'; ?>
</body>

</html>