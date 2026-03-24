<?php
require_once __DIR__ . '/../includes/logger.php';
$env = parse_ini_file(__DIR__ . '/../.env');
$host     = 'localhost';
$db       = 'simbio';
$user     = $env['db_user'];
$password = $env['db_password'];
$charset  = 'utf8mb4';

// DSN para la conexión
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // Conexión PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Conectado a la base de datos...\n";

    // Crear/alterar estructura necesaria para funcionalidades nuevas
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
    $sqlUserTags = "CREATE TABLE IF NOT EXISTS user_tags (
        user_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (user_id, tag_id),
        FOREIGN KEY (user_id) REFERENCES user(user_id),
        FOREIGN KEY (tag_id) REFERENCES tag(tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sqlUserTags);
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

    $sql = <<<SQL
    SET FOREIGN_KEY_CHECKS=0;
    TRUNCATE TABLE user;
    TRUNCATE TABLE project_tags;
    TRUNCATE TABLE project;
    SET FOREIGN_KEY_CHECKS=1;
    INSERT INTO user (email, password_hash, name, surnames, city, phone_number, entity, type, image_path, is_active, validation_token, validation_expires) VALUES
    ('alejandro.garcia@unican.es', '{$env['seeder_password']}', 'Alejandro', 'García Ruiz', 'Santander', '+34 942 201 000', 'CIFP César Manrique', 'Centre', 'imageproject1.jpeg', 1, NULL, NULL),
    ('marta.lopez@uab.cat', '{$env['seeder_password']}', 'Marta', 'López Sala', 'Barcelona', '+34 935 811 000', 'IES La Mercè', 'Centre', NULL, 1, NULL, NULL),
    ('jorge.ramirez@itesm.mx', '{$env['seeder_password']}', 'Jorge', 'Ramírez Ortiz', 'Monterrey', '+52 81 8358 2000', 'CIFP Juan de Herrera', 'Centre', NULL, 1, NULL, NULL),
    ('elena.vazquez@usal.es', '{$env['seeder_password']}', 'Elena', 'Vázquez Mota', 'Salamanca', '+34 923 294 400', 'IES Mare de Déu de la Mercè', 'Centre', NULL, 1, NULL, NULL),
    ('diego.torres@uchile.cl', '{$env['seeder_password']}', 'Diego', 'Torres Silva', 'Santiago', '+56 2 2978 2000', 'IES Esteve Terradas I Illa', 'Centre', 'imageproject3.jpg', 1, NULL, NULL),
    ('sofia.mendez@upv.es', '{$env['seeder_password']}', 'Sofía', 'Méndez Castro', 'Valencia', '+34 963 877 000', 'CIFP Escuela de Hostelería', 'Centre', 'imageproject5.jpg', 1, NULL, NULL),
    ('raul.jimenez@uniandes.edu.co', '{$env['seeder_password']}', 'Raúl', 'Jiménez Peña', 'Bogotá', '+57 1 339 4949', 'Universidad de los Andes', 'Centre', NULL, 1, NULL, NULL),
    ('lucia.ferrer@uam.es', '{$env['seeder_password']}', 'Lucía', 'Ferrer Vidal', 'Madrid', '+34 914 975 000', 'Universidad Autónoma de Madrid', 'Centre', NULL, 1, NULL, NULL),
    ('pablo.duarte@uba.ar', '{$env['seeder_password']}', 'Pablo', 'Duarte Sosa', 'Buenos Aires', '+54 11 4508 3500', 'Universidad de Buenos Aires', 'Centre', NULL, 1, NULL, NULL),
    ('isabel.roca@upc.edu', '{$env['seeder_password']}', 'Isabel', 'Roca Mora', 'Barcelona', '+34 934 000 000', 'Universidad Politécnica de Cataluña', 'Centre', 'imageproject6.jpg', 1, NULL, NULL),
    ('carlos.sanz@ucm.es', '{$env['seeder_password']}', 'Carlos', 'Sanz Guerrero', 'Madrid', '+34 913 941 000', 'Universidad Complutense de Madrid', 'Centre', NULL, 1, NULL, NULL),
    ('ana.belen@us.es', '{$env['seeder_password']}', 'Ana Belén', 'Heredia Pozos', 'Sevilla', '+34 954 551 000', 'Universidad de Sevilla', 'Centre', NULL, 1, NULL, NULL),
    ('javier.solis@pucp.edu.pe', '{$env['seeder_password']}', 'Javier', 'Solís Vargas', 'Lima', '+51 1 626 2000', 'Pontificia Universidad Católica del Perú', 'Centre', NULL, 1, NULL, NULL),
    ('beatriz.luna@unizar.es', '{$env['seeder_password']}', 'Beatriz', 'Luna Crespo', 'Zaragoza', '+34 976 761 000', 'Universidad de Zaragoza', 'Centre', NULL, 1, NULL, NULL),
    ('fernando.rios@ehu.eus', '{$env['seeder_password']}', 'Fernando', 'Ríos Ibarretxe', 'Bilbao', '+34 946 012 000', 'Universidad del País Vasco', 'Centre', NULL, 1, NULL, NULL),
    ('clara.poveda@uma.es', '{$env['seeder_password']}', 'Clara', 'Poveda Marín', 'Málaga', '+34 952 131 000', 'Universidad de Málaga', 'Centre', NULL, 1, NULL, NULL),
    ('hugo.paredes@unam.mx', '{$env['seeder_password']}', 'Hugo', 'Paredes Meza', 'CDMX', '+52 55 5622 1332', 'Universidad Nacional Autónoma de México', 'Centre', NULL, 1, NULL, NULL),
    ('valeria.gil@unlp.edu.ar', '{$env['seeder_password']}', 'Valeria', 'Gil Ortega', 'La Plata', '+54 221 423 6701', 'Universidad Nacional de La Plata', 'Centre', NULL, 1, NULL, NULL),
    ('sergio.nieto@uclm.es', '{$env['seeder_password']}', 'Sergio', 'Nieto Gallego', 'Ciudad Real', '+34 926 295 300', 'Universidad de Castilla-La Mancha', 'Centre', NULL, 1, NULL, NULL),
    ('adriana.vega@ucl.ac.uk', '{$env['seeder_password']}', 'Adriana', 'Vega Campos', 'Londres', '+44 20 7679 2000', 'University College London', 'Centre', NULL, 1, NULL, NULL),
    /*empresa*/
    ('m.gonzalez@inditex.com', '{$env['seeder_password']}', 'Marcos', 'González Pardo', 'Arteixo', '+34 981 185 400', 'Inditex', 'Empresa', 'imageproject2.jpg', 1, NULL, NULL),
    ('laura.rivas@telefonica.com', '{$env['seeder_password']}', 'Laura', 'Rivas Castro', 'Madrid', '+34 914 823 800', 'Telefónica', 'Empresa', NULL, 1, NULL, NULL),
    ('r.moreno@santander.com', '{$env['seeder_password']}', 'Roberto', 'Moreno Laza', 'Boadilla del Monte', '+34 912 572 020', 'Banco Santander', 'Empresa', NULL, 1, NULL, NULL),
    ('carmen.suarez@repsol.com', '{$env['seeder_password']}', 'Carmen', 'Suárez Fito', 'Madrid', '+34 917 538 000', 'Repsol', 'Empresa', NULL, 1, NULL, NULL),
    ('oscar.leon@bbva.com', '{$env['seeder_password']}', 'Óscar', 'León Domínguez', 'Bilbao', '+34 913 746 000', 'BBVA', 'Empresa', NULL, 1, NULL, NULL),
    ('patricia.oro@mercadona.es', '{$env['seeder_password']}', 'Patricia', 'Oro Blanco', 'Valencia', '+34 800 500 220', 'Mercadona', 'Empresa', NULL, 1, NULL, NULL),
    ('ignacio.bravo@iberdrola.es', '{$env['seeder_password']}', 'Ignacio', 'Bravo Santos', 'Bilbao', '+34 944 151 411', 'Iberdrola', 'Empresa', NULL, 1, NULL, NULL),
    ('sandra.milla@acciona.com', '{$env['seeder_password']}', 'Sandra', 'Milla Vicens', 'Alcobendas', '+34 916 632 850', 'Acciona', 'Empresa', NULL, 1, NULL, NULL),
    ('f.blanco@ferrovial.com', '{$env['seeder_password']}', 'Francisco', 'Blanco Urquijo', 'Madrid', '+34 915 862 500', 'Ferrovial', 'Empresa', NULL, 1, NULL, NULL),
    ('julia.diez@caixabank.com', '{$env['seeder_password']}', 'Julia', 'Diez Reverte', 'Valencia', '+34 934 046 000', 'CaixaBank', 'Empresa', NULL, 1, NULL, NULL),
    ('alberto.noya@globant.com', '{$env['seeder_password']}', 'Alberto', 'Noya Ruiz', 'Buenos Aires', '+54 11 4109 1700', 'Globant', 'Empresa', NULL, 1, NULL, NULL),
    ('monica.luz@bimbo.com', '{$env['seeder_password']}', 'Mónica', 'Luz Valiente', 'CDMX', '+52 55 5268 6600', 'Grupo Bimbo', 'Empresa', 'imageproject4.jpg', 1, NULL, NULL),
    ('esteban.perez@mercadolibre.com', '{$env['seeder_password']}', 'Esteban', 'Pérez Galán', 'Buenos Aires', '+54 11 4640 8000', 'Mercado Libre', 'Empresa', NULL, 1, NULL, NULL),
    ('rosa.maria@petrobras.com', '{$env['seeder_password']}', 'Rosa María', 'Almeida Santos', 'Río de Janeiro', '+55 21 3224 4477', 'Petrobras', 'Empresa', NULL, 1, NULL, NULL),
    ('victor.gomez@cemex.com', '{$env['seeder_password']}', 'Víctor', 'Gómez Herrera', 'Monterrey', '+52 81 8328 3000', 'Cemex', 'Empresa', NULL, 1, NULL, NULL),
    ('antonio.vera@grifols.com', '{$env['seeder_password']}', 'Antonio', 'Vera Ramos', 'Sant Cugat', '+34 935 710 500', 'Grifols', 'Empresa', NULL, 1, NULL, NULL),
    ('pilar.ortiz@seat.es', '{$env['seeder_password']}', 'Pilar', 'Ortiz Mesas', 'Martorell', '+34 937 085 000', 'SEAT' , 'Empresa', NULL, 1, NULL, NULL),
    ('manuel.cid@naturgy.com' , '{$env['seeder_password']}', 'Manuel', 'Cid Varela', 'Madrid', '+34 900 100 251', 'Naturgy', 'Empresa', NULL, 1, NULL, NULL),
    ('daniela.mar@latam.com' , '{$env['seeder_password']}', 'Daniela', 'Mar Adentro', 'Santiago', '+56 2 2677 4000', 'LATAM Airlines', 'Empresa' , NULL, 1, NULL, NULL),
    ('josep.orpi@vueling.com' , '{$env['seeder_password']}', 'Josep', 'Orpi', 'Mesas', '+34 741 103 152', 'Vueling', 'Empresa' , NULL, 1, NULL, NULL);

    /*insertar proyectos*/
    INSERT INTO project (project_id, user_id, title, description, image_path, video_path) VALUES
    (1, 1, 'App Gestió de Residus Urbans', 'Aplicació mòbil per a l''optimització de rutes de recollida de residus utilitzant sensors a contenidors.', 'imageproject1.jpeg', 'videoproject1.mp4'),
    (2, 21, 'Eco-Packaging Textil', 'Investigació de nous materials biodegradables a partir de polpa de paper per a l''embalatge logístic.', 'imageproject2.jpg', 'videoproject2.mp4'),
    (3, 4, 'Restauració Seat 600 Elèctric', 'Conversió d''un vehicle clàssic a motorització 100% elèctrica com a projecte de fi de grau.', 'imageproject3.jpg', 'videoproject3.mp4'),
    (4, 32, 'Fleca Saludable i Celíaca', 'Desenvolupament d''una línia de productes de forn industrial sense al·lèrgens amb traçabilitat total.', 'imageproject4.jpg', 'videoproject4.mp4'),
    (5, 6, 'Menú Gastronómico Km0', 'Disseny d''una carta estacional basada exclusivament en productes de l''horta local i tècniques d''avantguarda.', 'imageproject5.jpg', 'videoproject5.mp4'),
    (6, 10, 'Rehabilitació Energètica BIM', 'Projecte de modelatge 3D per millorar l''aïllament tèrmic d''un edifici públic dels anys 70.', 'imageproject6.jpg', 'videoproject6.mp4');

    /*insertar las etiquetas a los proyectos*/
    INSERT INTO project_tags (project_id, tag_id) VALUES
    (1, 91),
    (1, 92),
    (2, 126),
    (3, 155),
    (4, 146),
    (5, 81),
    (6, 67);

    /*insertar admin*/
    INSERT INTO admin_user (email, password_hash, name, surnames, city, phone_number, entity, image_path)
    VALUES ('admin@simbio.cat', '{$env['seeder_password']}', 'Profe', 'Principal', 'Barcelona', '+34 600 000 000', 'SIMBIO', NULL);
SQL;

        // Ejecutamos el SQL
        $pdo->exec($sql);

        echo "Base de dades executat amb exit!\n";

    } catch (PDOException $e) {
        // missatges d'error
        echo "ERROR DE BASE DE DADES: " . $e->getMessage() . "\n";
    }
    if (!is_dir("../uploads")) {
        mkdir("../uploads");
    }
    $ficheros = [
        "imageproject1.jpeg",
        "imageproject2.jpg",
        "imageproject3.jpg",
        "imageproject4.jpg",
        "imageproject5.jpg",
        "imageproject6.jpg",
        "videoproject1.mp4",
        "videoproject2.mp4",
        "videoproject3.mp4",
        "videoproject4.mp4",
        "videoproject5.mp4",
        "videoproject6.mp4",
    ];
    foreach ($ficheros as $fichero) {
        copy('media/'.$fichero, '../uploads/'.$fichero);
    }
?>
