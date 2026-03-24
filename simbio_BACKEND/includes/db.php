<?php
/* db.php actualizado */
require_once __DIR__ . '/logger.php';

// Importante: Asegúrate de que la ruta al .env es correcta. 
// Si moviste carpetas, puede que necesites ajustar este path.
$envPath = __DIR__ . '/../../.env'; // Ajustado según tu nueva estructura
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
    // log_info("Conexión a BD establecida exitosamente"); 
} catch (PDOException $e) {
    // Si falla la BD, enviamos JSON y headers para que React sepa qué pasa
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Content-Type: application/json");
    
    echo json_encode([
        "success" => false,
        "message" => "Error de base de dades: " . $e->getMessage()
    ]);
    exit; // Usamos exit en lugar de die para mantener el formato
}