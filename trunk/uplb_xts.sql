-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 28, 2011 at 01:59 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `uplb_xts`
--

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE IF NOT EXISTS `event` (
  `EventID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` int(255) NOT NULL,
  `Description` varchar(1000) NOT NULL,
  `FB_RSVP` int(11) NOT NULL,
  `Temp` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`EventID`),
  UNIQUE KEY `FB_RSVP` (`FB_RSVP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `event`
--


-- --------------------------------------------------------

--
-- Table structure for table `grand_permission`
--

CREATE TABLE IF NOT EXISTS `grand_permission` (
  `AccountNum_ID` int(11) NOT NULL,
  `ADMINISTRATOR` tinyint(1) DEFAULT '0',
  `EVENT_MANAGER` tinyint(1) DEFAULT '0',
  `RECEPTIONIST` tinyint(1) DEFAULT '0',
  `CUSTOMER` tinyint(1) DEFAULT '1',
  `FACULTY` tinyint(1) DEFAULT '0',
  KEY `accNum_id` (`AccountNum_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `grand_permission`
--

INSERT INTO `grand_permission` (`AccountNum_ID`, `ADMINISTRATOR`, `EVENT_MANAGER`, `RECEPTIONIST`, `CUSTOMER`, `FACULTY`) VALUES
(582327, 0, 1, 0, 1, 0),
(641378, 0, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `uplbconstituent`
--

CREATE TABLE IF NOT EXISTS `uplbconstituent` (
  `AccountNum_ID` int(11) NOT NULL,
  `studentNumber` int(9) DEFAULT NULL,
  `employeeNumber` int(11) DEFAULT NULL,
  UNIQUE KEY `studentNumber` (`studentNumber`),
  UNIQUE KEY `employeeNumber` (`employeeNumber`),
  KEY `accNum_id` (`AccountNum_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `uplbconstituent`
--

INSERT INTO `uplbconstituent` (`AccountNum_ID`, `studentNumber`, `employeeNumber`) VALUES
(582327, 200837120, NULL),
(641378, 200837122, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `AccountNum` int(11) NOT NULL AUTO_INCREMENT COMMENT 'there should be a GUID generator that will fill this up',
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `Fname` varchar(100) NOT NULL,
  `Mname` varchar(100) NOT NULL,
  `Lname` varchar(100) NOT NULL,
  `Gender` varchar(6) NOT NULL,
  `Cellphone` varchar(30) DEFAULT NULL COMMENT 'we made this varchar to account for special valid symbols like ''+''',
  `Landline` varchar(30) DEFAULT NULL COMMENT 'we made this varchar to account for special valid symbols like ''+''',
  `Email` varchar(50) NOT NULL,
  `addr_homestreet` varchar(150) DEFAULT NULL,
  `addr_barangay` varchar(50) DEFAULT NULL,
  `addr_cityMunicipality` varchar(50) DEFAULT NULL,
  `addr_province` varchar(50) DEFAULT NULL,
  `temp1` int(11) DEFAULT NULL,
  `temp2` int(11) DEFAULT NULL,
  PRIMARY KEY (`AccountNum`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=855860 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`AccountNum`, `username`, `password`, `Fname`, `Mname`, `Lname`, `Gender`, `Cellphone`, `Landline`, `Email`, `addr_homestreet`, `addr_barangay`, `addr_cityMunicipality`, `addr_province`, `temp1`, `temp2`) VALUES
(582327, 'abrahamdsl', '8sdk17a3', 'ABRAHAM', 'SENO', 'LLAVE', 'MALE', '9183981185', '0', 'AB@YAHOO.COM', '', '', '', '', NULL, NULL),
(641378, 'wordchamp427', '', 'EDRIARA ANN', 'SENO', 'LLAVE', 'MALE', '9183981185', '0', 'AB@YAHOO.COM', '', '', '', '', NULL, NULL),
(807519, 'wordchamp427', 'alfredobula', 'EDRIARA ANN', 'SENO', 'LLAVE', 'MALE', '9183981185', '0', 'AB@YAHOO.COM', '', '', '', '', NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grand_permission`
--
ALTER TABLE `grand_permission`
  ADD CONSTRAINT `grand_permission_ibfk_1` FOREIGN KEY (`AccountNum_ID`) REFERENCES `user` (`AccountNum`) ON DELETE CASCADE;

--
-- Constraints for table `uplbconstituent`
--
ALTER TABLE `uplbconstituent`
  ADD CONSTRAINT `uplbconstituent_ibfk_1` FOREIGN KEY (`AccountNum_ID`) REFERENCES `user` (`AccountNum`) ON DELETE CASCADE;
