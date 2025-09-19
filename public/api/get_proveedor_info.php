<?php
require_once '../../config/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de proveedor requerido'
    ]);
    exit;
}

try {
    $conn = getDBConnection();
    $proveedor_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if (!$proveedor_id) {
        throw new Exception('ID de proveedor inválido');
    }

    $sql = "SELECT id, codigo, nombre, telefono_principal, email, dias_credito, descuento_porcentaje 
            FROM proveedores 
            WHERE id = ? AND activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$proveedor_id]);

    $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($proveedor) {
        echo json_encode([
            'success' => true,
            'proveedor' => $proveedor
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Proveedor no encontrado'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
