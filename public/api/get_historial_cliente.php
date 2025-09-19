<?php
require_once '../../config/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de cliente requerido'
    ]);
    exit;
}

try {
    $conn = getDBConnection();
    $cliente_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if (!$cliente_id) {
        throw new Exception('ID de cliente inválido');
    }

    $sql = "SELECT 
                fv.id,
                fv.numero_factura,
                fv.fecha_factura as fecha,
                fv.total,
                fv.estado,
                fv.tipo_pago
            FROM facturas_venta fv
            WHERE fv.cliente_id = ? 
            AND fv.empresa_id = 1
            ORDER BY fv.fecha_factura DESC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$cliente_id]);

    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'historial' => $historial
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
