<?php
// ============================================================================
// LÓGICA DE ACCIONES (GUARDAR/ELIMINAR)
// ============================================================================
require_once '../config/connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para sanitizar entradas
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Acción de AGREGAR producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'agregar') {
    $conn = getDBConnection();
    try {
        // Sanitizar y validar datos
        $codigo = sanitizeInput($_POST['codigo']);
        $codigo_barras = sanitizeInput($_POST['codigo_barras']);
        $nombre = sanitizeInput($_POST['nombre']);
        $descripcion = sanitizeInput($_POST['descripcion']);
        $categoria_id = filter_var($_POST['categoria_id'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $marca_id = filter_var($_POST['marca_id'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $precio_compra = filter_var($_POST['precio_compra'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_FLAG_ALLOW_THOUSAND, 'options' => ['default' => 0.0]]);
        $precio_venta = filter_var($_POST['precio_venta'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_FLAG_ALLOW_THOUSAND]);
        $stock_minimo = filter_var($_POST['stock_minimo'], FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $stock_inicial = filter_var($_POST['stock_inicial'], FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $ubicacion = sanitizeInput($_POST['ubicacion']);
        $imagen_url = sanitizeInput($_POST['imagen_url']);

        // Handle image upload
        $imagen_final_url = $imagen_url;
        if (isset($_FILES['imagen_upload']) && $_FILES['imagen_upload']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'img/productos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = uniqid() . '-' . basename($_FILES['imagen_upload']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['imagen_upload']['tmp_name'], $target_file)) {
                $imagen_final_url = $target_file;
            }
        }

        if (empty($codigo) || empty($nombre) || $precio_venta === false) {
            throw new Exception("Código, nombre y precio de venta son campos obligatorios.");
        }

        $conn->beginTransaction();

        $sql_producto = "INSERT INTO productos (codigo, codigo_barras, nombre, descripcion, categoria_id, marca_id, precio_compra, precio_venta, stock_minimo, empresa_id, activo, ubicacion, imagen_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_producto = $conn->prepare($sql_producto);
        $stmt_producto->execute([$codigo, $codigo_barras, $nombre, $descripcion, $categoria_id, $marca_id, $precio_compra, $precio_venta, $stock_minimo, 1, 1, $ubicacion, $imagen_final_url]); // Asumiendo empresa_id=1, activo=1

        $producto_id = $conn->lastInsertId();

        $sql_inventario = "INSERT INTO inventario (producto_id, stock_actual, costo_promedio, fecha_ultima_actualizacion) VALUES (?, ?, ?, NOW())";
        $stmt_inventario = $conn->prepare($sql_inventario);
        $stmt_inventario->execute([$producto_id, $stock_inicial, $precio_compra]);

        if ($stock_inicial > 0) {
            // Asumiendo tipo_movimiento_id=1 (Entrada inicial), usuario_id=1 (Admin), empresa_id=1
            $sql_movimiento = "INSERT INTO movimientos_inventario (empresa_id, producto_id, tipo_movimiento_id, cantidad, costo_unitario, motivo, usuario_id, fecha_movimiento) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_movimiento = $conn->prepare($sql_movimiento);
            $stmt_movimiento->execute([1, $producto_id, 1, $stock_inicial, $precio_compra, 'Stock inicial', 1]);
        }

        $conn->commit();
        $_SESSION['success_message'] = "Producto '" . htmlspecialchars($nombre) . "' agregado correctamente.";
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Error: El código de producto o código de barras ya existe.";
        } else {
            $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header('Location: productos.php?page=productos');
    exit();
}

// Acción de ELIMINAR producto por POST (JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['id'])) {
        $id_eliminar = filter_var($input['id'], FILTER_VALIDATE_INT);
        if ($id_eliminar) {
            try {
                $conn = getDBConnection();
                $conn->beginTransaction();

                $stmt = $conn->prepare("DELETE FROM movimientos_inventario WHERE producto_id = ? AND empresa_id = ?");
                $stmt->execute([$id_eliminar, 1]);

                $stmt = $conn->prepare("DELETE FROM inventario WHERE producto_id = ?");
                $stmt->execute([$id_eliminar]);

                $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
                if ($stmt->execute([$id_eliminar])) {
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
                } else {
                    $conn->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto.']);
                }
            } catch (PDOException $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                // Si el error es por clave foránea (ventas asociadas)
                if (strpos($e->getMessage(), 'a foreign key constraint fails') !== false) {
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el producto porque tiene ventas asociadas.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de producto inválido para eliminar.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado.']);
    }
    exit();
}

// Acción de EDITAR producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $conn = getDBConnection();
    try {
        // Sanitizar y validar datos
        $producto_id = filter_var($_POST['producto_id'], FILTER_VALIDATE_INT);
        $codigo = sanitizeInput($_POST['codigo']);
        $codigo_barras = sanitizeInput($_POST['codigo_barras']);
        $nombre = sanitizeInput($_POST['nombre']);
        $descripcion = sanitizeInput($_POST['descripcion']);
        $categoria_id = filter_var($_POST['categoria_id'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $marca_id = filter_var($_POST['marca_id'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $precio_compra = filter_var($_POST['precio_compra'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_FLAG_ALLOW_THOUSAND, 'options' => ['default' => 0.0]]);
        $precio_venta = filter_var($_POST['precio_venta'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_FLAG_ALLOW_THOUSAND]);
        $stock_minimo = filter_var($_POST['stock_minimo'], FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

        if (empty($codigo) || empty($nombre) || $precio_venta === false || !$producto_id) {
            throw new Exception("Código, nombre, precio de venta e ID son campos obligatorios.");
        }

        $conn->beginTransaction();

        // Actualizar producto
        $sql_producto = "UPDATE productos SET codigo = ?, codigo_barras = ?, nombre = ?, descripcion = ?, categoria_id = ?, marca_id = ?, precio_compra = ?, precio_venta = ?, stock_minimo = ? WHERE id = ? AND empresa_id = ?";
        $stmt_producto = $conn->prepare($sql_producto);
        $stmt_producto->execute([$codigo, $codigo_barras, $nombre, $descripcion, $categoria_id, $marca_id, $precio_compra, $precio_venta, $stock_minimo, $producto_id, 1]);

        // Actualizar costo promedio en inventario
        $sql_inventario = "UPDATE inventario SET costo_promedio = ? WHERE producto_id = ?";
        $stmt_inventario = $conn->prepare($sql_inventario);
        $stmt_inventario->execute([$precio_compra, $producto_id]);

        $conn->commit();
        $_SESSION['success_message'] = "Producto '" . htmlspecialchars($nombre) . "' actualizado correctamente.";
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Error: El código de producto o código de barras ya existe.";
        } else {
            $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header('Location: productos.php?page=productos');
    exit();
}

// Endpoint para obtener datos de un producto (para editar)
if (isset($_GET['action']) && $_GET['action'] === 'obtener_producto') {
    $producto_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($producto_id) {
        try {
            $conn = getDBConnection();
            $sql = "SELECT p.*, c.nombre as categoria, m.nombre as marca, i.stock_actual 
                    FROM productos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    LEFT JOIN marcas m ON p.marca_id = m.id 
                    LEFT JOIN inventario i ON p.id = i.producto_id 
                    WHERE p.id = ? AND p.empresa_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$producto_id, 1]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'producto' => $producto]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    }
    exit();
}

// Acción de ACTIVAR/DESACTIVAR producto
if (isset($_GET['action']) && $_GET['action'] === 'toggle_estado') {
    $producto_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($producto_id) {
        try {
            $conn = getDBConnection();

            // Obtener estado actual
            $stmt = $conn->prepare("SELECT activo, nombre FROM productos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$producto_id, 1]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                $nuevo_estado = $producto['activo'] ? 0 : 1;
                $estado_texto = $nuevo_estado ? 'activado' : 'desactivado';

                $stmt = $conn->prepare("UPDATE productos SET activo = ? WHERE id = ? AND empresa_id = ?");
                if ($stmt->execute([$nuevo_estado, $producto_id, 1])) {
                    $_SESSION['success_message'] = "Producto '" . htmlspecialchars($producto['nombre']) . "' $estado_texto correctamente.";
                } else {
                    $_SESSION['error_message'] = "Error al cambiar el estado del producto.";
                }
            } else {
                $_SESSION['error_message'] = "Producto no encontrado.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "ID de producto inválido.";
    }
    header('Location: productos.php?page=productos');
    exit();
}

// Acción de AJUSTAR STOCK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajustar_stock') {
    $conn = getDBConnection();
    try {
        $producto_id = filter_var($_POST['producto_id'], FILTER_VALIDATE_INT);
        $nuevo_stock = filter_var($_POST['nuevo_stock'], FILTER_VALIDATE_INT);
        $motivo = sanitizeInput($_POST['motivo']);

        if (!$producto_id || $nuevo_stock === false) {
            throw new Exception("Datos inválidos para ajustar stock.");
        }

        $conn->beginTransaction();

        // Obtener stock actual y datos del producto
        $stmt = $conn->prepare("SELECT p.nombre, i.stock_actual FROM productos p LEFT JOIN inventario i ON p.id = i.producto_id WHERE p.id = ? AND p.empresa_id = ?");
        $stmt->execute([$producto_id, 1]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw new Exception("Producto no encontrado.");
        }

        $stock_actual = $producto['stock_actual'] ?? 0;
        $diferencia = $nuevo_stock - $stock_actual;

        // Actualizar inventario
        $sql_inventario = "UPDATE inventario SET stock_actual = ?, fecha_ultima_actualizacion = NOW() WHERE producto_id = ?";
        $stmt_inventario = $conn->prepare($sql_inventario);
        $stmt_inventario->execute([$nuevo_stock, $producto_id]);

        // Registrar movimiento de inventario
        if ($diferencia != 0) {
            $tipo_movimiento = $diferencia > 0 ? 1 : 2; // 1=Entrada, 2=Salida
            $sql_movimiento = "INSERT INTO movimientos_inventario (empresa_id, producto_id, tipo_movimiento_id, cantidad, costo_unitario, motivo, usuario_id, fecha_movimiento) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_movimiento = $conn->prepare($sql_movimiento);
            $stmt_movimiento->execute([1, $producto_id, $tipo_movimiento, abs($diferencia), 0, $motivo, 1]);
        }

        $conn->commit();
        $_SESSION['success_message'] = "Stock del producto '" . htmlspecialchars($producto['nombre']) . "' ajustado correctamente.";
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header('Location: productos.php?page=productos');
    exit();
}

// ============================================================================
// CARGA DE VISTA
// ============================================================================
include 'partials/head.php';
?>

<body>
    <div id="dashboardScreen" class="dashboard-container">
        <?php include 'partials/sidebar.php'; ?>
        <main class="main-content">
            <?php include 'partials/header.php'; ?>
            <div class="content-area">
                <?php
                // Inicializar variables para la vista
                $productos = [];
                $categorias = [];
                $marcas = [];
                $error_message_display = '';

                // Parámetros de paginación
                $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                $productos_por_pagina = 20; // 🔧 CAMBIAR AQUÍ: Modifica este número para cambiar elementos por página
                $offset = ($pagina_actual - 1) * $productos_por_pagina;

                try {
                    $conn = getDBConnection();
                    // Obtener categorías y marcas para los filtros
                    $stmt_cat = $conn->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
                    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

                    $stmt_mar = $conn->query("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");
                    $marcas = $stmt_mar->fetchAll(PDO::FETCH_ASSOC);

                    // Construir condiciones WHERE para filtros
                    $where_conditions = ["p.empresa_id = 1"]; // Asumiendo empresa_id = 1
                    $params = [];

                    // Aplicar filtros
                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $search = sanitizeInput($_GET['search']);
                        $where_conditions[] = "(p.nombre LIKE ? OR p.codigo LIKE ? OR p.codigo_barras LIKE ?)";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                    }

                    if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
                        $where_conditions[] = "p.categoria_id = ?";
                        $params[] = $_GET['categoria'];
                    }

                    if (isset($_GET['marca']) && !empty($_GET['marca'])) {
                        $where_conditions[] = "p.marca_id = ?";
                        $params[] = $_GET['marca'];
                    }

                    $where_clause = implode(" AND ", $where_conditions);

                    // Consulta para contar total de registros (para paginación)
                    $sql_count = "
    SELECT COUNT(*) as total
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    LEFT JOIN marcas m ON p.marca_id = m.id 
    LEFT JOIN inventario i ON p.id = i.producto_id
    WHERE $where_clause";

                    $stmt_count = $conn->prepare($sql_count);
                    $stmt_count->execute($params);
                    $total_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
                    $total_productos = $total_result['total'];
                    $total_paginas = ceil($total_productos / $productos_por_pagina);

                    // Consulta principal de productos con LIMIT y OFFSET
                    $sql = "SELECT 
        p.id, p.codigo, p.codigo_barras, p.nombre, p.descripcion, p.categoria_id,
        p.marca_id, p.precio_compra, p.precio_venta, p.stock_minimo, p.activo,
        c.nombre as categoria, m.nombre as marca,
        COALESCE(i.stock_actual, 0) as stock,
        CASE 
            WHEN COALESCE(i.stock_actual, 0) = 0 THEN 'critical'
            WHEN COALESCE(i.stock_actual, 0) <= p.stock_minimo THEN 'low'
            ELSE 'normal'
        END as estado_stock
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    LEFT JOIN marcas m ON p.marca_id = m.id 
    LEFT JOIN inventario i ON p.id = i.producto_id
    WHERE $where_clause
    ORDER BY p.nombre
    LIMIT $productos_por_pagina OFFSET $offset";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $error_message_display = "Error al cargar los datos: " . $e->getMessage();
                }
                ?>
                <div class="content-area">
                    <section id="productos-content" class="content-section active">
                        <div class="page-header">
                            <h1>Gestión de Productos</h1>
                            <button class="btn-primary" onclick="showAddProductModal()">
                                <i class="fas fa-plus"></i>
                                Agregar Producto
                            </button>
                        </div>

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

                        <?php if (!empty($error_message_display)): ?>
                        <div class="alert alert-danger"><?php echo $error_message_display; ?></div>
                        <?php endif; ?>


                        <!-- Filtros y tabla de productos -->
                        <div class="filters-bar">
                            <form method="GET" action="#" id="filter-form" class="filter-form">
                                <div class="filter-group">
                                    <input type="text" name="search" placeholder="Buscar productos..."
                                        class="filter-input" style="flex-grow: 1;"
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <select name="categoria" class="filter-select">
                                        <option value="">Todas las categorías</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>"
                                            <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $categoria['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($categoria['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="marca" class="filter-select">
                                        <option value="">Todas las marcas</option>
                                        <?php foreach ($marcas as $marca): ?>
                                        <option value="<?php echo $marca['id']; ?>"
                                            <?php echo (isset($_GET['marca']) && $_GET['marca'] == $marca['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($marca['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="filter-actions">
                                        <button type="submit" class="btn-primary filter-button"><i
                                                class="fas fa-search"></i> Buscar</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Información de paginación -->
                        <?php if ($total_productos > 0): ?>
                        <div class="pagination-info">
                            Mostrando <?php echo ($offset + 1); ?> -
                            <?php echo min($offset + $productos_por_pagina, $total_productos); ?>
                            de <?php echo number_format($total_productos); ?> productos
                        </div>
                        <?php endif; ?>

                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Marca</th>
                                        <th>Stock</th>
                                        <th>Precio Venta</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($productos)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No se encontraron productos</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                                        <td>
                                            <div class="product-info">
                                                <span
                                                    class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                                <span
                                                    class="product-desc"><?php echo htmlspecialchars($producto['descripcion']); ?></span>
                                            </div>
                                        </td>
                                        <td><span
                                                class="category-badge"><?php echo htmlspecialchars($producto['categoria'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($producto['marca'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if ($producto['estado_stock'] === 'critical'): ?>
                                            <span class="stock-badge critical"><?php echo $producto['stock']; ?></span>
                                            <?php elseif ($producto['estado_stock'] === 'low'): ?>
                                            <span class="stock-badge low"><?php echo $producto['stock']; ?></span>
                                            <?php else: ?>
                                            <span class="stock-badge normal"><?php echo $producto['stock']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?php echo number_format($producto['precio_venta'], 2); ?></td>
                                        <td><span
                                                class="status-badge <?php echo $producto['activo'] ? 'active' : 'inactive'; ?>"><?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <!-- <button class="btn-edit" title="Editar"
                                                    onclick="event.preventDefault(); showEditProductModal(<?php echo $producto['id']; ?>)"><i
                                                        class="fas fa-pencil-alt"></i></button> -->
                                                <button class="btn-delete" title="Eliminar"
                                                    onclick="event.preventDefault(); deleteProduct(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars(addslashes($producto['nombre'])); ?>')"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                        <div class="pagination-container mt-6">
                            <nav class="pagination-nav">
                                <ul class="pagination-list">
                                    <!-- Botón Anterior -->
                                    <?php if ($pagina_actual > 1): ?>
                                    <li>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])); ?>"
                                            class="pagination-link">
                                            <i class="fas fa-chevron-left"></i> Anterior
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <!-- Números de página -->
                                    <?php
                                        $inicio = max(1, $pagina_actual - 2);
                                        $fin = min($total_paginas, $pagina_actual + 2);

                                        if ($inicio > 1): ?>
                                    <li><a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>"
                                            class="pagination-link">1</a></li>
                                    <?php if ($inicio > 2): ?>
                                    <li><span class="pagination-ellipsis">...</span></li>
                                    <?php endif; ?>
                                    <?php endif; ?>

                                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"
                                            class="pagination-link <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>

                                    <?php if ($fin < $total_paginas): ?>
                                    <?php if ($fin < $total_paginas - 1): ?>
                                    <li><span class="pagination-ellipsis">...</span></li>
                                    <?php endif; ?>
                                    <li><a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>"
                                            class="pagination-link"><?php echo $total_paginas; ?></a></li>
                                    <?php endif; ?>

                                    <!-- Botón Siguiente -->
                                    <?php if ($pagina_actual < $total_paginas): ?>
                                    <li>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])); ?>"
                                            class="pagination-link">
                                            Siguiente <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>

                    </section>
                </div>
        </main>
    </div>
</body>

</html>

<?php include 'partials/modals.php'; ?>