<?php
require_once '../config/connection.php';
include 'partials/head.php';

// Obtener datos iniciales para los selects
$productos = ejecutarConsulta("SELECT id, codigo, nombre FROM productos WHERE empresa_id = 1 AND activo = 1 ORDER BY nombre ASC", [])->fetchAll(PDO::FETCH_ASSOC);
$proveedores = ejecutarConsulta("SELECT id, codigo, nombre FROM proveedores WHERE empresa_id = 1 AND activo = 1 ORDER BY nombre ASC", [])->fetchAll(PDO::FETCH_ASSOC);
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
                        <button class="btn btn-success" onclick="showAddOrderModal()">
                            <i class="fas fa-shopping-cart"></i> Nuevo Pedido
                        </button>
                    </div>
                </div>

                <!-- Registrar proveedor por producto -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Asignar Proveedor a Producto</h2>
                    </div>
                    <div class="card-body">
                        <form id="formProductoProveedor" class="row g-3 align-items-end">
                            <input type="hidden" name="action" value="link_product">
                            <div class="col-md-4">
                                <label for="link_producto_id" class="form-label">Producto</label>
                                <select id="link_producto_id" name="producto_id" class="form-select" required>
                                    <option selected disabled value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?= $producto['id'] ?>">
                                            <?= htmlspecialchars($producto['codigo'] . ' - ' . $producto['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="link_proveedor_id" class="form-label">Proveedor</label>
                                <select id="link_proveedor_id" name="proveedor_id" class="form-select" required>
                                    <option selected disabled value="">Seleccione un proveedor</option>
                                    <?php foreach ($proveedores as $proveedor): ?>
                                        <option value="<?= $proveedor['id'] ?>">
                                            <?= htmlspecialchars($proveedor['codigo'] . ' - ' . $proveedor['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                           
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-link"></i> Asignar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

               

                <!-- Lista de Proveedores -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0"><i class="fas fa-truck"></i> Lista de Proveedores</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>RUC</th>
                                        <th>Teléfono</th>
                                        <th>Días Crédito</th>
                                        <th>Descuento</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $proveedores = ejecutarConsulta("
                                    SELECT 
                                        codigo, nombre, tipo_proveedor, ruc,
                                        telefono_principal, dias_credito, descuento_porcentaje, activo
                                    FROM proveedores 
                                    WHERE empresa_id = 1
                                    ORDER BY nombre
                                ")->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($proveedores as $proveedor):
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($proveedor['codigo']) ?></strong></td>
                                            <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= ucfirst($proveedor['tipo_proveedor']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($proveedor['ruc']) ?></td>
                                            <td><?= htmlspecialchars($proveedor['telefono_principal']) ?></td>
                                            <td><?= $proveedor['dias_credito'] ?> días</td>
                                            <td><?= $proveedor['descuento_porcentaje'] ?>%</td>
                                            <td>
                                                <span class="badge <?= $proveedor['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $proveedor['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include 'partials/modals.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicialización básica - sin cargar pedidos por ahora
            console.log('Página de proveedores cargada correctamente');

            // Manejar envío del formulario de producto-proveedor
            const formProductoProveedor = document.getElementById('formProductoProveedor');
            if (formProductoProveedor) {
                formProductoProveedor.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch('api/proveedores_api.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showSuccessModal(data.message || 'Relación creada exitosamente.');
                                this.reset();
                            } else {
                                showErrorModal(data.message || 'Ocurrió un error.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
                        });
                });
            }
        });

        function cargarPedidos(estado = '') {
            // Esta función está comentada porque no hay tabla de pedidos en el HTML actual
            console.log('Función cargarPedidos llamada con estado:', estado);
            // TODO: Implementar cuando se agregue la tabla de pedidos al HTML
        }

        function renderizarPedidos(pedidos) {
            // Esta función está comentada porque no hay tabla de pedidos en el HTML actual
            console.log('Función renderizarPedidos llamada con:', pedidos);
            // TODO: Implementar cuando se agregue la tabla de pedidos al HTML
        }

        function getEstadoInfo(estado) {
            const estados = {
                'pendiente': {
                    clase: 'bg-warning text-dark',
                    texto: 'Pendiente',
                    deshabilitado: false
                },
                'confirmado': {
                    clase: 'bg-info text-dark',
                    texto: 'Confirmado',
                    deshabilitado: false
                },
                'en_camino': {
                    clase: 'bg-primary',
                    texto: 'En Camino',
                    deshabilitado: false
                },
                'recibido': {
                    clase: 'bg-success',
                    texto: 'Recibido',
                    deshabilitado: true
                },
                'cancelado': {
                    clase: 'bg-danger',
                    texto: 'Cancelado',
                    deshabilitado: true
                }
            };
            return estados[estado] || {
                clase: 'bg-secondary',
                texto: 'Desconocido',
                deshabilitado: true
            };
        }

        function actualizarEstadoPedido(pedidoId, nuevoEstado) {
            const confirmacion = confirm(`¿Está seguro que desea cambiar el estado del pedido a "${nuevoEstado}"?`);
            if (!confirmacion) return;

            const formData = new FormData();
            formData.append('action', 'update_order_status');
            formData.append('pedido_id', pedidoId);
            formData.append('estado', nuevoEstado);

            fetch('api/proveedores_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessModal(data.message);
                        const filtroActivo = document.querySelector('#filtrosPedidos .btn.active');
                        const estado = filtroActivo ? filtroActivo.dataset.estado : '';
                        cargarPedidos(estado); // Recargar la lista de pedidos
                    } else {
                        showErrorModal(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('Error de conexión.');
                });
        }

        function editarPedido(id) {
            alert('Función para editar pedido (ID: ' + id + ') no implementada.');
        }

        function showAddOrderModal() {
            alert('Función para mostrar modal de nuevo pedido no implementada.');
        }

        function showAddProviderModal() {
            alert('Función para mostrar modal de nuevo proveedor no implementada.');
        }
    </script>
</body>

</html>