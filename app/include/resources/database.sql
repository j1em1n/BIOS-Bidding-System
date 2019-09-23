-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 06, 2019 at 09:09 AM
-- Server version: 5.7.23
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pokemon`
--
CREATE DATABASE IF NOT EXISTS `pokemon` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `pokemon`;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `init_data`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `init_data` ()  BEGIN
	SET AUTOCOMMIT = 0;
	START TRANSACTION;

    TRUNCATE `pokemon_type`;

    TRUNCATE `pokemon`;
	
	TRUNCATE `user`;

    INSERT INTO `pokemon_type` (`name`) VALUES
    ('FIRE'),
    ('GRASS'),
    ('WATER');

    INSERT INTO `pokemon` (`name`, `type`) VALUES
    ('Bulbasaur', 'GRASS'),
    ('Bellsprout', 'GRASS'),
    ('Charmander', 'FIRE'),
    ('Vulpix', 'FIRE'),
    ('Squirtle', 'WATER'),
    ('Poliwag', 'WATER'),
    ('Psyduck', 'WATER');

	INSERT INTO `user` (`username`, `gender`, `password`, `name`) VALUES
	('apple.2016', 'male', '$2y$10$EvaPizh1Wrx9EuLef8I3UeivWWfCThV5XfE05IabwWTr2DnuWo4HW', 'Apple TAN'),
	('orange.2017', 'female', '$2y$10$6Gfc8QT8O5PerQRbEeffMeV9kEMfxisM6UojhV32hpA0Rv3AbtKqe', 'Orange TAN'),
	('pear.2018', 'male', '$2y$10$Pp1uJuQja1JLrA8RPDHfkupxyKrElzmRY.z/2nQbwRs3rn6iJZ0CS', 'Pear TAN');
    
    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pokemon`
--

DROP TABLE IF EXISTS `pokemon`;
CREATE TABLE IF NOT EXISTS `pokemon` (
  `name` char(64) NOT NULL,
  `type` char(16) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `pokemon`
--

TRUNCATE TABLE `pokemon`;
--
-- Dumping data for table `pokemon`
--

INSERT INTO `pokemon` (`name`, `type`) VALUES
('Bulbasaur', 'GRASS'),
('Bellsprout', 'GRASS'),
('Charmander', 'FIRE'),
('Vulpix', 'FIRE'),
('Squirtle', 'WATER'),
('Poliwag', 'WATER'),
('Psyduck', 'WATER');

-- --------------------------------------------------------

--
-- Table structure for table `pokemon_type`
--

DROP TABLE IF EXISTS `pokemon_type`;
CREATE TABLE IF NOT EXISTS `pokemon_type` (
  `name` char(16) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `pokemon_type`
--

TRUNCATE TABLE `pokemon_type`;
--
-- Dumping data for table `pokemon_type`
--

INSERT INTO `pokemon_type` (`name`) VALUES
('FIRE'),
('GRASS'),
('WATER');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `username` varchar(128) NOT NULL,
  `gender` varchar(45) DEFAULT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `user`
--

TRUNCATE TABLE `user`;
--
-- Dumping data for table `user`
--
-- passwords are apple123  orange123 and pear123 respectively
INSERT INTO `user` (`username`, `gender`, `password`, `name`) VALUES
('apple.2016', 'male', '$2y$10$EvaPizh1Wrx9EuLef8I3UeivWWfCThV5XfE05IabwWTr2DnuWo4HW', 'Apple TAN'),
('orange.2017', 'female', '$2y$10$6Gfc8QT8O5PerQRbEeffMeV9kEMfxisM6UojhV32hpA0Rv3AbtKqe', 'Orange TAN'),
('pear.2018', 'male', '$2y$10$Pp1uJuQja1JLrA8RPDHfkupxyKrElzmRY.z/2nQbwRs3rn6iJZ0CS', 'Pear TAN');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;