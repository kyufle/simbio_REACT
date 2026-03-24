-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: simbio
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.22.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_from_id` int NOT NULL,
  `user_to_id` int NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `user_from_id` (`user_from_id`),
  KEY `user_to_id` (`user_to_id`),
  CONSTRAINT `message_ibfk_1` FOREIGN KEY (`user_from_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `message_ibfk_2` FOREIGN KEY (`user_to_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `project_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` VALUES (1,1,'App Gestió de Residus Urbans','Aplicació mòbil per a l\'optimització de rutes de recollida de residus utilitzant sensors a contenidors.','imageproject1.jpeg','videoproject1.mp4'),(2,21,'Eco-Packaging Textil','Investigació de nous materials biodegradables a partir de polpa de paper per a l\'embalatge logístic.','imageproject2.jpg','videoproject2.mp4'),(3,4,'Restauració Seat 600 Elèctric','Conversió d\'un vehicle clàssic a motorització 100% elèctrica com a projecte de fi de grau.','imageproject3.jpg','videoproject3.mp4'),(4,32,'Fleca Saludable i Celíaca','Desenvolupament d\'una línia de productes de forn industrial sense al·lèrgens amb traçabilitat total.','imageproject4.jpg','videoproject4.mp4'),(5,6,'Menú Gastronómico Km0','Disseny d\'una carta estacional basada exclusivament en productes de l\'horta local i tècniques d\'avantguarda.','imageproject5.jpg','videoproject5.mp4'),(6,10,'Rehabilitació Energètica BIM','Projecte de modelatge 3D per millorar l\'aïllament tèrmic d\'un edifici públic dels anys 70.','imageproject6.jpg','videoproject6.mp4');
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_like`
--

DROP TABLE IF EXISTS `project_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_like` (
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `liked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`project_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `project_like_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `project_like_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_like`
--

LOCK TABLES `project_like` WRITE;
/*!40000 ALTER TABLE `project_like` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_like` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_partner_tag`
--

DROP TABLE IF EXISTS `project_partner_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_partner_tag` (
  `project_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`project_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `project_partner_tag_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  CONSTRAINT `project_partner_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_partner_tag`
--

LOCK TABLES `project_partner_tag` WRITE;
/*!40000 ALTER TABLE `project_partner_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_partner_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_tags`
--

DROP TABLE IF EXISTS `project_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_tags` (
  `project_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`project_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `project_tags_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  CONSTRAINT `project_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_tags`
--

LOCK TABLES `project_tags` WRITE;
/*!40000 ALTER TABLE `project_tags` DISABLE KEYS */;
INSERT INTO `project_tags` VALUES (6,67),(5,81),(1,91),(1,92),(2,126),(4,146),(3,155);
/*!40000 ALTER TABLE `project_tags` ENABLE KEYS */;
UNLOCK TABLES;

-- 
-- Table structure for table `user_tags`
-- (from create_database.php lines 71-77)
--

DROP TABLE IF EXISTS `user_tags`;
CREATE TABLE `user_tags` (
    user_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (user_id, tag_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (tag_id) REFERENCES tag(tag_id)
);

--
-- Table structure for table `admin_user`
--

DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `admin_user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(120) NOT NULL,
  `surnames` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`admin_user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_user`
--

LOCK TABLES `admin_user` WRITE;
/*!40000 ALTER TABLE `admin_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tag` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `tag` (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
INSERT INTO `tag` VALUES (1,'AF',NULL),(2,'AG',NULL),(3,'AR',NULL),(4,'CM',NULL),(5,'EA',NULL),(6,'EE',NULL),(7,'EO',NULL),(8,'FM',NULL),(9,'FS',NULL),(10,'HT',NULL),(11,'IA',NULL),(12,'IC',NULL),(13,'IE',NULL),(14,'IM',NULL),(15,'IP',NULL),(16,'IS',NULL),(17,'MP',NULL),(18,'QU',NULL),(19,'SA',NULL),(20,'SC',NULL),(21,'SM',NULL),(22,'TM',NULL),(23,'TX',NULL),(24,'AE',NULL),(25,'SE',NULL),(26,'Preimpressió digital',1),(27,'Impressió gràfica',1),(28,'Postimpressió i acabats gràfics',1),(29,'Disseny i edició de publicacions impreses i multimèdia',1),(30,'Disseny i gestió de la producció gràfica',1),(31,'Conducció d\'activitats fisicoesportives en el medi natural',1),(32,'Animació d\'activitats físiques i esportives',1),(33,'Gestió administrativa',2),(34,'Assistència a la direcció',2),(35,'Administració i finances',2),(36,'Producció agropecuària',3),(37,'Producció agroecològica',3),(38,'Aprofitament i conservació del medi natural',3),(39,'Jardineria i floristeria',3),(40,'Activitats eqüestres',3),(41,'Gestió forestal i del medi natural',3),(42,'Paisatgisme i medi rural',3),(43,'Ramaderia i assistència en sanitat animal',3),(44,'Gestió de vendes i espais comercials',4),(45,'Comerç internacional',4),(46,'Transport i logística',4),(47,'Màrqueting i publicitat',4),(48,'Serveis al consumidor',4),(49,'Activitats comercials',4),(50,'Xarxes, instal·lacions i estacions de tractament d\'aigua',5),(51,'Xarxes i estacions de tractament d\'aigües',5),(52,'Eficiència energètica i energia solar tèrmica',5),(53,'Energies renovables',5),(54,'Gestió de l\'aigua',5),(55,'Instal·lacions elèctriques i automàtiques',6),(56,'Instal.lacions de telecomunicacions',6),(57,'Sistemes electrotècnics i automatitzats',6),(58,'Automatització i robòtica industrial',6),(59,'Manteniment electrònic',6),(60,'Sistemes de telecomunicacions i informàtics',6),(61,'Electromedicina clínica',6),(62,'Obres d\'interior, decoració i rehabilitació',7),(63,'Construcció',7),(64,'Projectes d\'obra civil',7),(65,'Projectes d\'edificació',7),(66,'Organització i control d\'obres de construcció',7),(67,'Realització i plans d\'obres',7),(68,'Soldadura i caldereria',8),(69,'Mecanització',8),(70,'Construccions metàl·liques',8),(71,'Programació de la producció en fabricación mecànica',8),(72,'Disseny en fabricació mecànica',8),(73,'Programació de la producció en emmotllament de metalls i polímers',8),(74,'Fusteria i moble',9),(75,'Instal·lació i moblament',9),(76,'Disseny i moblament',9),(77,'Cuina i gastronomia',10),(78,'Serveis en restauració',10),(79,'Agències de viatges i gestió d\'esdeveniments',10),(80,'Gestió d\'allotjaments turístics',10),(81,'Direcció de cuina',10),(82,'Direcció de serveis en restauració',10),(83,'Guia, informació i assistència turístiques',10),(84,'Elaboració de productes alimentaris',11),(85,'Olis d\'oliva i vins',11),(86,'Forneria, pastisseria i confiteria',11),(87,'Vitivinicultura',11),(88,'Processos i qualitat en la indústria alimentària',11),(89,'Sistemes microinformàtics i xarxes',12),(90,'Administració de sistemes informàtics en xarxa',12),(91,'Desenvolupament d\'aplicacions multiplataforma',12),(92,'Desenvolupament d\'aplicacions web',12),(93,'Excavacions i sondatges',13),(94,'Pedra natural',13),(95,'Manteniment electromecànic',14),(96,'Instal·lacions de producció de calor',14),(97,'Instal·lacions frigorífiques i de climatització',14),(98,'Manteniment d\'instal·lacions tèrmiques i de fluids',14),(99,'Desenvolupament de projectes d\'instal·lacions tèrmiques i de fluids',14),(100,'Mecatrònica industrial',14),(101,'Perruqueria i cosmètica capil·lar',15),(102,'Estètica i bellesa',15),(103,'Assessoria d\'imatge personal i corporativa',15),(104,'Estètica integral i benestar',15),(105,'Estilisme i direcció de perruqueria',15),(106,'Caracterització i maquillatge professional',15),(107,'Vídeo, discjòquei i so',16),(108,'Realització de projectes d\'audiovisuals i espectacles',16),(109,'Il·luminació, captació i tractament d\'imatge',16),(110,'So per a audiovisuals i espectacles',16),(111,'Producció d\'audiovisuals i espectacles',16),(112,'Animacions 3D, jocs i entorns interactius',16),(114,'Cultius Aqüícoles',17),(115,'Operacions subaquàtiques i hiperbàriques',17),(116,'Navegació i pesca de litoral',17),(117,'Manteniment i control de la maquinària de vaixells i embarcacions',17),(118,'Transport marítim i pesca d\'altura',17),(119,'Aqüicultura',17),(120,'Organització del manteniment de la maquinària de vaixells i embarcacions',17),(121,'Planta química',18),(122,'Operacions de laboratori',18),(123,'Química industrial',18),(124,'Fabricació de productes farmacèutics, biotecnològics i afins',18),(125,'Laboratori d\'anàlisi i control de qualitat',18),(126,'Operacions de procés de pasta i paper',18),(127,'Operacions de fabricació de productes farmacèutics',18),(128,'Indústries de procés de pasta i paper',18),(129,'Química ambiental',18),(130,'Farmàcia i parafarmàcia',19),(131,'Emergències sanitàries',19),(132,'Pròtesis dentals',19),(133,'Ortopròtesi i productes de suport',19),(134,'Anatomia patològica i citodiagnòstic',19),(135,'Documentació i administració sanitària',19),(136,'Documentació i administració sanitàries',19),(137,'Laboratori clínic i biomèdic',19),(138,'Radioteràpia i dosimetria',19),(139,'Audiologia protètica',19),(140,'Higiene bucodental',19),(141,'Imatge per al diagnòstic i medicina nuclear',19),(142,'Laboratori d\'imatge',19),(143,'Cures auxiliars d\'infermeria',19),(144,'Òptica d\'ullera',19),(145,'Salut ambiental',19),(146,'Dietètica',19),(147,'Atenció a persones en situació de dependència',20),(148,'Animació sociocultural i turística',20),(149,'Educació Infantil',20),(150,'Integració social',20),(151,'Promoció d\'igualtat de gènere',20),(152,'Mediació comunicativa',20),(153,'Emergències i protecció civil',21),(154,'Educació i control ambiental',21),(155,'Coordinació d\'emergències i protecció civil',21),(156,'Electromecànica de vehicles automòbils',22),(157,'Carrosseria',22),(158,'Electromecànica de maquinària',22),(159,'Manteniment de material rodant ferroviari',22),(160,'Conducció de vehicles de transport per carretera',22),(161,'Manteniment d\'embarcacions d\'esbarjo',22),(162,'Automoció',22),(163,'Manteniment aeromecànic',22),(164,'Manteniment d\'aviònica',22),(165,'Fabricació i ennobliment de productes tèxtils',23),(166,'Confecció i moda',23),(167,'Disseny tècnic en tèxtil i pell',23),(168,'Vestuari a mida i d\'espectacles',23),(169,'Patronatge i moda',23),(170,'Ensenyament i animació socioesportiva',24),(171,'Condicionament físic',24),(172,'Prevenció de riscos professionals',25);
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(120) NOT NULL,
  `surnames` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `type` enum('Empresa','Centre') NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'alejandro.garcia@unican.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Alejandro','García Ruiz','Santander','+34 942 201 000','CIFP César Manrique','Centre','imageproject1.jpeg'),(2,'marta.lopez@uab.cat','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Marta','López Sala','Barcelona','+34 935 811 000','IES La Mercè','Centre',NULL),(3,'jorge.ramirez@itesm.mx','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Jorge','Ramírez Ortiz','Monterrey','+52 81 8358 2000','CIFP Juan de Herrera','Centre',NULL),(4,'elena.vazquez@usal.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Elena','Vázquez Mota','Salamanca','+34 923 294 400','IES Mare de Déu de la Mercè','Centre',NULL),(5,'diego.torres@uchile.cl','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Diego','Torres Silva','Santiago','+56 2 2978 2000','IES Esteve Terradas I Illa','Centre','imageproject3.jpg'),(6,'sofia.mendez@upv.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Sofía','Méndez Castro','Valencia','+34 963 877 000','CIFP Escuela de Hostelería','Centre','imageproject5.jpg'),(7,'raul.jimenez@uniandes.edu.co','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Raúl','Jiménez Peña','Bogotá','+57 1 339 4949','Universidad de los Andes','Centre',NULL),(8,'lucia.ferrer@uam.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Lucía','Ferrer Vidal','Madrid','+34 914 975 000','Universidad Autónoma de Madrid','Centre',NULL),(9,'pablo.duarte@uba.ar','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Pablo','Duarte Sosa','Buenos Aires','+54 11 4508 3500','Universidad de Buenos Aires','Centre',NULL),(10,'isabel.roca@upc.edu','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Isabel','Roca Mora','Barcelona','+34 934 000 000','Universidad Politécnica de Cataluña','Centre','imageproject6.jpg'),(11,'carlos.sanz@ucm.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Carlos','Sanz Guerrero','Madrid','+34 913 941 000','Universidad Complutense de Madrid','Centre',NULL),(12,'ana.belen@us.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Ana Belén','Heredia Pozos','Sevilla','+34 954 551 000','Universidad de Sevilla','Centre',NULL),(13,'javier.solis@pucp.edu.pe','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Javier','Solís Vargas','Lima','+51 1 626 2000','Pontificia Universidad Católica del Perú','Centre',NULL),(14,'beatriz.luna@unizar.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Beatriz','Luna Crespo','Zaragoza','+34 976 761 000','Universidad de Zaragoza','Centre',NULL),(15,'fernando.rios@ehu.eus','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Fernando','Ríos Ibarretxe','Bilbao','+34 946 012 000','Universidad del País Vasco','Centre',NULL),(16,'clara.poveda@uma.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Clara','Poveda Marín','Málaga','+34 952 131 000','Universidad de Málaga','Centre',NULL),(17,'hugo.paredes@unam.mx','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Hugo','Paredes Meza','CDMX','+52 55 5622 1332','Universidad Nacional Autónoma de México','Centre',NULL),(18,'valeria.gil@unlp.edu.ar','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Valeria','Gil Ortega','La Plata','+54 221 423 6701','Universidad Nacional de La Plata','Centre',NULL),(19,'sergio.nieto@uclm.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Sergio','Nieto Gallego','Ciudad Real','+34 926 295 300','Universidad de Castilla-La Mancha','Centre',NULL),(20,'adriana.vega@ucl.ac.uk','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Adriana','Vega Campos','Londres','+44 20 7679 2000','University College London','Centre',NULL),(21,'m.gonzalez@inditex.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Marcos','González Pardo','Arteixo','+34 981 185 400','Inditex','Empresa','imageproject2.jpg'),(22,'laura.rivas@telefonica.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Laura','Rivas Castro','Madrid','+34 914 823 800','Telefónica','Empresa',NULL),(23,'r.moreno@santander.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Roberto','Moreno Laza','Boadilla del Monte','+34 912 572 020','Banco Santander','Empresa',NULL),(24,'carmen.suarez@repsol.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Carmen','Suárez Fito','Madrid','+34 917 538 000','Repsol','Empresa',NULL),(25,'oscar.leon@bbva.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Óscar','León Domínguez','Bilbao','+34 913 746 000','BBVA','Empresa',NULL),(26,'patricia.oro@mercadona.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Patricia','Oro Blanco','Valencia','+34 800 500 220','Mercadona','Empresa',NULL),(27,'ignacio.bravo@iberdrola.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Ignacio','Bravo Santos','Bilbao','+34 944 151 411','Iberdrola','Empresa',NULL),(28,'sandra.milla@acciona.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Sandra','Milla Vicens','Alcobendas','+34 916 632 850','Acciona','Empresa',NULL),(29,'f.blanco@ferrovial.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Francisco','Blanco Urquijo','Madrid','+34 915 862 500','Ferrovial','Empresa',NULL),(30,'julia.diez@caixabank.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Julia','Diez Reverte','Valencia','+34 934 046 000','CaixaBank','Empresa',NULL),(31,'alberto.noya@globant.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Alberto','Noya Ruiz','Buenos Aires','+54 11 4109 1700','Globant','Empresa',NULL),(32,'monica.luz@bimbo.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Mónica','Luz Valiente','CDMX','+52 55 5268 6600','Grupo Bimbo','Empresa','imageproject4.jpg'),(33,'esteban.perez@mercadolibre.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Esteban','Pérez Galán','Buenos Aires','+54 11 4640 8000','Mercado Libre','Empresa',NULL),(34,'rosa.maria@petrobras.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Rosa María','Almeida Santos','Río de Janeiro','+55 21 3224 4477','Petrobras','Empresa',NULL),(35,'victor.gomez@cemex.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Víctor','Gómez Herrera','Monterrey','+52 81 8328 3000','Cemex','Empresa',NULL),(36,'antonio.vera@grifols.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Antonio','Vera Ramos','Sant Cugat','+34 935 710 500','Grifols','Empresa',NULL),(37,'pilar.ortiz@seat.es','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Pilar','Ortiz Mesas','Martorell','+34 937 085 000','SEAT','Empresa',NULL),(38,'manuel.cid@naturgy.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Manuel','Cid Varela','Madrid','+34 900 100 251','Naturgy','Empresa',NULL),(39,'daniela.mar@latam.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Daniela','Mar Adentro','Santiago','+56 2 2677 4000','LATAM Airlines','Empresa',NULL),(40,'josep.orpi@vueling.com','3411e586758b96100c7f982e6efd239cdbe2b76afd414bf742ad5fb88b9ef9f3','Josep','Orpi','Mesas','+34 741 103 152','Vueling','Empresa',NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-19 16:24:22
