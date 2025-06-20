<?php
require_once '../config/connection.php';
session_start();

// Validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Solicitud no permitida.');
}

// Asignar empresa_id (ajustar según la lógica de sesión de tu aplicación)
$empresa_id = $_SESSION['empresa_id'] ?? 1; // Usar 1 como fallback si no hay sesión

// Recoger y sanear los datos del formulario
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$razon_social = filter_input(INPUT_POST, 'razon_social', FILTER_SANITIZE_STRING);
$ruc = filter_input(INPUT_POST, 'ruc', FILTER_SANITIZE_STRING);
$telefono_principal = filter_input(INPUT_POST, 'telefono_principal', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
$nombre_contacto = filter_input(INPUT_POST, 'nombre_contacto', FILTER_SANITIZE_STRING);
$telefono_contacto = filter_input(INPUT_POST, 'telefono_contacto', FILTER_SANITIZE_STRING);
$email_contacto = filter_input(INPUT_POST, 'email_contacto', FILTER_VALIDATE_EMAIL);


// Validaciones básicas
if (empty($nombre) || empty($ruc)) {
    // Manejar error de validación
    // Puedes devolver un mensaje de error específico
    header('Location: proveedores.php?status=error&message=El nombre y el RUC son obligatorios.');
    exit;
}

if ($email === false || $email_contacto === false) {
    // Manejar error de validación de email
    header('Location: proveedores.php?status=error&message=El formato del correo electrónico no es válido.');
    exit;
}


// Preparar la consulta SQL para evitar inyección SQL
$sql = "INSERT INTO proveedores (empresa_id, nombre, razon_social, ruc, telefono_principal, email, direccion, nombre_contacto, telefono_contacto, email_contacto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Manejar error en la preparación de la consulta
    header('Location: proveedores.php?status=error&message=' . urlencode('Error al preparar la consulta: ' . $conn->error));
    exit;
}

// Vincular parámetros
$stmt->bind_param(
    "isssssssss",
    $empresa_id,
    $nombre,
    $razon_social,
    $ruc,
    $telefono_principal,
    $email,
    $direccion,
    $nombre_contacto,
    $telefono_contacto,
    $email_contacto
);

// Ejecutar la consulta
if ($stmt->execute()) {
    // Éxito
    $stmt->close();
    $conn->close();
    // Redirigir a la página de proveedores con un mensaje de éxito
    header('Location: proveedores.php?status=success&message=Proveedor guardado exitosamente.');
    exit;
} else {
    // Error
    $error_message = $stmt->error;
    $stmt->close();
    $conn->close();
    // Redirigir con mensaje de error
    header('Location: proveedores.php?status=error&message=' . urlencode('Error al guardar el proveedor: ' . $error_message));
    exit;
}
