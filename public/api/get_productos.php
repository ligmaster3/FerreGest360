<?php
require_once '../../config/connection.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();

    $sql = "SELECT p.id, p.codigo, p.nombre, p.precio_compra, p.impuesto 
            FROM productos p 
            WHERE p.activo = 1 
            ORDER BY p.nombre";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar productos: ' . $e->getMessage()
    ]);
}
