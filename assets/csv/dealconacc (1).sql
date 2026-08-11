-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2023 at 05:01 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dealconacc`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc_histories`
--

DROP TABLE IF EXISTS `acc_histories`;
CREATE TABLE `acc_histories` (
  `id` bigint(20) NOT NULL,
  `account_id` bigint(11) NOT NULL,
  `state` varchar(10) NOT NULL COMMENT '1 = pending,2 = processing,3 = waiting,4 = closed, 5 = paid',
  `item_id` bigint(11) NOT NULL,
  `comment` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` bigint(11) NOT NULL,
  `lastmachine_no` varchar(6) NOT NULL,
  `tax_no` varchar(13) DEFAULT NULL,
  `customername` varchar(50) DEFAULT NULL,
  `doc1` varchar(100) DEFAULT NULL,
  `doc2` varchar(100) DEFAULT NULL,
  `doc3` varchar(100) DEFAULT NULL,
  `doc4` varchar(100) DEFAULT NULL,
  `doc5` varchar(100) DEFAULT NULL,
  `branchno` varchar(45) DEFAULT NULL,
  `ischeck` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT '1' COMMENT '1 = pending,2 = processing,3 = waiting,4 = closed, 5 = paid',
  `tax_date` date DEFAULT NULL,
  `dealer_id` bigint(11) DEFAULT NULL,
  `ic_no` varchar(15) NOT NULL,
  `ic_date` date NOT NULL,
  `ap_no` varchar(15) NOT NULL,
  `ap_date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(11) NOT NULL,
  `name` varchar(10) DEFAULT NULL,
  `username` varchar(11) DEFAULT NULL,
  `password` int(8) DEFAULT NULL,
  `branchgroup` int(3) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `role` varchar(6) DEFAULT NULL,
  `dealer` varchar(28) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `branchgroup`, `code`, `role`, `dealer`, `created_at`) VALUES
