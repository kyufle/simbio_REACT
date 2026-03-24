-- 1. ASEGURARNOS DE ESTAR EN LA BASE DE DATOS CORRECTA
-- Si no existe, la crea. Si existe, la selecciona.
CREATE DATABASE IF NOT EXISTS `simbio` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `simbio`;

-- 2. DESACTIVAR RESTRICCIONES TEMPORALMENTE
-- Esto permite borrar tablas que están enlazadas entre sí sin errores.
SET FOREIGN_KEY_CHECKS = 0;

-- 3. LIMPIEZA (Solo dentro de 'simbio')
DROP TABLE IF EXISTS `message`;
DROP TABLE IF EXISTS `project_like`;
DROP TABLE IF EXISTS `project_tags`;
DROP TABLE IF EXISTS `user_tags`;
DROP TABLE IF EXISTS `project`;
DROP TABLE IF EXISTS `tag`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `admin_user`;

-- 4. CREACIÓN DE TABLAS CON ESTRUCTURA ACTUALIZADA
CREATE TABLE `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(120) NOT NULL,
  `surnames` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `type` enum('Empresa','Centre') NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `validation_token` varchar(255) DEFAULT NULL,
  `validation_expires` timestamp NULL DEFAULT NULL,
  `login_code` varchar(6) DEFAULT NULL,
  `login_code_expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE `tag` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`tag_id`),
  FOREIGN KEY (`parent_id`) REFERENCES `tag` (`tag_id`)
) ENGINE=InnoDB;

CREATE TABLE `project` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `user_tags` (
  `user_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`user_id`, `tag_id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. REACTIVAR RESTRICCIONES
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `user` (
  `email`, 
  `password_hash`, 
  `name`, 
  `surnames`, 
  `city`, 
  `phone_number`, 
  `entity`, 
  `type`, 
  `is_active`
) VALUES (
  'test@gmail.com', 
  '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 
  'Usuario', 
  'de Test', 
  'Barcelona', 
  '123456789', 
  'Entidad de Prueba', 
  'Centre', 
  1
);