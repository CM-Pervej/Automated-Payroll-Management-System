-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2024 at 08:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payroll`
--

-- --------------------------------------------------------

--
-- Table structure for table `addduty`
--

CREATE TABLE `addduty` (
  `id` int(11) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `addSalary` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addduty`
--

INSERT INTO `addduty` (`id`, `designation`, `addSalary`, `created_at`) VALUES
(1, 'No Additional Duty', 0.00, '2024-10-09 12:41:23'),
(2, 'অনুষদের ডিন', 4500.00, '2024-10-09 12:42:52'),
(3, 'ইনস্টিটিউটের পরিচালক', 4500.00, '2024-10-09 12:44:37'),
(4, 'পরিচালক (আই কিউ এ সি)', 4500.00, '2024-10-09 12:46:02'),
(5, 'বিভাগীয় চেয়ারম্যান', 4000.00, '2024-10-09 12:46:46'),
(6, 'প্রভোস্ট', 4000.00, '2024-10-09 12:47:28'),
(7, 'প্রক্টর', 4000.00, '2024-10-09 12:50:37'),
(8, 'পরিচালক (ছাত্র পরামর্শ ও নির্দেশনা)', 4000.00, '2024-10-09 12:52:10'),
(9, 'পরিচালক (সিএসআইআরএল)', 4000.00, '2024-10-09 12:54:52'),
(10, 'অতিরিক্ত পরিচালক (আইকিউএসি)', 4000.00, '2024-10-09 12:58:48'),
(11, 'পরিবাহন প্রশাসক', 4000.00, '2024-10-09 13:01:15'),
(12, 'পরিচালক (রোভার / টি এস সি / বি এন সিসি)', 3000.00, '2024-10-09 13:04:31'),
(13, 'পরিচালক (কম্পিউটার সেন্টার)', 3000.00, '2024-10-09 13:06:57'),
(14, 'উপাচার্য মহোদয়ের উপদেষ্টা', 2500.00, '2024-10-09 13:08:01'),
(15, 'উপদেষ্টা (রিসার্স সেল)', 2500.00, '2024-10-09 13:08:56'),
(16, 'উপ-পরিচালক (ইনস্টিটিউট)', 2500.00, '2024-10-09 13:09:44'),
(17, 'উপ-পরিচালক (সিএসআইআরএল)', 2500.00, '2024-10-09 13:10:56'),
(18, 'সহকারী প্রভোস্ট', 2500.00, '2024-10-09 13:11:33'),
(19, 'সহকারী প্রক্টর', 2500.00, '2024-10-09 13:12:22'),
(20, 'সহকারী পরিচালক (টি এস সি)', 2000.00, '2024-10-09 13:13:23'),
(21, 'সহকারী পরিচালক (সিএসআইআরএল)', 2000.00, '2024-10-09 13:13:56'),
(22, 'সহকারী পরিচালক (ছাত্র পরামর্শ ও নির্দেশনা)', 2000.00, '2024-10-09 13:14:16'),
(23, 'পি এস টু ভিসি', 1500.00, '2024-10-10 10:15:16'),
(24, 'গ্রন্থাগারিক', 0.00, '2024-10-10 10:16:36'),
(25, 'পরিচালক (হিসাব)', 0.00, '2024-10-10 10:18:25'),
(26, 'পরিচালক (পরিকল্পনা, উন্নয়ন ও ওয়ার্কস)', 0.00, '2024-10-10 10:19:05'),
(27, 'পরীক্ষা নিয়ন্ত্রক', 0.00, '2024-10-10 10:19:51'),
(28, 'বিশ্ববিদ্যালয় প্রকৌশলী', 0.00, '2024-10-10 10:21:32'),
(29, 'প্রধান চিকিৎসা কর্মকর্তা', 0.00, '2024-10-10 10:22:08'),
(30, 'পরিচালক (শরীরচর্চাশিক্ষা)', 0.00, '2024-10-10 10:22:54'),
(31, 'পরিচালক (ইন্টারন্যাশনাল সার্ভিস সেন্টার)', 0.00, '2024-10-10 10:25:14'),
(32, 'পরিচালক (আইসিটি সেল)', 0.00, '2024-10-10 10:26:01'),
(33, 'রেজিস্ট্রার', 0.00, '2024-10-10 10:31:19'),
(34, 'উপ-রেজিস্ট্রার (সংস্থাপন ও প্রশাসন)', 0.00, '2024-10-10 10:32:17'),
(35, 'উপ-রেজিস্ট্রার (কাউন্সিল)', 0.00, '2024-10-10 10:32:41'),
(36, 'উপ-পরিচালক (অর্থ)', 0.00, '2024-10-10 10:35:03'),
(37, 'উপ-পরিচালক (হিসাব)', 0.00, '2024-10-10 10:35:23'),
(38, 'উপ-পরিচালক (জনসংযোগ)', 0.00, '2024-10-10 10:36:23'),
(39, 'মেডিকেল অফিসার (আবাসিক)', 0.00, '2024-10-10 10:37:12'),
(40, 'সহকারী প্রকৌশলী (যানবাহন পুল)', 0.00, '2024-10-10 10:37:34'),
(41, 'সহকারী পরিচালক (জনসংযোগ ও প্রকাশনা)', 0.00, '2024-10-10 10:41:38'),
(42, 'সহকারী প্রকৌশলী (আবাসিক)', 0.00, '2024-10-10 10:42:09'),
(43, 'সহকারী রেজিস্ট্রার (নিরাপত্তা ও এস্টেট)', 0.00, '2024-10-10 10:42:35'),
(44, 'জনসংযোগ কর্মকর্তা', 0.00, '2024-10-10 10:43:17'),
(45, 'নিরাপত্তা কর্মকর্তা', 0.00, '2024-10-10 10:45:15'),
(46, 'এস্টেট অফিসার', 0.00, '2024-10-10 10:45:39'),
(47, 'বিশ্ববিদ্যালয়ের ফোকাল পয়েন্ট', 0.00, '2024-10-10 10:46:00'),
(59, 'Chanda', 1000.00, '2024-11-10 08:33:16');

-- --------------------------------------------------------

--
-- Table structure for table `allowancelist`
--

