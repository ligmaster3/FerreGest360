<?php
require_once '../../config/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de producto requerido'
    ]);
    exit;
}

try {
    $conn = getDBConnection();
    $producto_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if (!$producto_id) {
        throw new Exception('ID de producto inválido');
    }

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
                i.stock_actual,
                p.activo
            FROM productos p
            LEFT JOIN inventario i ON p.id = i.producto_id
            WHERE p.id = ? AND p.empresa_id = 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$producto_id]);

    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        echo json_encode([
            'success' => true,
            'data' => $producto
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Producto no encontrado'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar el producto: ' . $e->getMessage()
    ]);
}



if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// --- Buscar producto por ID ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $producto_id = (int)$_GET['id'];
    $empresa_id = 1; // Ajusta según tu sistema

    try {
        $conn = getDBConnection();

        $sql = "SELECT 
                    id, codigo, nombre, descripcion, precio, stock, categoria_id, activo, imagen
                FROM productos
                WHERE id = ? AND empresa_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$producto_id, $empresa_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            exit;
        }

        echo json_encode(['success' => true, 'producto' => $producto]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    }
    exit;
}

// --- Buscar productos por nombre o código ---
if (isset($_GET['q'])) {
    $busqueda = '%' . $_GET['q'] . '%';

    try {
        $conn = getDBConnection();

        $sql = "SELECT id, codigo, nombre, precio, stock, imagen 
                FROM productos 
                WHERE (nombre LIKE ? OR codigo LIKE ?) 
                  AND empresa_id = 1 AND activo = 1
                ORDER BY nombre 
                LIMIT 10";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$busqueda, $busqueda]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'productos' => $productos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Parámetro no válido']);