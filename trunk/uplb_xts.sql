-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 30, 2011 at 01:01 PM
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
  `Name` varchar(255) NOT NULL,
  `Description` varchar(1000) NOT NULL,
  `FB_RSVP` varchar(255) DEFAULT NULL,
  `Temp` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`EventID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=880 ;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`EventID`, `Name`, `Description`, `FB_RSVP`, `Temp`) VALUES
(139, 'Faculty Follies', 'echos', '', 100),
(686, 'Elbi Pie', 'echos', '', 100),
(879, 'Beviant Loves', 'echos', '', 100);

-- --------------------------------------------------------

--
-- Table structure for table `event_slot`
--

CREATE TABLE IF NOT EXISTS `event_slot` (
  `EventID` int(11) NOT NULL AUTO_INCREMENT,
  `Showtime_ID` int(11) NOT NULL,
  `Status` varchar(255) NOT NULL,
  `Assigned_To_User` int(11) NOT NULL,
  `Seat` int(11) NOT NULL,
  `Sold_by` int(11) NOT NULL,
  `Ticket_Class_FK` varchar(255) NOT NULL,
  `Hold_Duration` time NOT NULL,
  PRIMARY KEY (`EventID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `event_slot`
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
-- Table structure for table `showing_time`
--

CREATE TABLE IF NOT EXISTS `showing_time` (
  `UniqueID` int(11) NOT NULL,
  `EventID` int(11) NOT NULL,
  `StartDate` date NOT NULL,
  `StartTime` time NOT NULL,
  `EndDate` date NOT NULL,
  `EndTime` time NOT NULL,
  `Book_Completion_Option` varchar(255) NOT NULL DEFAULT 'FIXED_SAMEDAY',
  `Book_Completion_Days` int(11) NOT NULL DEFAULT '0',
  `Book_Completion_Time` time NOT NULL DEFAULT '00:15:00',
  `Selling_Start_Date` date NOT NULL,
  `Selling_Start_Time` time NOT NULL,
  `Selling_End_Date` date NOT NULL,
  `Selling_End_Time` time NOT NULL,
  `NoMoreSeat_StillSell` tinyint(1) DEFAULT '0',
  `SeatRequiredOnConfirmation` tinyint(1) DEFAULT '0',
  `Location` varchar(255) DEFAULT NULL,
  `seat_pattern` varchar(255) DEFAULT NULL,
  `Slots` int(10) NOT NULL DEFAULT '0',
  `ticket_class` int(10) NOT NULL DEFAULT '0',
  `Status` varchar(255) NOT NULL DEFAULT 'UNCONFIGURED',
  `UUID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`UniqueID`,`EventID`),
  UNIQUE KEY `EventID` (`EventID`,`StartDate`,`StartTime`,`EndDate`,`EndTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `showing_time`
--

INSERT INTO `showing_time` (`UniqueID`, `EventID`, `StartDate`, `StartTime`, `EndDate`, `EndTime`, `Book_Completion_Option`, `Book_Completion_Days`, `Book_Completion_Time`, `Selling_Start_Date`, `Selling_Start_Time`, `Selling_End_Date`, `Selling_End_Time`, `NoMoreSeat_StillSell`, `SeatRequiredOnConfirmation`, `Location`, `seat_pattern`, `Slots`, `ticket_class`, `Status`, `UUID`) VALUES
(1, 139, '2012-01-30', '18:05:00', '2012-01-30', '22:05:00', 'FIXED_SAMEDAY', 0, '17:00:00', '2012-01-04', '09:00:00', '2012-01-07', '12:31:00', 0, 0, NULL, NULL, 100, 1, 'CONFIGURED', NULL),
(1, 686, '2011-12-12', '15:55:00', '2011-12-12', '17:55:00', 'FIXED_SAMEDAY', 0, '07:00:00', '2011-12-21', '14:00:00', '2011-12-23', '12:17:00', 1, 1, NULL, NULL, 100, 1, 'CONFIGURED', NULL),
(1, 879, '2012-01-04', '19:30:00', '2012-01-04', '23:46:00', 'FIXED_AFTER', 1, '17:00:00', '2011-12-30', '11:00:00', '2011-12-31', '23:00:00', 0, 1, NULL, NULL, 108, 2, 'CONFIGURED', NULL),
(2, 139, '2012-01-31', '18:05:00', '2012-01-31', '22:05:00', 'FIXED_SAMEDAY', 0, '17:00:00', '2012-01-04', '09:00:00', '2012-01-07', '12:31:00', 0, 0, NULL, NULL, 100, 1, 'CONFIGURED', NULL),
(2, 686, '2011-12-12', '18:55:00', '2011-12-13', '00:55:00', 'RELATIVE_AFTER', 2, '10:00:00', '2011-12-22', '14:00:00', '2011-12-31', '15:31:00', 0, 0, NULL, NULL, 50, 2, 'CONFIGURED', NULL),
(2, 879, '2012-01-05', '19:30:00', '2012-01-05', '23:46:00', 'FIXED_AFTER', 1, '17:00:00', '2011-12-30', '11:00:00', '2011-12-31', '23:00:00', 0, 1, NULL, NULL, 108, 2, 'CONFIGURED', NULL),
(3, 686, '2011-12-13', '15:55:00', '2011-12-13', '17:55:00', 'FIXED_SAMEDAY', 0, '07:00:00', '2011-12-21', '14:00:00', '2011-12-23', '12:17:00', 1, 1, NULL, NULL, 100, 1, 'CONFIGURED', NULL),
(3, 879, '2012-01-06', '19:30:00', '2012-01-06', '23:46:00', 'FIXED_AFTER', 1, '17:00:00', '2011-12-30', '11:00:00', '2011-12-31', '23:00:00', 0, 1, NULL, NULL, 108, 2, 'CONFIGURED', NULL),
(4, 686, '2011-12-14', '15:55:00', '2011-12-14', '17:55:00', 'FIXED_SAMEDAY', 0, '07:00:00', '2011-12-21', '14:00:00', '2011-12-23', '12:17:00', 1, 1, NULL, NULL, 100, 1, 'CONFIGURED', NULL),
(5, 686, '2011-12-14', '18:55:00', '2011-12-15', '00:55:00', 'RELATIVE_AFTER', 2, '10:00:00', '2011-12-22', '14:00:00', '2011-12-31', '15:31:00', 0, 0, NULL, NULL, 50, 2, 'CONFIGURED', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_class`
--

CREATE TABLE IF NOT EXISTS `ticket_class` (
  `EventID` int(11) NOT NULL,
  `UniqueID` int(11) NOT NULL DEFAULT '-1',
  `Name` varchar(255) NOT NULL,
  `Price` double NOT NULL DEFAULT '0',
  `Slots` int(11) NOT NULL DEFAULT '0',
  `Privileges` varchar(1000) NOT NULL,
  `Restrictions` varchar(1000) NOT NULL,
  `priority` int(11) DEFAULT '0',
  PRIMARY KEY (`EventID`,`UniqueID`,`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ticket_class`
--

INSERT INTO `ticket_class` (`EventID`, `UniqueID`, `Name`, `Price`, `Slots`, `Privileges`, `Restrictions`, `priority`) VALUES
(0, -1, 'BUSINESS', 0, 0, '', '', 2),
(0, -1, 'REGULAR', 0, 0, '', '', 3),
(0, -1, 'STANDING', 0, 0, '', '', 4),
(0, -1, 'VIP', 0, 0, '', '', 1),
(139, 1, 'BUSINESS', 0, 5, 'IDK', 'IDY', 0),
(139, 1, 'REGULAR', 0, 3, 'IDK', 'IDY', 0),
(139, 1, 'STANDING', 0, 4, 'IDK', 'IDY', 0),
(139, 1, 'VIP', 0, 10, 'IDK', 'IDY', 0),
(686, 1, 'BUSINESS', 50, 50, 'IDK', 'IDY', 0),
(686, 1, 'REGULAR', 20, 20, 'IDK', 'IDY', 0),
(686, 1, 'STANDING', 0, 20, 'IDK', 'IDY', 0),
(686, 1, 'VIP', 100, 10, 'IDK', 'IDY', 0),
(686, 2, 'BUSINESS', 80, 25, 'IDK', 'IDY', 0),
(686, 2, 'REGULAR', 0, 0, 'IDK', 'IDY', 0),
(686, 2, 'STANDING', 0, 0, 'IDK', 'IDY', 0),
(686, 2, 'VIP', 100, 25, 'IDK', 'IDY', 0),
(879, 1, 'BUSINESS', 0, 8, 'IDK', 'IDY', 0),
(879, 1, 'REGULAR', 0, 8, 'IDK', 'IDY', 0),
(879, 1, 'STANDING', 0, 9, 'IDK', 'IDY', 0),
(879, 1, 'VIP', 0, 6, 'IDK', 'IDY', 0),
(879, 2, 'BUSINESS', 0, 8, 'IDK', 'IDY', 0),
(879, 2, 'REGULAR', 0, 8, 'IDK', 'IDY', 0),
(879, 2, 'STANDING', 0, 9, 'IDK', 'IDY', 0),
(879, 2, 'VIP', 0, 6, 'IDK', 'IDY', 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=807520 ;

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
