-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 04, 2025 at 10:50 AM
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
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `cid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `did` int NOT NULL,
  `quantity` int DEFAULT '1',
  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=420 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `creg`
--

INSERT INTO `creg` (`cid`, `cname`, `email`, `address`, `city`, `distr`, `phone`, `pincode`) VALUES
(419, 'Sruthy S', 'srths.32@gmail.com', 'pranavam karazhma', 'Mavelikara', 'Alappuzha', '9744950507', 690104),
(418, 'Akhil Raj', 'akhilrajakhil0987@gmail.com', 'kurathikad', 'Mavelikara', 'Alappuzha', '8113018358', 678999),
(417, 'Akhil Raj', 'akhilrajsheeja455@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'mavelikara', 'Thiruvananthapuram', '8113018358', 678999),
(416, 'adwaith', 'adwaith@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'chengannur', 'Malappuram', '7890063443', 678999),
(415, 'Amal R', 'amal344@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'chengannur', 'Kozhikode', '9090090900', 678999),
(414, 'Amal R', 'amal344@gmail.com', 'nnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn', 'mavelikara', 'Thrissur', '9090090900', 678999),
(413, 'Nayana M', 'nayana@gmail.com', 'olakettill po mavelikara', 'mavelikara', 'Ernakulam', '8113018358', 456789),
(100, '', '', '', '', '', '', 0),
(101, '', '', '', '', '', '', 0),
(102, '', '', '', '', '', '', 0),
(103, '', '', '', '', '', '', 0),
(104, '', '', '', '', '', '', 0),
(105, '', '', '', '', '', '', 0),
(106, '', '', '', '', '', '', 0),
(107, '', '', '', '', '', '', 0),
(108, '', '', '', '', '', '', 0),
(109, '', '', '', '', '', '', 0),
(110, '', '', '', '', '', '', 0),
(111, '', '', '', '', '', '', 0),
(112, '', '', '', '', '', '', 0),
(113, '', '', '', '', '', '', 0),
(114, '', '', '', '', '', '', 0),
(115, '', '', '', '', '', '', 0),
(116, '', '', '', '', '', '', 0),
(117, '', '', '', '', '', '', 0),
(118, '', '', '', '', '', '', 0),
(119, '', '', '', '', '', '', 0),
(120, '', '', '', '', '', '', 0),
(121, '', '', '', '', '', '', 0),
(122, '', '', '', '', '', '', 0),
(123, '', '', '', '', '', '', 0),
(124, '', '', '', '', '', '', 0),
(125, '', '', '', '', '', '', 0),
(126, '', '', '', '', '', '', 0),
(127, '', '', '', '', '', '', 0),
(128, '', '', '', '', '', '', 0),
(129, '', '', '', '', '', '', 0),
(130, '', '', '', '', '', '', 0),
(131, '', '', '', '', '', '', 0),
(132, '', '', '', '', '', '', 0),
(133, '', '', '', '', '', '', 0),
(134, '', '', '', '', '', '', 0),
(135, '', '', '', '', '', '', 0),
(136, '', '', '', '', '', '', 0),
(137, '', '', '', '', '', '', 0),
(138, '', '', '', '', '', '', 0),
(139, '', '', '', '', '', '', 0),
(140, '', '', '', '', '', '', 0),
(141, '', '', '', '', '', '', 0),
(142, '', '', '', '', '', '', 0),
(143, '', '', '', '', '', '', 0),
(144, '', '', '', '', '', '', 0),
(145, '', '', '', '', '', '', 0),
(146, '', '', '', '', '', '', 0),
(147, '', '', '', '', '', '', 0),
(148, '', '', '', '', '', '', 0),
(149, '', '', '', '', '', '', 0),
(150, '', '', '', '', '', '', 0),
(151, '', '', '', '', '', '', 0),
(152, '', '', '', '', '', '', 0),
(153, '', '', '', '', '', '', 0),
(154, '', '', '', '', '', '', 0),
(155, '', '', '', '', '', '', 0),
(156, '', '', '', '', '', '', 0),
(157, '', '', '', '', '', '', 0),
(158, '', '', '', '', '', '', 0),
(159, '', '', '', '', '', '', 0),
(160, '', '', '', '', '', '', 0),
(161, '', '', '', '', '', '', 0),
(162, '', '', '', '', '', '', 0),
(163, '', '', '', '', '', '', 0),
(164, '', '', '', '', '', '', 0),
(165, '', '', '', '', '', '', 0),
(166, '', '', '', '', '', '', 0),
(167, '', '', '', '', '', '', 0),
(168, '', '', '', '', '', '', 0),
(169, '', '', '', '', '', '', 0),
(170, '', '', '', '', '', '', 0),
(171, '', '', '', '', '', '', 0),
(172, '', '', '', '', '', '', 0),
(173, '', '', '', '', '', '', 0),
(174, '', '', '', '', '', '', 0),
(175, '', '', '', '', '', '', 0),
(176, '', '', '', '', '', '', 0),
(177, '', '', '', '', '', '', 0),
(178, '', '', '', '', '', '', 0),
(179, '', '', '', '', '', '', 0),
(180, '', '', '', '', '', '', 0),
(181, '', '', '', '', '', '', 0),
(182, '', '', '', '', '', '', 0),
(183, '', '', '', '', '', '', 0),
(184, '', '', '', '', '', '', 0),
(185, '', '', '', '', '', '', 0),
(186, '', '', '', '', '', '', 0),
(187, '', '', '', '', '', '', 0),
(188, '', '', '', '', '', '', 0),
(189, '', '', '', '', '', '', 0),
(190, '', '', '', '', '', '', 0),
(191, '', '', '', '', '', '', 0),
(192, '', '', '', '', '', '', 0),
(193, '', '', '', '', '', '', 0),
(194, '', '', '', '', '', '', 0),
(195, '', '', '', '', '', '', 0),
(196, '', '', '', '', '', '', 0),
(197, '', '', '', '', '', '', 0),
(198, '', '', '', '', '', '', 0),
(199, '', '', '', '', '', '', 0),
(200, '', '', '', '', '', '', 0),
(201, '', '', '', '', '', '', 0),
(202, '', '', '', '', '', '', 0),
(203, '', '', '', '', '', '', 0),
(204, '', '', '', '', '', '', 0),
(205, '', '', '', '', '', '', 0),
(206, '', '', '', '', '', '', 0),
(207, '', '', '', '', '', '', 0),
(208, '', '', '', '', '', '', 0),
(209, '', '', '', '', '', '', 0),
(210, '', '', '', '', '', '', 0),
(211, '', '', '', '', '', '', 0),
(212, '', '', '', '', '', '', 0),
(213, '', '', '', '', '', '', 0),
(214, '', '', '', '', '', '', 0),
(215, '', '', '', '', '', '', 0),
(216, '', '', '', '', '', '', 0),
(217, '', '', '', '', '', '', 0),
(218, '', '', '', '', '', '', 0),
(219, '', '', '', '', '', '', 0),
(220, '', '', '', '', '', '', 0),
(221, '', '', '', '', '', '', 0),
(222, '', '', '', '', '', '', 0),
(223, '', '', '', '', '', '', 0),
(224, '', '', '', '', '', '', 0),
(225, '', '', '', '', '', '', 0),
(226, '', '', '', '', '', '', 0),
(227, '', '', '', '', '', '', 0),
(228, '', '', '', '', '', '', 0),
(229, '', '', '', '', '', '', 0),
(230, '', '', '', '', '', '', 0),
(231, '', '', '', '', '', '', 0),
(232, '', '', '', '', '', '', 0),
(233, '', '', '', '', '', '', 0),
(234, '', '', '', '', '', '', 0),
(235, '', '', '', '', '', '', 0),
(236, '', '', '', '', '', '', 0),
(237, '', '', '', '', '', '', 0),
(238, '', '', '', '', '', '', 0),
(239, '', '', '', '', '', '', 0),
(240, '', '', '', '', '', '', 0),
(241, '', '', '', '', '', '', 0),
(242, '', '', '', '', '', '', 0),
(243, '', '', '', '', '', '', 0),
(244, '', '', '', '', '', '', 0),
(245, '', '', '', '', '', '', 0),
(246, '', '', '', '', '', '', 0),
(247, '', '', '', '', '', '', 0),
(248, '', '', '', '', '', '', 0),
(249, '', '', '', '', '', '', 0),
(250, '', '', '', '', '', '', 0),
(251, '', '', '', '', '', '', 0),
(252, '', '', '', '', '', '', 0),
(253, '', '', '', '', '', '', 0),
(254, '', '', '', '', '', '', 0),
(255, '', '', '', '', '', '', 0),
(256, '', '', '', '', '', '', 0),
(257, '', '', '', '', '', '', 0),
(258, '', '', '', '', '', '', 0),
(259, '', '', '', '', '', '', 0),
(260, '', '', '', '', '', '', 0),
(261, '', '', '', '', '', '', 0),
(262, '', '', '', '', '', '', 0),
(263, '', '', '', '', '', '', 0),
(264, '', '', '', '', '', '', 0),
(265, '', '', '', '', '', '', 0),
(266, '', '', '', '', '', '', 0),
(267, '', '', '', '', '', '', 0),
(268, '', '', '', '', '', '', 0),
(269, '', '', '', '', '', '', 0),
(270, '', '', '', '', '', '', 0),
(271, '', '', '', '', '', '', 0),
(272, '', '', '', '', '', '', 0),
(273, '', '', '', '', '', '', 0),
(274, '', '', '', '', '', '', 0),
(275, '', '', '', '', '', '', 0),
(276, '', '', '', '', '', '', 0),
(277, '', '', '', '', '', '', 0),
(278, '', '', '', '', '', '', 0),
(279, '', '', '', '', '', '', 0),
(280, '', '', '', '', '', '', 0),
(281, '', '', '', '', '', '', 0),
(282, '', '', '', '', '', '', 0),
(283, '', '', '', '', '', '', 0),
(284, '', '', '', '', '', '', 0),
(285, '', '', '', '', '', '', 0),
(286, '', '', '', '', '', '', 0),
(287, '', '', '', '', '', '', 0),
(288, '', '', '', '', '', '', 0),
(289, '', '', '', '', '', '', 0),
(290, '', '', '', '', '', '', 0),
(291, '', '', '', '', '', '', 0),
(292, '', '', '', '', '', '', 0),
(293, '', '', '', '', '', '', 0),
(294, '', '', '', '', '', '', 0),
(295, '', '', '', '', '', '', 0),
(296, '', '', '', '', '', '', 0),
(297, '', '', '', '', '', '', 0),
(298, '', '', '', '', '', '', 0),
(299, '', '', '', '', '', '', 0),
(300, '', '', '', '', '', '', 0),
(301, '', '', '', '', '', '', 0),
(302, '', '', '', '', '', '', 0),
(303, '', '', '', '', '', '', 0),
(304, '', '', '', '', '', '', 0),
(305, '', '', '', '', '', '', 0),
(306, '', '', '', '', '', '', 0),
(307, '', '', '', '', '', '', 0),
(308, '', '', '', '', '', '', 0),
(309, '', '', '', '', '', '', 0),
(310, '', '', '', '', '', '', 0),
(311, '', '', '', '', '', '', 0),
(312, '', '', '', '', '', '', 0),
(313, '', '', '', '', '', '', 0),
(314, '', '', '', '', '', '', 0),
(315, '', '', '', '', '', '', 0),
(316, '', '', '', '', '', '', 0),
(317, '', '', '', '', '', '', 0),
(318, '', '', '', '', '', '', 0),
(319, '', '', '', '', '', '', 0),
(320, '', '', '', '', '', '', 0),
(321, '', '', '', '', '', '', 0),
(322, '', '', '', '', '', '', 0),
(323, '', '', '', '', '', '', 0),
(324, '', '', '', '', '', '', 0),
(325, '', '', '', '', '', '', 0),
(326, '', '', '', '', '', '', 0),
(327, '', '', '', '', '', '', 0),
(328, '', '', '', '', '', '', 0),
(329, '', '', '', '', '', '', 0),
(330, '', '', '', '', '', '', 0),
(331, '', '', '', '', '', '', 0),
(332, '', '', '', '', '', '', 0),
(333, '', '', '', '', '', '', 0),
(334, '', '', '', '', '', '', 0),
(335, '', '', '', '', '', '', 0),
(336, '', '', '', '', '', '', 0),
(337, '', '', '', '', '', '', 0),
(338, '', '', '', '', '', '', 0),
(339, '', '', '', '', '', '', 0),
(340, '', '', '', '', '', '', 0),
(341, '', '', '', '', '', '', 0),
(342, '', '', '', '', '', '', 0),
(343, '', '', '', '', '', '', 0),
(344, '', '', '', '', '', '', 0),
(345, '', '', '', '', '', '', 0),
(346, '', '', '', '', '', '', 0),
(347, '', '', '', '', '', '', 0),
(348, '', '', '', '', '', '', 0),
(349, '', '', '', '', '', '', 0),
(350, '', '', '', '', '', '', 0),
(351, '', '', '', '', '', '', 0),
(352, '', '', '', '', '', '', 0),
(353, '', '', '', '', '', '', 0),
(354, '', '', '', '', '', '', 0),
(355, '', '', '', '', '', '', 0),
(356, '', '', '', '', '', '', 0),
(357, '', '', '', '', '', '', 0),
(358, '', '', '', '', '', '', 0),
(359, '', '', '', '', '', '', 0),
(360, '', '', '', '', '', '', 0),
(361, '', '', '', '', '', '', 0),
(362, '', '', '', '', '', '', 0),
(363, '', '', '', '', '', '', 0),
(364, '', '', '', '', '', '', 0),
(365, '', '', '', '', '', '', 0),
(366, '', '', '', '', '', '', 0),
(367, '', '', '', '', '', '', 0),
(368, '', '', '', '', '', '', 0),
(369, '', '', '', '', '', '', 0),
(370, '', '', '', '', '', '', 0),
(371, '', '', '', '', '', '', 0),
(372, '', '', '', '', '', '', 0),
(373, '', '', '', '', '', '', 0),
(374, '', '', '', '', '', '', 0),
(375, '', '', '', '', '', '', 0),
(376, '', '', '', '', '', '', 0),
(377, '', '', '', '', '', '', 0),
(378, '', '', '', '', '', '', 0),
(379, '', '', '', '', '', '', 0),
(380, '', '', '', '', '', '', 0),
(381, '', '', '', '', '', '', 0),
(382, '', '', '', '', '', '', 0),
(383, '', '', '', '', '', '', 0),
(384, '', '', '', '', '', '', 0),
(385, '', '', '', '', '', '', 0),
(386, '', '', '', '', '', '', 0),
(387, '', '', '', '', '', '', 0),
(388, '', '', '', '', '', '', 0),
(389, '', '', '', '', '', '', 0),
(390, '', '', '', '', '', '', 0),
(391, '', '', '', '', '', '', 0),
(392, '', '', '', '', '', '', 0),
(393, '', '', '', '', '', '', 0),
(394, '', '', '', '', '', '', 0),
(395, '', '', '', '', '', '', 0),
(396, '', '', '', '', '', '', 0),
(397, '', '', '', '', '', '', 0),
(398, '', '', '', '', '', '', 0),
(399, '', '', '', '', '', '', 0),
(400, '', '', '', '', '', '', 0),
(401, '', '', '', '', '', '', 0),
(402, '', '', '', '', '', '', 0),
(403, '', '', '', '', '', '', 0),
(404, '', '', '', '', '', '', 0),
(405, '', '', '', '', '', '', 0),
(406, '', '', '', '', '', '', 0),
(407, '', '', '', '', '', '', 0),
(408, '', '', '', '', '', '', 0),
(409, '', '', '', '', '', '', 0),
(410, '', '', '', '', '', '', 0),
(411, '', '', '', '', '', '', 0),
(412, '', '', '', '', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customreq`
--

