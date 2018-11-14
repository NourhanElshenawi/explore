-- MySQL dump 10.13  Distrib 5.7.12, for osx10.9 (x86_64)
--
-- Host: 127.0.0.1    Database: dereeAthletics
-- ------------------------------------------------------
-- Server version	5.7.13-0ubuntu0.16.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `duration` varchar(45) NOT NULL,
  `instructorID` int(11) NOT NULL,
  `startTime` varchar(45) NOT NULL,
  `period` varchar(45) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(45) NOT NULL,
  `currentCapacity` int(11) NOT NULL DEFAULT '0',
  `monday` tinyint(1) NOT NULL DEFAULT '0',
  `tuesday` tinyint(1) NOT NULL DEFAULT '0',
  `wednesday` tinyint(1) NOT NULL DEFAULT '0',
  `thursday` tinyint(1) NOT NULL DEFAULT '0',
  `friday` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,'Yoga','20',1,'11:00','fall',10,'studio 1',5,1,0,1,0,0),(2,'kick boxing','50',2,'15:00','spring',5,'studio 3',4,0,1,0,1,0),(3,'aaaa','21',1,'12:02','spring',12,'studio 1',2,1,1,0,0,0),(4,'aaaa','21',1,'12:02','spring',12,'studio 1',3,1,1,0,0,0);
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instructors`
--

DROP TABLE IF EXISTS `instructors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instructors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `specialty` varchar(45) DEFAULT NULL,
  `phone` int(11) DEFAULT NULL,
  `mobile` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instructors`
--

LOCK TABLES `instructors` WRITE;
/*!40000 ALTER TABLE `instructors` DISABLE KEYS */;
INSERT INTO `instructors` VALUES (1,'Antony','a.kal@acg.edu','latin',NULL,NULL),(2,'Nourhan','n.el@acg.edu','everything',NULL,NULL);
/*!40000 ALTER TABLE `instructors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `login` datetime NOT NULL,
  `logout` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userID_idx` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,2,'2016-10-30 20:54:19','2016-10-30 23:08:35'),(2,1,'2016-10-30 23:08:35','2016-10-30 23:10:35'),(3,1,'2016-10-30 23:22:02','2016-10-30 23:42:12'),(4,1,'2016-10-30 23:42:15','2016-10-30 23:43:28'),(5,1,'2016-10-30 23:43:31','2016-10-30 23:44:35'),(6,1,'2016-10-30 23:44:37','2016-10-30 23:44:47'),(7,1,'2016-10-30 23:44:49','2016-10-30 23:45:13'),(8,1,'2016-10-30 23:45:13',NULL),(9,2,'2016-10-30 23:45:50','2016-10-30 23:45:57'),(10,2,'2016-10-30 23:52:41','2016-10-30 23:54:06'),(11,2,'2016-10-30 23:56:20','2016-10-30 23:58:00'),(12,2,'2016-10-30 23:58:08','2016-10-30 00:05:08'),(13,2,'2016-03-12 23:58:08','2016-03-12 23:59:08'),(14,2,'2016-03-12 20:58:08','2016-03-12 21:58:08'),(15,1,'2016-02-12 23:58:08','2016-02-12 23:59:08'),(16,1,'2016-02-12 20:58:08','2016-02-12 21:58:08'),(17,2,'2016-02-12 23:58:08','2016-02-12 23:59:08'),(18,2,'2016-02-12 20:58:08','2016-02-12 21:58:08'),(19,4,'2016-02-12 23:58:08','2016-02-12 23:59:08'),(20,4,'2016-02-12 20:58:08','2016-03-12 21:58:08'),(21,1,'2016-01-12 23:58:08','2016-01-12 23:59:08'),(22,1,'2016-01-12 20:58:08','2016-01-12 21:58:08'),(23,2,'2016-01-12 23:58:08','2016-01-12 23:59:08'),(24,2,'2016-01-12 20:58:08','2016-01-12 21:58:08'),(25,4,'2016-01-12 23:58:08','2016-01-12 23:59:08'),(26,4,'2016-01-12 20:58:08','2016-01-12 21:58:08'),(27,1,'2014-05-12 23:58:08','2014-05-12 23:59:08'),(28,1,'2014-05-12 20:58:08','2014-05-12 21:58:08'),(29,2,'2014-05-12 23:58:08','2014-05-12 23:59:08'),(30,2,'2014-11-12 20:58:08','2014-11-12 21:58:08'),(31,4,'2014-11-12 23:58:08','2014-11-12 23:59:08'),(32,4,'2015-04-12 20:58:08','2015-04-12 21:58:08'),(33,1,'2015-04-12 23:58:08','2015-04-12 23:59:08'),(34,1,'2015-04-12 20:58:08','2015-04-12 21:58:08'),(35,2,'2015-07-12 23:58:08','2015-07-12 23:59:08'),(36,2,'2015-06-12 20:58:08','2015-06-12 21:58:08'),(37,4,'2015-08-12 23:58:08','2015-08-12 23:59:08'),(38,3,'2015-08-12 20:58:08','2015-08-12 21:58:08'),(39,2,'2014-05-12 22:58:08',NULL),(40,5,'2014-05-12 22:58:08',NULL),(41,4,'2014-05-12 21:58:08',NULL);
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `program_requests`
--

DROP TABLE IF EXISTS `program_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `height` int(11) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '0',
  `pastExercise` tinyint(1) NOT NULL DEFAULT '0',
  `currentlyExercising` tinyint(1) NOT NULL DEFAULT '0',
  `currentExercisingIntensity` tinyint(2) NOT NULL DEFAULT '0',
  `activities` varchar(100) NOT NULL DEFAULT '',
  `monday` tinyint(1) NOT NULL DEFAULT '0',
  `tuesday` tinyint(1) NOT NULL DEFAULT '0',
  `wednesday` tinyint(1) NOT NULL DEFAULT '0',
  `thursday` tinyint(1) NOT NULL DEFAULT '0',
  `friday` tinyint(1) NOT NULL DEFAULT '0',
  `saturday` tinyint(1) NOT NULL DEFAULT '0',
  `sunday` tinyint(1) NOT NULL DEFAULT '0',
  `developMuscleStrength` tinyint(1) NOT NULL DEFAULT '0',
  `rehabilitateInjury` tinyint(1) NOT NULL DEFAULT '0',
  `overallFitness` tinyint(1) NOT NULL DEFAULT '0',
  `loseBodyFat` tinyint(1) NOT NULL DEFAULT '0',
  `startExerciseProgram` tinyint(1) NOT NULL DEFAULT '0',
  `designAdvnaceProgram` tinyint(1) NOT NULL DEFAULT '0',
  `increaseFlexibility` tinyint(1) NOT NULL DEFAULT '0',
  `sportsSpecificTraining` tinyint(1) NOT NULL DEFAULT '0',
  `increaseMuscleSize` tinyint(1) NOT NULL DEFAULT '0',
  `cardioExercise` tinyint(1) NOT NULL DEFAULT '0',
  `comments` varchar(150) NOT NULL DEFAULT '',
  `trainerResponse` tinyint(1) NOT NULL DEFAULT '0',
  `trainerComments` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `program_requests`
