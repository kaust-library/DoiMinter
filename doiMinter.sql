-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 29, 2019 at 03:42 PM
-- Server version: 5.7.25-0ubuntu0.16.04.2-log
-- PHP Version: 7.0.33-0ubuntu0.16.04.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `doiMinter`
--

-- --------------------------------------------------------

--
-- Table structure for table `dois`
--

CREATE TABLE `dois` (
  `rowID` int(11) NOT NULL,
  `handle` varchar(50) NOT NULL,
  `doi` varchar(50) NOT NULL,
  `url` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `messageID` int(11) NOT NULL,
  `process` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `message` longtext NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `metadata`
--

CREATE TABLE `metadata` (
  `rowID` int(11) NOT NULL,
  `source` varchar(50) NOT NULL,
  `idInSource` varchar(100) NOT NULL,
  `parentRowID` int(11) DEFAULT NULL,
  `field` varchar(200) NOT NULL,
  `place` int(11) NOT NULL,
  `value` longtext NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `replacedByRowID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sourceData`
--

CREATE TABLE `sourceData` (
  `rowID` int(11) NOT NULL,
  `source` varchar(30) NOT NULL,
  `idInSource` varchar(100) NOT NULL,
  `sourceData` longtext NOT NULL,
  `format` varchar(10) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `replacedByRowID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dois`
--
ALTER TABLE `dois`
  ADD PRIMARY KEY (`rowID`),
  ADD UNIQUE KEY `doi` (`doi`),
  ADD KEY `handle` (`handle`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`messageID`);

--
-- Indexes for table `metadata`
--
ALTER TABLE `metadata`
  ADD PRIMARY KEY (`rowID`),
  ADD UNIQUE KEY `checkAll` (`source`,`idInSource`,`parentRowID`,`rowID`,`field`,`deleted`),
  ADD KEY `value` (`value`(200)) KEY_BLOCK_SIZE=200,
  ADD KEY `field` (`field`),
  ADD KEY `replacedBy` (`replacedByRowID`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `place` (`place`),
  ADD KEY `added` (`added`),
  ADD KEY `parentRowID` (`parentRowID`),
  ADD KEY `source` (`source`,`idInSource`),
  ADD KEY `idInSource` (`idInSource`),
  ADD KEY `sourceFieldValueDeleted` (`source`,`field`,`value`(50),`deleted`);

--
-- Indexes for table `sourceData`
--
ALTER TABLE `sourceData`
  ADD PRIMARY KEY (`rowID`),
  ADD KEY `added` (`added`),
  ADD KEY `replacedBy` (`replacedByRowID`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `idInSource` (`idInSource`),
  ADD KEY `source` (`source`,`idInSource`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dois`
--
ALTER TABLE `dois`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1220;
--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=455;
--
-- AUTO_INCREMENT for table `metadata`
--
ALTER TABLE `metadata`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108938;
--
-- AUTO_INCREMENT for table `sourceData`
--
ALTER TABLE `sourceData`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3024;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
