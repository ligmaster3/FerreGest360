<?php
require_once '../config/connection.php';
session_start();

// Procesar acciones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'buscar_clientes':
            if (isset($_GET['q'])) {
                $busqueda = '%' . $_GET['q'] . '%';
                try {
                    $sql = "SELECT id, nombre, cedula_ruc, telefono, email, direccion 
                            FROM clientes 
                            WHERE (nombre LIKE ? OR cedula_ruc LIKE ?) 
                            AND empresa_id = 1 AND activo = 1 
                            ORDER BY nombre 
                            LIMIT 10";
                    
                    $stmt = ejecutarConsulta($sql, [$busqueda, $busqueda]);
                    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode(['success' => true, 'clientes' => $clientes]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
            }
            break;
            
        case 'obtener_cliente':
            if (isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
                if ($id) {
                    try {
                        $sql = "SELECT id, nombre, cedula_ruc, telefono, email, direccion 
                                FROM clientes 
                                WHERE id = ? AND empresa_id = 1 AND activo = 1";
                        
                        $stmt = ejecutarConsulta($sql, [$id]);
                        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($cliente) {
                            echo json_encode(['success' => true, 'cliente' => $cliente]);
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Cliente no encontrado']);
                        }
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    }
                }
            }
            break;
            
   case 'buscar_productos':
    if (isset($_GET['q'])) {
        $busqueda = '%' . $_GET['q'] . '%';
        try {
            $sql = "SELECT 
                        p.id, 
                        p.codigo, 
                        p.nombre, 
                        p.precio_venta,
                        COALESCE(i.stock_actual, 0) as stock_actual
                    FROM productos p
                    LEFT JOIN inventario i ON p.id = i.producto_id
                    WHERE (p.nombre LIKE ? OR p.codigo LIKE ?) 
                    AND p.empresa_id = 1 
                    AND p.activo = 1 
                    ORDER BY p.nombre 
                    LIMIT 10";
            $stmt = ejecutarConsulta($sql, [$busqueda, $busqueda]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'productos' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    break;
            
        case 'obtener_producto':
            if (isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
                if ($id) {
                    try {
                        $sql = "SELECT id, codigo, nombre, descripcion, precio_venta, stock_actual 
                                FROM productos 
                                WHERE id = ? AND empresa_id = 1 AND activo = 1";
                        
                        $stmt = ejecutarConsulta($sql, [$id]);
                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($producto) {
                            echo json_encode(['success' => true, 'producto' => $producto]);
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
                        }
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    }
                }
            }
            break;
            
        case 'guardar_factura':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $conn = getDBConnection();
                $input = json_decode(file_get_contents('php://input'), true);
                
                try {
                    // Validar datos requeridos
                    $required = ['empresa_id', 'vendedor_id', 'cliente_id', 'fecha_factura', 'tipo_pago', 'subtotal', 'total'];
                    foreach ($required as $field) {
                        if (!isset($input[$field]) || empty($input[$field])) {
                            throw new Exception("El campo $field es requerido");
                        }
                    }
                    
                    // Validar productos
                    if (!isset($input['productos']) || empty($input['productos'])) {
                        throw new Exception("Debe agregar al menos un producto");
                    }
                    
                    $productos = $input['productos'];
                    
                    $conn->beginTransaction();
                    
                    // Generar número de factura
                    $numero_factura = generarNumeroFactura($conn);
                    
                    // Insertar factura
                    $sql_factura = "INSERT INTO facturas_venta 
                                    (empresa_id, numero_factura, cliente_id, vendedor_id, fecha_factura, 
                                     fecha_vencimiento, tipo_pago, subtotal, descuento, itbms, total, 
                                     estado, observaciones) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $estado = isset($input['estado']) && $input['estado'] === 'borrador' ? 'borrador' : 'pendiente';
                    
                    $params_factura = [
                        $input['empresa_id'],
                        $numero_factura,
                        $input['cliente_id'],
                        $input['vendedor_id'],
                        $input['fecha_factura'],
                        $input['fecha_vencimiento'] ?? null,
                        $input['tipo_pago'],
                        $input['subtotal'],
                        $input['descuento'] ?? 0,
                        $input['itbms'] ?? 0,
                        $input['total'],
                        $estado,
                        $input['observaciones'] ?? null
                    ];
                    
                    $stmt = ejecutarConsulta($sql_factura, $params_factura);
                    $factura_id = $conn->lastInsertId();
                    
                    // Insertar detalles de factura
                    $sql_detalle = "INSERT INTO detalle_facturas_venta 
                                    (factura_id, producto_id, cantidad, precio_unitario, descuento, subtotal) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                    
                    foreach ($productos as $producto) {
                        $params_detalle = [
                            $factura_id,
                            $producto['id'],
                            $producto['cantidad'],
                            $producto['precio'],
                            $producto['descuento'],
                            $producto['subtotal'] - $producto['descuento']
                        ];
                        
                        ejecutarConsulta($sql_detalle, $params_detalle);
                        
                        // Actualizar stock si la factura no es borrador
                        if ($estado !== 'borrador') {
                            $sql_update_stock = "UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?";
                            ejecutarConsulta($sql_update_stock, [$producto['cantidad'], $producto['id']]);
                        }
                    }
                    
                    $conn->commit();
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Factura guardada correctamente',
                        'factura_id' => $factura_id,
                        'numero_factura' => $numero_factura
                    ]);
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
            }
            break;
    }
    exit;
}

// Función para generar número de factura
function generarNumeroFactura($conn) {
    try {
        // Obtener el último número de factura
        $sql = "SELECT numero_factura FROM facturas_venta WHERE empresa_id = 1 ORDER BY id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $ultimaFactura = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ultimaFactura) {
            // Extraer el número secuencial y incrementarlo
            $partes = explode('-', $ultimaFactura['numero_factura']);
            $secuencial = intval(end($partes)) + 1;
            return "F-" . date('Y') . "-001-" . str_pad($secuencial, 5, '0', STR_PAD_LEFT);
        } else {
            // Primera factura
            return "F-" . date('Y') . "-001-00001";
        }
    } catch (Exception $e) {
        // En caso de error, generar número basado en timestamp
        return "F-" . date('Y') . "-001-" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}

// Obtener datos iniciales
try {
    // Generar número de factura para mostrar
    $numero_factura = generarNumeroFactura(getDBConnection());
} catch (Exception $e) {
    $numero_factura = "F-" . date('Y') . "-001-" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

$vendedor_id = $_SESSION['user_id'] ?? 1;

include 'partials/head.php';
?>

<body>
    <div id="dashboardScreen" class="dashboard-container">
       
        <main class="">
           <a
            name="Home ventas"
            id="Id_Home ventas"
            class="action-btn primary py-3 mb-4"
            href="ventas.php"
            role="button"><- Volver
            </a>
           
            <div class="content-area">
                <div class="page-header">
                    <h1><i class="fas fa-file-invoice-dollar"></i> Nueva Factura</h1>
                    <p>Complete los datos para generar una nueva factura de venta</p>
                </div>

                <div class="container-fluid py-4">
                    <div class="row">
                        <!-- Formulario de Factura -->
                        <div class="col-lg-8">
                            <form id="facturaForm">
                                <!-- Información del Cliente -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5><i class="fas fa-user"></i> Información del Cliente</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Cliente *</label>
                                                <div style="position: relative;">
                                                    <input type="text" id="cliente_busqueda" class="form-control" 
                                                           placeholder="Nombre o cédula/RUC" autocomplete="off">
                                                    <div id="cliente-resultados" class="search-results"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Tipo de Pago *</label>
                                                <select class="form-select" id="tipo_pago" name="tipo_pago" required>
                                                    <option value="contado">Contado</option>
                                                    <option value="credito">Crédito</option>
                                                    <option value="mixto">Mixto</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Fecha Vencimiento</label>
                                                <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                                            </div>
                                        </div>
                                        
                                        <!-- Información del cliente seleccionado -->
                                        <div id="clienteInfo" class="customer-card mt-3 p-3" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>Nombre:</strong> <span id="clienteNombre"></span>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Teléfono:</strong> <span id="clienteTelefono"></span>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Identificación:</strong> <span id="clienteCedula"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Productos -->
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5><i class="fas fa-shopping-cart"></i> Productos</h5>
                                        <button type="button" class="btn btn-primary" onclick="agregarProducto()">
                                            <i class="fas fa-plus"></i> Agregar Producto
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="productosContainer">
                                            <!-- Los productos se agregarán dinámicamente aquí -->
                                        </div>
                                    </div>
                                </div>

                                
                            </form>
                        </div>

                        <!-- Resumen de Factura -->
                        <div class="col-lg-4">
                            <div class="invoice-summary">
                                <div class="total-section">
                                    <h5 class="text-center mb-4"><i class="fas fa-calculator"></i> Resumen de Factura</h5>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <strong>$<span id="subtotalDisplay">0.00</span></strong>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Descuento:</span>
                                        <strong>$<span id="descuentoDisplay">0.00</span></strong>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>ITBMS (7%):</span>
                                        <strong>$<span id="itbmsDisplay">0.00</span></strong>
                                    </div>
                                    
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <h5>Total:</h5>
                                        <h5 class="text-primary">$<span id="totalDisplay">0.00</span></h5>
                                    </div>

                                    <!-- Información adicional -->
                                    <div class="alert alert-info">
                                        <small>
                                            <strong>Número de Factura:</strong> <span id="numeroFactura"><?php echo $numero_factura; ?></span><br>
                                            <strong>Fecha:</strong> <span id="fechaFactura"><?php echo date('d/m/Y'); ?></span><br>
                                            <strong>Vendedor:</strong> <?php echo $_SESSION['user_nombre'] ?? 'Admin'; ?>
                                        </small>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-primary btn-lg" onclick="guardarFactura()">
                                            <i class="fas fa-save"></i> Guardar Factura
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="guardarFactura(true)">
                                            <i class="fas fa-print"></i> Guardar e Imprimir
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                                            <i class="fas fa-broom"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include 'partials/modals.php'; ?>

    <style>
        .search-results {
            position: absolute;
            top: 120%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        
        .search-result-item {
            margin: auto;
            padding: 15px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .search-result-item:hover {
            background-color: #f0f0f0;
        }
        
        .customer-card {
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        
        .product-row {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 6px;
        }
    </style>

    <script>
        // Variables globales
        let productosFactura = [];
        let contadorProductos = 0;
        let clienteSeleccionado = null;

        // Inicializar la aplicación
        document.addEventListener('DOMContentLoaded', function() {
            establecerFechaActual();
            agregarProducto(); // Agregar primer producto al cargar
            
            // Evento para cambio de tipo de pago
            document.getElementById('tipo_pago').addEventListener('change', manejarTipoPago);
        });

        // Establecer fecha actual
        function establecerFechaActual() {
            const hoy = new Date();
            document.getElementById('fechaFactura').textContent = hoy.toLocaleDateString('es-PA');
            
            // Establecer fecha de vencimiento por defecto (30 días)
            const fechaVenc = new Date();
            fechaVenc.setDate(fechaVenc.getDate() + 30);
            document.getElementById('fecha_vencimiento').value = fechaVenc.toISOString().split('T')[0];
        }

        // Buscar clientes desde la base de datos
        document.getElementById('cliente_busqueda').addEventListener('input', async function(e) {
            const query = e.target.value.trim();
            const resultados = document.getElementById('cliente-resultados');
            
            if (query.length < 2) {
                resultados.style.display = 'none';
                return;
            }
            
            try {
                const response = await fetch(`?action=buscar_clientes&q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success && data.clientes.length > 0) {
                    resultados.innerHTML = data.clientes.map(cliente => `
                        <div class="search-result-item" data-id="${cliente.id}">
                            <strong>${cliente.nombre}</strong> (${cliente.cedula_ruc})
                        </div>
                    `).join('');
                    
                    // Add click event to results
                    document.querySelectorAll('.search-result-item[data-id]').forEach(item => {
                        item.addEventListener('click', function() {
                            const clienteId = parseInt(this.getAttribute('data-id'));
                            seleccionarCliente(clienteId);
                            resultados.style.display = 'none';
                        });
                    });
                    
                    resultados.style.display = 'block';
                } else {
                    resultados.innerHTML = '<div class="search-result-item">No se encontraron clientes</div>';
                    resultados.style.display = 'block';
                }
            } catch (error) {
                resultados.innerHTML = '<div class="search-result-item">Error en la búsqueda</div>';
                resultados.style.display = 'block';
                console.error('Error:', error);
            }
        });

        // Seleccionar cliente
        async function seleccionarCliente(id) {
            try {
                const response = await fetch(`?action=obtener_cliente&id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    clienteSeleccionado = data.cliente;
                    document.getElementById('cliente_busqueda').value = data.cliente.nombre;
                    document.getElementById('clienteNombre').textContent = data.cliente.nombre;
                    document.getElementById('clienteTelefono').textContent = data.cliente.telefono || 'N/A';
                    document.getElementById('clienteCedula').textContent = data.cliente.cedula_ruc;
                    document.getElementById('clienteInfo').style.display = 'block';
                    
                    mostrarMensaje(`Cliente "${data.cliente.nombre}" seleccionado`, 'success');
                } else {
                    mostrarMensaje('Error al cargar el cliente', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión', 'error');
            }
        }

        // Manejar cambio de tipo de pago
        function manejarTipoPago() {
            const tipoPago = document.getElementById('tipo_pago').value;
            const fechaVencField = document.getElementById('fecha_vencimiento');
            
            if (tipoPago === 'contado') {
                fechaVencField.value = '';
                fechaVencField.disabled = true;
            } else {
                fechaVencField.disabled = false;
                // Establecer fecha de vencimiento por defecto si está vacía
                if (!fechaVencField.value) {
                    const fechaVenc = new Date();
                    fechaVenc.setDate(fechaVenc.getDate() + 30);
                    fechaVencField.value = fechaVenc.toISOString().split('T')[0];
                }
            }
        }

        // Agregar producto a la factura
        function agregarProducto() {
            contadorProductos++;
            const container = document.getElementById('productosContainer');
            
            const productRow = document.createElement('div');
            productRow.className = 'product-row';
            productRow.id = `producto_${contadorProductos}`;
            
            productRow.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label">Producto *</label>
                        <div style="position: relative;">
                            <input type="text" class="form-control" 
                                   placeholder="Buscar producto..." 
                                   onkeyup="buscarProducto(this, ${contadorProductos})"
                                   onclick="mostrarTodosProductos(this, ${contadorProductos})">
                            <div class="search-results" id="results_${contadorProductos}"></div>
                        </div>
                        <input type="hidden" id="producto_id_${contadorProductos}">
                        <small class="text-muted">Stock: <span id="stock_${contadorProductos}">-</span></small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad_${contadorProductos}" 
                               min="1" value="1" onchange="calcularSubtotal(${contadorProductos})">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Unit. *</label>
                        <input type="number" class="form-control" id="precio_${contadorProductos}" 
                               step="0.01" min="0" onchange="calcularSubtotal(${contadorProductos})">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Descuento</label>
                        <input type="number" class="form-control" id="descuento_${contadorProductos}" 
                               step="0.01" value="0" min="0" onchange="calcularSubtotal(${contadorProductos})">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Subtotal</label>
                        <input type="text" class="form-control" id="subtotal_${contadorProductos}" readonly 
                               style="background-color: #f8f9fa;">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger w-100" onclick="eliminarProducto(${contadorProductos})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(productRow);
            
            // Enfocar en el campo de búsqueda
            setTimeout(() => {
                productRow.querySelector('input[type="text"]').focus();
            }, 100);
        }

        // Buscar producto
        async function buscarProducto(input, rowId) {
            const query = input.value.trim();
            const resultsDiv = document.getElementById(`results_${rowId}`);
            
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }
            
            try {
                const response = await fetch(`?action=buscar_productos&q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success && data.productos.length > 0) {
                    resultsDiv.innerHTML = data.productos.map(p => `
                        <div class="search-result-item" onclick="seleccionarProducto(${p.id}, '${p.codigo}', '${p.nombre.replace("'", "\\'")}', ${p.precio_venta}, ${p.stock_actual}, ${rowId})">
                            <strong>${p.codigo}</strong> - ${p.nombre}
                            <br><small class="text-muted">Precio: $${parseFloat(p.precio_venta).toFixed(2)} | Stock: ${p.stock_actual}</small>
                        </div>
                    `).join('');
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.innerHTML = '<div class="search-result-item">No se encontraron productos</div>';
                    resultsDiv.style.display = 'block';
                }
            } catch (error) {
                resultsDiv.innerHTML = '<div class="search-result-item">Error en la búsqueda</div>';
                resultsDiv.style.display = 'block';
                console.error('Error:', error);
            }
        }

        // Mostrar todos los productos
        async function mostrarTodosProductos(input, rowId) {
            const resultsDiv = document.getElementById(`results_${rowId}`);
            
            try {
                const response = await fetch(`?action=buscar_productos&q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success && data.productos.length > 0) {
                    resultsDiv.innerHTML = data.productos.slice(0, 10).map(p => `
                        <div class="search-result-item" onclick="seleccionarProducto(${p.id}, '${p.codigo}', '${p.nombre.replace("'", "\\'")}', ${p.precio_venta}, ${p.stock_actual}, ${rowId})">
                            <strong>${p.codigo}</strong> - ${p.nombre}
                            <br><small class="text-muted">Precio: $${parseFloat(p.precio_venta).toFixed(2)} | Stock: ${p.stock_actual}</small>
                        </div>
                    `).join('');
                    resultsDiv.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Seleccionar producto
        function seleccionarProducto(id, codigo, nombre, precio, stock, rowId) {
            const input = document.querySelector(`#producto_${rowId} input[type="text"]`);
            input.value = `${codigo} - ${nombre}`;
            
            document.getElementById(`producto_id_${rowId}`).value = id;
            document.getElementById(`precio_${rowId}`).value = parseFloat(precio).toFixed(2);
            document.getElementById(`stock_${rowId}`).textContent = stock;
            
            // Ocultar resultados
            document.getElementById(`results_${rowId}`).style.display = 'none';
            
            // Calcular subtotal
            calcularSubtotal(rowId);
        }

        // Calcular subtotal de producto
        function calcularSubtotal(rowId) {
            const cantidad = parseFloat(document.getElementById(`cantidad_${rowId}`).value) || 0;
            const precio = parseFloat(document.getElementById(`precio_${rowId}`).value) || 0;
            const descuento = parseFloat(document.getElementById(`descuento_${rowId}`).value) || 0;
            
            const subtotal = (cantidad * precio) - descuento;
            document.getElementById(`subtotal_${rowId}`).value = subtotal >= 0 ? subtotal.toFixed(2) : '0.00';
            
            calcularTotales();
        }

        // Eliminar producto
        function eliminarProducto(rowId) {
            const row = document.getElementById(`producto_${rowId}`);
            if (row) {
                row.remove();
                calcularTotales();
                mostrarMensaje('Producto eliminado', 'success');
            }
        }

        // Calcular totales de la factura
        function calcularTotales() {
            let subtotal = 0;
            let descuentoTotal = 0;
            
            // Sumar todos los subtotales
            for (let i = 1; i <= contadorProductos; i++) {
                const subtotalInput = document.getElementById(`subtotal_${i}`);
                const descuentoInput = document.getElementById(`descuento_${i}`);
                
                if (subtotalInput) {
                    subtotal += parseFloat(subtotalInput.value) || 0;
                }
                if (descuentoInput) {
                    descuentoTotal += parseFloat(descuentoInput.value) || 0;
                }
            }
            
            // Calcular ITBMS (7% sobre subtotal - descuento)
            const baseImponible = subtotal;
            const itbms = baseImponible * 0.07;
            const total = baseImponible + itbms;
            
            // Actualizar display
            document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
            document.getElementById('descuentoDisplay').textContent = descuentoTotal.toFixed(2);
            document.getElementById('itbmsDisplay').textContent = itbms.toFixed(2);
            document.getElementById('totalDisplay').textContent = total.toFixed(2);
        }

        // Guardar factura
        async function guardarFactura(imprimir = false) {
            if (!validarFormulario()) return;
            
            const facturaData = recopilarDatosFactura();
            
            try {
                const response = await fetch('?action=guardar_factura', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(facturaData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarMensaje(`Factura guardada exitosamente. Número: ${result.numero_factura}`, 'success');
                    
                    if (imprimir) {
                        // Aquí puedes agregar la funcionalidad de impresión
                        setTimeout(() => {
                            window.open(`imprimir_factura.php?id=${result.factura_id}`, '_blank');
                        }, 1000);
                    }
                    
                    setTimeout(() => {
                        window.location.href = 'ventas.php';
                    }, 2000);
                } else {
                    mostrarMensaje(`Error al guardar factura: ${result.message}`, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error al guardar la factura', 'error');
            }
        }

        // Validar formulario
        function validarFormulario() {
            if (!clienteSeleccionado) {
                mostrarMensaje('Debe seleccionar un cliente', 'error');
                return false;
            }
            
            let tieneProductos = false;
            for (let i = 1; i <= contadorProductos; i++) {
                const productoId = document.getElementById(`producto_id_${i}`);
                const cantidad = document.getElementById(`cantidad_${i}`);
                const precio = document.getElementById(`precio_${i}`);
                
                if (productoId && productoId.value && cantidad && cantidad.value && precio && precio.value) {
                    tieneProductos = true;
                    break;
                }
            }
            
            if (!tieneProductos) {
                mostrarMensaje('Debe agregar al menos un producto válido', 'error');
                return false;
            }
            
            return true;
        }

        // Recopilar datos de la factura
        function recopilarDatosFactura() {
            const productos = [];
            
            for (let i = 1; i <= contadorProductos; i++) {
                const productoId = document.getElementById(`producto_id_${i}`);
                const cantidad = document.getElementById(`cantidad_${i}`);
                const precio = document.getElementById(`precio_${i}`);
                const descuento = document.getElementById(`descuento_${i}`);
                const subtotal = document.getElementById(`subtotal_${i}`);
                
                if (productoId && productoId.value && cantidad && cantidad.value && precio && precio.value) {
                    productos.push({
                        id: productoId.value,
                        cantidad: parseFloat(cantidad.value),
                        precio: parseFloat(precio.value),
                        descuento: parseFloat(descuento?.value || 0),
                        subtotal: parseFloat(subtotal?.value || 0)
                    });
                }
            }
            
            return {
                empresa_id: 1,
                vendedor_id: <?php echo $vendedor_id; ?>,
                cliente_id: clienteSeleccionado.id,
                fecha_factura: new Date().toISOString().split('T')[0],
                fecha_vencimiento: document.getElementById('fecha_vencimiento').value,
                tipo_pago: document.getElementById('tipo_pago').value,
                subtotal: parseFloat(document.getElementById('subtotalDisplay').textContent),
                descuento: parseFloat(document.getElementById('descuentoDisplay').textContent),
                itbms: parseFloat(document.getElementById('itbmsDisplay').textContent),
                total: parseFloat(document.getElementById('totalDisplay').textContent),
                observaciones: document.getElementById('observaciones').value,
                productos: productos
            };
        }

        // Limpiar formulario
        function limpiarFormulario() {
            document.getElementById('facturaForm').reset();
            document.getElementById('productosContainer').innerHTML = '';
            document.getElementById('clienteInfo').style.display = 'none';
            document.getElementById('cliente_busqueda').value = '';
            clienteSeleccionado = null;
            contadorProductos = 0;
            calcularTotales();
            agregarProducto();
            establecerFechaActual();
            mostrarMensaje('Formulario limpiado', 'success');
        }

        // Mostrar mensaje
        function mostrarMensaje(mensaje, tipo = 'success') {
            // Crear toast notification
            const toast = document.createElement('div');
            toast.className = `alert alert-${tipo} alert-dismissible fade show`;
            toast.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.querySelector('.content-area').insertBefore(toast, document.querySelector('.container-fluid'));
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Ocultar resultados al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!e.target.matches('input[type="text"]')) {
                document.querySelectorAll('.search-results').forEach(div => {
                    div.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>