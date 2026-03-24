<?php
// register_db_script.php
// Script para preparar la tabla `user` para registro con email

$host = 'localhost';
$db   = 'simbio';
$user = 'admin';  // <- Cambia esto
$pass = 'Ko8^Xg1Xr';  // <- Cambia esto
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conexión a la base de datos OK.\n";

    // Añadir columnas una a una, ignorando el error si ya existen
    $columns = [
        ["is_active", "TINYINT(1) NOT NULL DEFAULT 0 AFTER `type`"],
        ["validation_token", "VARCHAR(64) DEFAULT NULL AFTER `is_active`"],
        ["validation_expires", "DATETIME DEFAULT NULL AFTER `validation_token`"],
        ["login_code", "VARCHAR(6) DEFAULT NULL AFTER `validation_expires`"],
        ["login_code_expires", "DATETIME DEFAULT NULL AFTER `login_code`"]
    ];
    $added = [];
    foreach ($columns as [$col, $def]) {
        try {
            $sql = "ALTER TABLE `user` ADD COLUMN `$col` $def;";
            $pdo->exec($sql);
            $added[] = $col;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                // La columna ya existe, ignorar
            } else {
                throw $e;
            }
        }
    }
    echo "Columnas añadidas correctamente (si no existían): ".implode(", ", $added)."\n";

} catch (\PDOException $e) {
    echo "ERROR: No se pudo modificar la tabla `user`.\n";
    echo "Detalles: " . $e->getMessage() . "\n";
    exit(1);
}
