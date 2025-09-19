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
