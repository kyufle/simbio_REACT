<?php
/* bd ejemplo */
require_once __DIR__ . '/logger.php';

$env = parse_ini_file(__DIR__ .'/../.env');
$servername = "localhost";
$username     = $env['db_user'];
$password = $env['db_password'];
$dbname = "simbio";

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
	// Hacer que MySQL use MD5 en contraseñas si se usa directamente en SQL (por ejemplo en inserts).
	// Ejemplo: INSERT INTO users (email, password) VALUES ('a@b.com', MD5('clave'))
	// Nota: La comparación de contraseñas se hace en PHP (md5(...)), por consistencia.
	log_info("Conexión a BD establecida exitosamente");
} catch (PDOException $e) {
	log_error("Error de conexión a BD", [
		'servidor' => $servername,
		'base_datos' => $dbname,
		'error' => $e->getMessage()
	]);
	die("Error en connectar amb la base de dades.");
}

?>

