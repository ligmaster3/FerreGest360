<?php
require_once '../config/connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// LÓGICA DE ACCIONES (EDITAR, DESACTIVAR, OBTENER DATOS)
// ============================================================================

// --- Acción para obtener datos de un producto (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] === 'get_producto' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }

    $sql = "SELECT p.id, p.nombre, p.codigo, p.codigo_barras, p.descripcion, p.precio_compra, p.precio_venta, p.stock_minimo, p.ubicacion, p.imagen_url, p.categoria_id, p.marca_id, i.stock_actual, i.costo_promedio, c.nombre as categoria_nombre, m.nombre as marca_nombre
            FROM productos p 
            LEFT JOIN inventario i ON p.id = i.producto_id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN marcas m ON p.marca_id = m.id
            WHERE p.id = ?";
    $producto = ejecutarConsulta($sql, [$id])->fetch(PDO::FETCH_ASSOC);

    echo json_encode($producto ?: ['error' => 'Producto no encontrado']);
    exit;
}

// --- Acción para EDITAR un producto (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_producto') {
    $conn = getDBConnection();
    try {
        $id = filter_var($_POST['id_producto'], FILTER_VALIDATE_INT);
        if (!$id) throw new Exception("ID de producto inválido.");

        $params = [
            'nombre' => htmlspecialchars(strip_tags(trim($_POST['nombre']))),
            'descripcion' => htmlspecialchars(strip_tags(trim($_POST['descripcion']))),
            'codigo' => htmlspecialchars(strip_tags(trim($_POST['codigo']))),
            'precio_venta' => filter_var($_POST['precio_venta'], FILTER_VALIDATE_FLOAT),
            'stock_minimo' => filter_var($_POST['stock_minimo'], FILTER_VALIDATE_INT),
            'categoria_id' => filter_var($_POST['categoria_id'], FILTER_VALIDATE_INT),
            'marca_id' => filter_var($_POST['marca_id'], FILTER_VALIDATE_INT),
            'id' => $id
        ];

        $sql = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, codigo = :codigo, precio_venta = :precio_venta, stock_minimo = :stock_minimo, categoria_id = :categoria_id, marca_id = :marca_id WHERE id = :id";
        ejecutarConsulta($sql, $params);
        $_SESSION['success_message'] = "Producto actualizado correctamente.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error al actualizar el producto: " . $e->getMessage();
    }
    header('Location: inventario.php');
    exit;
}

// --- Acción para DESACTIVAR un producto (GET) ---
if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $sql = "UPDATE productos SET activo = 0 WHERE id = ?";
            $stmt = ejecutarConsulta($sql, [$id]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Producto desactivado correctamente.";
            } else {
                $_SESSION['error_message'] = "El producto no se encontró o ya estaba inactivo.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al desactivar el producto: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "ID de producto inválido.";
    }
    header('Location: inventario.php');
    exit;
}

// ============================================================================
// CARGA DE VISTA Y CONSULTAS PRINCIPALES
// ============================================================================
include 'partials/head.php';

// Parámetros de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$productos_por_pagina = 20; // 🔧 CAMBIAR AQUÍ: Modifica este número para cambiar elementos por página (10, 15, 25, 50, etc.)
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Parámetros de búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$estado_stock = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir la consulta base con condiciones WHERE
$where_conditions = ["p.activo = 1"];
$params = [];

// Agregar condición de búsqueda si existe
if (!empty($busqueda)) {
    $where_conditions[] = "(p.nombre LIKE ? OR p.codigo LIKE ? OR p.codigo_barras LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
}

// Agregar filtro por categoría
if ($categoria_id > 0) {
    $where_conditions[] = "p.categoria_id = ?";
    $params[] = $categoria_id;
}

// Agregar filtro por estado de stock
if (!empty($estado_stock)) {
    switch ($estado_stock) {
        case 'critical':
            $where_conditions[] = "i.stock_actual = 0";
            break;
        case 'low':
            $where_conditions[] = "i.stock_actual <= p.stock_minimo AND i.stock_actual > 0";
            break;
        case 'normal':
            $where_conditions[] = "i.stock_actual > p.stock_minimo";
            break;
    }
}

$where_clause = implode(" AND ", $where_conditions);

// Consulta para contar total de registros (para paginación)
$sql_count = "
SELECT COUNT(*) as total
FROM productos p
LEFT JOIN inventario i ON p.id = i.producto_id
WHERE $where_clause";

