    // Añadir columnas login_code y login_code_expires a user
    try {
        $pdo->exec("ALTER TABLE user ADD COLUMN login_code VARCHAR(6) DEFAULT NULL;");
        echo "Columna login_code añadida a user.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "La columna login_code ya existe en user.\n";
        } else {
            echo "Error al añadir login_code a user: " . $e->getMessage() . "\n";
        }
    }
    try {
        $pdo->exec("ALTER TABLE user ADD COLUMN login_code_expires DATETIME DEFAULT NULL;");
        echo "Columna login_code_expires añadida a user.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "La columna login_code_expires ya existe en user.\n";
        } else {
            echo "Error al añadir login_code_expires a user: " . $e->getMessage() . "\n";
        }
    }
<?php
// Seeder para actualizar la base de datos con los cambios recientes
// Añade la columna created_at a project_like y crea la tabla user_tags si no existe

$env = parse_ini_file('../.env');
$host     = 'localhost';
$db       = 'simbio';
$user     = $env['db_user'];
$password = $env['db_password'];
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Conectado a la base de datos...\n";

    // Añadir columna created_at a project_like
    try {
        $pdo->exec("ALTER TABLE project_like ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
        echo "Columna created_at añadida a project_like.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "La columna created_at ya existe en project_like.\n";
        } else {
            echo "Error al añadir created_at a project_like: " . $e->getMessage() . "\n";
        }
    }

    // Crear tabla user_tags si no existe
    $sql = "CREATE TABLE IF NOT EXISTS user_tags (
        user_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (user_id, tag_id),
        FOREIGN KEY (user_id) REFERENCES user(user_id),
        FOREIGN KEY (tag_id) REFERENCES tag(tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Tabla user_tags creada o ya existente.\n";

    // Añadir columna sent_at a message
    try {
        $pdo->exec("ALTER TABLE message ADD COLUMN sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
        echo "Columna sent_at añadida a message.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "La columna sent_at ya existe en message.\n";
        } else {
            echo "Error al añadir sent_at a message: " . $e->getMessage() . "\n";
        }
    }

    echo "Actualización de estructura completada.\n";

} catch (PDOException $e) {
    echo "ERROR DE BASE DE DADES: " . $e->getMessage() . "\n";
}
?>