CREATE TABLE `allowancelist` (
  `id` int(11) NOT NULL,
  `allwName` varchar(100) NOT NULL,
  `allwPercentage` int(11) NOT NULL,
  `allwValue` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allowancelist`
--

INSERT INTO `allowancelist` (`id`, `allwName`, `allwPercentage`, `allwValue`) VALUES
(1, 'Dearness / Special Allw', 5, 0.00),
(3, 'House Rent Allowance', 35, 0.00),
(4, 'Medical Allowance', 0, 1500.00),
(5, 'Education Allowance', 0, 0.00),
(6, 'Festival Bonus', 100, 0.00),
(7, 'Research Allowance', 0, 0.00),
(8, 'New Bangla Yr. Bonus', 20, 0.00),
(11, 'Recreation Allowance', 100, 0.00),
(12, 'Others', 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `allwconfirm`
--

CREATE TABLE `allwconfirm` (
  `id` int(11) NOT NULL,
  `allwName` varchar(255) DEFAULT NULL,
  `allwTotal` decimal(10,2) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `empAllowance_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allwconfirm`
--

INSERT INTO `allwconfirm` (`id`, `allwName`, `allwTotal`, `employee_id`, `empAllowance_id`) VALUES
(306, 'Dearness / Special Allw', 3550.00, 13, 379),
(307, 'House Rent Allowance', 12425.00, 13, 380),
(308, 'Medical Allowance', 1500.00, 13, 381),
(309, 'Education Allowance', 0.00, 13, 382),
(310, 'Festival Bonus', 0.00, 13, 383),
(311, 'Research Allowance', 0.00, 13, 384),
(312, 'New Bangla Yr. Bonus', 0.00, 13, 385),
(313, 'Recreation Allowance', 0.00, 13, 386),
(314, 'Others', 0.00, 13, 387),
(333, 'Dearness / Special Allw', 1775.00, 11, 424),
(334, 'House Rent Allowance', 12425.00, 11, 425),
(335, 'Medical Allowance', 1500.00, 11, 426),
(336, 'Education Allowance', 0.00, 11, 427),
(337, 'Festival Bonus', 35500.00, 11, 428),
(338, 'Research Allowance', 0.00, 11, 429),
(339, 'New Bangla Yr. Bonus', 7100.00, 11, 430),
(340, 'Recreation Allowance', 35500.00, 11, 431),
(341, 'Others', 0.00, 11, 432),
(351, 'Dearness / Special Allw', 1100.00, 12, 433),
(352, 'House Rent Allowance', 7700.00, 12, 434),
(353, 'Medical Allowance', 1500.00, 12, 435),
(354, 'Education Allowance', 0.00, 12, 436),
(355, 'Festival Bonus', 22000.00, 12, 437),
(356, 'Research Allowance', 0.00, 12, 438),
(357, 'New Bangla Yr. Bonus', 4400.00, 12, 439),
(358, 'Recreation Allowance', 22000.00, 12, 440),
(359, 'Others', 0.00, 12, 441),
(360, 'Dearness / Special Allw', 1775.00, 14, 388),
(361, 'House Rent Allowance', 12425.00, 14, 389),
(362, 'Medical Allowance', 1500.00, 14, 390),
(363, 'Education Allowance', 0.00, 14, 391),
(364, 'Festival Bonus', 35500.00, 14, 392),
(365, 'Research Allowance', 0.00, 14, 393),
(366, 'New Bangla Yr. Bonus', 7100.00, 14, 394),
(367, 'Recreation Allowance', 35500.00, 14, 395),
(368, 'Others', 0.00, 14, 396),
(369, 'Dearness / Special Allw', 2500.00, 15, 406),
(370, 'House Rent Allowance', 17500.00, 15, 407),
(371, 'Medical Allowance', 1500.00, 15, 408),
(372, 'Education Allowance', 0.00, 15, 409),
(373, 'Festival Bonus', 50000.00, 15, 410),
(374, 'Research Allowance', 0.00, 15, 411),
(375, 'New Bangla Yr. Bonus', 10000.00, 15, 412),
(376, 'Recreation Allowance', 50000.00, 15, 413),
(377, 'Others', 0.00, 15, 414),
(387, 'Dearness / Special Allw', 3825.00, 9, 442),
(388, 'House Rent Allowance', 26772.00, 9, 443),
(389, 'Medical Allowance', 1500.00, 9, 444),
(390, 'Education Allowance', 0.00, 9, 445),
(391, 'Festival Bonus', 0.00, 9, 446),
(392, 'Research Allowance', 0.00, 9, 447),
(393, 'New Bangla Yr. Bonus', 0.00, 9, 448),
(394, 'Recreation Allowance', 0.00, 9, 449),
(395, 'Others', 0.00, 9, 450),
(396, 'Dearness / Special Allw', 3306.00, 16, 451),
(397, 'House Rent Allowance', 23142.00, 16, 452),
(398, 'Medical Allowance', 1500.00, 16, 453),
(399, 'Education Allowance', 1000.00, 16, 454),
(400, 'Festival Bonus', 0.00, 16, 455),
(401, 'Research Allowance', 0.00, 16, 456),
(402, 'New Bangla Yr. Bonus', 0.00, 16, 457),
(403, 'Recreation Allowance', 0.00, 16, 458),
(404, 'Others', 0.00, 16, 459),
(405, 'Dearness / Special Allw', 3728.00, 10, 469),
(406, 'House Rent Allowance', 13048.00, 10, 470),
(407, 'Medical Allowance', 1500.00, 10, 471),
(408, 'Education Allowance', 0.00, 10, 472),
(409, 'Festival Bonus', 0.00, 10, 473),
(410, 'Research Allowance', 0.00, 10, 474),
(411, 'New Bangla Yr. Bonus', 0.00, 10, 475),
(412, 'Recreation Allowance', 0.00, 10, 476),
(413, 'Others', 0.00, 10, 477);

-- --------------------------------------------------------

--
-- Table structure for table `checkemployee`
--

CREATE TABLE `checkemployee` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `employeeNo` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `empStatus` varchar(50) DEFAULT NULL,
  `grade` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `department_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dedconfirm`
--

CREATE TABLE `dedconfirm` (
  `id` int(11) NOT NULL,
  `dedName` varchar(255) DEFAULT NULL,
  `dedTotal` decimal(10,2) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `empDeduction_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dedconfirm`
--

INSERT INTO `dedconfirm` (`id`, `dedName`, `dedTotal`, `employee_id`, `empDeduction_id`) VALUES
(133, 'GPF', 3550.00, 13, 211),
(134, 'GPF Installment: 0 / 0', 0.00, 13, 212),
(135, 'House Rent Deduction', 0.00, 13, 213),
(136, 'Benevolent Fund', 0.00, 13, 214),
(137, 'Insurance Premium', 0.00, 13, 215),
(138, 'Electricity Bill', 0.00, 13, 216),
(139, 'HRD Extra', 0.00, 13, 217),
(140, 'Club Subscription', 0.00, 13, 218),
(141, 'Association Subscription', 0.00, 13, 219),
(142, 'Transport Bill', 0.00, 13, 220),
(143, 'Telephone Bill', 0.00, 13, 221),
(144, 'Pension Fund', 0.00, 13, 222),
(145, 'Fish Bill', 0.00, 13, 223),
(146, 'Income Tax', 0.00, 13, 224),
(147, 'Donation', 0.00, 13, 225),
(148, 'Guest House Rent', 0.00, 13, 226),
(149, 'Home Loan Installment1: 0 / 0', 0.00, 13, 227),
(150, 'Home Loan Installment2: 0 / 0', 0.00, 13, 228),
(151, 'Home Loan Installment3: 0 / 0', 0.00, 13, 229),
(152, 'Salary Adjustment: 0 / 0', 0.00, 13, 230),
(153, 'Others', 0.00, 13, 231),
(154, 'Revenue', 10.00, 13, 232),
(155, 'GPF', 4300.00, 15, 233),
(156, 'GPF Installment: 0 / 0', 0.00, 15, 234),
(157, 'House Rent Deduction', 0.00, 15, 235),
(158, 'Benevolent Fund', 0.00, 15, 236),
(159, 'Insurance Premium', 0.00, 15, 237),
(160, 'Electricity Bill', 0.00, 15, 238),
(161, 'HRD Extra', 0.00, 15, 239),
(162, 'Club Subscription', 0.00, 15, 240),
(163, 'Association Subscription', 0.00, 15, 241),
(164, 'Transport Bill', 0.00, 15, 242),
(165, 'Telephone Bill', 0.00, 15, 243),
(166, 'Pension Fund', 0.00, 15, 244),
(167, 'Fish Bill', 0.00, 15, 245),
(168, 'Income Tax', 0.00, 15, 246),
(169, 'Donation', 0.00, 15, 247),
(170, 'Guest House Rent', 0.00, 15, 248),
(171, 'Home Loan Installment1: 0 / 0', 0.00, 15, 249),
(172, 'Home Loan Installment2: 0 / 0', 0.00, 15, 250),
(173, 'Home Loan Installment3: 0 / 0', 0.00, 15, 251),
(174, 'Salary Adjustment: 0 / 0', 0.00, 15, 252),
(175, 'Others', 0.00, 15, 253),
(176, 'Revenue', 10.00, 15, 254),
(177, 'GPF', 3550.00, 11, 139),
(178, 'GPF Installment: 0 / 0', 0.00, 11, 255),
(179, 'House Rent Deduction', 0.00, 11, 256),
(180, 'Benevolent Fund', 0.00, 11, 140),
(181, 'Insurance Premium', 0.00, 11, 257),
(182, 'Electricity Bill', 0.00, 11, 258),
(183, 'HRD Extra', 0.00, 11, 259),
(184, 'Club Subscription', 0.00, 11, 141),
(185, 'Association Subscription', 0.00, 11, 142),
(186, 'Transport Bill', 0.00, 11, 260),
(187, 'Telephone Bill', 0.00, 11, 261),
(188, 'Pension Fund', 0.00, 11, 262),
(189, 'Fish Bill', 0.00, 11, 263),
(190, 'Income Tax', 0.00, 11, 264),
(191, 'Donation', 0.00, 11, 265),
(192, 'Guest House Rent', 0.00, 11, 266),
(193, 'Home Loan Installment1: 0 / 0', 0.00, 11, 267),
(194, 'Home Loan Installment2: 0 / 0', 0.00, 11, 268),
(195, 'Home Loan Installment3: 0 / 0', 0.00, 11, 269),
(196, 'Salary Adjustment: 0 / 0', 0.00, 11, 270),
(197, 'Others', 0.00, 11, 271),
(198, 'Revenue', 10.00, 11, 143),
(199, 'GPF', 2200.00, 12, 272),
(200, 'GPF Installment: 0 / 0', 0.00, 12, 273),
(201, 'House Rent Deduction', 0.00, 12, 274),
(202, 'Benevolent Fund', 0.00, 12, 275),
(203, 'Insurance Premium', 0.00, 12, 276),
(204, 'Electricity Bill', 0.00, 12, 277),
(205, 'HRD Extra', 0.00, 12, 278),
(206, 'Club Subscription', 0.00, 12, 279),
(207, 'Association Subscription', 0.00, 12, 280),
(208, 'Transport Bill', 0.00, 12, 281),
(209, 'Telephone Bill', 0.00, 12, 282),
(210, 'Pension Fund', 0.00, 12, 283),
(211, 'Fish Bill', 0.00, 12, 284),
(212, 'Income Tax', 0.00, 12, 285),
(213, 'Donation', 0.00, 12, 286),
(214, 'Guest House Rent', 0.00, 12, 287),
(215, 'Home Loan Installment1: 0 / 0', 0.00, 12, 288),
(216, 'Home Loan Installment2: 0 / 0', 0.00, 12, 289),
(217, 'Home Loan Installment3: 0 / 0', 0.00, 12, 290),
(218, 'Salary Adjustment: 0 / 0', 0.00, 12, 291),
(219, 'Others', 0.00, 12, 292),
(220, 'Revenue', 10.00, 12, 293),
(221, 'GPF', 3550.00, 14, 294),
(222, 'GPF Installment: 0 / 0', 0.00, 14, 295),
(223, 'House Rent Deduction', 0.00, 14, 296),
(224, 'Benevolent Fund', 0.00, 14, 297),
(225, 'Insurance Premium', 0.00, 14, 298),
(226, 'Electricity Bill', 0.00, 14, 299),
(227, 'HRD Extra', 0.00, 14, 300),
(228, 'Club Subscription', 0.00, 14, 301),
(229, 'Association Subscription', 0.00, 14, 302),
(230, 'Transport Bill', 0.00, 14, 303),
(231, 'Telephone Bill', 0.00, 14, 304),
(232, 'Pension Fund', 0.00, 14, 305),
(233, 'Fish Bill', 0.00, 14, 306),
(234, 'Income Tax', 0.00, 14, 307),
(235, 'Donation', 0.00, 14, 308),
(236, 'Guest House Rent', 0.00, 14, 309),
(237, 'Home Loan Installment1: 0 / 0', 0.00, 14, 310),
(238, 'Home Loan Installment2: 0 / 0', 0.00, 14, 311),
(239, 'Home Loan Installment3: 0 / 0', 0.00, 14, 312),
(240, 'Salary Adjustment: 0 / 0', 0.00, 14, 313),
(241, 'Others', 0.00, 14, 314),
(242, 'Revenue', 10.00, 14, 315),
(243, 'GPF', 7649.00, 9, 316),
(244, 'GPF Installment: 0 / 0', 0.00, 9, 317),
(245, 'House Rent Deduction', 0.00, 9, 318),
(246, 'Benevolent Fund', 200.00, 9, 319),
(247, 'Insurance Premium', 0.00, 9, 320),
(248, 'Electricity Bill', 0.00, 9, 321),
(249, 'HRD Extra', 0.00, 9, 322),
(250, 'Club Subscription', 30.00, 9, 323),
(251, 'Association Subscription', 150.00, 9, 324),
(252, 'Transport Bill', 200.00, 9, 325),
(253, 'Telephone Bill', 0.00, 9, 326),
(254, 'Pension Fund', 0.00, 9, 327),
(255, 'Fish Bill', 0.00, 9, 328),
(256, 'Income Tax', 3931.00, 9, 329),
(257, 'Donation', 0.00, 9, 330),
(258, 'Guest House Rent', 0.00, 9, 331),
(259, 'Home Loan Installment1: 0 / 0', 0.00, 9, 332),
(260, 'Home Loan Installment2: 0 / 0', 0.00, 9, 333),
(261, 'Home Loan Installment3: 0 / 0', 0.00, 9, 334),
(262, 'Salary Adjustment: 0 / 0', 0.00, 9, 335),
(263, 'Others', 0.00, 9, 336),
(264, 'Revenue', 10.00, 9, 337),
(265, 'GPF', 6612.00, 16, 338),
(266, 'GPF Installment: 0 / 0', 0.00, 16, 339),
(267, 'House Rent Deduction', 0.00, 16, 340),
(268, 'Benevolent Fund', 50.00, 16, 341),
(269, 'Insurance Premium', 0.00, 16, 342),
(270, 'Electricity Bill', 0.00, 16, 343),
(271, 'HRD Extra', 0.00, 16, 344),
(272, 'Club Subscription', 30.00, 16, 345),
(273, 'Association Subscription', 150.00, 16, 346),
(274, 'Transport Bill', 300.00, 16, 347),
(275, 'Telephone Bill', 0.00, 16, 348),
(276, 'Pension Fund', 0.00, 16, 349),
(277, 'Fish Bill', 0.00, 16, 350),
(278, 'Income Tax', 2805.00, 16, 351),
(279, 'Donation', 0.00, 16, 352),
(280, 'Guest House Rent', 0.00, 16, 353),
(281, 'Home Loan Installment1: 0 / 0', 20608.00, 16, 354),
(282, 'Home Loan Installment2: 0 / 0', 0.00, 16, 355),
(283, 'Home Loan Installment3: 0 / 0', 0.00, 16, 356),
(284, 'Salary Adjustment: 0 / 0', 0.00, 16, 357),
(285, 'Others', 0.00, 16, 358),
(286, 'Revenue', 10.00, 16, 359),
(287, 'GPF', 3728.00, 10, 360),
(288, 'GPF Installment: 0 / 0', 0.00, 10, 361),
(289, 'House Rent Deduction', 0.00, 10, 362),
(290, 'Benevolent Fund', 50.00, 10, 363),
(291, 'Insurance Premium', 0.00, 10, 364),
(292, 'Electricity Bill', 0.00, 10, 365),
(293, 'HRD Extra', 0.00, 10, 366),
(294, 'Club Subscription', 30.00, 10, 367),
(295, 'Association Subscription', 150.00, 10, 368),
(296, 'Transport Bill', 0.00, 10, 369),
(297, 'Telephone Bill', 0.00, 10, 370),
(298, 'Pension Fund', 0.00, 10, 371),
(299, 'Fish Bill', 0.00, 10, 372),
(300, 'Income Tax', 250.00, 10, 373),
(301, 'Donation', 0.00, 10, 374),
(302, 'Guest House Rent', 0.00, 10, 375),
(303, 'Home Loan Installment1: 0 / 0', 0.00, 10, 376),
(304, 'Home Loan Installment2: 0 / 0', 0.00, 10, 377),
(305, 'Home Loan Installment3: 0 / 0', 0.00, 10, 378),
(306, 'Salary Adjustment: 0 / 0', 0.00, 10, 379),
(307, 'Others', 0.00, 10, 380),
(308, 'Revenue', 10.00, 10, 381);

-- --------------------------------------------------------

--
-- Table structure for table `deductionlist`
--

CREATE TABLE `deductionlist` (
  `id` int(11) NOT NULL,
  `dedName` varchar(100) NOT NULL,
  `dedPercentage` int(11) NOT NULL,
  `dedValue` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deductionlist`
--

INSERT INTO `deductionlist` (`id`, `dedName`, `dedPercentage`, `dedValue`) VALUES
(1, 'GPF', 10, 0.00),
(2, 'GPF Installment: 0 / 0', 0, 0.00),
(3, 'House Rent Deduction', 0, 0.00),
(4, 'Benevolent Fund', 0, 0.00),
(5, 'Insurance Premium', 0, 0.00),
(6, 'Electricity Bill', 0, 0.00),
(7, 'HRD Extra', 0, 0.00),
(8, 'Club Subscription', 0, 0.00),
(9, 'Association Subscription', 0, 0.00),
(10, 'Transport Bill', 0, 0.00),
(11, 'Telephone Bill', 0, 0.00),
(12, 'Pension Fund', 0, 0.00),
(13, 'Fish Bill', 0, 0.00),
(14, 'Income Tax', 0, 0.00),
(15, 'Donation', 0, 0.00),
(16, 'Guest House Rent', 0, 0.00),
(17, 'Home Loan Installment1: 0 / 0', 0, 0.00),
(18, 'Home Loan Installment2: 0 / 0', 0, 0.00),
(19, 'Home Loan Installment3: 0 / 0', 0, 0.00),
(20, 'Salary Adjustment: 0 / 0', 0, 0.00),
(21, 'Others', 0, 0.00),
(22, 'Revenue', 0, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`) VALUES
(1, 'Biomedical Engineering'),
(2, 'Chemical Engineering'),
(3, 'Computer Science and Engineering'),
(4, 'Electrical and Electronic Engineering'),
(5, 'Industrial and Production Engineering'),
(6, 'Petroleum and Mining Engineering'),
(7, 'Textile Engineering'),
(8, 'Agro Product Processing Technology'),
(9, 'Climate and Disaster Management'),
(10, 'Environmental Science and Technology'),
(11, 'Nutrition and Food Technology'),
(12, 'Biochemistry and Molecular Biology'),
(13, 'Fisheries and Marine Bioscience'),
(14, 'Genetic Engineering and Biotechnology'),
(15, 'Microbiology'),
(16, 'Pharmacy'),
(17, 'Nursing and Health Science'),
(18, 'Physical Education and Sports Science'),
(19, 'Physiotherapy and Rehabilitation'),
(20, 'English'),
(21, 'Applied Statistics'),
(22, 'Chemistry'),
(23, 'Mathematics'),
(24, 'Physics'),
(25, 'Accounting and Information Systems'),
(26, 'Finance and Banking'),
(27, 'Management'),
(28, 'Marketing'),
(29, 'Office of the Accounting');

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` int(11) NOT NULL,
  `designation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `designation`) VALUES
(1, 'Professor'),
(2, 'Associate Professor'),
(3, 'Assistant Professor'),
(4, 'Lecturer'),
(5, 'Director'),
(6, 'Deputy Director'),
(8, 'Associate Director');

-- --------------------------------------------------------

--
-- Table structure for table `empadddesignation`
--

CREATE TABLE `empadddesignation` (
  `id` int(11) NOT NULL,
  `empAddSalary_id` int(11) NOT NULL,
  `addDuty_id` int(11) NOT NULL,
  `AdditionalDesignation` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empadddesignation`
--

INSERT INTO `empadddesignation` (`id`, `empAddSalary_id`, `addDuty_id`, `AdditionalDesignation`) VALUES
(-227577996, -415269372, -81640593, 'nsol');

-- --------------------------------------------------------

--
-- Table structure for table `empaddsalary`
--

CREATE TABLE `empaddsalary` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `chargeAllw` decimal(10,2) NOT NULL,
  `telephoneAllwance` decimal(10,2) NOT NULL,
  `telephoneAllw_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empaddsalary`
--

INSERT INTO `empaddsalary` (`id`, `employee_id`, `chargeAllw`, `telephoneAllwance`, `telephoneAllw_id`, `created_at`) VALUES
(39, 11, 4000.00, 1500.00, 26, '2024-10-31 19:35:03'),
(40, 13, 4500.00, 1500.00, 2, '2024-11-10 08:50:37'),
(41, 15, 4500.00, 1500.00, 6, '2024-11-10 09:16:07'),
(42, 12, 0.00, 0.00, 1, '2024-11-11 17:59:00'),
(43, 14, 0.00, 0.00, 1, '2024-11-11 17:59:24'),
(44, 9, 4000.00, 1500.00, 14, '2024-11-12 16:56:02'),
(45, 16, 5500.00, 1500.00, 26, '2024-11-12 17:07:36'),
(46, 10, 0.00, 0.00, 1, '2024-11-12 17:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `empallowance`
--

CREATE TABLE `empallowance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `allowanceList_id` int(11) NOT NULL,
  `allwName` varchar(100) NOT NULL,
  `allwPercentage` decimal(10,2) NOT NULL,
  `allwValue` decimal(10,2) NOT NULL,
  `allwTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empallowance`
--

INSERT INTO `empallowance` (`id`, `employee_id`, `allowanceList_id`, `allwName`, `allwPercentage`, `allwValue`, `allwTotal`) VALUES
(379, 13, 1, 'Dearness / Special Allw', 10.00, 0.00, 3728.00),
(380, 13, 3, 'House Rent Allowance', 35.00, 0.00, 13048.00),
(381, 13, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(382, 13, 5, 'Education Allowance', 0.00, 0.00, 0.00),
(383, 13, 6, 'Festival Bonus', 0.00, 0.00, 0.00),
(384, 13, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(385, 13, 8, 'New Bangla Yr. Bonus', 0.00, 0.00, 0.00),
(386, 13, 11, 'Recreation Allowance', 0.00, 0.00, 0.00),
(387, 13, 12, 'Others', 0.00, 0.00, 0.00),
(388, 14, 1, 'Dearness / Special Allw', 5.00, 0.00, 1775.00),
(389, 14, 3, 'House Rent Allowance', 35.00, 0.00, 12425.00),
(390, 14, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(391, 14, 5, 'Education Allowance', 0.00, 0.00, 0.00),
(392, 14, 6, 'Festival Bonus', 100.00, 0.00, 35500.00),
(393, 14, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(394, 14, 8, 'New Bangla Yr. Bonus', 20.00, 0.00, 7100.00),
(395, 14, 11, 'Recreation Allowance', 100.00, 0.00, 35500.00),
(396, 14, 12, 'Others', 0.00, 0.00, 0.00),
(406, 15, 1, 'Dearness / Special Allw', 5.00, 0.00, 2500.00),
(407, 15, 3, 'House Rent Allowance', 35.00, 0.00, 17500.00),
(408, 15, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(409, 15, 5, 'Education Allowance', 0.00, 0.00, 0.00),
(410, 15, 6, 'Festival Bonus', 100.00, 0.00, 50000.00),
(411, 15, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(412, 15, 8, 'New Bangla Yr. Bonus', 20.00, 0.00, 10000.00),
(413, 15, 11, 'Recreation Allowance', 100.00, 0.00, 50000.00),
(414, 15, 12, 'Others', 0.00, 0.00, 0.00),
(424, 11, 1, 'Dearness / Special Allw', 5.00, 0.00, 1775.00),
(425, 11, 3, 'House Rent Allowance', 35.00, 0.00, 12425.00),
(426, 11, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(427, 11, 5, 'Education Allowance', 0.00, 0.00, 0.00),
(428, 11, 6, 'Festival Bonus', 100.00, 0.00, 35500.00),
(429, 11, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(430, 11, 8, 'New Bangla Yr. Bonus', 20.00, 0.00, 7100.00),
(431, 11, 11, 'Recreation Allowance', 100.00, 0.00, 35500.00),
(432, 11, 12, 'Others', 0.00, 0.00, 0.00),
(433, 12, 1, 'Dearness / Special Allw', 5.00, 0.00, 1100.00),
(434, 12, 3, 'House Rent Allowance', 35.00, 0.00, 7700.00),
(435, 12, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(436, 12, 5, 'Education Allowance', 0.00, 0.00, 0.00),
(437, 12, 6, 'Festival Bonus', 100.00, 0.00, 22000.00),
(438, 12, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(439, 12, 8, 'New Bangla Yr. Bonus', 20.00, 0.00, 4400.00),
(440, 12, 11, 'Recreation Allowance', 100.00, 0.00, 22000.00),
(441, 12, 12, 'Others', 0.00, 0.00, 0.00),
(442, 9, 1, 'Dearness / Special Allw', 5.00, 0.00, 3825.00),
(443, 9, 3, 'House Rent Allowance', 35.00, 0.00, 26772.00),
(444, 9, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(445, 9, 5, 'Education Allowance', 0.00, 0.00, 0.00),
(446, 9, 6, 'Festival Bonus', 0.00, 0.00, 0.00),
(447, 9, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(448, 9, 8, 'New Bangla Yr. Bonus', 0.00, 0.00, 0.00),
(449, 9, 11, 'Recreation Allowance', 0.00, 0.00, 0.00),
(450, 9, 12, 'Others', 0.00, 0.00, 0.00),
(451, 16, 1, 'Dearness / Special Allw', 5.00, 0.00, 3306.00),
(452, 16, 3, 'House Rent Allowance', 35.00, 0.00, 23142.00),
(453, 16, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(454, 16, 5, 'Education Allowance', 0.00, 1000.00, 1000.00),
(455, 16, 6, 'Festival Bonus', 0.00, 0.00, 0.00),
(456, 16, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(457, 16, 8, 'New Bangla Yr. Bonus', 0.00, 0.00, 0.00),
(458, 16, 11, 'Recreation Allowance', 0.00, 0.00, 0.00),
(459, 16, 12, 'Others', 0.00, 0.00, 0.00),
(469, 10, 1, 'Dearness / Special Allw', 10.00, 0.00, 3728.00),
(470, 10, 3, 'House Rent Allowance', 35.00, 0.00, 13048.00),
(471, 10, 4, 'Medical Allowance', 0.00, 1500.00, 1500.00),
(472, 10, 5, 'Education Allowance', 0.00, 0.00, 0.00),
(473, 10, 6, 'Festival Bonus', 0.00, 0.00, 0.00),
(474, 10, 7, 'Research Allowance', 0.00, 0.00, 0.00),
(475, 10, 8, 'New Bangla Yr. Bonus', 0.00, 0.00, 0.00),
(476, 10, 11, 'Recreation Allowance', 0.00, 0.00, 0.00),
(477, 10, 12, 'Others', 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `empdeduction`
--

CREATE TABLE `empdeduction` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `deductionList_id` int(11) NOT NULL,
  `dedName` varchar(100) NOT NULL,
  `dedPercentage` decimal(10,2) NOT NULL,
  `dedValue` decimal(10,2) NOT NULL,
  `dedTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empdeduction`
--

INSERT INTO `empdeduction` (`id`, `employee_id`, `deductionList_id`, `dedName`, `dedPercentage`, `dedValue`, `dedTotal`) VALUES
(139, 11, 1, 'GPF', 10.00, 0.00, 3550.00),
(140, 11, 4, 'Benevolent Fund', 0.00, 0.00, 0.00),
(141, 11, 8, 'Club Subscription', 0.00, 0.00, 0.00),
(142, 11, 9, 'Association Subscription', 0.00, 0.00, 0.00),
(143, 11, 22, 'Revenue', 0.00, 10.00, 10.00),
(211, 13, 1, 'GPF', 10.00, 0.00, 3728.00),
(212, 13, 2, 'GPF Installment: 0 / 0', 0.00, 0.00, 0.00),
(213, 13, 3, 'House Rent Deduction', 0.00, 0.00, 0.00),
(214, 13, 4, 'Benevolent Fund', 0.00, 0.00, 0.00),
(215, 13, 5, 'Insurance Premium', 0.00, 0.00, 0.00),
(216, 13, 6, 'Electricity Bill', 0.00, 0.00, 0.00),
(217, 13, 7, 'HRD Extra', 0.00, 0.00, 0.00),
(218, 13, 8, 'Club Subscription', 0.00, 0.00, 0.00),
(219, 13, 9, 'Association Subscription', 0.00, 0.00, 0.00),
(220, 13, 10, 'Transport Bill', 0.00, 0.00, 0.00),
(221, 13, 11, 'Telephone Bill', 0.00, 0.00, 0.00),
(222, 13, 12, 'Pension Fund', 0.00, 0.00, 0.00),
(223, 13, 13, 'Fish Bill', 0.00, 0.00, 0.00),
(224, 13, 14, 'Income Tax', 0.00, 0.00, 0.00),
(225, 13, 15, 'Donation', 0.00, 0.00, 0.00),
(226, 13, 16, 'Guest House Rent', 0.00, 0.00, 0.00),
(227, 13, 17, 'Home Loan Installment1: 0 / 0', 0.00, 0.00, 0.00),
(228, 13, 18, 'Home Loan Installment2: 0 / 0', 0.00, 0.00, 0.00),
(229, 13, 19, 'Home Loan Installment3: 0 / 0', 0.00, 0.00, 0.00),
(230, 13, 20, 'Salary Adjustment: 0 / 0', 0.00, 0.00, 0.00),
(231, 13, 21, 'Others', 0.00, 0.00, 0.00),
(232, 13, 22, 'Revenue', 0.00, 10.00, 10.00),
(233, 15, 1, '', 0.00, 0.00, 0.00),
(234, 15, 2, '', 0.00, 0.00, 0.00),
(235, 15, 3, '', 0.00, 0.00, 0.00),
(236, 15, 4, '', 0.00, 0.00, 0.00),
(237, 15, 5, '', 0.00, 0.00, 0.00),
(238, 15, 6, '', 0.00, 0.00, 0.00),
(239, 15, 7, '', 0.00, 0.00, 0.00),
(240, 15, 8, '', 0.00, 0.00, 0.00),
(241, 15, 9, '', 0.00, 0.00, 0.00),
(242, 15, 10, '', 0.00, 0.00, 0.00),
(243, 15, 11, '', 0.00, 0.00, 0.00),
(244, 15, 12, '', 0.00, 0.00, 0.00),
(245, 15, 13, '', 0.00, 0.00, 0.00),
(246, 15, 14, '', 0.00, 0.00, 0.00),
(247, 15, 15, '', 0.00, 0.00, 0.00),
(248, 15, 16, '', 0.00, 0.00, 0.00),
(249, 15, 17, '', 0.00, 0.00, 0.00),
(250, 15, 18, '', 0.00, 0.00, 0.00),
(251, 15, 19, '', 0.00, 0.00, 0.00),
(252, 15, 20, '', 0.00, 0.00, 0.00),
(253, 15, 21, '', 0.00, 0.00, 0.00),
(254, 15, 22, '', 0.00, 0.00, 0.00),
(255, 11, 2, 'GPF Installment: 0 / 0', 0.00, 0.00, 0.00),
(256, 11, 3, 'House Rent Deduction', 0.00, 0.00, 0.00),
(257, 11, 5, 'Insurance Premium', 0.00, 0.00, 0.00),
(258, 11, 6, 'Electricity Bill', 0.00, 0.00, 0.00),
(259, 11, 7, 'HRD Extra', 0.00, 0.00, 0.00),
(260, 11, 10, 'Transport Bill', 0.00, 0.00, 0.00),
(261, 11, 11, 'Telephone Bill', 0.00, 0.00, 0.00),
(262, 11, 12, 'Pension Fund', 0.00, 0.00, 0.00),
(263, 11, 13, 'Fish Bill', 0.00, 0.00, 0.00),
(264, 11, 14, 'Income Tax', 0.00, 0.00, 0.00),
(265, 11, 15, 'Donation', 0.00, 0.00, 0.00),
(266, 11, 16, 'Guest House Rent', 0.00, 0.00, 0.00),
(267, 11, 17, 'Home Loan Installment1: 0 / 0', 0.00, 0.00, 0.00),
(268, 11, 18, 'Home Loan Installment2: 0 / 0', 0.00, 0.00, 0.00),
(269, 11, 19, 'Home Loan Installment3: 0 / 0', 0.00, 0.00, 0.00),
(270, 11, 20, 'Salary Adjustment: 0 / 0', 0.00, 0.00, 0.00),
(271, 11, 21, 'Others', 0.00, 0.00, 0.00),
(272, 12, 1, 'GPF', 10.00, 0.00, 2200.00),
(273, 12, 2, 'GPF Installment: 0 / 0', 0.00, 0.00, 0.00),
(274, 12, 3, 'House Rent Deduction', 0.00, 0.00, 0.00),
(275, 12, 4, 'Benevolent Fund', 0.00, 0.00, 0.00),
(276, 12, 5, 'Insurance Premium', 0.00, 0.00, 0.00),
(277, 12, 6, 'Electricity Bill', 0.00, 0.00, 0.00),
(278, 12, 7, 'HRD Extra', 0.00, 0.00, 0.00),
(279, 12, 8, 'Club Subscription', 0.00, 0.00, 0.00),
(280, 12, 9, 'Association Subscription', 0.00, 0.00, 0.00),
(281, 12, 10, 'Transport Bill', 0.00, 0.00, 0.00),
(282, 12, 11, 'Telephone Bill', 0.00, 0.00, 0.00),
(283, 12, 12, 'Pension Fund', 0.00, 0.00, 0.00),
(284, 12, 13, 'Fish Bill', 0.00, 0.00, 0.00),
(285, 12, 14, 'Income Tax', 0.00, 0.00, 0.00),
(286, 12, 15, 'Donation', 0.00, 0.00, 0.00),
(287, 12, 16, 'Guest House Rent', 0.00, 0.00, 0.00),
(288, 12, 17, 'Home Loan Installment1: 0 / 0', 0.00, 0.00, 0.00),
(289, 12, 18, 'Home Loan Installment2: 0 / 0', 0.00, 0.00, 0.00),
(290, 12, 19, 'Home Loan Installment3: 0 / 0', 0.00, 0.00, 0.00),
(291, 12, 20, 'Salary Adjustment: 0 / 0', 0.00, 0.00, 0.00),
(292, 12, 21, 'Others', 0.00, 0.00, 0.00),
(293, 12, 22, 'Revenue', 0.00, 10.00, 10.00),
(294, 14, 1, 'GPF', 10.00, 0.00, 3550.00),
(295, 14, 2, 'GPF Installment: 0 / 0', 0.00, 0.00, 0.00),
(296, 14, 3, 'House Rent Deduction', 0.00, 0.00, 0.00),
(297, 14, 4, 'Benevolent Fund', 0.00, 0.00, 0.00),
(298, 14, 5, 'Insurance Premium', 0.00, 0.00, 0.00),
(299, 14, 6, 'Electricity Bill', 0.00, 0.00, 0.00),
(300, 14, 7, 'HRD Extra', 0.00, 0.00, 0.00),
(301, 14, 8, 'Club Subscription', 0.00, 0.00, 0.00),
(302, 14, 9, 'Association Subscription', 0.00, 0.00, 0.00),
(303, 14, 10, 'Transport Bill', 0.00, 0.00, 0.00),
(304, 14, 11, 'Telephone Bill', 0.00, 0.00, 0.00),
(305, 14, 12, 'Pension Fund', 0.00, 0.00, 0.00),
(306, 14, 13, 'Fish Bill', 0.00, 0.00, 0.00),
(307, 14, 14, 'Income Tax', 0.00, 0.00, 0.00),
(308, 14, 15, 'Donation', 0.00, 0.00, 0.00),
(309, 14, 16, 'Guest House Rent', 0.00, 0.00, 0.00),
(310, 14, 17, 'Home Loan Installment1: 0 / 0', 0.00, 0.00, 0.00),
(311, 14, 18, 'Home Loan Installment2: 0 / 0', 0.00, 0.00, 0.00),
(312, 14, 19, 'Home Loan Installment3: 0 / 0', 0.00, 0.00, 0.00),
(313, 14, 20, 'Salary Adjustment: 0 / 0', 0.00, 0.00, 0.00),
(314, 14, 21, 'Others', 0.00, 0.00, 0.00),
(315, 14, 22, 'Revenue', 0.00, 10.00, 10.00),
(316, 9, 1, 'GPF', 10.00, 0.00, 7649.00),
(317, 9, 2, 'GPF Installment: 0 / 0', 0.00, 0.00, 0.00),
(318, 9, 3, 'House Rent Deduction', 0.00, 0.00, 0.00),
(319, 9, 4, 'Benevolent Fund', 0.00, 200.00, 200.00),
(320, 9, 5, 'Insurance Premium', 0.00, 0.00, 0.00),
(321, 9, 6, 'Electricity Bill', 0.00, 0.00, 0.00),
(322, 9, 7, 'HRD Extra', 0.00, 0.00, 0.00),
(323, 9, 8, 'Club Subscription', 0.00, 30.00, 30.00),
(324, 9, 9, 'Association Subscription', 0.00, 150.00, 150.00),
(325, 9, 10, 'Transport Bill', 0.00, 200.00, 200.00),
(326, 9, 11, 'Telephone Bill', 0.00, 0.00, 0.00),
(327, 9, 12, 'Pension Fund', 0.00, 0.00, 0.00),
(328, 9, 13, 'Fish Bill', 0.00, 0.00, 0.00),
(329, 9, 14, 'Income Tax', 0.00, 3931.00, 3931.00),
(330, 9, 15, 'Donation', 0.00, 0.00, 0.00),
(331, 9, 16, 'Guest House Rent', 0.00, 0.00, 0.00),
(332, 9, 17, 'Home Loan Installment1: 0 / 0', 0.00, 0.00, 0.00),
(333, 9, 18, 'Home Loan Installment2: 0 / 0', 0.00, 0.00, 0.00),
(334, 9, 19, 'Home Loan Installment3: 0 / 0', 0.00, 0.00, 0.00),
(335, 9, 20, 'Salary Adjustment: 0 / 0', 0.00, 0.00, 0.00),
(336, 9, 21, 'Others', 0.00, 0.00, 0.00),
(337, 9, 22, 'Revenue', 0.00, 10.00, 10.00),
(338, 16, 1, 'GPF', 10.00, 0.00, 6612.00),
(339, 16, 2, 'GPF Installment: 0 / 0', 0.00, 0.00, 0.00),
(340, 16, 3, 'House Rent Deduction', 0.00, 0.00, 0.00),
(341, 16, 4, 'Benevolent Fund', 0.00, 50.00, 50.00),
(342, 16, 5, 'Insurance Premium', 0.00, 0.00, 0.00),
(343, 16, 6, 'Electricity Bill', 0.00, 0.00, 0.00),
(344, 16, 7, 'HRD Extra', 0.00, 0.00, 0.00),
(345, 16, 8, 'Club Subscription', 0.00, 30.00, 30.00),
(346, 16, 9, 'Association Subscription', 0.00, 150.00, 150.00),
(347, 16, 10, 'Transport Bill', 0.00, 300.00, 300.00),
(348, 16, 11, 'Telephone Bill', 0.00, 0.00, 0.00),
(349, 16, 12, 'Pension Fund', 0.00, 0.00, 0.00),
(350, 16, 13, 'Fish Bill', 0.00, 0.00, 0.00),
(351, 16, 14, 'Income Tax', 0.00, 2805.00, 2805.00),
(352, 16, 15, 'Donation', 0.00, 0.00, 0.00),
(353, 16, 16, 'Guest House Rent', 0.00, 0.00, 0.00),
(354, 16, 17, 'Home Loan Installment1: 0 / 0', 0.00, 20608.00, 20608.00),
(355, 16, 18, 'Home Loan Installment2: 0 / 0', 0.00, 0.00, 0.00),
(356, 16, 19, 'Home Loan Installment3: 0 / 0', 0.00, 0.00, 0.00),
(357, 16, 20, 'Salary Adjustment: 0 / 0', 0.00, 0.00, 0.00),
(358, 16, 21, 'Others', 0.00, 0.00, 0.00),
(359, 16, 22, 'Revenue', 0.00, 10.00, 10.00),
(360, 10, 1, 'GPF', 10.00, 0.00, 3728.00),
(361, 10, 2, 'GPF Installment: 0 / 0', 0.00, 0.00, 0.00),
(362, 10, 3, 'House Rent Deduction', 0.00, 0.00, 0.00),
(363, 10, 4, 'Benevolent Fund', 0.00, 50.00, 50.00),
(364, 10, 5, 'Insurance Premium', 0.00, 0.00, 0.00),
(365, 10, 6, 'Electricity Bill', 0.00, 0.00, 0.00),
(366, 10, 7, 'HRD Extra', 0.00, 0.00, 0.00),
(367, 10, 8, 'Club Subscription', 0.00, 30.00, 30.00),
(368, 10, 9, 'Association Subscription', 0.00, 150.00, 150.00),
(369, 10, 10, 'Transport Bill', 0.00, 0.00, 0.00),
(370, 10, 11, 'Telephone Bill', 0.00, 0.00, 0.00),
(371, 10, 12, 'Pension Fund', 0.00, 0.00, 0.00),
(372, 10, 13, 'Fish Bill', 0.00, 0.00, 0.00),
(373, 10, 14, 'Income Tax', 0.00, 250.00, 250.00),
(374, 10, 15, 'Donation', 0.00, 0.00, 0.00),
(375, 10, 16, 'Guest House Rent', 0.00, 0.00, 0.00),
(376, 10, 17, 'Home Loan Installment1: 0 / 0', 0.00, 0.00, 0.00),
(377, 10, 18, 'Home Loan Installment2: 0 / 0', 0.00, 0.00, 0.00),
(378, 10, 19, 'Home Loan Installment3: 0 / 0', 0.00, 0.00, 0.00),
(379, 10, 20, 'Salary Adjustment: 0 / 0', 0.00, 0.00, 0.00),
(380, 10, 21, 'Others', 0.00, 0.00, 0.00),
(381, 10, 22, 'Revenue', 0.00, 10.00, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL,
  `employeeNo` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `contactNo` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `empStatus` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `designation_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `no_of_increment` int(11) DEFAULT 0,
  `basic` decimal(10,2) DEFAULT NULL,
  `account_number` varchar(50) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `joining_date` date NOT NULL,
  `e_tin` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `employeeNo`, `name`, `date_of_birth`, `gender`, `contactNo`, `email`, `empStatus`, `image`, `designation_id`, `department_id`, `no_of_increment`, `basic`, `account_number`, `grade_id`, `joining_date`, `e_tin`) VALUES
(9, '12400001', 'DR. SYED MD. GALIB', '2009-02-01', 'Male', '01982525570', 'galib.cse@just.edu.bd', 'Active', 'uploads/syed.jpeg', 1, 3, 4, 76490.00, '0200012251421', 2, '2018-07-02', '572488598148'),
(10, '28900002', 'ROMANA RAHMAN EMA', '1989-12-31', 'Female', '01982525570', 'rr.ema@just.edu.bd', 'On Leave', 'uploads/Ema.jpeg', 3, 3, 1, 37280.00, '0200016263617', 6, '2020-12-19', '233142416000'),
(11, '12400003', 'DR. MD. NASIM ADNAN', '2024-10-01', 'Male', '01982525570', 'nasim.adnan@just.edu.bd', 'Active', '../uploads/adnan.jpeg', 3, 3, 0, 35500.00, '200012251421', 6, '2024-10-02', '123456'),
(12, '12400004', 'ABU RAFE MD JAMIL', '2024-01-07', 'Male', '01719506604', 'nasim.adnan@just.edu.bd', 'Active', 'uploads/Jamil.jpeg', 4, 3, 0, 22000.00, '200006132708', 9, '2024-10-02', '572488598148'),
(13, '12400005', 'MOSTAFIJUR RAHMAN AKHOND', '2024-10-31', 'Male', '01719506604', 'mr.akhond@just.edu.bd', 'On Leave', 'uploads/akhond.jpeg', 3, 4, 1, 37280.00, '200012251421', 6, '2024-11-01', '123456'),
(14, '12400006', 'S.M. ARIFUL HOQUE', '2024-11-07', 'Male', '01719506604', 'mr.akhond@just.edu.bd', 'Active', 'uploads/ariful.jpeg', 4, 5, 0, 35500.00, '200012251421', 6, '2024-10-27', '572488598148'),
(15, '12400007', 'MD. YASIR ARAFAT', '2024-10-30', 'Male', '01982525570', 'bornomala616@gmail.com', 'On Leave', 'uploads/arafat.jpeg', 3, 16, 1, 52000.00, '200012251421', 4, '2024-11-08', '572488598148'),
(16, '12400008', 'DR. MD. ALAM HOSSAIN', '2024-11-01', 'Male', '01982525570', 'alam@just.edu.bd', 'Active', '../uploads/67338a1ada282.jpeg', 1, 3, 4, 66120.00, '200006132708', 3, '2009-06-29', '117077868492'),
(75, '12400009', 'DR. MD. KAMRUL ISLAM', '2024-10-31', 'Male', '01982525570', 'pervejbd2029@gmail.com', 'Active', '../uploads/673f607c19b18.jpeg', 2, 3, 8, 68460.00, '200006133850', 4, '2024-10-27', '0'),
(76, '12400010', 'A.F.M. SHAHAB UDDIN', '2024-10-31', 'Male', '01982525570', 'pervejbd2029@gmail.com', 'Active', '../uploads/673f62f258cce.jpeg', 3, 3, 0, 35500.00, '200012251421', 6, '2024-10-30', '117077868492'),
(77, '12400011', 'MD. SHAFIUZZAMAN', '2024-10-30', 'Male', '01982525570', 'pervejbd2039@gmail.com', 'On Leave', '../uploads/673f6d345c49f.jpeg', 3, 3, 0, 35500.00, '200012251421', 6, '2024-11-13', '117077868492'),
(78, '12400012', 'NAZMUL HOSSAIN', '2024-11-07', 'Male', '01982525570', 'pervejbd4029@gmail.com', 'Active', '../uploads/673f727d1ec34.jpg', 3, 3, 0, 35500.00, '200006132708', 6, '2024-11-12', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `faculty` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `faculty`) VALUES
(0, 'Science'),
(1, 'Engineering and Technology'),
(2, 'Applied Science and Technology'),
(3, 'Biological Science and Technology'),
(4, 'Health Science'),
(5, 'Arts and Social Science'),
(6, 'Science'),
(7, 'Business Studies');

-- --------------------------------------------------------

--
-- Table structure for table `grade`
--

CREATE TABLE `grade` (
  `id` int(11) NOT NULL,
  `grade` int(11) NOT NULL,
  `scale` varchar(50) NOT NULL,
  `increment` int(11) NOT NULL,
  `gradePercentage` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade`
--

INSERT INTO `grade` (`id`, `grade`, `scale`, `increment`, `gradePercentage`) VALUES
(1, 1, '78000', 0, 0.00),
(2, 2, '66000', 4, 3.75),
(3, 3, '56500', 7, 4.00),
(4, 4, '50000', 9, 4.00),
(5, 5, '43000', 11, 4.50),
(6, 6, '35500', 13, 5.00),
(7, 7, '29000', 16, 5.00),
(8, 8, '23000', 18, 5.00),
(9, 9, '22000', 18, 5.00),
(10, 10, '16000', 18, 5.00),
(11, 11, '12500', 18, 5.00),
(12, 12, '11300', 18, 5.00),
(13, 13, '11000', 18, 5.00),
(14, 14, '10200', 18, 5.00),
(15, 15, '9700', 18, 5.00),
(16, 16, '9300', 18, 5.00),
(17, 17, '9000', 18, 5.00),
(18, 18, '8800', 18, 5.00),
(19, 19, '8500', 18, 5.00),
(20, 20, '8250', 18, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `telephoneallw`
--

CREATE TABLE `telephoneallw` (
  `id` int(11) NOT NULL,
  `addDuty_id` int(11) NOT NULL,
  `telephoneAllw` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `telephoneallw`
--

INSERT INTO `telephoneallw` (`id`, `addDuty_id`, `telephoneAllw`) VALUES
(1, 1, 0.00),
(2, 2, 1500.00),
(3, 2, 1300.00),
(4, 2, 1200.00),
(5, 2, 1100.00),
(6, 3, 1500.00),
(7, 3, 1300.00),
(8, 3, 1200.00),
(9, 3, 1100.00),
(10, 4, 1500.00),
(11, 4, 1300.00),
(12, 4, 1200.00),
(13, 4, 1100.00),
(14, 5, 1500.00),
(15, 5, 1300.00),
(16, 5, 1200.00),
(17, 5, 1100.00),
(18, 6, 1500.00),
(19, 6, 1300.00),
(20, 6, 1200.00),
(21, 6, 1100.00),
(22, 7, 1500.00),
(23, 7, 1300.00),
(24, 7, 1200.00),
(25, 7, 1100.00),
(26, 8, 1500.00),
(27, 8, 1300.00),
(28, 8, 1200.00),
(29, 8, 1100.00),
(30, 9, 1500.00),
(31, 9, 1300.00),
(32, 9, 1200.00),
(33, 9, 1100.00),
(34, 10, 1200.00),
(35, 11, 1500.00),
(36, 11, 1300.00),
(37, 11, 1200.00),
(38, 11, 1100.00),
(39, 12, 1500.00),
(40, 12, 1300.00),
(41, 12, 1200.00),
(42, 12, 1100.00),
(43, 13, 0.00),
(44, 14, 0.00),
(45, 15, 1500.00),
(46, 15, 1300.00),
(47, 15, 1200.00),
(48, 15, 1100.00),
(49, 16, 800.00),
(50, 16, 700.00),
(51, 16, 600.00),
(52, 17, 800.00),
(53, 17, 700.00),
(54, 17, 600.00),
(55, 18, 700.00),
(56, 18, 400.00),
(57, 19, 700.00),
(58, 19, 400.00),
(59, 20, 700.00),
(60, 20, 400.00),
(61, 21, 700.00),
(62, 21, 400.00),
(63, 22, 700.00),
(64, 22, 400.00),
(65, 23, 1100.00),
(66, 24, 1500.00),
(67, 24, 1300.00),
(68, 24, 1200.00),
(69, 24, 1100.00),
(70, 25, 1500.00),
(71, 25, 1300.00),
(72, 25, 1200.00),
(73, 25, 1100.00),
(74, 26, 1500.00),
(75, 26, 1300.00),
(76, 26, 1200.00),
(77, 26, 1100.00),
(78, 27, 1500.00),
(79, 27, 1300.00),
(80, 27, 1200.00),
(81, 27, 1100.00),
(82, 28, 1500.00),
(83, 28, 1300.00),
(84, 28, 1200.00),
(85, 28, 1100.00),
(86, 29, 1500.00),
(87, 29, 1300.00),
(88, 29, 1200.00),
(89, 29, 1100.00),
(90, 30, 1500.00),
(91, 30, 1300.00),
(92, 30, 1200.00),
(93, 30, 1100.00),
(94, 31, 1500.00),
(95, 31, 1300.00),
(96, 31, 1200.00),
(97, 31, 1100.00),
(98, 32, 1500.00),
(99, 32, 1300.00),
(100, 32, 1200.00),
(101, 32, 1100.00),
(102, 33, 2500.00),
(103, 34, 800.00),
(104, 34, 700.00),
(105, 34, 600.00),
(106, 35, 800.00),
(107, 35, 700.00),
(108, 35, 600.00),
(109, 36, 800.00),
(110, 36, 700.00),
(111, 36, 600.00),
(112, 37, 800.00),
(113, 37, 700.00),
(114, 37, 600.00),
(115, 38, 800.00),
(116, 38, 700.00),
(117, 38, 600.00),
(118, 39, 800.00),
(119, 39, 700.00),
(120, 39, 600.00),
(121, 40, 800.00),
(122, 40, 700.00),
(123, 40, 600.00),
(124, 41, 700.00),
(125, 41, 400.00),
(126, 42, 700.00),
(127, 42, 400.00),
(128, 43, 700.00),
(129, 43, 400.00),
(130, 44, 700.00),
(131, 44, 400.00),
(132, 45, 500.00),
(133, 45, 400.00),
(134, 45, 300.00),
(135, 46, 500.00),
(136, 46, 400.00),
(137, 46, 300.00),
(138, 47, 500.00),
(139, 47, 400.00),
(140, 47, 300.00),
(174, 59, 0.00),
(175, 59, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `employeeNo` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `employee_id`, `employeeNo`, `name`, `email`, `password`) VALUES
(1, 76, '12400010', 'A.F.M. SHAHAB UDDIN', 'pervejbd2029@gmail.com', '$2y$10$flI6ibTycFlWAgfUMKCwU.naZQnMGhHvxFTKHNGg3NJE3rXVn3ml.'),
(2, 77, '12400011', 'MD. SHAFIUZZAMAN', 'pervejbd2039@gmail.com', '$2y$10$Nxa/JyQqkMf.1HqcdeilFu.gqRFC4neaMSTvhHgacF.tAotp1s3KK'),
(6, 78, '12400012', 'NAZMUL HOSSAIN', 'pervejbd4029@gmail.com', '$2y$10$DtLLBZkum3iS3nzZNK8xzuVgftmDWH6Lit2P6nflWKnIMcWgESmBq');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addduty`
--
ALTER TABLE `addduty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `allowancelist`
--
ALTER TABLE `allowancelist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `allwconfirm`
--
ALTER TABLE `allwconfirm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `empAllowance_id` (`empAllowance_id`);

--
-- Indexes for table `checkemployee`
--
ALTER TABLE `checkemployee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `dedconfirm`
--
ALTER TABLE `dedconfirm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `empDeduction_id` (`empDeduction_id`);

--
-- Indexes for table `deductionlist`
--
ALTER TABLE `deductionlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `empadddesignation`
--
ALTER TABLE `empadddesignation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empAddSalary_id` (`empAddSalary_id`),
  ADD KEY `addDuty_id` (`addDuty_id`);

--
-- Indexes for table `empaddsalary`
--
ALTER TABLE `empaddsalary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `telephoneAllw_id` (`telephoneAllw_id`);

--
-- Indexes for table `empallowance`
--
ALTER TABLE `empallowance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `allowanceList_id` (`allowanceList_id`);

--
-- Indexes for table `empdeduction`
--
ALTER TABLE `empdeduction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `deductionList_id` (`deductionList_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `designation_id` (`designation_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `telephoneallw`
--
ALTER TABLE `telephoneallw`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addDuty_id` (`addDuty_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addduty`
--
ALTER TABLE `addduty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `allowancelist`
--
ALTER TABLE `allowancelist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `allwconfirm`
--
ALTER TABLE `allwconfirm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=414;

--
-- AUTO_INCREMENT for table `checkemployee`
--
ALTER TABLE `checkemployee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `dedconfirm`
--
ALTER TABLE `dedconfirm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=309;

--
-- AUTO_INCREMENT for table `deductionlist`
--
ALTER TABLE `deductionlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `empadddesignation`
--
ALTER TABLE `empadddesignation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `empaddsalary`
--
ALTER TABLE `empaddsalary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `empallowance`
--
ALTER TABLE `empallowance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=478;

--
-- AUTO_INCREMENT for table `empdeduction`
--
ALTER TABLE `empdeduction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=382;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `grade`
--
ALTER TABLE `grade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `telephoneallw`
--
ALTER TABLE `telephoneallw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allwconfirm`
--
ALTER TABLE `allwconfirm`
  ADD CONSTRAINT `allwconfirm_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`),
  ADD CONSTRAINT `allwconfirm_ibfk_2` FOREIGN KEY (`empAllowance_id`) REFERENCES `empallowance` (`id`);

--
-- Constraints for table `checkemployee`
--
ALTER TABLE `checkemployee`
  ADD CONSTRAINT `checkemployee_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`);

--
-- Constraints for table `dedconfirm`
--
ALTER TABLE `dedconfirm`
  ADD CONSTRAINT `dedconfirm_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`),
  ADD CONSTRAINT `dedconfirm_ibfk_2` FOREIGN KEY (`empDeduction_id`) REFERENCES `empdeduction` (`id`);

--
-- Constraints for table `empadddesignation`
--
ALTER TABLE `empadddesignation`
  ADD CONSTRAINT `empadddesignation_ibfk_1` FOREIGN KEY (`empAddSalary_id`) REFERENCES `empaddsalary` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `empadddesignation_ibfk_2` FOREIGN KEY (`addDuty_id`) REFERENCES `addduty` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `empaddsalary`
--
ALTER TABLE `empaddsalary`
  ADD CONSTRAINT `empaddsalary_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `empaddsalary_ibfk_2` FOREIGN KEY (`telephoneAllw_id`) REFERENCES `telephoneallw` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `empallowance`
--
ALTER TABLE `empallowance`
  ADD CONSTRAINT `empallowance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`),
  ADD CONSTRAINT `empallowance_ibfk_2` FOREIGN KEY (`allowanceList_id`) REFERENCES `allowancelist` (`id`);

--
-- Constraints for table `empdeduction`
--
ALTER TABLE `empdeduction`
  ADD CONSTRAINT `empdeduction_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`),
  ADD CONSTRAINT `empdeduction_ibfk_2` FOREIGN KEY (`deductionList_id`) REFERENCES `deductionlist` (`id`);

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`),
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `employee_ibfk_3` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`);

--
-- Constraints for table `telephoneallw`
--
ALTER TABLE `telephoneallw`
  ADD CONSTRAINT `telephoneallw_ibfk_1` FOREIGN KEY (`addDuty_id`) REFERENCES `addduty` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
