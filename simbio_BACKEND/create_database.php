<?php
$env = parse_ini_file('.env');
$host     = 'localhost';
$user     = $env['db_user'];
$password = $env['db_password'];
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";

try {
    // Conexión PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Creant la base de dades...\n";

    $sql = <<<SQL
    DROP DATABASE IF EXISTS simbio;
    CREATE DATABASE IF NOT EXISTS simbio;
    USE simbio;

    CREATE TABLE user (
        user_id int AUTO_INCREMENT,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(120) NOT NULL,
        surnames VARCHAR(255) NOT NULL,
        city VARCHAR(255) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        entity VARCHAR(255) NOT NULL,
        type ENUM('Empresa', 'Centre') NOT NULL,
        image_path VARCHAR(255),
        is_active TINYINT(1) DEFAULT 0,
        validation_token VARCHAR(255) NULL,
        validation_expires DATETIME NULL,
        PRIMARY KEY (user_id)
    );

    CREATE TABLE tag (
        tag_id int AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        parent_id INT NULL, 
        PRIMARY KEY (tag_id),
        FOREIGN KEY (parent_id) REFERENCES tag(tag_id)
    );

    CREATE TABLE project (
        project_id INT AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(120) NOT NULL,
        description TEXT NOT NULL,
        image_path VARCHAR(255),
        video_path VARCHAR(255),
        PRIMARY KEY (project_id),
        FOREIGN KEY (user_id) REFERENCES user(user_id)
    );

    CREATE TABLE message (
        message_id INT AUTO_INCREMENT,
        text TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_from_id INT NOT NULL,
        user_to_id INT NOT NULL,
        PRIMARY KEY (message_id),
        FOREIGN KEY (user_from_id) REFERENCES user(user_id),
        FOREIGN KEY (user_to_id) REFERENCES user(user_id)
    );

    CREATE TABLE user_tags (
        user_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (user_id, tag_id),
        FOREIGN KEY (user_id) REFERENCES user(user_id),
        FOREIGN KEY (tag_id) REFERENCES tag(tag_id)
    );

    CREATE TABLE project_tags (
        project_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (project_id, tag_id),
        FOREIGN KEY (project_id) REFERENCES project(project_id),
        FOREIGN KEY (tag_id) REFERENCES tag(tag_id)
    );

    CREATE TABLE project_partner_tag (
        project_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (project_id, tag_id),
        FOREIGN KEY (project_id) REFERENCES project(project_id),
        FOREIGN KEY (tag_id) REFERENCES tag(tag_id)
    );

    CREATE TABLE project_like (
        user_id INT NOT NULL,
        project_id INT NOT NULL,
        liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, project_id), 
        FOREIGN KEY (user_id) REFERENCES user(user_id),
        FOREIGN KEY (project_id) REFERENCES project(project_id)
    );
    SQL;

        // Ejecutamos el SQL
        $pdo->exec($sql);

        echo "Base de dades executat amb exit!\n";

    } catch (PDOException $e) {
        // missatges d'error
        echo "ERROR DE BASE DE DADES: " . $e->getMessage() . "\n";
    }
