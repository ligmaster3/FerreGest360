<?php
require_once '../../config/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cliente requerido']);
    exit;
}

$cliente_id = (int)$_GET['id'];
$empresa_id = 1; // Asumiendo empresa_id = 1

try {
    $conn = getDBConnection();

    $sql = "SELECT 
        id, codigo, tipo_cliente, cedula_ruc, nombre, razon_social, 
        direccion, telefono, email, limite_credito, dias_credito, 
        descuento_porcentaje, activo, fecha_registro
    FROM clientes 
    WHERE id = ? AND empresa_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$cliente_id, $empresa_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente no encontrado']);
        exit;
    }

    echo json_encode(['success' => true, 'cliente' => $cliente]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}