DROP TABLE IF EXISTS `customreq`;
CREATE TABLE IF NOT EXISTS `customreq` (
  `cdid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `cdname` varchar(90) NOT NULL,
  `cdtype` varchar(90) NOT NULL,
  `cdes` text NOT NULL,
  `cddate` date NOT NULL,
  `cdimg` varchar(2000) NOT NULL,
  PRIMARY KEY (`cdid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedb`
--

DROP TABLE IF EXISTS `feedb`;
CREATE TABLE IF NOT EXISTS `feedb` (
  `fid` int NOT NULL AUTO_INCREMENT,
  `tid` int NOT NULL,
  `uid` int NOT NULL,
  `feedbck` text NOT NULL,
  `rate` int NOT NULL,
  PRIMARY KEY (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedb`
--

INSERT INTO `feedb` (`fid`, `tid`, `uid`, `feedbck`, `rate`) VALUES
(33, 14, 418, 'aaaaaaaaaaaaaaaaaa', 5),
(34, 14, 418, 'good', 4),
(35, 16, 418, 'awesome', 4);

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
CREATE TABLE IF NOT EXISTS `login` (
  `lid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `uname` varchar(30) NOT NULL,
  `upass` varchar(50) NOT NULL,
  `utype` varchar(20) NOT NULL,
  `reset_token` varchar(50) NOT NULL,
  `token_expiry` varchar(50) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM AUTO_INCREMENT=424 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`lid`, `uid`, `uname`, `upass`, `utype`, `reset_token`, `token_expiry`) VALUES
(423, 419, 'srths.32@gmail.com', '12345678', 'customer', '', ''),
(422, 22, 'athuljo555@gmail.com', '12345678', 'tailor', '', ''),
(421, 21, 'nandanabiju111@gmail.com', '12345678', 'tailor', '', ''),
(420, 20, 'akhilrajsheeja456@gmail.com', '12345678', 'tailor', '', ''),
(419, 418, 'akhilrajakhil0987@gmail.com', '12345678', 'customer', '', ''),
(417, 417, 'akhilrajsheeja455@gmail.com', '12345678', 'customer', 'd45c1b30321463e6e0aa03e5d5d7d777ed6a2a8b46a9622804', '2025-08-01 19:57:42'),
(416, 19, 'zahidmuhammed001@gmail.com', '12345678', 'tailor', '', ''),
(414, 415, 'amal344@gmail.com', '$2y$10$hp8sOJp0MnBCfgA3kMIToeM838sxMbErS9AxdDGSrlY', 'customer', '', ''),
(415, 416, 'adwaith@gmail.com', '12345678', 'customer', '', ''),
(412, 18, 'nayana@gmail.com', '12345678', 'tailor', '', '');

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
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `measurements`
--

INSERT INTO `measurements` (`mid`, `uid`, `height`, `weight`, `neck`, `shoulder`, `chest`, `bust`, `waist`, `hip`, `arm_length`, `sleeve_length`, `bicep`, `wrist`, `thigh`, `knee`, `calf`, `inseam`, `outseam`, `ankle`) VALUES
(25, 25, 165, '50', '12', '20', '32', 'nil', '32', '39', '30', '28', '14', '5', '16', '10', '8', '30', '25', '4'),
(26, 418, 90, '90', '66', '23', '90', '45445', '4545', '23', '34', '45', '45', '56', '88', '77', '90', '45', '44', '44'),
(27, 419, 180, '45', '23', '23', '23', '23', '23', '23', '12', '12', '12', '12', '12', '12', '12', '12', '12', '12');

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
  PRIMARY KEY (`oid`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orderdesign`
--

INSERT INTO `orderdesign` (`oid`, `uid`, `did`, `ostatus`, `orderdate`) VALUES
(99, 418, 25, 'Paid', '2025-08-03'),
(98, 418, 25, 'Paid', '2025-08-03'),
(95, 418, 22, 'Paid', '2025-08-03'),
(97, 418, 24, 'Paid', '2025-08-03'),
(96, 418, 24, 'Paid', '2025-08-03'),
(100, 418, 25, 'Paid', '2025-08-04'),
(101, 419, 25, 'Paid', '2025-08-04');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `pid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `order_id` int NOT NULL,
  `card_name` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `card_no` int NOT NULL,
  `carexp_dt` date NOT NULL,
  `cvv` int NOT NULL,
  `pdate` varchar(20) NOT NULL,
  `pstatus` varchar(11) NOT NULL,
  `pmode` varchar(20) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`pid`, `uid`, `order_id`, `card_name`, `card_no`, `carexp_dt`, `cvv`, `pdate`, `pstatus`, `pmode`) VALUES
(76, 418, 43, 'Debit Card', 1111, '2028-12-31', 123, '2025-08-03 13:49:50', 'Paid', 'Card'),
(75, 418, 95, 'Credit Card', 1111, '0000-00-00', 111, '2025-08-03', 'Paid', 'Card'),
(74, 418, 0, '', 0, '0000-00-00', 0, '2025-08-03', 'Paid', ''),
(77, 418, 44, 'Debit Card', 1111, '2028-12-31', 111, '2025-08-03 14:01:05', 'Paid', 'Card'),
(78, 418, 96, 'Credit Card', 1111, '0000-00-00', 123, '2025-08-03', 'Paid', 'Card'),
(79, 418, 97, 'Credit Card', 1111, '0000-00-00', 111, '2025-08-03', 'Paid', 'Card'),
(80, 418, 41, 'Debit Card', 1111, '2026-08-31', 111, '2025-08-03 17:06:29', 'Paid', 'Card'),
(81, 418, 98, 'Debit Card', 1111, '0000-00-00', 111, '2025-08-03', 'Paid', 'Card'),
(82, 418, 99, 'Debit Card', 1111, '0000-00-00', 123, '2025-08-03', 'Paid', 'Card'),
(83, 418, 46, 'Debit Card', 1111, '0000-00-00', 123, '2025-08-03', 'Paid', 'Card'),
(84, 418, 100, 'Credit Card', 1234, '0000-00-00', 123, '2025-08-04', 'Paid', 'Card'),
(85, 419, 101, 'Credit Card', 2334, '0000-00-00', 111, '2025-08-04', 'Paid', 'Card'),
(86, 419, 48, 'Credit Card', 1111, '0000-00-00', 111, '2025-08-04', 'Paid', 'Card');

-- --------------------------------------------------------

--
-- Table structure for table `reqdesign`
--

DROP TABLE IF EXISTS `reqdesign`;
CREATE TABLE IF NOT EXISTS `reqdesign` (
  `rid` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `did` int NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reqdesign`
--

INSERT INTO `reqdesign` (`rid`, `uid`, `did`) VALUES
(15, 418, 23);

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
  `sneck` varchar(50) DEFAULT NULL,
  `sshoulder` varchar(50) DEFAULT NULL,
  `ssleeve` varchar(50) DEFAULT NULL,
  `sinstructions` varchar(255) DEFAULT NULL,
  `scustom` text,
  `spriority` varchar(50) DEFAULT 'Standard',
  `sddate` date DEFAULT NULL,
  `simg` text,
  `scomments` text,
  `sprice` decimal(10,2) DEFAULT NULL,
  `sstatus` varchar(50) DEFAULT 'Pending',
  PRIMARY KEY (`sdid`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stitchreq`
--

INSERT INTO `stitchreq` (`sdid`, `tid`, `uid`, `sdname`, `sdtype`, `sfabric`, `scolor`, `spattern`, `sneck`, `sshoulder`, `ssleeve`, `sinstructions`, `scustom`, `spriority`, `sddate`, `simg`, `scomments`, `sprice`, `sstatus`) VALUES
(41, 18, 418, 'Anarkali Suit', 'Suit', 'Silk', 'Green', 'Embroidered', 'Collared', 'Structured', 'Long Sleeve', 'nothing', 'aaaaaaaaaaaaaa', 'Standard', '2025-08-10', '', '0', 1300.00, 'Paid'),
(42, 20, 418, 'Anarkali Suit', 'Kurti', 'Linen', 'Blue', 'Checked', 'Other', 'Other', 'Sleeveless', 'nothing', 'aaaaaaaaaaaaaaaaa', 'Standard', '2025-08-28', '', '0', 1200.00, 'Pending'),
(43, 21, 418, 'Office suit', 'Suit', 'Linen', 'Black', 'Printed', 'Collared', 'Structured', 'Long Sleeve', 'nill', 'aaaaaaaaa', 'Standard', '2025-08-27', 'uploads/1754216117_688f36b59666d_wedding.jpg', '0', 1200.00, 'Paid'),
(46, 18, 418, 'Anarkali Suit', 'Suit', 'Polyester', 'Black', 'Printed', 'Collared', 'Other', 'Half Sleeve', 'nothing', 'qqqqqqqqqqqqq', 'Standard', '2025-08-28', 'uploads/1754230416_688f6e90a4cb0_1blackshirt.jpg', '0', 1000.00, 'Paid'),
(44, 21, 418, 'shirt', 'Other', 'Linen', 'Black', 'Checked', 'Standard', 'Standard', 'Standard', 'nothing', 'aaaaaaaaaaaaaaaa', 'Standard', '2025-08-20', 'uploads/1754216861_688f399d71cb5_topcotton.jpg', '0', 1200.00, 'Paid'),
(45, 18, 418, 'nill', 'Suit', 'Linen', 'Green', 'Striped', 'Mandarin', 'Structured', 'Long Sleeve', 'nothing', 'qqqqqqqqqqq', 'Standard', '2025-08-27', 'uploads/1754228791_688f6837bf80c_wedding.jpg', '0', 1200.00, 'Accepted'),
(47, 18, 418, 'Office suit', 'Kurti', 'Linen', 'Green', 'Striped', 'Square Neck', 'Drop Shoulder', 'Three Quarter Sleeve', 'nothing', 'qqqqqqqqqqqqqqqqqqqqqq', 'Standard', '2025-08-19', 'uploads/1754230502_688f6ee665aee_wedding.jpg', '0', 1200.00, 'Rejected'),
(48, 18, 419, 'Anarkali Suit', 'Kurti', 'Silk', 'Black', 'Solid', 'V-Neck', 'Other', 'Three Quarter Sleeve', 'add buttons', 'embroidery on sleeves', 'Standard', '2025-08-27', 'uploads/1754299354_68907bdae8144_lehenga.jpg', '0', 1300.00, 'Paid');

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
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `treg`
--

INSERT INTO `treg` (`tid`, `tname`, `email`, `address`, `city`, `distri`, `pinc`, `phone`, `spect`, `quali`) VALUES
(15, 'Akhil S', 'akhilrajsheeja455@gmail.com', 'Kezhakethu Tharayil House ponnezha po kurathikad', 'mavelikara', 'Alappuzha', 690505, '24354789922', 'Formal', 'nift'),
(14, 'Nandana Biju', 'nandanabiju111@gmail.com', 'padinjare bhagathu puliyoor po chengannur', 'chengannur', 'Alappuzha', 689510, '9562811353', 'All', 'nift'),
(16, 'Nandana Biju', 'nandanabiju@gmail.com', 'pulimada  po mavelikara', 'kayamkulam', 'Thrissur', 345678, '8113018358', 'Formal', 'nift'),
(18, 'Nayana P', 'nayana@gmail.com', 'olakettiyill maveilkara', 'mavelikara', 'Alappuzha', 345678, '0987437899', 'Formal', '10_years_experience'),
(19, 'Zahid Muhammad', 'zahidmuhammed001@gmail.com', 'jinnabhavan kollam', 'mavelikara', 'Pathanamthitta', 345678, '9090090900', 'Bride&Groom wear', 'nift'),
(20, 'Zahid Muhammad', 'akhilrajsheeja456@gmail.com', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'chengannur', 'Kannur', 345678, '9090090900', 'Traditional', 'nift'),
(21, 'Nandana Biju', 'nandanabiju111@gmail.com', 'puliyoor aaaaaaaa', 'kayamkulam', 'Idukki', 345678, '9090090900', 'Traditional', 'pg_fashion_design'),
(22, 'Athul Jo Yohannan', 'athuljo555@gmail.com', 'aaaaaaaaaaaaaaaaaa', 'kayamkulam', 'Malappuram', 111111, '1111111111', 'Formal', '5_years_experience');

-- --------------------------------------------------------

--
-- Table structure for table `upload`
--

DROP TABLE IF EXISTS `upload`;
CREATE TABLE IF NOT EXISTS `upload` (
  `did` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `dname` varchar(50) NOT NULL,
  `dtype` varchar(70) NOT NULL,
  `ddesc` text NOT NULL,
  `dprice` int NOT NULL,
  `dimg` varchar(100) NOT NULL,
  PRIMARY KEY (`did`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `upload`
--

INSERT INTO `upload` (`did`, `uid`, `dname`, `dtype`, `ddesc`, `dprice`, `dimg`) VALUES
(23, 21, 'traditional', 'saree', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 5000, 'uploads/1754154301_688e453d86b7c.jpg'),
(22, 21, 'modern', 'saree', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 5500, 'uploads/1754154266_688e451a34c9d.jpg'),
(25, 18, 'traditional', 'cord set', 'aaaaaaaaaaaaaaaaaaaaaaa', 3000, 'uploads/1754242803_688f9ef369323.jpg'),
(26, 21, 'casual', 'cord set', 'aaaaaaaaaaaaaaaa', 550, 'uploads/1754300108_68907ecc43b60.jpg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
