-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2023 at 06:55 AM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 7.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gempa`
--

-- --------------------------------------------------------

--
-- Table structure for table `gempa`
--

CREATE TABLE `gempa` (
  `ot` datetime NOT NULL,
  `lat` float(4,2) NOT NULL,
  `lon` float(5,2) NOT NULL,
  `mag` float(4,2) NOT NULL,
  `loc` varchar(100) NOT NULL,
  `dept` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gempa`
--

INSERT INTO `gempa` (`ot`, `lat`, `lon`, `mag`, `loc`, `dept`) VALUES
('2001-10-23 07:18:41', -10.69, 113.07, 5.00, '284 km BaratDaya JEMBER-JATIM', 10),
('2001-10-23 11:00:26', -7.26, 106.60, 5.40, '30 km Tenggara KAB-SUKABUMI-JABAR', NULL),
('2002-09-23 01:25:35', 0.77, 95.58, 3.30, '208 km BaratDaya SINABANG-ACEH', NULL),
('2005-09-23 21:58:04', 2.68, 95.89, 4.80, '57 km BaratLaut SINABANG-ACEH', NULL),
('2023-09-28 19:47:30', 5.01, 140.40, 3.50, 'Pusat gempa berada di darat, 11 km BaratLaut Kab. Jayapura', NULL),
('2023-09-29 21:19:36', 2.48, 104.13, 3.90, 'Pusat gempa berada di darat 7 km TimurLaut Lampung Barat', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gempa`
--
ALTER TABLE `gempa`
  ADD PRIMARY KEY (`ot`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
