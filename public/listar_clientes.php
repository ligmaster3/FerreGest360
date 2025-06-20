<?php
include '../config/connection.php';

try {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM clientes ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>" . htmlspecialchars($row['nombre']) . "</td>
                <td>" . htmlspecialchars($row['cedula']) . "</td>
                <td>" . htmlspecialchars($row['telefono']) . "</td>
                <td>" . htmlspecialchars($row['correo']) . "</td>
                <td>" . htmlspecialchars($row['direccion']) . "</td>
            </tr>";
    }
} catch (PDOException $e) {
    echo "<tr><td colspan='5'>Error al cargar los clientes: " . $e->getMessage() . "</td></tr>";
}
?>
