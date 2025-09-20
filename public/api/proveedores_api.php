<?php
require_once '../../config/connection.php';
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'link_product':
            // Vincular producto con proveedor
            $producto_id = $_POST['producto_id'] ?? '';
            $proveedor_id = $_POST['proveedor_id'] ?? '';
            
            if (empty($producto_id) || empty($proveedor_id)) {
                throw new Exception('Producto y proveedor son requeridos');
            }
            
            // Verificar si ya existe la relación
            $checkSql = "SELECT id FROM productos_proveedores WHERE producto_id = ? AND proveedor_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$producto_id, $proveedor_id]);
            
            if ($checkStmt->fetch()) {
                throw new Exception('Esta relación ya existe');
            }
            
            // Crear la relación
            $insertSql = "INSERT INTO productos_proveedores (empresa_id, producto_id, proveedor_id, activo) VALUES (1, ?, ?, 1)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->execute([$producto_id, $proveedor_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Relación creada exitosamente'
            ]);
            break;
            
        case 'get_pedidos':
            // Por ahora retornar array vacío hasta que se implemente la tabla de pedidos
            echo json_encode([
                'success' => true,
                'pedidos' => []
            ]);
            break;
            
        case 'update_order_status':
            // Por ahora retornar error hasta que se implemente la tabla de pedidos
            echo json_encode([
                'success' => false,
                'message' => 'Funcionalidad de pedidos no implementada aún'
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
