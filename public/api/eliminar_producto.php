<?php
require_once '../../config/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
    exit;
}

try {
    $conn = getDBConnection();
    $producto_id = filter_var($input['id'], FILTER_VALIDATE_INT);

    if (!$producto_id) {
        throw new Exception('ID de producto inválido');
    }

    // Verificar si el producto tiene ventas asociadas
    $sql_check = "SELECT COUNT(*) as total FROM facturas_venta_detalle WHERE producto_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$producto_id]);
    $ventas = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($ventas['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar el producto porque tiene ventas asociadas. Se recomienda desactivarlo en su lugar.'
        ]);
        exit;
    }

    // Desactivar el producto en lugar de eliminarlo
    $sql = "UPDATE productos SET activo = 0 WHERE id = ? AND empresa_id = 1";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$producto_id]);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto desactivado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo desactivar el producto'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al desactivar el producto: ' . $e->getMessage()
    ]);
}