--

LOCK TABLES `program_requests` WRITE;
/*!40000 ALTER TABLE `program_requests` DISABLE KEYS */;
INSERT INTO `program_requests` VALUES (14,1,23,234,1,0,2,'asdf',1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,'asdf',1,'eeee'),(15,1,222,222,0,0,0,'ewrwe',1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'ertrete',0,''),(16,1,111,111,0,0,1,'aaa',0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,'aaaa',0,'eeee');
/*!40000 ALTER TABLE `program_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `classID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registrations`
--

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
INSERT INTO `registrations` VALUES (2,2,1),(3,1,3),(4,1,4),(5,2,2),(13,1,2),(14,1,1),(15,2,2);
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_certificates`
--

DROP TABLE IF EXISTS `user_certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `certificate_file` varchar(45) NOT NULL,
  `certificate_status` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cerificate_file_UNIQUE` (`certificate_file`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_certificates`
--

LOCK TABLES `user_certificates` WRITE;
/*!40000 ALTER TABLE `user_certificates` DISABLE KEYS */;
INSERT INTO `user_certificates` VALUES (1,1,'test.pdf',0,'2016-11-08 15:14:26'),(2,2,'test2.pdf',1,'2016-11-08 15:15:12'),(45,10,'test3.pdf',1,'2016-11-08 15:45:30');
/*!40000 ALTER TABLE `user_certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `picture` varchar(100) DEFAULT NULL,
  `admin` varchar(45) NOT NULL DEFAULT '0',
  `birthDate` date NOT NULL,
  `gender` char(1) NOT NULL,
  `membershipType` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'nourhan','n@acg.edu','1234','headshot.jpg','1','1994-10-01','f',0,1),(2,'nora','f@acg.edu','1234','headshot.jpg','0','1994-06-01','f',1,0),(7,'kostas','kostas@example.com','kostas','headshot.jpg','1','1991-09-16','m',0,1),(8,'Jane','jane@example.com','jane','headshot.jpg','0','1991-09-16','f',0,1),(9,'kate','kate@acg.edu','1234','headshot.jpg','0','1994-10-01','f',0,1),(10,'Robin','robin@acg.edu','1234','headshot.jpg','0','1994-10-01','m',0,1),(14,'layla','layla@acg.edu','1234','layla@acg.edu_Application Confirmation1444.png','0','2016-11-09','m',1,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-11-19 16:48:34
