-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 02, 2012 at 01:29 PM
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
-- Table structure for table `booking_details`
--

CREATE TABLE IF NOT EXISTS `booking_details` (
  `bookingNumber` varchar(20) NOT NULL,
  `EventID` int(11) NOT NULL,
  `ShowingTimeUniqueID` int(11) NOT NULL,
  `TicketClassGroupID` int(11) DEFAULT NULL,
  `TicketClassUniqueID` int(11) NOT NULL,
  `PaymentDeadline_Date` date DEFAULT NULL,
  `PaymentDeadline_Time` time DEFAULT NULL,
  `Status` varchar(100) NOT NULL DEFAULT 'BEING_BOOKED' COMMENT 'Values: "BEING_BOOKED", "PENDING-PAYMENT_NEW", ""PENDING-PAYMENT_MODIFY", ''PAID'', "CONSUMED"',
  `Status2` varchar(255) DEFAULT NULL COMMENT 'Mainly for use of "PENDING-PAYMENT". I realized it would be nice to separate "-NEW" from `status`., For ''CONSUMED'', then values are { ''PARTIAL'', ''FULL'' }',
  `MadeBy` int(11) NOT NULL DEFAULT '-1' COMMENT 'references to `user`.`Accountnum`',
  PRIMARY KEY (`bookingNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `booking_details`
--


-- --------------------------------------------------------

--
-- Table structure for table `booking_guests`
--

CREATE TABLE IF NOT EXISTS `booking_guests` (
  `UUID` varchar(37) NOT NULL,
  `bookingNumber` varchar(20) NOT NULL,
  `AccountNum` int(11) DEFAULT NULL,
  `Fname` varchar(100) NOT NULL,
  `Mname` varchar(100) DEFAULT NULL,
  `Lname` varchar(100) NOT NULL,
  `Gender` varchar(6) NOT NULL,
  `Cellphone` varchar(30) DEFAULT NULL COMMENT 'we made this varchar to account for special valid symbols like ''+''',
  `Landline` varchar(30) DEFAULT NULL COMMENT 'we made this varchar to account for special valid symbols like ''+''',
  `Email` varchar(50) NOT NULL,
  `studentNumber` int(9) DEFAULT NULL,
  `employeeNumber` int(11) DEFAULT NULL,
  PRIMARY KEY (`UUID`),
  UNIQUE KEY `bookingNumber` (`bookingNumber`,`Fname`,`Mname`,`Lname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `booking_guests`
--


-- --------------------------------------------------------

--
-- Table structure for table `coordinate_security`
--

CREATE TABLE IF NOT EXISTS `coordinate_security` (
  `UUID` varchar(40) NOT NULL DEFAULT 'UUID',
  `ACTIVITY_NAME` varchar(255) NOT NULL,
  `VALUE` varchar(255) NOT NULL,
  `VALUE_TYPE` varchar(50) NOT NULL DEFAULT 'INT',
  PRIMARY KEY (`UUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `coordinate_security`
--


-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE IF NOT EXISTS `event` (
  `EventID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Location` varchar(255) DEFAULT 'D.L. Umali Hall',
  `Description` varchar(1000) NOT NULL,
  `FB_RSVP` varchar(255) DEFAULT NULL,
  `Temp` int(11) NOT NULL DEFAULT '100',
  `ByUser` int(11) NOT NULL,
  PRIMARY KEY (`EventID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `event`
--


-- --------------------------------------------------------

--
-- Table structure for table `event_and_class_pair`
--

CREATE TABLE IF NOT EXISTS `event_and_class_pair` (
  `EC_UniqueID` int(11) NOT NULL AUTO_INCREMENT,
  `EventID` int(11) NOT NULL,
  `ShowtimeID` int(11) NOT NULL,
  `UPLBClassID` int(11) NOT NULL,
  PRIMARY KEY (`EventID`,`ShowtimeID`,`UPLBClassID`,`EC_UniqueID`),
  KEY `UniqueID` (`EC_UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `event_and_class_pair`
--


-- --------------------------------------------------------

--
-- Table structure for table `event_attendance_real`
--

CREATE TABLE IF NOT EXISTS `event_attendance_real` (
  `GuestUUID` varchar(36) NOT NULL,
  `EntryDate` date DEFAULT NULL,
  `EntryTime` time DEFAULT NULL,
  `ExitDate` date DEFAULT NULL,
  `ExitTime` time DEFAULT NULL,
  PRIMARY KEY (`GuestUUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `event_attendance_real`
--


-- --------------------------------------------------------

--
-- Table structure for table `event_slot`
--

CREATE TABLE IF NOT EXISTS `event_slot` (
  `UUID` char(36) NOT NULL,
  `UniqueID` int(11) NOT NULL,
  `EventID` int(11) NOT NULL,
  `Showtime_ID` int(11) NOT NULL,
  `Ticket_Class_GroupID` int(11) NOT NULL,
  `Ticket_Class_UniqueID` int(100) NOT NULL,
  `Status` varchar(100) NOT NULL DEFAULT 'AVAILABLE' COMMENT 'Values: "AVAILABLE", "OFFLINE_SELLING", "BEING_BOOKED", "UNAVAILABLE", "BOOKED", "RESERVED-PENDING_PAYMENT"',
  `Assigned_To_User` varchar(37) DEFAULT NULL,
  `Seat_x` int(11) DEFAULT NULL,
  `Seat_y` int(11) DEFAULT NULL,
  `Sold_by` int(11) NOT NULL DEFAULT '0',
  `Start_Contact` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`EventID`,`Showtime_ID`,`Ticket_Class_GroupID`,`UniqueID`,`Ticket_Class_UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
(582327, 1, 1, 1, 1, 1),
(771566, 0, 0, 0, 1, 0),
(150949, 0, 0, 0, 1, 0),
(593835, 0, 0, 0, 1, 0),
(351916, 0, 0, 0, 1, 0),
(228018, 0, 0, 0, 1, 1),
(582327, 0, 1, 1, 1, 1),
(392648, 0, 0, 0, 1, 0),
(372076, 0, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE IF NOT EXISTS `notification` (
  `Date` date NOT NULL DEFAULT '2012-05-03',
  `Time` time NOT NULL DEFAULT '00:00:00',
  `UniqueID` int(30) NOT NULL AUTO_INCREMENT,
  `Status` int(5) DEFAULT '1' COMMENT '1 - Not yet viewed ; 0 - Viewed already',
  `ByUser` int(11) NOT NULL DEFAULT '0' COMMENT 'references to `user`.`Accountnum`',
  `Title` varchar(255) NOT NULL,
  `Title_Sub` varchar(255) DEFAULT NULL,
  `ObjectIdentifier` varchar(255) DEFAULT NULL,
  `Description` varchar(2048) DEFAULT NULL,
  `DataType` varchar(50) NOT NULL DEFAULT 'WIN5',
  `Data` text,
  PRIMARY KEY (`UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `notification`
--


-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `UniqueID` int(11) NOT NULL AUTO_INCREMENT,
  `bookingNumber` varchar(20) NOT NULL,
  `amount` float NOT NULL DEFAULT '0',
  `processedBy` int(11) NOT NULL COMMENT 'This is just a reference to `user`->`AccountNum`',
  `payment_mode` varchar(50) NOT NULL DEFAULT 'CASH-ON-DELIVERY' COMMENT 'Values: "CASH-ON-DELIVERY", "SITE-ACCOUNT", "PAYPAL", "FREE"',
  `Processed_Time` time NOT NULL,
  `Processed_Date` date NOT NULL,
  `data` text,
  PRIMARY KEY (`UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `payments`
--


-- --------------------------------------------------------

--
-- Table structure for table `payment_channel`
--

CREATE TABLE IF NOT EXISTS `payment_channel` (
  `UniqueID` int(11) NOT NULL COMMENT '0 - Factory Default: Free/No Charge',
  `Type` varchar(100) NOT NULL DEFAULT 'COD' COMMENT 'COD | ONLINE ',
  `Name` varchar(255) NOT NULL,
  `Contact_Person` varchar(255) DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `Cellphone` varchar(50) DEFAULT NULL,
  `Landline` varchar(50) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Comments` text,
  PRIMARY KEY (`UniqueID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payment_channel`
--

INSERT INTO `payment_channel` (`UniqueID`, `Type`, `Name`, `Contact_Person`, `Location`, `Cellphone`, `Landline`, `Email`, `Comments`) VALUES
(0, 'FREE', 'Automatic Confirmation Since Free', NULL, NULL, NULL, NULL, NULL, 'Your booking has been confirmed automatically because you do not need to pay anything.'),
(1, 'COD', 'Personal payment to Department of Humanities', NULL, 'CAS Annex 2 UPLB', '09181234567', '(043) 1234567', 'dhum@uplb.edu.ph', 'Office hours until 5PM only.'),
(2, 'ONLINE', 'Online via Credit Card ( PayPal )', 'Abraham Darius Llave', NULL, '9183981185', NULL, 'abraham.darius.llave@gmail.com', 'There are additional processing fees additional charge if you use this. It would be displayed once you are in PayPal. If you don''t want to use this once you''re there, just click "Cancel and return to.."   to select another payment mode.'),
(3, 'COD', 'LBC', NULL, NULL, NULL, NULL, NULL, 'Any LBC branch');

-- --------------------------------------------------------

--
-- Table structure for table `payment_channel_availability`
--

CREATE TABLE IF NOT EXISTS `payment_channel_availability` (
  `EventID` int(11) NOT NULL,
  `ShowtimeID` int(11) NOT NULL,
  `PaymentChannel_UniqueID` int(11) NOT NULL,
  `Comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`EventID`,`ShowtimeID`,`PaymentChannel_UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payment_channel_availability`
--


-- --------------------------------------------------------

--
-- Table structure for table `payment_channel_permission`
--

CREATE TABLE IF NOT EXISTS `payment_channel_permission` (
  `AccountNum` int(11) NOT NULL,
  `EventID` int(11) NOT NULL,
  `ShowtimeID` int(11) NOT NULL,
  `PaymentChannel_UniqueID` int(11) NOT NULL,
  `Status` int(2) NOT NULL DEFAULT '1' COMMENT '0 - Denied | -1 - Suspended | 1 - Granted ',
  `Comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`AccountNum`,`EventID`,`ShowtimeID`,`PaymentChannel_UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payment_channel_permission`
--


-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE IF NOT EXISTS `purchase` (
  `UniqueID` int(11) NOT NULL AUTO_INCREMENT,
  `BookingNumber` varchar(20) NOT NULL,
  `Charge_type` varchar(255) NOT NULL DEFAULT 'TICKET' COMMENT '''TICKET'' | ''VAT'' | ''PROCESSING_FEE'' | ''CHANGE_SEAT'' | ''REBOOK''',
  `Charge_type_Description` varchar(255) DEFAULT NULL,
  `Quantity` int(5) NOT NULL DEFAULT '1',
  `Amount` float NOT NULL DEFAULT '0',
  `Payment_UniqueID` int(11) NOT NULL COMMENT 'this determines if paid already. If this is 0 (int), then not yet.',
  `Payment_Channel_ID` int(5) NOT NULL,
  `Deadline_Date` date NOT NULL,
  `Deadline_Time` time NOT NULL,
  `Comments` varchar(255) DEFAULT NULL COMMENT 'This will contain variables like, "onLapse" -> points to data on ''transactionList'' that is needed to rollback',
  PRIMARY KEY (`UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `purchase`
--


-- --------------------------------------------------------

--
-- Table structure for table `seats_actual`
--

CREATE TABLE IF NOT EXISTS `seats_actual` (
  `EventID` int(11) NOT NULL,
  `Showing_Time_ID` int(11) NOT NULL,
  `Matrix_x` int(2) NOT NULL,
  `Matrix_y` int(2) NOT NULL,
  `Visual_row` varchar(4) DEFAULT NULL,
  `Visual_col` varchar(4) DEFAULT NULL,
  `Status` int(1) NOT NULL DEFAULT '0' COMMENT ' -4 - ON-HOLD during manage booking -| -3 Removed for some other purpose | -2 - Unassigned | -1 - Aisle | 0 - Available | 1 - Assigned ',
  `Ticket_Class_GroupID` int(100) DEFAULT NULL,
  `Ticket_Class_UniqueID` varchar(100) DEFAULT NULL,
  `Comments` text,
  PRIMARY KEY (`EventID`,`Matrix_x`,`Matrix_y`,`Showing_Time_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `seats_actual`
--


-- --------------------------------------------------------

--
-- Table structure for table `seats_default`
--

CREATE TABLE IF NOT EXISTS `seats_default` (
  `Seat_map_UniqueID` int(11) NOT NULL,
  `Matrix_x` int(2) NOT NULL,
  `Matrix_y` int(2) NOT NULL,
  `Visual_row` varchar(4) DEFAULT NULL,
  `Visual_col` varchar(4) DEFAULT NULL,
  `Status` int(1) NOT NULL DEFAULT '0',
  `Comments` text,
  PRIMARY KEY (`Seat_map_UniqueID`,`Matrix_x`,`Matrix_y`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `seats_default`
--


-- --------------------------------------------------------

--
-- Table structure for table `seat_map`
--

CREATE TABLE IF NOT EXISTS `seat_map` (
  `UniqueID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Rows` int(3) NOT NULL DEFAULT '0',
  `Cols` int(3) NOT NULL DEFAULT '0',
  `Location` text,
  `Status` text COMMENT 'values; "BEING_CONFIGURED", "CONFIGURED", "UNCONFIGURED"',
  `UsableCapacity` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UniqueID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `seat_map`
--


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
  `Seat_map_UniqueID` int(11) DEFAULT NULL COMMENT 'the seat pattern',
  `Slots` int(10) NOT NULL DEFAULT '0',
  `Ticket_Class_GroupID` int(10) NOT NULL DEFAULT '0',
  `Status` varchar(255) NOT NULL DEFAULT 'UNCONFIGURED' COMMENT 'UNCONFIGURED | BEING_CONFIGURED | CONFIGURED | FINAL - No more changes allowed | CHECK-IN | STRAGGLE - All bookings confirmed/cancelled so leftover is left for chance guests | SEALED - Stage before check-in',
  `UUID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`UniqueID`,`EventID`),
  UNIQUE KEY `EventID` (`EventID`,`StartDate`,`StartTime`,`EndDate`,`EndTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `showing_time`
--


-- --------------------------------------------------------

--
-- Table structure for table `ticket_class`
--

CREATE TABLE IF NOT EXISTS `ticket_class` (
  `EventID` int(11) NOT NULL,
  `GroupID` int(11) NOT NULL DEFAULT '-1',
  `UniqueID` int(100) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Price` double NOT NULL DEFAULT '0',
  `Slots` int(11) NOT NULL DEFAULT '0',
  `Privileges` varchar(1000) NOT NULL,
  `Restrictions` varchar(1000) NOT NULL,
  `priority` int(11) DEFAULT '0',
  `HoldingTime` time DEFAULT '00:20:00',
  PRIMARY KEY (`EventID`,`GroupID`,`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ticket_class`
--

INSERT INTO `ticket_class` (`EventID`, `GroupID`, `UniqueID`, `Name`, `Price`, `Slots`, `Privileges`, `Restrictions`, `priority`, `HoldingTime`) VALUES
(0, -1, -1, 'BUSINESS', 0, 0, '', '', 2, '00:20:00'),
(0, -1, -1, 'ECONOMY', 0, 0, '', '', 4, '00:20:00'),
(0, -1, -1, 'REGULAR', 0, 0, '', '', 3, '00:20:00'),
(0, -1, -1, 'VIP', 0, 0, '', '', 1, '00:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactionlist`
--

CREATE TABLE IF NOT EXISTS `transactionlist` (
  `Date` date NOT NULL DEFAULT '2012-05-03',
  `Time` time NOT NULL DEFAULT '00:00:00',
  `UniqueID` int(30) NOT NULL,
  `ByUser` int(11) NOT NULL DEFAULT '0' COMMENT 'references to `user`.`Accountnum`',
  `Title` varchar(255) NOT NULL,
  `Title_Sub` varchar(255) DEFAULT NULL,
  `ObjectIdentifier` varchar(255) DEFAULT NULL,
  `Description` varchar(2048) DEFAULT NULL,
  `DataType` varchar(50) NOT NULL DEFAULT 'WIN5',
  `Data` text,
  PRIMARY KEY (`UniqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transactionlist`
--


-- --------------------------------------------------------

--
-- Table structure for table `uplbconstituent`
--

CREATE TABLE IF NOT EXISTS `uplbconstituent` (
  `AccountNum_ID` int(11) NOT NULL,
  `studentNumber` int(9) DEFAULT NULL,
  `sNum_verified` int(1) NOT NULL DEFAULT '0' COMMENT '1 | 0 - BOOLEAN TRUE and FALSE representation, for future use when verifying if student number is indeed for this user',
  `employeeNumber` int(11) DEFAULT NULL,
  `eNum_verified` int(11) NOT NULL,
  UNIQUE KEY `studentNumber` (`studentNumber`),
  UNIQUE KEY `employeeNumber` (`employeeNumber`),
  KEY `accNum_id` (`AccountNum_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `uplbconstituent`
--

INSERT INTO `uplbconstituent` (`AccountNum_ID`, `studentNumber`, `sNum_verified`, `employeeNumber`, `eNum_verified`) VALUES
(582327, 200837120, 0, 1234567899, 0),
(392648, 200837121, 0, NULL, 0),
(372076, 201018788, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `uplb_class`
--

CREATE TABLE IF NOT EXISTS `uplb_class` (
  `UUID` int(7) NOT NULL,
  `CourseTitle` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `CourseNum` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `LectureSect` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `RecitSect` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `Term` int(11) NOT NULL DEFAULT '2',
  `AcadYear1` int(11) NOT NULL DEFAULT '2011',
  `AcadYear2` int(11) NOT NULL DEFAULT '2012',
  `FacultyAccountNum` int(11) DEFAULT NULL,
  `Comments` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`UUID`),
  UNIQUE KEY `CourseTitle` (`CourseTitle`,`CourseNum`,`LectureSect`,`RecitSect`,`Term`,`AcadYear1`,`AcadYear2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `uplb_class`
--


-- --------------------------------------------------------

--
-- Table structure for table `uplb_class_and_student_pair`
--

CREATE TABLE IF NOT EXISTS `uplb_class_and_student_pair` (
  `GuestUUID` varchar(36) NOT NULL,
  `UPLBClassUUID` varchar(37) NOT NULL,
  PRIMARY KEY (`GuestUUID`,`UPLBClassUUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `uplb_class_and_student_pair`
--


-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `AccountNum` int(11) NOT NULL AUTO_INCREMENT COMMENT 'there should be a GUID generator that will fill this up',
  `username` varchar(50) NOT NULL,
  `password` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Fname` varchar(100) NOT NULL,
  `Mname` varchar(100) NOT NULL,
  `Lname` varchar(100) NOT NULL,
  `BookableByFriend` tinyint(1) NOT NULL DEFAULT '1',
  `Gender` varchar(6) NOT NULL DEFAULT 'MALE',
  `Cellphone` varchar(30) DEFAULT NULL COMMENT 'we made this varchar to account for special valid symbols like ''+''',
  `Landline` varchar(30) DEFAULT NULL COMMENT 'we made this varchar to account for special valid symbols like ''+''',
  `Email` varchar(50) NOT NULL,
  `addr_homestreet` varchar(150) DEFAULT NULL,
  `addr_barangay` varchar(50) DEFAULT NULL,
  `addr_cityMunicipality` varchar(50) DEFAULT NULL,
  `addr_province` varchar(50) DEFAULT NULL,
  `temp1` int(11) DEFAULT NULL,
  `temp2` int(11) DEFAULT NULL,
  PRIMARY KEY (`AccountNum`),
  UNIQUE KEY `username_unique` (`username`),
  UNIQUE KEY `names_unique` (`Fname`,`Mname`,`Lname`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=807520 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`AccountNum`, `username`, `password`, `Fname`, `Mname`, `Lname`, `BookableByFriend`, `Gender`, `Cellphone`, `Landline`, `Email`, `addr_homestreet`, `addr_barangay`, `addr_cityMunicipality`, `addr_province`, `temp1`, `temp2`) VALUES
(150949, 'sampleuser01', '69211b57e61853fc156da911f7e78eaf3390c3ae276a685e395f4622c49cebb923dddd578b7382af36c851da0bda6da4243d7cd9782f0bdfc8b3c6bbf3a49c25', 'SAMPLE USER', '', 'ONE', 1, 'MALE', '9183981185', '0', 'LMB@GOV.KR', '', '', '', '', NULL, NULL),
(228018, 'sampleuser04', '9bf951b1ac2f8c69aebcd430b95497f823dcdf1bd381ca97145987637fe7996bec9069f248bc225679a95ea7f0b50587399dc989e3ca41a32ed03f61d2fab0c2', 'BARACK', '', 'OBAMA', 1, 'MALE', '9091234567', '0', 'FEBFAIR@UPLB.EDU.PH', '', '', '', '', NULL, NULL),
(351916, 'sampleuser03', '760eb2f7611e7d09cde257a7538fb0044c8738d5b4a270d9098928838249170d16ba36ab323319323b0fd394cbcf487f1b08c6374be66a047d0826b9133989f5', 'HANAMICHI', '', 'SAKURAGI', 1, 'MALE', '91832948924', '0', 'AAA@AAA.COM', '', '', '', '', NULL, NULL),
(372076, 'kimjongeun', '5534dba47abe3241141d5cee392a8f6e4feac77263c82d8446120b9c438be4c41c8a5e02a84200a49a207bbf252c9be1bdd6e397208899478d50971a678f79ea', 'JONG EUN', '', 'KIM', 1, 'MALE', '9183981185', '0', 'AA@LKC.COM', '', '', '', '', NULL, NULL),
(392648, 'kangsongdaeguk', '5534dba47abe3241141d5cee392a8f6e4feac77263c82d8446120b9c438be4c41c8a5e02a84200a49a207bbf252c9be1bdd6e397208899478d50971a678f79ea', 'BARACKY', '', 'OBAMA', 1, '', '9183981185', '0', 'AAA@AA.COM', '', '', '', '', NULL, NULL),
(582327, 'abrahamdsl', '5534dba47abe3241141d5cee392a8f6e4feac77263c82d8446120b9c438be4c41c8a5e02a84200a49a207bbf252c9be1bdd6e397208899478d50971a678f79ea', 'YBRAHIM', 'DARUSSALAM', 'MIFTAH', 1, 'MALE', '9183981185', '', 'AB@YAHOOA.COM', '', '', '', '', NULL, NULL),
(593835, 'sampleuser02', 'a3d6a749fce7c494232bed96758e73f97caa3c3e31b7df72c463327a63d2b70c89d59c0fec23f6bd7f38a089bcd095d81a1824859f1624920304c11ed0cb591f', 'JONG IL', '', 'KIM', 1, 'MALE', '9183981185', '0', 'KJI@GOV.NK', '', '', '', '', NULL, NULL),
(771566, 'meowmeow', 'f92c092673da1cda19ec8edbd91cfc6b6b965763ce7917be8e2ea1f838ba6875fa9df7a41a5a87415f4f44bd9e4845de85720f9c759e468790d25a3905a76aa6', 'NYAN', 'S.', 'CAT', 1, 'FEMALE', '9183981185', '0', 'ADS@YAHOO.COM', '', '', '', '', NULL, NULL),
(807519, 'wordchamp427', 'c03d6524ebc8603bc69e5981d6ca19ef46e0fc20277cbf250c5be0f5edda104e3c04c08715038794951b8b783f924556a17a72dd20578880d46bdecc365c96ae', 'EDRIARA ANN', 'SENO', 'LLAVE', 1, 'MALE', '9183981185', '0', 'ab@yahoo.com', '', '', '', '', NULL, NULL);

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