(1, '', 'aqua', 73997825, 401, '', 'Dealer', 'ทวียนต์ทวีกิจ', '2023-09-27 02:01:09'),
(2, '', 'barrens', 69025012, 441, '', 'Dealer', 'ทวีมอเตอร์ ', '2023-09-27 02:01:09'),
(3, '', 'granite', 53738193, 450, '', 'Dealer', 'ทวีมอเตอร์ 1993', '2023-09-27 02:01:09'),
(4, '', 'torch', 56501235, 451, '', 'Dealer', 'เกียรติยานยนต์', '2023-09-27 02:01:09'),
(5, '', 'bedrock', 66722591, 461, '', 'Dealer', 'บ้านใหม่ยนต์กิจ', '2023-09-27 02:01:09'),
(6, '', 'tundra', 82452199, 471, '', 'Dealer', 'วรภัณฑ์', '2023-09-27 02:01:09'),
(7, '', 'note', 86642557, 481, '', 'Dealer', 'เดชากลการบ้านนา', '2023-09-27 02:01:09'),
(8, '', 'lilypad', 19261528, 491, '', 'Dealer', 'บุรีรัมย์สยามมอเตอร์', '2023-09-27 02:01:09'),
(9, '', 'jungle', 56302349, 496, '', 'Dealer', 'บุรีรัมย์สยามมอเตอร์ไบค์', '2023-09-27 02:01:09'),
(10, '', 'leaves', 63626576, 501, '', 'Dealer', 'เกริกไกร', '2023-09-27 02:01:09'),
(11, '', 'knockback', 92614523, 561, '', 'Dealer', 'เอเอมอเตอร์ไบค์', '2023-09-27 02:01:09'),
(12, '', 'piston', 21465368, 571, '', 'Dealer', 'เอ.บี.มอเตอร์ไบค์', '2023-09-27 02:01:09'),
(13, '', 'cobweb', 25699425, 576, '', 'Dealer', 'สินเจริญฮอนด้า', '2023-09-27 02:01:09'),
(14, '', 'hills', 50679046, 581, '', 'Dealer', 'พัฒนชัยยนต์-ปืนวิฑูรย์', '2023-09-27 02:01:09'),
(15, '', 'cobblestone', 86111055, 598, '', 'Dealer', 'บึงสามพันเทรดดิ้ง', '2023-09-27 02:01:09'),
(16, '', 'gunpowder', 69301674, 599, '', 'Dealer', 'เจริญมอเตอร์วิเชียรบุรี', '2023-09-27 02:01:09'),
(17, '', 'frozen', 92914363, 601, '', 'Dealer', 'ตรังเอสที', '2023-09-27 02:01:09'),
(18, '', 'tube', 86583521, 621, '', 'Dealer', 'ส. สมบูรณ์ออโต้', '2023-09-27 02:01:09'),
(19, '', 'weeping', 96873555, 631, '', 'Dealer', 'กันชัยมอเตอร์', '2023-09-27 02:01:09'),
(20, '', 'creeper', 72986280, 641, '', 'Dealer', 'พิพัฒน์มอเตอร์เทค', '2023-09-27 02:01:09'),
(21, '', 'blaze', 19566354, 646, '', 'Dealer', 'เทียนวา', '2023-09-27 02:01:09'),
(22, '', 'remnant', 59183161, 650, '', 'Dealer', 'อื้อใจ้เส็ง5 มอเตอร์', '2023-09-27 02:01:09'),
(23, '', 'pigman', 14847870, 651, '', 'Dealer', 'ส.อรุณ เซลส์เซอร์วิส', '2023-09-27 02:01:09'),
(24, '', 'mansion', 26777143, 656, '', 'Dealer', 'ดราก้อนมอเตอร์ไบค์', '2023-09-27 02:01:09'),
(25, '', 'monument', 38762590, 660, '', 'Dealer', 'ถาวร เพาเวอร์', '2023-09-27 02:01:09'),
(26, '', 'midlands', 97442069, 670, '', 'Dealer', 'บุรีรัมย์สยามยานยนต์', '2023-09-27 02:01:09'),
(27, '', 'lamp', 13381247, 675, '', 'Dealer', 'วี.พี.เอส.เซลล์แอนด์เซอร์วิส', '2023-09-27 02:01:09'),
(28, '', 'farmland', 82568043, 681, '', 'Dealer', 'บุรีรัมย์ยนตรการ', '2023-09-27 02:01:09'),
(29, '', 'crossbow', 61708716, 701, '', 'Dealer', 'เจียง ฮอนด้าหนองคาย', '2023-09-27 02:01:09'),
(30, '', 'mycelium', 33402773, 751, '', 'Dealer', 'เจียงพาณิชย์', '2023-09-27 02:01:09'),
(31, '', 'respiration', 75275001, 756, '', 'Dealer', 'ส่งเส็งซาวด์เจริญยนต์', '2023-09-27 02:01:09'),
(32, '', 'ingot', 15392051, 761, '', 'Dealer', 'วิศวกานต์มอเตอร์', '2023-09-27 02:01:09'),
(33, '', 'sugar', 82934345, 771, '', 'Dealer', 'สุทธิยนต์', '2023-09-27 02:01:09'),
(34, '', 'red', 74608753, 781, '', 'Dealer', 'ส.ยานยนต์บุรีรัมย์ 1994', '2023-09-27 02:01:09'),
(35, '', 'coalore', 28168955, 791, '', 'Dealer', 'บีอาร์วาย โมโต', '2023-09-27 02:01:09'),
(36, '', 'cracked', 61231458, 811, '', 'Dealer', 'เอนกยนต์', '2023-09-27 02:01:09'),
(37, '', 'multishot', 74679538, 821, '', 'Dealer', 'พิจิตรเอนกยนต์', '2023-09-27 02:01:09'),
(38, '', 'river', 18298279, 831, '', 'Dealer', 'ไทยสิริ', '2023-09-27 02:01:09'),
(39, '', 'pigman', 17646001, 836, '', 'Dealer', 'กิจชัยฮอนด้า 2548', '2023-09-27 02:01:09'),
(40, '', 'pufferfish', 52043491, 841, '', 'Dealer', 'พรพิวัฒน์ยานยนต์', '2023-09-27 02:01:09'),
(41, '', 'brewing', 73005607, 851, '', 'Dealer', 'พรสมพิศมอเตอร์เซลล์', '2023-09-27 02:01:09'),
(42, '', 'beacon', 31309887, 861, '', 'Dealer', 'ตรังมอเตอร์ไซค์', '2023-09-27 02:01:09'),
(43, '', 'spectral', 29528401, 871, '', 'Dealer', 'โชติอนันต์เจริญ', '2023-09-27 02:01:09'),
(44, '', 'ominous', 26770280, 891, '', 'Dealer', 'กิจชัยฮอนด้า', '2023-09-27 02:01:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_histories`
--
ALTER TABLE `acc_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dealer_id` (`dealer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_histories`
--
ALTER TABLE `acc_histories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`dealer_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
