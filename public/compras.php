<?php
require_once '../config/connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// LÓGICA DE ACCIONES (PROVEEDORES, PRODUCTOS, ÓRDENES)
// ============================================================================

// --- Acción para REGISTRAR PROVEEDOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registrar_proveedor') {
    $conn = getDBConnection();
    try {
        $codigo = htmlspecialchars(strip_tags($_POST['codigo']));
        $nombre = htmlspecialchars(strip_tags($_POST['nombre']));
        $razon_social = htmlspecialchars(strip_tags($_POST['razon_social']));
        $ruc = htmlspecialchars(strip_tags($_POST['ruc']));
        $direccion = htmlspecialchars(strip_tags($_POST['direccion']));
        $telefono = htmlspecialchars(strip_tags($_POST['telefono']));
        $email = htmlspecialchars(strip_tags($_POST['email']));
        $dias_credito = filter_var($_POST['dias_credito'], FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $empresa_id = 1;

        if (empty($nombre)) {
            throw new Exception("El nombre del proveedor es obligatorio.");
        }

        $sql = "INSERT INTO proveedores (empresa_id, codigo, nombre, razon_social, ruc, direccion, telefono_principal, email, dias_credito, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$empresa_id, $codigo, $nombre, $razon_social, $ruc, $direccion, $telefono, $email, $dias_credito]);

        $_SESSION['success_message'] = "Proveedor registrado correctamente.";
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Error: El RUC o código ya existe.";
        } else {
            $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header('Location: compras.php');
    exit();
}

// --- Acción para REGISTRAR PRODUCTO-PROVEEDOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registrar_producto_proveedor') {
    $conn = getDBConnection();
    try {
        $producto_id = filter_var($_POST['producto_id'], FILTER_VALIDATE_INT);
        $proveedor_id = filter_var($_POST['proveedor_id'], FILTER_VALIDATE_INT);
        $codigo_proveedor = htmlspecialchars(strip_tags($_POST['codigo_proveedor']));
        $precio_compra = filter_var($_POST['precio_compra'], FILTER_VALIDATE_FLOAT);
        $tiempo_entrega = filter_var($_POST['tiempo_entrega'], FILTER_VALIDATE_INT, ['options' => ['default' => 7]]);
        $es_principal = isset($_POST['es_principal']) ? 1 : 0;
        $empresa_id = 1;

        if (!$producto_id || !$proveedor_id || $precio_compra === false) {
            throw new Exception("Datos incompletos o inválidos.");
        }

        $sql = "INSERT INTO productos_proveedores (empresa_id, producto_id, proveedor_id, codigo_proveedor, precio_compra, tiempo_entrega, es_principal, activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$empresa_id, $producto_id, $proveedor_id, $codigo_proveedor, $precio_compra, $tiempo_entrega, $es_principal]);

        $_SESSION['success_message'] = "Producto asociado al proveedor correctamente.";
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Error: Este producto ya está asociado a este proveedor.";
        } else {
            $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header('Location: compras.php');
    exit();
}

// --- Acción para CREAR ORDEN DE COMPRA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear_orden_compra') {
    $conn = getDBConnection();
    try {
        $proveedor_id = filter_var($_POST['proveedor_id'], FILTER_VALIDATE_INT);
        $fecha_entrega = $_POST['fecha_entrega'];
        $observaciones = htmlspecialchars(strip_tags($_POST['observaciones']));
        $usuario_id = 1; // Asumiendo usuario logueado
        $empresa_id = 1;

        if (!$proveedor_id) {
            throw new Exception("Proveedor es obligatorio.");
        }

        $conn->beginTransaction();

        // Generar número de orden
        $sql_secuencia = "SELECT siguiente_numero FROM secuencias WHERE empresa_id = ? AND tipo = 'orden_compra'";
        $stmt = $conn->prepare($sql_secuencia);
        $stmt->execute([$empresa_id]);
        $secuencia = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero_orden = 'OC-' . str_pad($secuencia['siguiente_numero'], 6, '0', STR_PAD_LEFT);

        // Crear orden
        $sql_orden = "INSERT INTO ordenes_compra (empresa_id, numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_entrega_esperada, subtotal, itbms, total, estado, observaciones) VALUES (?, ?, ?, ?, CURDATE(), ?, 0, 0, 0, 'pendiente', ?)";
        $stmt = $conn->prepare($sql_orden);
        $stmt->execute([$empresa_id, $numero_orden, $proveedor_id, $usuario_id, $fecha_entrega, $observaciones]);

        $orden_id = $conn->lastInsertId();

        // Actualizar secuencia
        $sql_update_secuencia = "UPDATE secuencias SET siguiente_numero = siguiente_numero + 1 WHERE empresa_id = ? AND tipo = 'orden_compra'";
        $conn->prepare($sql_update_secuencia)->execute([$empresa_id]);

        $conn->commit();
        $_SESSION['success_message'] = "Orden de compra creada: " . $numero_orden;
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header('Location: compras.php');
    exit();
}

// --- Acción para CAMBIAR ESTADO DE ORDEN ---
if (isset($_GET['action']) && $_GET['action'] === 'cambiar_estado' && isset($_GET['id']) && isset($_GET['estado'])) {
    $orden_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $nuevo_estado = $_GET['estado'];

    if ($orden_id && in_array($nuevo_estado, ['pendiente', 'confirmada', 'recibida', 'cancelada'])) {
        try {
            $sql = "UPDATE ordenes_compra SET estado = ? WHERE id = ? AND empresa_id = 1";
            $stmt = ejecutarConsulta($sql, [$nuevo_estado, $orden_id]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Estado de orden actualizado correctamente.";
            } else {
                $_SESSION['error_message'] = "No se pudo actualizar el estado.";
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Parámetros inválidos.";
    }

    header('Location: compras.php');
    exit();
}

// --- Acción para obtener datos de proveedor (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] === 'get_proveedor' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id) {
        $sql = "SELECT * FROM proveedores WHERE id = ? AND empresa_id = 1";
        $proveedor = ejecutarConsulta($sql, [$id])->fetch(PDO::FETCH_ASSOC);
        echo json_encode($proveedor ?: ['error' => 'Proveedor no encontrado']);
    } else {
        echo json_encode(['error' => 'ID inválido']);
    }
    exit();
}

// ============================================================================
// CARGA DE VISTA
// ============================================================================
include 'partials/head.php';

// Obtener datos para filtros y listas
$proveedores = ejecutarConsulta("SELECT id, nombre, codigo FROM proveedores WHERE empresa_id = 1 AND activo = 1 ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$productos = ejecutarConsulta("SELECT id, codigo, nombre FROM productos WHERE empresa_id = 1 AND activo = 1 ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Parámetros de filtros
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
$proveedor_filtro = isset($_GET['proveedor']) ? (int)$_GET['proveedor'] : 0;

// Consulta de órdenes de compra
$where_conditions = ["oc.empresa_id = 1"];
$params = [];

if (!empty($estado_filtro)) {
    $where_conditions[] = "oc.estado = ?";
    $params[] = $estado_filtro;
}

if ($proveedor_filtro > 0) {
    $where_conditions[] = "oc.proveedor_id = ?";
    $params[] = $proveedor_filtro;
}

$where_clause = implode(" AND ", $where_conditions);

$sql_ordenes = "
SELECT 
    oc.id, oc.numero_orden, oc.fecha_orden, oc.fecha_entrega_esperada,
    oc.subtotal, oc.itbms, oc.total, oc.estado, oc.observaciones,
    p.nombre as proveedor_nombre, p.codigo as proveedor_codigo,
    u.nombre as usuario_nombre, u.apellido as usuario_apellido,
    CASE 
        WHEN oc.estado = 'pendiente' THEN 'warning'
        WHEN oc.estado = 'confirmada' THEN 'info'
        WHEN oc.estado = 'recibida' THEN 'success'
        WHEN oc.estado = 'cancelada' THEN 'danger'
        ELSE 'secondary'
    END as estado_clase
FROM ordenes_compra oc
LEFT JOIN proveedores p ON oc.proveedor_id = p.id
LEFT JOIN usuarios u ON oc.usuario_id = u.id
WHERE $where_clause
ORDER BY oc.fecha_orden DESC, oc.id DESC";

$ordenes = ejecutarConsulta($sql_ordenes, $params)->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas
$stats = ejecutarConsulta("
SELECT 
    COUNT(*) as total_ordenes,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
    SUM(CASE WHEN estado = 'recibida' THEN 1 ELSE 0 END) as recibidas,
    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
    COALESCE(SUM(total), 0) as total_valor
FROM ordenes_compra 
WHERE empresa_id = 1")->fetch(PDO::FETCH_ASSOC);
?>

<body>
    <div id="dashboardScreen" class="dashboard-container">
        <?php include 'partials/sidebar.php'; ?>
        <main class="main-content">
            <?php include 'partials/header.php'; ?>
            <div class="content-area">
                <div class="page-header">
                    <h1>Gestión de Compras</h1>
                    <div class="header-actions">
                        <button class="btn-secondary" onclick="showAddSupplierModal()">
                            <i class="fas fa-plus"></i> Nuevo Proveedor
                        </button>
                        <button class="btn-secondary" onclick="showAddProductSupplierModal()">
                            <i class="fas fa-link"></i> Asociar Producto
                        </button>
                        <button class="btn-primary" onclick="showAddOrderModal()">
                            <i class="fas fa-shopping-cart"></i> Nueva Orden
                        </button>
                    </div>
                </div>

                <!-- Mensajes de sesión -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                        <div class="stat-content">
                            <h3>Total Órdenes</h3>
                            <p class="stat-value"><?php echo $stats['total_ordenes']; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-content">
                            <h3>Pendientes</h3>
                            <p class="stat-value"><?php echo $stats['pendientes']; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-truck"></i></div>
                        <div class="stat-content">
                            <h3>En Camino</h3>
                            <p class="stat-value"><?php echo $stats['confirmadas']; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-content">
                            <h3>Recibidas</h3>
                            <p class="stat-value"><?php echo $stats['recibidas']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filters-bar">
                    <form method="GET" class="filter-group">
                        <select name="estado" class="filter-select">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" <?php echo $estado_filtro === 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                            <option value="confirmada" <?php echo $estado_filtro === 'confirmada' ? 'selected' : ''; ?>>En Camino</option>
                            <option value="recibida" <?php echo $estado_filtro === 'recibida' ? 'selected' : ''; ?>>Recibidas</option>
                            <option value="cancelada" <?php echo $estado_filtro === 'cancelada' ? 'selected' : ''; ?>>Canceladas</option>
                        </select>

                        <select name="proveedor" class="filter-select">
                            <option value="">Todos los proveedores</option>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?php echo $prov['id']; ?>" <?php echo $proveedor_filtro == $prov['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prov['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn-secondary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>

                        <?php if (!empty($estado_filtro) || $proveedor_filtro > 0): ?>
                            <a href="compras.php" class="btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Tabla de órdenes -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th>Entrega</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ordenes)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron órdenes de compra</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ordenes as $orden): ?>
                                    <tr>
                                        <td>
                                            <div class="order-info">
                                                <div class="order-number"><?php echo htmlspecialchars($orden['numero_orden']); ?></div>
                                                <div class="order-date"><?php echo date('d/m/Y', strtotime($orden['fecha_orden'])); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="supplier-info">
                                                <div class="supplier-name"><?php echo htmlspecialchars($orden['proveedor_nombre']); ?></div>
                                                <div class="supplier-code"><?php echo htmlspecialchars($orden['proveedor_codigo']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($orden['fecha_orden'])); ?></td>
                                        <td><?php echo $orden['fecha_entrega_esperada'] ? date('d/m/Y', strtotime($orden['fecha_entrega_esperada'])) : 'N/A'; ?></td>
                                        <td>$<?php echo number_format($orden['total'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $orden['estado_clase']; ?>">
                                                <?php echo ucfirst($orden['estado']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($orden['usuario_nombre'] . ' ' . $orden['usuario_apellido']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-view" onclick="verOrden(<?php echo $orden['id']; ?>)" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn-edit" onclick="editarOrden(<?php echo $orden['id']; ?>)" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn-secondary dropdown-toggle" onclick="toggleDropdown(<?php echo $orden['id']; ?>)" title="Cambiar Estado">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <div class="dropdown-menu" id="dropdown-<?php echo $orden['id']; ?>">
                                                        <a href="compras.php?action=cambiar_estado&id=<?php echo $orden['id']; ?>&estado=pendiente">Pendiente</a>
                                                        <a href="compras.php?action=cambiar_estado&id=<?php echo $orden['id']; ?>&estado=confirmada">Confirmada</a>
                                                        <a href="compras.php?action=cambiar_estado&id=<?php echo $orden['id']; ?>&estado=recibida">Recibida</a>
                                                        <a href="compras.php?action=cambiar_estado&id=<?php echo $orden['id']; ?>&estado=cancelada">Cancelada</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include 'partials/modals.php'; ?>

    <!-- Modal para nuevo proveedor -->
    <div id="addSupplierModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Nuevo Proveedor</h2>
                <button class="modal-close" onclick="closeModal('addSupplierModal')">&times;</button>
            </div>
            <form method="POST" action="compras.php" class="modal-form">
                <input type="hidden" name="action" value="registrar_proveedor">

                <div class="form-row">
                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" name="codigo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="razon_social">Razón Social</label>
                        <input type="text" name="razon_social" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ruc">RUC</label>
                        <input type="text" name="ruc" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea name="direccion" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="dias_credito">Días de Crédito</label>
                    <input type="number" name="dias_credito" class="form-control" value="0" min="0">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('addSupplierModal')">Cancelar</button>
                    <button type="submit" class="btn-primary">Registrar Proveedor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para nueva orden de compra -->
    <div id="addOrderModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-shopping-cart"></i> Nueva Orden de Compra</h2>
                <button class="modal-close" onclick="closeModal('addOrderModal')">&times;</button>
            </div>
            <form method="POST" action="compras.php" class="modal-form">
                <input type="hidden" name="action" value="crear_orden_compra">

                <div class="form-group">
                    <label for="proveedor_id">Proveedor *</label>
                    <select name="proveedor_id" class="form-control" required>
                        <option value="">Seleccionar proveedor</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?php echo $proveedor['id']; ?>">
                                <?php echo htmlspecialchars($proveedor['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_entrega">Fecha de Entrega Esperada</label>
                    <input type="date" name="fecha_entrega" class="form-control">
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('addOrderModal')">Cancelar</button>
                    <button type="submit" class="btn-primary">Crear Orden</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para producto-proveedor -->
    <div id="addProductSupplierModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-link"></i> Asociar Producto con Proveedor</h2>
                <button class="modal-close" onclick="closeModal('addProductSupplierModal')">&times;</button>
            </div>
            <form method="POST" action="compras.php" class="modal-form">
                <input type="hidden" name="action" value="registrar_producto_proveedor">

                <div class="form-row">
                    <div class="form-group">
                        <label for="producto_id">Producto *</label>
                        <select name="producto_id" class="form-control" required>
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>">
                                    <?php echo htmlspecialchars($producto['codigo'] . ' - ' . $producto['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="proveedor_id">Proveedor *</label>
                        <select name="proveedor_id" class="form-control" required>
                            <option value="">Seleccionar proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id']; ?>">
                                    <?php echo htmlspecialchars($proveedor['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="codigo_proveedor">Código del Proveedor</label>
                        <input type="text" name="codigo_proveedor" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="precio_compra">Precio de Compra *</label>
                        <input type="number" name="precio_compra" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tiempo_entrega">Tiempo de Entrega (días)</label>
                        <input type="number" name="tiempo_entrega" class="form-control" value="7" min="1">
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="es_principal">
                            <span class="checkmark"></span>
                            Proveedor Principal
                        </label>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('addProductSupplierModal')">Cancelar</button>
                    <button type="submit" class="btn-primary">Asociar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddSupplierModal() {
            document.getElementById('addSupplierModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevenir scroll del body
        }

        function showAddOrderModal() {
            document.getElementById('addOrderModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function showAddProductSupplierModal() {
            document.getElementById('addProductSupplierModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = ''; // Restaurar scroll del body
        }

        function toggleDropdown(orderId) {
            const dropdown = document.getElementById('dropdown-' + orderId);
            dropdown.classList.toggle('show');
        }

        function verOrden(id) {
            // Implementar vista detallada de orden
            window.open('compras.php?action=ver&id=' + id, '_blank');
        }

        function editarOrden(id) {
            // Implementar edición de orden
            window.location.href = 'compras.php?action=editar&id=' + id;
        }

        // Cerrar dropdowns al hacer clic fuera
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-toggle')) {
                const dropdowns = document.getElementsByClassName('dropdown-menu');
                for (let dropdown of dropdowns) {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                }
            }
        }

        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });

        // Cerrar modales con la tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                });
            }
        });

        // Prevenir que los clics dentro del modal lo cierren
        document.addEventListener('click', function(event) {
            if (event.target.closest('.modal-content')) {
                event.stopPropagation();
            }
        });
    </script>

    <style>
        .header-actions {
            display: flex;
            gap: var(--spacing-md);
        }

        .order-info,
        .supplier-info {
            display: flex;
            flex-direction: column;
        }

        .order-number,
        .supplier-name {
            font-weight: bold;
            color: var(--primary-color);
        }

        .order-date,
        .supplier-code {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: var(--shadow-medium);
            z-index: 1;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu a {
            color: var(--text-primary);
            padding: var(--spacing-sm) var(--spacing-md);
            text-decoration: none;
            display: block;
        }

        .dropdown-menu a:hover {
            background-color: var(--bg-primary);
        }

        .dropdown-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: var(--spacing-sm);
            border-radius: var(--radius-sm);
        }

        .dropdown-toggle:hover {
            background-color: var(--bg-primary);
        }

        .modal.hidden {
            display: none;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            margin: 0;
        }

        /* Mejoras adicionales para los modales */
        .modal {
            backdrop-filter: blur(5px);
        }

        .modal-content {
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }

        .modal:not(.hidden) .modal-content {
            transform: scale(1);
        }

        .form-control:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 73, 94, 0.15);
        }

        .btn-primary,
        .btn-secondary {
            transition: all 0.3s ease;
        }

        .btn-primary:hover,
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Animación para los botones de acción */
        .action-buttons button {
            transition: all 0.2s ease;
        }

        .action-buttons button:hover {
            transform: scale(1.1);
        }

        /* Mejoras para las estadísticas */
        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(52, 73, 94, 0.2);
        }

        /* Mejoras para la tabla */
        .data-table tr {
            transition: background-color 0.2s ease;
        }

        .data-table tr:hover {
            background-color: rgba(52, 73, 94, 0.05);
        }

        /* Estilos para los filtros */
        .filter-group {
            transition: all 0.3s ease;
        }

        .filter-group:hover {
            transform: translateY(-1px);
        }

        /* Mejoras para los estados */
        .status-badge {
            transition: all 0.2s ease;
        }

        .status-badge:hover {
            transform: scale(1.05);
        }
    </style>
</body>

</html>