$total_result = ejecutarConsulta($sql_count, $params)->fetch(PDO::FETCH_ASSOC);
$total_productos = $total_result['total'];
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Consulta para obtener estadísticas del inventario (optimizada)
$sql_stats = "
SELECT 
    COUNT(*) as total_productos,
    SUM(CASE WHEN i.stock_actual > p.stock_minimo THEN 1 ELSE 0 END) as stock_normal,
    SUM(CASE WHEN i.stock_actual <= p.stock_minimo AND i.stock_actual > 0 THEN 1 ELSE 0 END) as stock_bajo,
    SUM(CASE WHEN i.stock_actual = 0 THEN 1 ELSE 0 END) as stock_critico
FROM productos p
LEFT JOIN inventario i ON p.id = i.producto_id
WHERE p.activo = 1";

$stats = ejecutarConsulta($sql_stats)->fetch(PDO::FETCH_ASSOC);

// Consulta para obtener categorías para el filtro
$sql_categorias = "
SELECT id, nombre 
FROM categorias 
WHERE empresa_id = 1 AND activo = 1 
ORDER BY nombre";

$categorias = ejecutarConsulta($sql_categorias)->fetchAll(PDO::FETCH_ASSOC);

// Consulta principal optimizada con LIMIT y OFFSET
$sql_productos = "
SELECT 
    p.id,
    p.codigo,
    p.codigo_barras,
    p.nombre,
    p.descripcion,
    p.precio_compra,
    p.precio_venta,
    p.stock_minimo,
    p.ubicacion,
    p.imagen_url,
    c.nombre as categoria_nombre,
    m.nombre as marca_nombre,
    i.stock_actual,
    i.costo_promedio,
    CASE 
        WHEN i.stock_actual = 0 THEN 'critical'
        WHEN i.stock_actual <= p.stock_minimo THEN 'low'
        ELSE 'normal'
    END as estado_stock,
    ROUND(((p.precio_venta - COALESCE(i.costo_promedio, p.precio_compra)) / p.precio_venta) * 100, 0) as margen_porcentaje
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN marcas m ON p.marca_id = m.id
LEFT JOIN inventario i ON p.id = i.producto_id
WHERE $where_clause
ORDER BY p.nombre
LIMIT $productos_por_pagina OFFSET $offset";

