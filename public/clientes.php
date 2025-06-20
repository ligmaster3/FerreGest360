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
                        <h1>Clientes</h1>
                        <p>Gestión de clientes y créditos</p>
                    </div>
                    <button class="btn-primary" onclick="showAddClientModal()">
                        <i class="fas fa-plus"></i>
                        Nuevo Cliente
                    </button>
                </div>

                <div class="filters-bar bg-light rounded-lg shadow p-6 mb-6">
                    <div class="filter-group">
                        <input type="text" placeholder="Buscar por nombre, NIF, teléfono..." class="filter-input" style="flex-grow: 1;">
                        <select class="filter-select">
                            <option value="">Todos los tipos</option>
                            <option value="Profesional">Profesional</option>
                            <option value="Particular">Particular</option>
                        </select>
                        <button class="btn-secondary"><i class="fas fa-search"></i> Buscar</button>
                    </div>
                </div>

                <div class="stats-grid  rounded-lg  p-4 mb-6">
                    <div class="stat-card clients">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-content">
                            <h3>Total Clientes</h3>
                            <p class="stat-value">6</p>
                        </div>
                    </div>
                    <div class="stat-card revenue">
                        <div class="stat-icon"><i class="fas fa-hard-hat"></i></div>
                        <div class="stat-content">
                            <h3>Profesionales</h3>
                            <p class="stat-value">5</p>
                        </div>
                    </div>
                    <div class="stat-card stock">
                        <div class="stat-icon"><i class="fas fa-credit-card"></i></div>
                        <div class="stat-content">
                            <h3>Con Deuda</h3>
                            <p class="stat-value">4</p>
                        </div>
                    </div>
                    <div class="stat-card products">
                        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-content">
                            <h3>Deuda Total</h3>
                            <p class="stat-value">$1,477</p>
                        </div>
                    </div>
                </div>

                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Tipo</th>
                                <th>Crédito</th>
                                <th>Compras Totales</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Construcciones Herrera S.A.</div>
                                        <div class="product-desc">J-12345678-9</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">507-6234-5678</div>
                                        <div class="product-desc">j.herrera@construccionesherrera.com.pa</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Profesional</span></td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name" style="color: var(--danger-color);">$425.75 / $2500.00</div>
                                        <div class="product-desc">17.0% usado</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">$9850.50</div>
                                        <div class="product-desc">Última: 2024-05-28</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-history"><i class="fas fa-history"></i></button>
                                        <button class="btn-delete" onclick="showConfirmationModal('Eliminar Cliente', '¿Estás seguro de que quieres eliminar a este cliente?', () => { alert('Funcionalidad de eliminar cliente pendiente de implementación.') })"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Reformas Castillo</div>
                                        <div class="product-desc">J-87654321-0</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">507-6789-0123</div>
                                        <div class="product-desc">carlos@reformascastillo.com</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Profesional</span></td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name" style="color: var(--success-color);">$0.00 / $1800.00</div>
                                        <div class="product-desc">0.0% usado</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">$3785.90</div>
                                        <div class="product-desc">Última: 2024-05-30</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-history"><i class="fas fa-history"></i></button>
                                        <button class="btn-delete" onclick="showConfirmationModal('Eliminar Cliente', '¿Estás seguro de que quieres eliminar a este cliente?', () => { alert('Funcionalidad de eliminar cliente pendiente de implementación.') })"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Electricidad Rodríguez</div>
                                        <div class="product-desc">J-24681357-0</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">507-6456-7890</div>
                                        <div class="product-desc">info@electricidadrodriguez.com.pa</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Profesional</span></td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name" style="color: var(--danger-color);">$340.25 / $1500.00</div>
                                        <div class="product-desc">22.7% usado</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">$6420.75</div>
                                        <div class="product-desc">Última: 2024-06-01</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-history"><i class="fas fa-history"></i></button>
                                        <button class="btn-delete" onclick="showConfirmationModal('Eliminar Cliente', '¿Estás seguro de que quieres eliminar a este cliente?', () => { alert('Funcionalidad de eliminar cliente pendiente de implementación.') })"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Plomería Express 24h</div>
                                        <div class="product-desc">J-13579246-8</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">507-6789-1234</div>
                                        <div class="product-desc">ana@plomeriaexpress.com</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Profesional</span></td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name" style="color: var(--danger-color);">$165.80 / $950.00</div>
                                        <div class="product-desc">17.5% usado</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">$2145.30</div>
                                        <div class="product-desc">Última: 2024-05-29</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-history"><i class="fas fa-history"></i></button>
                                        <button class="btn-delete" onclick="showConfirmationModal('Eliminar Cliente', '¿Estás seguro de que quieres eliminar a este cliente?', () => { alert('Funcionalidad de eliminar cliente pendiente de implementación.') })"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Particular - María Delgado</div>
                                        <div class="product-desc">V-12345678-0</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">507-6123-4567</div>
                                        <div class="product-desc">maria.delgado@gmail.com</div>
                                    </div>
                                </td>
                                <td><span class="status-badge active" style="background-color: rgba(127, 140, 141, 0.1); color: var(--text-secondary);">Particular</span></td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name" style="color: var(--success-color);">$0.00 / $250.00</div>
                                        <div class="product-desc">0.0% usado</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">$185.40</div>
                                        <div class="product-desc">Última: 2024-05-25</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-history"><i class="fas fa-history"></i></button>
                                        <button class="btn-delete" onclick="showConfirmationModal('Eliminar Cliente', '¿Estás seguro de que quieres eliminar a este cliente?', () => { alert('Funcionalidad de eliminar cliente pendiente de implementación.') })"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">Jardinería El Trébol</div>
                                        <div class="product-desc">J-98765432-1</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">507-6987-6543</div>
                                        <div class="product-desc">contacto@jardineriaeltrebol.com</div>
                                    </div>
                                </td>
                                <td><span class="category-badge">Profesional</span></td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name" style="color: var(--danger-color);">$545.20 / $1200.00</div>
                                        <div class="product-desc">45.4% usado</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">$4500.00</div>
                                        <div class="product-desc">Última: 2024-06-02</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn-history"><i class="fas fa-history"></i></button>
                                        <button class="btn-delete" onclick="showConfirmationModal('Eliminar Cliente', '¿Estás seguro de que quieres eliminar a este cliente?', () => { alert('Funcionalidad de eliminar cliente pendiente de implementación.') })"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <?php include 'partials/modals.php'; ?>
</body>

</html>