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
            $empresa_id = $_SESSION['empresa_id'] ?? 1;
            
            if (empty($producto_id) || empty($proveedor_id)) {
                throw new Exception('Producto y proveedor son requeridos');
            }
            
            // Validar que el producto y proveedor pertenezcan a la empresa
            $validateProductSql = "SELECT id FROM productos WHERE id = ? AND empresa_id = ? AND activo = 1";
            $validateProductStmt = $conn->prepare($validateProductSql);
            $validateProductStmt->execute([$producto_id, $empresa_id]);
            
            if (!$validateProductStmt->fetch()) {
                throw new Exception('Producto no válido o inactivo');
            }
            
            $validateProviderSql = "SELECT id FROM proveedores WHERE id = ? AND empresa_id = ? AND activo = 1";
            $validateProviderStmt = $conn->prepare($validateProviderSql);
            $validateProviderStmt->execute([$proveedor_id, $empresa_id]);
            
            if (!$validateProviderStmt->fetch()) {
                throw new Exception('Proveedor no válido o inactivo');
            }
            
            // Verificar si ya existe la relación
            $checkSql = "SELECT id FROM productos_proveedores WHERE producto_id = ? AND proveedor_id = ? AND empresa_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$producto_id, $proveedor_id, $empresa_id]);
            
            if ($checkStmt->fetch()) {
                throw new Exception('Esta relación ya existe');
            }
            
            // Crear la relación
            $insertSql = "INSERT INTO productos_proveedores (empresa_id, producto_id, proveedor_id, activo, fecha_registro) VALUES (?, ?, ?, 1, NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->execute([$empresa_id, $producto_id, $proveedor_id]);
            
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
            
        case 'delete_relation':
            // Eliminar relación producto-proveedor
            $relacion_id = $_POST['relacion_id'] ?? '';
            $empresa_id = $_SESSION['empresa_id'] ?? 1;
            
            if (empty($relacion_id)) {
                throw new Exception('ID de relación requerido');
            }
            
            // Verificar que la relación pertenece a la empresa
            $checkSql = "SELECT id FROM productos_proveedores WHERE id = ? AND empresa_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$relacion_id, $empresa_id]);
            
            if (!$checkStmt->fetch()) {
                throw new Exception('Relación no encontrada o no autorizada');
            }
            
            // Eliminar la relación
            $deleteSql = "DELETE FROM productos_proveedores WHERE id = ? AND empresa_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->execute([$relacion_id, $empresa_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Relación eliminada exitosamente'
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