$productos = ejecutarConsulta($sql_productos, $params)->fetchAll(PDO::FETCH_ASSOC);
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

                <!-- Filtros mejorados -->
                <div class="filters-bar bg-light rounded-lg shadow p-6 mb-6">
                    <form method="GET" action="" class="filter-form">
                        <div class="filter-group">
                            <input type="text" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>"
                                placeholder="Nombre, Código, ID..." class="filter-input" style="flex-grow: 1;">

                            <select name="categoria" class="filter-select">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"
                                    <?php echo $categoria_id == $categoria['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="estado" class="filter-select">
                                <option value="">Todos los estados</option>
                                <option value="normal" <?php echo $estado_stock == 'normal' ? 'selected' : ''; ?>>Stock
                                    Normal</option>
                                <option value="low" <?php echo $estado_stock == 'low' ? 'selected' : ''; ?>>Stock Bajo
                                </option>
                                <option value="critical" <?php echo $estado_stock == 'critical' ? 'selected' : ''; ?>>
                                    Stock Crítico</option>
                            </select>

                            <button type="submit" class="btn-secondary">
                                <i class="fas fa-search"></i> Buscar
                            </button>

                            <?php if (!empty($busqueda) || $categoria_id > 0 || !empty($estado_stock)): ?>
                            <a href="inventario.php" class="btn-secondary btn-clear">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Estadísticas -->
                <div class="rounded-lg p-4 stats-grid">
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-primary text-center">Total Productos</h3>
                            <p class="stat-value text-info text-center"><?php echo $stats['total_productos']; ?></p>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-success text-center">Stock Normal</h3>
                            <p class="stat-value text-success text-center"><?php echo $stats['stock_normal']; ?></p>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-warning text-center">Stock Bajo</h3>
                            <p class="stat-value text-warning text-center"><?php echo $stats['stock_bajo']; ?></p>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-danger text-center">Stock Crítico</h3>
                            <p class="stat-value text-danger text-center"><?php echo $stats['stock_critico']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de productos -->
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
                            <tbody id="productosTableBody">
                                <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <p>No se encontraron productos con los filtros aplicados</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($productos as $producto): ?>
                                <tr class="producto-row">
                                    <td>
                                        <div class="product-info">
                                            <div class="product-name">
                                                <?php echo htmlspecialchars($producto['nombre']); ?></div>
                                            <div class="product-desc">
                                                <?php echo htmlspecialchars($producto['codigo']); ?>
                                                <?php if ($producto['codigo_barras']): ?>
                                                - <?php echo htmlspecialchars($producto['codigo_barras']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge">
                                            <?php echo htmlspecialchars($producto['categoria_nombre'] ?: 'Sin categoría'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?php echo $producto['estado_stock']; ?>">
                                            <?php echo $producto['stock_actual']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-900">
                                            $<?php echo number_format($producto['precio_venta'], 2); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Costo:
                                            $<?php echo number_format($producto['costo_promedio'] ?: $producto['precio_compra'], 2); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm font-medium text-green-600">
                                            <?php echo $producto['margen_porcentaje']; ?>%
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['ubicacion'] ?: 'Sin ubicación'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit"
                                                onclick="showProductDetailModal(<?php echo $producto['id']; ?>)"
                                                title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-edit"
                                                onclick="editarProducto(<?php echo $producto['id']; ?>)"
                                                title="Editar Producto">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn-stock" title="Ajustar Stock"
                                                onclick="event.preventDefault(); showAdjustStockModal(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars(addslashes($producto['nombre'])); ?>', <?php echo $producto['stock_actual']; ?>)">
                                                <i class="fas fa-warehouse"></i>
                                            </button>
                                            <button class="btn-delete"
                                                onclick="eliminarProducto(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars(addslashes($producto['nombre'])); ?>')"
                                                title="Desactivar Producto">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
            </div>
        </main>
    </div>

    <?php include 'partials/modals.php'; ?>

    <style>
    .filter-form {
        width: 100%;
    }

    .btn-clear {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
    }

    .btn-clear:hover {
        background-color: #5a6268;
        color: white;
        text-decoration: none;
    }

    .results-info {
        background-color: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        border-left: 4px solid #007bff;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .pagination-nav {
        display: inline-block;
    }

    .pagination-list {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 5px;
    }

    .pagination-link {
        display: inline-block;
        padding: 8px 12px;
        text-decoration: none;
        color: #007bff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background-color: white;
    }

    .pagination-link:hover {
        background-color: #e9ecef;
        color: #0056b3;
    }

    .pagination-link.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .pagination-ellipsis {
        padding: 8px 12px;
        color: #6c757d;
    }
    </style>

    <script>
    function showProductDetailModal(id) {
        openModal('productDetailModal');
        const content = document.getElementById('productDetailContent');
        content.innerHTML =
            `<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p>Cargando detalles...</p></div>`;

        fetch(`inventario.php?action=get_producto&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    content.innerHTML = `<p class="text-danger">${data.error}</p>`;
                    return;
                }

                const formatCurrency = (value) => value ? `$${parseFloat(value).toFixed(2)}` : '$0.00';
                const checkEmpty = (value, fallback = 'N/A') => value || fallback;

                let imageUrl = data.imagen_url ? data.imagen_url : 'https://placehold.co/400x400'
                if (imageUrl && !imageUrl.startsWith('http')) {
                    imageUrl = '../public/' + imageUrl;
                }

                const detailHtml = `
                        <style>
                            .product-detail-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
                            .product-detail-info, .product-detail-pricing, .product-detail-stock { grid-column: 2 / 3; }
                            .product-detail-image { grid-row: 1 / 4; }
                            .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
                        </style>
                        <div class="product-detail-grid">
                            <div class="product-detail-image">
                                <img src="${imageUrl}" alt="${checkEmpty(data.nombre, 'Imagen de producto')}" style="max-width:100%; height:auto; border-radius:8px;">
                            </div>
                            <div class="product-detail-info">
                                <h3>${checkEmpty(data.nombre)}</h3>
                                <p class="text-muted p-sm ">${checkEmpty(data.descripcion)}</p>
                                <hr>
                                <div class="detail-row">
                                    <div><strong>Código:</strong> ${checkEmpty(data.codigo)}</div>
                                    <div><strong>Cód. Barras:</strong> ${checkEmpty(data.codigo_barras)}</div>
                                </div>
                                <div class="detail-row">
                                    <div><strong>Categoría:</strong> ${checkEmpty(data.categoria_nombre)}</div>
                                    <div><strong>Marca:</strong> ${checkEmpty(data.marca_nombre)}</div>
                                </div>
                                <div class="detail-row">
                                    <div><strong>Ubicación:</strong> ${checkEmpty(data.ubicacion)}</div>
                                </div>
                            </div>
                            <div class="product-detail-pricing">
                                <h4 class="text-left fw-bold p-sm ">Precios y Costos</h4>
                                <hr>
                                <div class="detail-row">
                                    <div><strong>Precio Venta:</strong> <span class="text-success">${formatCurrency(data.precio_venta)}</span></div>
                                    <div><strong>Precio Compra:</strong> ${formatCurrency(data.precio_compra)}</div>
                                </div>
                                <div class="detail-row">
                                    <div><strong>Costo Promedio:</strong> ${formatCurrency(data.costo_promedio)}</div>
                                </div>
                            </div>
                            <div class="product-detail-stock">
                                <h4 class="text-left fw-bold p-sm ">Inventario</h4><hr>
                                <div class="detail-row">
                                    <div><strong>Stock Actual:</strong> <span class="font-bold">${checkEmpty(data.stock_actual, 0)}</span></div>
                                    <div><strong>Stock Mínimo:</strong> <span class="text-warning">${checkEmpty(data.stock_minimo, 0)}</span></div>
                                </div>
                            </div>
                        </div>
                    `;
                content.innerHTML = detailHtml;
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<p class="text-danger">Error al cargar los datos del producto.</p>`;
            });
    }

    function editarProducto(id) {
        fetch(`inventario.php?action=get_producto&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showErrorModal(data.error);
                    return;
                }

                // Usar el modal de edición en lugar del de agregar
                const modal = document.getElementById('editProductModal');
                if (!modal) {
                    showErrorModal('Modal de edición de producto no encontrado');
                    return;
                }

                // Función helper para establecer valor de forma segura
                function setValueSafely(selector, value) {
                    const element = modal.querySelector(selector);
                    if (element) {
                        element.value = value || '';
                    }
                }

                // Establecer valores de forma segura usando los IDs del modal de edición
                setValueSafely('#edit_producto_id', data.id);
                setValueSafely('#edit_codigo', data.codigo);
                setValueSafely('#edit_nombre', data.nombre);
                setValueSafely('#edit_descripcion', data.descripcion);
                setValueSafely('#edit_precio_venta', data.precio_venta);
                setValueSafely('#edit_stock_minimo', data.stock_minimo);

                // Llenar selects de forma segura
                const categoriaSelect = modal.querySelector('#edit_categoria_id');
                if (categoriaSelect && data.categorias) {
                    categoriaSelect.innerHTML = data.categorias.map(c =>
                        `<option value="${c.id}" ${c.id == data.categoria_id ? 'selected' : ''}>${c.nombre}</option>`
                    ).join('');
                }

                const marcaSelect = modal.querySelector('#edit_marca_id');
                if (marcaSelect && data.marcas) {
                    marcaSelect.innerHTML = data.marcas.map(m =>
                        `<option value="${m.id}" ${m.id == data.marca_id ? 'selected' : ''}>${m.nombre}</option>`
                    ).join('');
                }

                // Cambiar action del formulario de forma segura
                const form = modal.querySelector('form');
                if (form) {
                    form.action = 'inventario.php';
                }

                // Cambiar la acción a 'editar_producto' de forma segura
                let actionInput = modal.querySelector('input[name="action"]');
                if (actionInput) {
                    actionInput.value = 'editar_producto';
                } else if (form) {
                    const newInput = document.createElement('input');
                    newInput.type = 'hidden';
                    newInput.name = 'action';
                    newInput.value = 'editar_producto';
                    form.appendChild(newInput);
                }

                openModal('editProductModal');
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('Error al cargar los datos del producto');
            });
    }

    function eliminarProducto(id, nombre) {
        showConfirmationModal(
            'Desactivar Producto',
            `¿Estás seguro de que quieres desactivar el producto "${nombre}"? Ya no estará disponible para nuevas transacciones.`,
            () => {
                window.location.href = `inventario.php?action=eliminar&id=${id}`;
            }
        );
    }
    </script>
</body>

</html>