<?php
require_once '../../config/connection.php';
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$proveedor_id = $_GET['id'] ?? '';
$empresa_id = $_SESSION['empresa_id'] ?? 1;

try {
    if (empty($proveedor_id)) {
        throw new Exception('ID de proveedor requerido');
    }
    
    $conn = getDBConnection();
    
    // Obtener información completa del proveedor
    $sql = "SELECT 
                id, codigo, nombre, razon_social, tipo_proveedor, ruc,
                direccion, telefono_principal, email, nombre_contacto, 
                cargo_contacto, telefono_contacto, email_contacto,
                dias_credito, descuento_porcentaje, tiempo_entrega, 
                monto_minimo, condiciones_pago, sitio_web, 
                horario_atencion, productos_principales, observaciones, 
                activo, fecha_registro
            FROM proveedores 
            WHERE id = ? AND empresa_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$proveedor_id, $empresa_id]);
    $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proveedor) {
        throw new Exception('Proveedor no encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'proveedor' => $proveedor
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>