-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 30, 2025 at 04:25 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stitchverse1`
--

-- --------------------------------------------------------

--
-- Table structure for table `creg`
--

DROP TABLE IF EXISTS `creg`;
CREATE TABLE IF NOT EXISTS `creg` (
  `cid` int NOT NULL AUTO_INCREMENT,
  `cname` varchar(50) NOT NULL,
  `email` varchar(70) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(70) NOT NULL,
  `distr` varchar(70) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `pincode` int NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=434 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `creg`
--

INSERT INTO `creg` (`cid`, `cname`, `email`, `address`, `city`, `distr`, `phone`, `pincode`) VALUES
(433, 'Anu', 'panusivadas@gmail.com', 'panayatharayil', 'Mavelikara', 'Alappuzha', '9207446349', 690110),
(432, 'Amal R', 'akhilrajakhraj@gmail.com', 'gggggggggggggggggggggggggggg', 'Mavelikara', 'Wayanad', '9090090900', 690105),
(431, 'Amal R', 'akhilrajakhraj0987@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'Mavelikara', 'Wayanad', '7890063443', 678965),
(430, 'Zahid Muhammed', 'zahidmuhammed001@gmail.com', 'Darulfauz mannar po', 'Mannar', 'Alappuzha', '8304961371', 689622),
(429, 'Ajmai H', 'ajmalaju8970@gmail.com', 'Jeenas Cheravalli kayamkulam', 'kayamkulam', 'Alappuzha', '9037968970', 678965),
(428, 'Athul Jo Yohannan', 'athuljo555@gmail.com', 'panyamtharayil athul villa', 'Mavelikara', 'Alappuzha', '9746648573', 690110),
(427, 'Paravathy S', 'parvathyadiparashakthi@gmail.com', 'aaaaaaaaaaaaaaaaaaa', 'mavelikara', 'Wayanad', '1234567890', 678999),
(426, 'Akhil Raj', 'akhilrajsheeja455@gmail.com', 'allumoottill house komalloor po charumood', 'Charumood', 'Alappuzha', '8113018358', 690105);

-- --------------------------------------------------------

--
-- Table structure for table `feedb`
--

DROP TABLE IF EXISTS `feedb`;
CREATE TABLE IF NOT EXISTS `feedb` (
  `fid` int NOT NULL AUTO_INCREMENT,
  `tid` int NOT NULL,
  `uid` int NOT NULL,
  `feedtype` varchar(55) NOT NULL,
  `itemid` int NOT NULL,
  `feedbck` text NOT NULL,
  `rate` int NOT NULL,
  PRIMARY KEY (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedb`
--

