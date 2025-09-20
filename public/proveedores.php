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
                        <th>Teléfono</th>
                        <th>Tipo</th>
                        <th>Email</th>
                        <th>Días Crédito</th>
                        <th>Descuento</th>
                        <th>Fecha de Registro</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // CONSULTA CORREGIDA CON TODOS LOS CAMPOS NECESARIOS
                        $proveedores_tabla = ejecutarConsulta("
                            SELECT 
                                id,
                                codigo,
                                nombre, 
                                telefono_principal,
                                CASE 
                                    WHEN tipo_proveedor = 'distribuidor' THEN 'Distribuidor'
                                    WHEN tipo_proveedor = 'fabricante' THEN 'Fabricante'
                                    WHEN tipo_proveedor = 'importador' THEN 'Importador'
                                    WHEN tipo_proveedor = 'mayorista' THEN 'Mayorista'
                                    WHEN tipo_proveedor = 'otro' THEN 'Otro'
                                    ELSE 'Sin definir'
                                END AS tipo_proveedor_texto,
                                email,
                                dias_credito,
                                descuento_porcentaje,
                                DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i') AS fecha_registro_formateada,
                                activo,
                                CASE 
                                    WHEN activo = 1 THEN 'Activo'
                                    ELSE 'Inactivo'
                                END AS estado_texto
                            FROM proveedores 
                            WHERE empresa_id = 1
                            ORDER BY fecha_registro DESC
                        ")->fetchAll(PDO::FETCH_ASSOC);

                        if (empty($proveedores_tabla)):
                    ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No hay proveedores registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proveedores_tabla as $proveedor): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($proveedor['codigo'] ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                                    <td><?= htmlspecialchars($proveedor['telefono_principal'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            <?= htmlspecialchars($proveedor['tipo_proveedor_texto']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($proveedor['email'] ?? 'N/A') ?></td>
                                    <td><?= $proveedor['dias_credito'] ?> días</td>
                                    <td><?= number_format($proveedor['descuento_porcentaje'], 2) ?>%</td>
                                    <td><?= $proveedor['fecha_registro_formateada'] ?></td>
                                    <td>
                                        <span class="badge <?= $proveedor['activo'] == 1 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $proveedor['estado_texto'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="verDetalleProveedor(<?= $proveedor['id'] ?>)" 
                                                    title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="editarProveedor(<?= $proveedor['id'] ?>)" 
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($proveedor['activo'] == 1): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="desactivarProveedor(<?= $proveedor['id'] ?>)" 
                                                        title="Desactivar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-success" 
                                                        onclick="activarProveedor(<?= $proveedor['id'] ?>)" 
                                                        title="Activar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { ?>
                        <tr>
                            <td colspan="10" class="text-center text-danger">
                                <i class="fas fa-exclamation-triangle"></i> Error al cargar proveedores: <?= htmlspecialchars($e->getMessage()) ?>
                            </td>
                        </tr>
                    <?php } ?>
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
function verDetalleProveedor(proveedorId) {
    // Crear modal dinámico para mostrar detalles del proveedor
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'modalDetalleProveedor';
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-truck"></i> Detalles del Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleProveedorContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información del proveedor...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="editarProveedor(${proveedorId})">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();

    // Cargar detalles del proveedor
    fetch(`api/get_proveedor_info.php?id=${proveedorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('detalleProveedorContent').innerHTML = `
                    <!-- Pestañas para organizar la información -->
                    <ul class="nav nav-tabs" id="proveedorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button">
                                <i class="fas fa-info-circle"></i> Información General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="productos-tab" data-bs-toggle="tab" data-bs-target="#productos-pane" type="button">
                                <i class="fas fa-boxes"></i> Productos (${data.productos.length})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats-pane" type="button">
                                <i class="fas fa-chart-bar"></i> Estadísticas
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="proveedorTabsContent">
                        <!-- Información General -->
                        <div class="tab-pane fade show active" id="info-pane">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-building"></i> Información Básica</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Código:</strong> <span class="badge bg-primary">${data.proveedor.codigo || 'N/A'}</span></p>
                                            <p><strong>Nombre:</strong> ${data.proveedor.nombre}</p>
                                            <p><strong>Razón Social:</strong> ${data.proveedor.razon_social || 'N/A'}</p>
                                            <p><strong>Tipo:</strong> <span class="badge bg-info">${data.proveedor.tipo_proveedor}</span></p>
                                            <p><strong>RUC:</strong> ${data.proveedor.ruc || 'N/A'}</p>
                                            <p><strong>Estado:</strong> <span class="badge ${data.proveedor.activo ? 'bg-success' : 'bg-danger'}">${data.proveedor.activo ? 'Activo' : 'Inactivo'}</span></p>
                                            <p><strong>Fecha Registro:</strong> ${data.proveedor.fecha_registro}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-address-book"></i> Información de Contacto</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Teléfono:</strong> ${data.proveedor.telefono_principal || 'N/A'}</p>
                                            <p><strong>Email:</strong> ${data.proveedor.email ? `<a href="mailto:${data.proveedor.email}">${data.proveedor.email}</a>` : 'N/A'}</p>
                                            <p><strong>Sitio Web:</strong> ${data.proveedor.sitio_web ? `<a href="${data.proveedor.sitio_web}" target="_blank">${data.proveedor.sitio_web}</a>` : 'N/A'}</p>
                                            <p><strong>Dirección:</strong> ${data.proveedor.direccion || 'N/A'}</p>
                                            <p><strong>Horario:</strong> ${data.proveedor.horario_atencion || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-user-tie"></i> Persona de Contacto</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Nombre:</strong> ${data.proveedor.nombre_contacto || 'N/A'}</p>
                                            <p><strong>Cargo:</strong> ${data.proveedor.cargo_contacto || 'N/A'}</p>
                                            <p><strong>Teléfono:</strong> ${data.proveedor.telefono_contacto || 'N/A'}</p>
                                            <p><strong>Email:</strong> ${data.proveedor.email_contacto ? `<a href="mailto:${data.proveedor.email_contacto}">${data.proveedor.email_contacto}</a>` : 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-handshake"></i> Condiciones Comerciales</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Días de Crédito:</strong> <span class="badge bg-warning text-dark">${data.proveedor.dias_credito} días</span></p>
                                            <p><strong>Descuento:</strong> <span class="badge bg-success">${data.proveedor.descuento_porcentaje}%</span></p>
                                            <p><strong>Tiempo de Entrega:</strong> ${data.proveedor.tiempo_entrega} días</p>
                                            <p><strong>Monto Mínimo:</strong> $${data.proveedor.monto_minimo}</p>
                                            <p><strong>Condiciones de Pago:</strong> ${data.proveedor.condiciones_pago || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${data.proveedor.productos_principales || data.proveedor.observaciones ? `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Información Adicional</h6>
                                        </div>
                                        <div class="card-body">
                                            ${data.proveedor.productos_principales ? `<p><strong>Productos Principales:</strong><br>${data.proveedor.productos_principales}</p>` : ''}
                                            ${data.proveedor.observaciones ? `<p><strong>Observaciones:</strong><br>${data.proveedor.observaciones}</p>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>

                        <!-- Productos -->
                        <div class="tab-pane fade" id="productos-pane">
                            ${data.productos.length > 0 ? `
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Producto</th>
                                                <th>Precio Compra</th>
                                                <th>Tiempo Entrega</th>
                                                <th>Principal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${data.productos.map(producto => `
                                                <tr>
                                                    <td><code>${producto.codigo}</code></td>
                                                    <td>${producto.nombre}</td>
                                                    <td>$${parseFloat(producto.precio_compra).toFixed(2)}</td>
                                                    <td>${producto.tiempo_entrega} días</td>
                                                    <td>${producto.es_principal ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                                ${data.productos.length === 10 ? '<p class="text-muted"><small>* Mostrando los primeros 10 productos</small></p>' : ''}
                            ` : `
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <p>No hay productos asociados a este proveedor</p>
                                </div>
                            `}
                        </div>

                        <!-- Estadísticas -->
                        <div class="tab-pane fade" id="stats-pane">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                            <h5 class="card-title">${data.estadisticas.total_ordenes}</h5>
                                            <p class="card-text">Órdenes de Compra<br><small class="text-muted">(Últimos 6 meses)</small></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                                            <h5 class="card-title">$${data.estadisticas.total_comprado}</h5>
                                            <p class="card-text">Total Comprado<br><small class="text-muted">(Últimos 6 meses)</small></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                            <h5 class="card-title">$${data.estadisticas.promedio_orden}</h5>
                                            <p class="card-text">Promedio por Orden<br><small class="text-muted">(Últimos 6 meses)</small></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-calendar fa-2x text-warning mb-2"></i>
                                            <h5 class="card-title">${data.estadisticas.ultima_compra}</h5>
                                            <p class="card-text">Última Compra<br><small class="text-muted">&nbsp;</small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('detalleProveedorContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detalleProveedorContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del proveedor.
                </div>
            `;
        });

    // Limpiar modal cuando se cierre
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}    </script>
</body>

</html>