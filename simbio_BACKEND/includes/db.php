<?php
require_once __DIR__ . '/logger.php';

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
} else {
    // Valores por defecto si no encuentra el .env en local
    $env = ['db_user' => 'root', 'db_password' => ''];
}

$servername = "localhost";
$username   = $env['db_user'];
$password   = $env['db_password'];
$dbname     = "simbio";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Content-Type: application/json");
    
    echo json_encode([
        "success" => false,
        "message" => "Error de base de dades: " . $e->getMessage()
    ]);
    exit;
}