INSERT INTO `feedb` (`fid`, `tid`, `uid`, `feedtype`, `itemid`, `feedbck`, `rate`) VALUES
(44, 30, 426, 'stitch_request', 91, 'perfectly stitched', 5),
(43, 30, 426, 'design_purchase', 36, 'good', 3);

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
CREATE TABLE IF NOT EXISTS `login` (
  `lid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `uname` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `upass` varchar(255) NOT NULL,
  `utype` varchar(20) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM AUTO_INCREMENT=455 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`lid`, `uid`, `uname`, `upass`, `utype`, `reset_token`, `token_expiry`) VALUES
(424, 0, 'admin@gmail.com', 'admin222', 'admin', '', '0000-00-00 00:00:00'),
(438, 30, 'nandanabiju111@gmail.com', 'As0@dfgh', 'tailor', '', '0000-00-00 00:00:00'),
(443, 428, 'athuljo555@gmail.com', 'Athul@2005', 'customer', NULL, NULL),
(440, 427, 'parvathyadiparashakthi@gmail.com', 'As!0dfgh', 'removed_customer', '', '0000-00-00 00:00:00'),
(436, 426, 'akhilrajsheeja455@gmail.com', 'akhil222', 'customer', NULL, NULL),
(454, 42, 'meenuj18@gmail.com', 'Meenu@2000', 'tailor', NULL, NULL),
(437, 29, 'akhilassheeja455@gmail.com', 'As0@dfgh', 'removed_tailor', '', '0000-00-00 00:00:00'),
(453, 433, 'panusivadas@gmail.com', 'Anu@2003', 'customer', NULL, NULL),
(451, 431, 'akhilrajakhraj0987@gmail.com', 'Akhil@2005', 'customer', NULL, NULL),
(444, 36, 'nandanamaya11@gmail.com', 'Nandana@2005', 'tailor', NULL, NULL),
(445, 37, 'nayanamaya11@gmail.com', 'Nayana@2005', 'tailor', NULL, NULL),
(446, 38, 'nandanasharan@gmail.com', 'Nandanas@2005', 'tailor', NULL, NULL),
(447, 39, 'aryajayan284@gmail.com', 'Arya@2006', 'tailor', NULL, NULL),
(448, 429, 'ajmalaju8970@gmail.com', 'Ajmal@2004', 'customer', NULL, NULL),
(449, 430, 'zahidmuhammed001@gmail.com', 'Zahid@2005', 'customer', NULL, NULL),
(450, 40, 'shonesaji72@gmail.com', 'Shone@2004', 'tailor', NULL, NULL),
(452, 432, 'akhilrajakhraj@gmail.com', 'Akhil@2005', 'removed_customer', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `measurements`
--

DROP TABLE IF EXISTS `measurements`;
CREATE TABLE IF NOT EXISTS `measurements` (
  `mid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `height` float NOT NULL,
  `weight` varchar(11) NOT NULL,
  `neck` varchar(11) NOT NULL,
  `shoulder` varchar(11) NOT NULL,
  `chest` varchar(11) NOT NULL,
  `bust` varchar(11) NOT NULL,
  `waist` varchar(11) NOT NULL,
  `hip` varchar(11) NOT NULL,
  `arm_length` varchar(11) NOT NULL,
  `sleeve_length` varchar(11) NOT NULL,
  `bicep` varchar(11) NOT NULL,
  `wrist` varchar(11) NOT NULL,
  `thigh` varchar(11) NOT NULL,
  `knee` varchar(11) NOT NULL,
  `calf` varchar(11) NOT NULL,
  `inseam` varchar(11) NOT NULL,
  `outseam` varchar(11) NOT NULL,
  `ankle` varchar(11) NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `measurements`
--

INSERT INTO `measurements` (`mid`, `uid`, `height`, `weight`, `neck`, `shoulder`, `chest`, `bust`, `waist`, `hip`, `arm_length`, `sleeve_length`, `bicep`, `wrist`, `thigh`, `knee`, `calf`, `inseam`, `outseam`, `ankle`) VALUES
(25, 25, 165, '50', '12', '20', '32', 'nil', '32', '39', '30', '28', '14', '5', '16', '10', '8', '30', '25', '4'),
(26, 418, 90, '90', '66', '23', '90', '45445', '4545', '23', '34', '45', '45', '56', '88', '77', '90', '45', '44', '44'),
(27, 419, 180, '45', '23', '23', '23', '23', '23', '23', '12', '12', '12', '12', '12', '12', '12', '12', '12', '12'),
(28, 426, 167, '23', '66', '77', '25', '22', '33', '44', '55', '66', '23', '78', '66', '77', '89', '44', '89', '44'),
(29, 428, 149, '99', '4', '6', '122', '99', '45', '99', '3', '20', '33', '12', '88', '45', '23', '22.8', '23', '15');

-- --------------------------------------------------------

--
-- Table structure for table `orderdesign`
--

DROP TABLE IF EXISTS `orderdesign`;
CREATE TABLE IF NOT EXISTS `orderdesign` (
  `oid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `did` int NOT NULL,
  `ostatus` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `orderdate` date NOT NULL,
  `ordered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`oid`)
) ENGINE=MyISAM AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orderdesign`
--

INSERT INTO `orderdesign` (`oid`, `uid`, `did`, `ostatus`, `orderdate`, `ordered_at`) VALUES
(141, 433, 36, 'Payment Pending', '2025-09-29', '2025-09-29 04:16:37'),
(140, 426, 31, 'Shipped', '2025-09-29', '2025-09-29 03:42:01'),
(139, 426, 42, 'Paid', '2025-09-25', '2025-09-25 03:49:53'),
(138, 426, 33, 'Paid', '2025-09-24', '2025-09-24 11:45:07'),
(137, 426, 34, 'Paid', '2025-09-24', '2025-09-24 11:39:57'),
(136, 426, 32, 'Paid', '2025-09-24', '2025-09-24 11:38:17'),
(135, 426, 31, 'Paid', '2025-09-24', '2025-09-24 11:34:16'),
(134, 426, 36, 'Paid', '2025-09-24', '2025-09-24 11:27:13'),
(133, 426, 36, 'Paid', '2025-09-20', '2025-09-20 09:54:16'),
(132, 426, 36, 'Shipped', '2025-09-20', '2025-09-20 08:56:47');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `pid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `order_id` int NOT NULL,
  `card_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `card_no` varchar(25) NOT NULL,
  `carexp_dt` varchar(10) NOT NULL,
  `cvv` varchar(5) NOT NULL,
  `pdate` varchar(20) NOT NULL,
  `pstatus` varchar(11) NOT NULL,
  `pmode` varchar(20) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`pid`, `uid`, `order_id`, `card_name`, `card_no`, `carexp_dt`, `cvv`, `pdate`, `pstatus`, `pmode`) VALUES
(123, 426, 137, 'Akhil', '0', '0000-00-00', '0', '2025-09-24 17:10:29', 'Paid', 'Card'),
(122, 426, 136, 'Akhil', '0', '0000-00-00', '0', '2025-09-24 17:08:45', 'Paid', 'Card'),
(121, 426, 135, 'Akhil', '0', '0000-00-00', '0', '2025-09-24 17:04:55', 'Paid', 'Card'),
(120, 426, 134, 'Akhil', '0', '0000-00-00', '0', '2025-09-24 16:57:59', 'Paid', 'Card'),
(119, 426, 91, 'Akhil', '0', '0000-00-00', '0', '2025-09-24 16:52:42', 'Paid', 'Card'),
(118, 426, 88, 'joy pulimoodu', '0', '0000-00-00', '0', '2025-09-20 15:27:20', 'Paid', 'Card'),
(117, 426, 88, 'joy pulimoodu', '0', '0000-00-00', '0', '2025-09-20 15:27:09', 'Paid', 'Card'),
(116, 426, 133, 'joy biju', '0', '0000-00-00', '0', '2025-09-20 15:25:40', 'Paid', 'Card'),
(115, 426, 132, 'joy pulimoodu', '0', '0000-00-00', '0', '2025-09-20 14:28:01', 'Paid', 'Card'),
(114, 426, 132, 'joy pulimoodu', '0', '0000-00-00', '0', '2025-09-20 14:27:49', 'Paid', 'Card'),
(124, 426, 138, 'Akhil', '**** **** **** 2222', '12 / 25', '***', '2025-09-24 17:15:43', 'Paid', 'Card'),
(125, 426, 96, 'Akhil', '**** **** **** 3333', '12 / 25', '', '2025-09-28 21:57:38', 'Paid', 'Card'),
(126, 426, 140, 'Akhil', '**** **** **** 3444', '11 / 25', '***', '2025-09-29 09:12:41', 'Paid', 'Card'),
(127, 426, 139, 'Akhil', '**** **** **** 8888', '12 / 25', '***', '2025-09-29 09:30:01', 'Paid', 'Card'),
(128, 426, 139, 'Akhil', '**** **** **** 8888', '12 / 25', '***', '2025-09-29 09:30:13', 'Paid', 'Card');

-- --------------------------------------------------------

--
-- Table structure for table `stitchreq`
--

DROP TABLE IF EXISTS `stitchreq`;
CREATE TABLE IF NOT EXISTS `stitchreq` (
  `sdid` int NOT NULL AUTO_INCREMENT,
  `tid` int NOT NULL,
  `uid` int NOT NULL,
  `sdname` varchar(255) NOT NULL,
  `sdtype` varchar(50) NOT NULL,
  `sfabric` varchar(50) NOT NULL,
  `scolor` varchar(50) DEFAULT NULL,
  `spattern` varchar(50) DEFAULT NULL,
  `sdesign_details` text NOT NULL,
  `sinstructions` varchar(255) DEFAULT NULL,
  `sddate` date DEFAULT NULL,
  `simg` text,
  `sprice` decimal(10,2) DEFAULT NULL,
  `sstatus` varchar(50) DEFAULT 'Pending',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sdid`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stitchreq`
--

INSERT INTO `stitchreq` (`sdid`, `tid`, `uid`, `sdname`, `sdtype`, `sfabric`, `scolor`, `spattern`, `sdesign_details`, `sinstructions`, `sddate`, `simg`, `sprice`, `sstatus`, `submitted_at`) VALUES
(98, 30, 433, 'A-Line Midi Skirt – Casual Chic', 'Skirt', 'Cotton Poplin', 'Mustard Yellow', 'Solid', 'Waist Style: High-Waist\\nClosure: Back Zipper\\n\\n--- Measurements ---\\nWaist: 45 cm\\nHip: 23 cm\\nHeight: 178 cm', 'add lining', '2025-10-09', 'uploads/1759119617_68da090131efe_cherry.jpg', 1300.00, 'Accepted', '2025-09-29 04:20:17'),
(97, 0, 433, 'A-Line Midi Skirt – Casual Chic', 'Skirt', 'Cotton Poplin', 'Mustard Yellow', 'Solid', 'Waist Style: High-Waist\\nClosure: Back Zipper\\n\\n--- Measurements ---\\nWaist: 45 cm\\nHip: 23 cm\\nHeight: 178 cm', 'add lining', '2025-10-09', 'uploads/1759119578_68da08da0914c_cherry.jpg', NULL, 'Pending', '2025-09-29 04:19:38'),
(96, 30, 426, 'Formal Linen Trousers – Women', 'Trousers', 'Linen', 'Charcoal Grey', 'Solid', 'Fit: Slim Fit\\nPleats: No Pleats (Flat Front)\\n\\n--- Measurements ---\\nWaist: 33 cm\\nHip: 44 cm\\nThigh: 66 cm\\nKnee: 77 cm\\nInseam: 44 cm\\nOutseam: 89 cm\\nAnkle: 44 cm', 'Add lining using specific buttons', '2025-09-30', 'uploads/1758980592_68d7e9f0a202c_sharan.jpg', 2300.00, 'Paid', '2025-09-27 13:43:12'),
(95, 30, 429, 'Polo T-Shirt – Smart Casual', 'T-Shirt', 'Cotton Piqué', 'Burgundy', 'Solid', 'Collar: Ribbed Knit\\nButtons: Three-Button\\n\\n--- Measurements ---\\nNeck: 23 cm\\nShoulder: 45 cm\\nChest: 123 cm\\nSleeve Length: 25 cm', 'Add lining', '2025-10-10', 'uploads/1758773578_68d4c14a9c44f_cherry.jpg', NULL, 'Pending', '2025-09-25 04:12:58'),
(94, 0, 429, 'Tailored Wool Trousers', 'Trousers', 'Wool-Blend', 'Navy', 'Pinstripe', 'Pleats: No Pleats (Flat Front)\\nClosure: Hook and Bar\\n\\n--- Measurements ---\\nWaist: 22 cm\\nHip: 45 cm\\nThigh: 45 cm\\nKnee: 23 cm\\nInseam: 56 cm\\nOutseam: 56 cm', 'add lining', '2025-10-08', 'uploads/1758773430_68d4c0b69a7f8_sharan.jpg', NULL, 'Pending', '2025-09-25 04:10:30'),
(93, 39, 426, 'Pleated Maxi Skirt – Formal Occasions', 'Skirt', 'Georgette with Satin Lining', 'Emerald Green', 'Solid', 'Pleat Size: Micro Pleats\\nLining: Full Lining\\n\\n--- Measurements ---\\nWaist: 33 cm\\nHip: 44 cm\\nHeight: 123 cm', 'add lining', '2025-10-11', 'uploads/1758772142_68d4bbae66f5a_nayana.jpg', NULL, 'Pending', '2025-09-25 03:49:02'),
(91, 30, 426, 'Mandarin Collar Shirt with Palazzo', 'Suit', 'Polyester', 'Beige/Navy', 'Solid', 'Shirt Sleeve: Roll-up Tabs\\nBottom Style: Palazzo\\n\\n--- Measurements ---\\nNeck: 66 cm\\nShoulder: 77 cm\\nChest: 25 cm\\nBust: 22 cm\\nWaist: 33 cm\\nHip: 44 cm\\nSleeve Length: 66 cm\\nWrist: 78 cm\\nInseam: 44 cm\\nOutseam: 89 cm', 'pleated with more strong stictching', '2025-10-02', 'uploads/1758712795_68d3d3db28ce6_cherry.jpg', 1000.00, 'Shipped', '2025-09-24 11:19:55'),
(92, 39, 426, 'Formal Linen Trousers – Women', 'Trousers', 'Linen', 'Charcoal Grey', 'Solid', 'Fit: Slim Fit\\nPleats: No Pleats (Flat Front)\\n\\n--- Measurements ---\\nWaist: 33 cm\\nHip: 44 cm\\nThigh: 66 cm\\nKnee: 77 cm\\nInseam: 44 cm\\nOutseam: 89 cm\\nAnkle: 44 cm', 'add lining', '2025-10-01', 'uploads/1758772067_68d4bb63adcf5_red.jpg', 1300.00, 'Accepted', '2025-09-25 03:47:47'),
(90, 0, 426, 'Classic Formal Shirt', 'Shirt', 'Egyptian Cotton', 'White', 'Solid', 'Collar: Cutaway\\nCuff: French Cuffs\\n\\n--- Measurements ---\\nNeck: 66 cm\\nShoulder: 77 cm\\nChest: 25 cm\\nWaist: 33 cm\\nSleeve Length: 66 cm\\nWrist: 78 cm', 'Please use high-quality mother-of-pearl buttons. Ensure the collar is stiffened for a sharp, professional look. Standard placket is preferred.', '2025-10-01', 'uploads/1758711064_68d3cd18981b6_cherry.jpg', NULL, 'Cancelled', '2025-09-24 10:51:04'),
(88, 39, 426, 'Formal Linen Trousers – Women', 'Trousers', 'Linen', 'Charcoal Grey', 'Solid', 'Fit: Regular Fit\\nPleats: No Pleats (Flat Front)\\n\\n--- Measurements ---\\nWaist: 33 cm\\nHip: 44 cm\\nThigh: 66 cm\\nKnee: 77 cm\\nInseam: 44 cm\\nOutseam: 89 cm\\nAnkle: 44 cm', 'sjkdhshd', '2025-09-25', 'uploads/1758354154_68ce5aeabeae9_cherry.jpg', 2000.00, 'Paid', '2025-09-20 07:42:34'),
(89, 39, 426, 'Pleated Maxi Skirt – Formal Occasions', 'Skirt', 'Georgette with Satin Lining', 'Emerald Green', 'Solid', 'Pleat Size: Micro Pleats\\nLining: Full Lining\\n\\n--- Measurements ---\\nWaist: 33 cm\\nHip: 44 cm\\nHeight: 123 cm', 'ffff', '2025-10-08', 'uploads/1758354328_68ce5b9819c21_cherry.jpg', NULL, 'Pending', '2025-09-20 07:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `treg`
--

DROP TABLE IF EXISTS `treg`;
CREATE TABLE IF NOT EXISTS `treg` (
  `tid` int NOT NULL AUTO_INCREMENT,
  `tname` varchar(50) NOT NULL,
  `email` varchar(70) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(20) NOT NULL,
  `distri` varchar(20) NOT NULL,
  `pinc` int NOT NULL,
  `phone` varchar(12) NOT NULL,
  `spect` varchar(85) NOT NULL,
  `quali` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `treg`
--

INSERT INTO `treg` (`tid`, `tname`, `email`, `address`, `city`, `distri`, `pinc`, `phone`, `spect`, `quali`, `password`, `status`) VALUES
(37, 'Nayana M', 'nayanamaya11@gmail.com', 'Pournammi koippallikarazhma\r\nMavelikara', 'Mavelikara', 'Alappuzha', 690510, '6282329004', 'All', 'ba_costume_design', 'Nayana@2005', 'Approved'),
(36, 'Nandana M', 'nandanamaya11@gmail.com', 'Pournammi koippallikarazhma\r\nMavelikara', 'Mavelikara', 'Alappuzha', 690510, '9074847378', 'All', 'bsc_fashion_design', 'Nandana@2005', 'Approved'),
(41, 'Akhil R', 'akhilrajakhil282@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'Mavelikara', 'Malappuram', 690510, '7890063442', 'Casuals', '5_years_experience', 'Akhil@2005', 'pending'),
(35, 'Akhil Raj', 'akhilrajakhil0987@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaa', 'Charumood', 'Kozhikode', 903678, '8113018357', 'Traditional', '5_years_experience', 'As0@dfgh', 'Rejected'),
(29, 'Akhila S', 'akhilassheeja455@gmail.com', 'aaaaaaaaaaaaaaaaaaaa', 'mavelikara', 'Alappuzha', 903678, '0987437899', 'Formal', '10_years_experience', 'As0@dfgh', 'Removed'),
(30, 'Nandana Biju', 'nandanabiju111@gmail.com', 'puliyoor po house', 'mavelikara', 'Alappuzha', 690505, '9562811354', 'Uniform', '5_years_experience', 'As0@dfgh', 'Approved'),
(38, 'Nandana Sharan', 'nandanasharan@gmail.com', 'Swarna Bhavan', 'Mavelikara', 'Alappuzha', 690101, '9495902005', 'All', 'pg_fashion_design', 'Nandanas@2005', 'Approved'),
(39, 'Arya Jayan', 'aryajayan284@gmail.com', 'Jayan villa', 'pavumba', 'Kollam', 698760, '9645026803', 'All', 'ba_costume_design', 'Arya@2006', 'Approved'),
(40, 'Shone Saji', 'shonesaji72@gmail.com', 'st.Thomas villa,Kannanamkuzhi', 'kattanam', 'Alappuzha', 690505, '8891185413', 'All', 'pg_fashion_design', 'Shone@2004', 'Approved'),
(42, 'meenu', 'meenuj18@gmail.com', 'mavelikara north', 'Mavelikara', 'Alappuzha', 111111, '9526132645', 'All', 'bsc_fashion_design', 'Meenu@2000', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `upload`
--

DROP TABLE IF EXISTS `upload`;
CREATE TABLE IF NOT EXISTS `upload` (
  `did` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `dname` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `dtype` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ddesc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `dprice` int NOT NULL,
  `dimg` varchar(100) NOT NULL,
  PRIMARY KEY (`did`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `upload`
--

INSERT INTO `upload` (`did`, `uid`, `dname`, `dtype`, `ddesc`, `dprice`, `dimg`) VALUES
(28, 28, 'casualnbbn', 'saree', 'aaaaaaaaaaaaaaaaaaaa', 5500, 'uploads/1754499115_6893882bcedd1.jpg'),
(36, 30, 'modern', 'saree', 'Crafted from premium, soft-touch cotton flannel, this shirt features a timeless red, white, and navy blue plaid pattern. Designed with a classic button-down collar, a stylish diagonal-checked chest pocket, and versatile roll-tab sleeves, this shirt offers a comfortable regular fit perfect for everyday wear or layering over a t-shirt.', 5500, 'uploads/1756996702_68b9a45e6af34.jpg'),
(31, 30, ' Elegant Burgundy Midi Dress with V-Neck and 3/4 S', 'Women’s Dresses / Midi Dress', 'An elegant midi dress in rich burgundy, designed with a flattering deep V-neckline, soft pleated bodice, and 3/4 sleeves. Crafted from premium polyester blend for a smooth drape and all-day comfort. Perfect for cocktail parties, evening gatherings, or formal dinners. Style with heels and statement earrings for a complete look.', 6700, 'uploads/1754801107_689823d3005a1.jpg'),
(32, 30, 'Elegant Lace White Bridal Gown with Train', 'Bridal Wear', 'A luxurious white bridal gown crafted from premium satin and delicate lace appliqué. Features a figure-flattering silhouette, high neckline, cap sleeves, and an extended chapel train adorned with floral lace patterns. Ideal for traditional and modern weddings alike, offering both grace and comfort.', 24999, 'uploads/1754802160_689827f0c83ab.jpg'),
(33, 30, 'Black Draped Evening Dress', 'Evening Wear', 'A sophisticated black evening gown with a one-shoulder-inspired draped bodice and asymmetrical pleating. Made from a high-quality stretch satin blend for a smooth, body-hugging fit. Perfect for formal dinners, gala nights, and red-carpet occasions.', 7999, 'uploads/1754802211_689828238d673.jpg'),
(34, 30, 'Classic Black Tuxedo Suit with Satin Lapel', 'Men’s Formal Wear', 'A timeless black tuxedo suit featuring a sleek satin peak lapel, single-breasted design, and tailored fit. Complete with matching trousers and a crisp white shirt. Perfect for weddings, black-tie events, and formal celebrations.', 11999, 'uploads/1754802288_68982870e43eb.jpg'),
(35, 30, 'Tailored Navy Blue Business Suit', ' Men’s Business Wear', 'A premium two-piece navy blue suit designed for modern professionals. Crafted from a fine wool blend for year-round wear, featuring a slim fit, notch lapels, and matching trousers. Perfect for office meetings, corporate events, and formal gatherings.', 10999, 'uploads/1754802340_689828a41b22e.jpg'),
(41, 37, 'traditional', 'long skirt and crop top', 'kanchipuram silk', 1500, 'uploads/1758274873-nayana.jpg'),
(42, 38, 'casual', 'half frock', 'cotton schiffli (embroidered eyelet cotton/lace cotton)', 799, 'uploads/1758284050-sharan.jpg'),
(43, 36, 'Cherry Ruched Square Neck Ruffle High-Low Midi Dress with Ruffle Hem', 'modern', 'size-Extrasmall,Small,Medium,Large', 1500, 'uploads/1758352251-cherry.jpg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
