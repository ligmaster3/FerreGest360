<?php
require_once '../../config/connection.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';

if ($q === '') {
    echo json_encode(['success' => false, 'clientes' => []]);
    exit;
}

try {
    $busqueda = '%' . $q . '%';
    $conn = getDBConnection();

    $sql = "SELECT id, nombre, cedula_ruc, telefono, email, direccion 
            FROM clientes 
            WHERE (nombre LIKE ? OR cedula_ruc LIKE ?) 
              AND empresa_id = 1 AND activo = 1 
            ORDER BY nombre 
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$busqueda, $busqueda]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'clientes' => $clientes]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
