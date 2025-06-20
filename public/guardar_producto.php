<?php
require_once '../config/connection.php';

try {
    // Sanitizar y validar los datos de entrada
    $codigo = sanitizeInput($_POST['codigo']);
    $barra = sanitizeInput($_POST['codigo_barras']);
    $nombre = sanitizeInput($_POST['nombre']);
    $descripcion = sanitizeInput($_POST['descripcion']);
    $categoria = filter_var($_POST['categoria_id'], FILTER_VALIDATE_INT);
    $marca = filter_var($_POST['marca_id'], FILTER_VALIDATE_INT);
    $precioCompra = filter_var($_POST['precio_compra'], FILTER_VALIDATE_FLOAT);
    $precioVenta = filter_var($_POST['precio_venta'], FILTER_VALIDATE_FLOAT);
    $stockMin = filter_var($_POST['stock_minimo'], FILTER_VALIDATE_INT);
    $stockIni = filter_var($_POST['stock_inicial'], FILTER_VALIDATE_INT);

    // Iniciar transacción
    $conn->beginTransaction();

    // Insertar el producto
    $sql = "INSERT INTO productos (codigo, codigo_barras, nombre, descripcion, categoria_id, marca_id, precio_compra, precio_venta, stock_minimo) 
            VALUES (:codigo, :barra, :nombre, :descripcion, :categoria, :marca, :precioCompra, :precioVenta, :stockMin)";

    $stmt = $conn->prepare($sql);

    // Vincular los parámetros
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':barra', $barra);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':marca', $marca);
    $stmt->bindParam(':precioCompra', $precioCompra);
    $stmt->bindParam(':precioVenta', $precioVenta);
    $stmt->bindParam(':stockMin', $stockMin);

    // Ejecutar la consulta
    $stmt->execute();
    $producto_id = $conn->lastInsertId();

    // Insertar el registro de inventario inicial
    $sql = "INSERT INTO inventario (producto_id, stock_actual, costo_promedio) 
            VALUES (:producto_id, :stock_actual, :costo_promedio)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':producto_id', $producto_id);
    $stmt->bindParam(':stock_actual', $stockIni);
    $stmt->bindParam(':costo_promedio', $precioCompra);
    $stmt->execute();

    // Registrar el movimiento de inventario inicial
    $tipo_movimiento = 7; // INICIAL
    $sql = "INSERT INTO movimientos_inventario (empresa_id, producto_id, tipo_movimiento_id, cantidad, costo_unitario, usuario_id) 
            VALUES (1, :producto_id, :tipo_movimiento, :cantidad, :costo_unitario, 1)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':producto_id', $producto_id);
    $stmt->bindParam(':tipo_movimiento', $tipo_movimiento);
    $stmt->bindParam(':cantidad', $stockIni);
    $stmt->bindParam(':costo_unitario', $precioCompra);
    $stmt->execute();

    // Confirmar la transacción
    $conn->commit();
    $_SESSION['success_message'] = "Producto guardado correctamente.";
} catch (PDOException $e) {
    // Revertir la transacción en caso de error
    $conn->rollBack();
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
}

// Redirigir de vuelta a la página de productos
header('Location: dashboard.php?page=productos');
exit();
