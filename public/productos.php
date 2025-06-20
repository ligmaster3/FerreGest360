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
                <?php

                // Función para sanitizar entradas
                function sanitizeInput($data)
                {
                    return htmlspecialchars(strip_tags(trim($data)));
                }

                // Inicializar variables
                $productos = [];
                $categorias = [];
                $marcas = [];
                $success_message = '';
                $error_message = '';

                try {
                    $conn = getDBConnection();
                    // Obtener categorías
                    $stmt = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
                    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Obtener marcas
                    $stmt = $conn->query("SELECT id, nombre FROM marcas ORDER BY nombre");
                    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Consulta base de productos
                    $sql = "SELECT 
                p.id,
                p.codigo,
                p.codigo_barras,
                p.nombre,
                p.descripcion,
                p.categoria_id,
                p.marca_id,
                p.precio_compra,
                p.precio_venta,
                p.stock_minimo,
                c.nombre as categoria,
                m.nombre as marca,
                COALESCE(i.stock_actual, 0) as stock
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            LEFT JOIN marcas m ON p.marca_id = m.id 
            LEFT JOIN inventario i ON p.id = i.producto_id
            WHERE 1=1";
                    $params = [];

                    // Filtros
                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $search = sanitizeInput($_GET['search']);
                        $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ? OR p.codigo_barras LIKE ?)";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                    }

                    if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
                        $sql .= " AND p.categoria_id = ?";
                        $params[] = $_GET['categoria'];
                    }

                    if (isset($_GET['marca']) && !empty($_GET['marca'])) {
                        $sql .= " AND p.marca_id = ?";
                        $params[] = $_GET['marca'];
                    }

                    $sql .= " ORDER BY p.nombre";

                    // Ejecutar consulta
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $error_message = "Error al cargar los datos: " . $e->getMessage();
                }

                // Acciones de agregar/eliminar
                if (isset($_POST['action']) && $_POST['action'] === 'agregar') {
                    // Lógica delegada a guardar_producto.php
                } else if (isset($_GET['action']) && $_GET['action'] === 'eliminar') {
                    $id_eliminar = filter_var($_GET['id'], FILTER_VALIDATE_INT);
                    if ($id_eliminar) {
                        try {
                            $conn->beginTransaction();

                            $stmt = $conn->prepare("DELETE FROM movimientos_inventario WHERE producto_id = ?");
                            $stmt->execute([$id_eliminar]);

                            $stmt = $conn->prepare("DELETE FROM inventario WHERE producto_id = ?");
                            $stmt->execute([$id_eliminar]);

                            $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
                            if ($stmt->execute([$id_eliminar])) {
                                $_SESSION['success_message'] = "Producto eliminado correctamente.";
                            } else {
                                $_SESSION['error_message'] = "Error al eliminar el producto.";
                            }
                            $conn->commit();
                        } catch (PDOException $e) {
                            $conn->rollBack();
                            $_SESSION['error_message'] = "Error al eliminar: " . $e->getMessage();
                        }
                    } else {
                        $_SESSION['error_message'] = "ID de producto inválido para eliminar.";
                    }
                    header('Location: dashboard.php?page=productos');
                    exit();
                }
                ?> <div class="content-area">
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

                        <?php if (isset($_GET['action']) && $_GET['action'] === 'nuevo'): ?>
                            <!-- Modal para agregar nuevo producto -->
                            <div class="modal" style="display: block;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2>
                                            <i class="fas fa-plus"></i> Agregar Nuevo Producto
                                        </h2>
                                        <button class="modal-close" onclick="loadContent('productos.php')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <form class="modal-form" method="POST" action="guardar_producto.php">
                                        <input type="hidden" name="action" value="agregar">

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="productCode">Código del Producto</label>
                                                <input type="text" id="productCode" name="codigo" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="productBarcode">Código de Barras</label>
                                                <input type="text" id="productBarcode" name="codigo_barras">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="productName">Nombre del Producto</label>
                                            <input type="text" id="productName" name="nombre" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="productDescription">Descripción</label>
                                            <textarea id="productDescription" name="descripcion" rows="3"></textarea>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="productCategory">Categoría</label>
                                                <select id="productCategory" name="categoria_id">
                                                    <option value="">Seleccionar categoría</option>
                                                    <?php foreach ($categorias as $categoria): ?>
                                                        <option value="<?php echo $categoria['id']; ?>">
                                                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="productBrand">Marca</label>
                                                <select id="productBrand" name="marca_id">
                                                    <option value="">Seleccionar marca</option>
                                                    <?php foreach ($marcas as $marca): ?>
                                                        <option value="<?php echo $marca['id']; ?>">
                                                            <?php echo htmlspecialchars($marca['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="purchasePrice">Precio de Compra</label>
                                                <input type="number" id="purchasePrice" name="precio_compra" step="0.01" min="0">
                                            </div>
                                            <div class="form-group">
                                                <label for="salePrice">Precio de Venta</label>
                                                <input type="number" id="salePrice" name="precio_venta" step="0.01" min="0" required>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="minStock">Stock Mínimo</label>
                                                <input type="number" id="minStock" name="stock_minimo" min="0" value="5">
                                            </div>
                                            <div class="form-group">
                                                <label for="initialStock">Stock Inicial</label>
                                                <input type="number" id="initialStock" name="stock_inicial" min="0" value="0">
                                            </div>
                                        </div>

                                        <div class="modal-actions">
                                            <button type="button" class="btn-secondary" onclick="loadContent('productos.php')">
                                                Cancelar
                                            </button>
                                            <button type="submit" class="btn-primary">
                                                <i class="fas fa-save"></i>
                                                Guardar Producto
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Filtros y tabla de productos -->
                            <div class="filters-bar">
                                <form method="GET" action="#" class="filter-form" onsubmit="event.preventDefault(); loadContent('productos.php?' + new URLSearchParams(new FormData(this)).toString())">
                                    <div class="filter-group">
                                        <input
                                            type="text"
                                            name="search"
                                            placeholder="Buscar productos..."
                                            class="filter-input"
                                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        <select name="categoria" class="filter-select">
                                            <option value="">Todas las categorías</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option
                                                    value="<?php echo $categoria['id']; ?>"
                                                    <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $categoria['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="marca" class="filter-select">
                                            <option value="">Todas las marcas</option>
                                            <?php foreach ($marcas as $marca): ?>
                                                <option
                                                    value="<?php echo $marca['id']; ?>"
                                                    <?php echo (isset($_GET['marca']) && $_GET['marca'] == $marca['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($marca['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="filter-actions">
                                            <button type="submit" class="btn-primary filter-button">Filtrar</button>
                                            <button type="button" class="btn-secondary filter-button" onclick="loadContent('productos.php')">Limpiar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

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
                                        <?php foreach ($productos as $producto): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                                                <td>
                                                    <div class="product-info">
                                                        <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                                        <span class="product-desc"><?php echo htmlspecialchars($producto['descripcion']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($producto['categoria'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($producto['marca'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if ($producto['stock'] <= $producto['stock_minimo']): ?>
                                                        <span class="stock-badge low"><?php echo $producto['stock']; ?></span>
                                                    <?php else: ?>
                                                        <span class="stock-badge normal"><?php echo $producto['stock']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>$<?php echo number_format($producto['precio_venta'], 2); ?></td>
                                                <td>
                                                    <span class="status-badge active">Activo</span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a
                                                            href="#"
                                                            class="btn-edit"
                                                            title="Editar"
                                                            onclick="loadContent('productos.php?action=editar&id=<?php echo $producto['id']; ?>')">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </a>
                                                        <button class="btn-delete" onclick="showConfirmationModal('Eliminar Producto', '¿Estás seguro de que quieres eliminar \'<?php echo htmlspecialchars(addslashes($producto['nombre'])); ?>\'? Esta acción no se puede deshacer.', () => { loadContent('productos.php?action=eliminar&id=<?php echo $producto['id']; ?>') })">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($productos)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No se encontraron productos</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
        </main>
    </div>
    <?php include 'partials/modals.php'; ?>
</body>

</html>