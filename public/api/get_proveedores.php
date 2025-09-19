<?php
require_once '../../config/connection.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();

    $sql = "SELECT id, codigo, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'proveedores' => $proveedores
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar proveedores: ' . $e->getMessage()
    ]);
}
