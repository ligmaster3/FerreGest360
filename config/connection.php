<?php
define('BASE_URL', '/FerreGest360/');

$host = "localhost";
$usuario = "root";
$contrasena = "";
$basededatos = "ferregest360";

function getDBConnection()
{
    global $host, $usuario, $contrasena, $basededatos;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$basededatos;charset=utf8", $usuario, $contrasena);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

function ejecutarConsulta($sql, $params = [])
{
    $conn = getDBConnection();
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        die("Error en la consulta: " . $e->getMessage());
    }
}

// Opcional: establecer codificación UTF-8
// La conexión ya se establece con charset=utf8
