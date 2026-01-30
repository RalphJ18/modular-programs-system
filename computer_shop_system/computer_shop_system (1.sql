-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 06:54 AM
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
-- Database: `computer_shop_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `activity_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`activity_id`, `user_id`, `activity`, `activity_time`) VALUES
(21, 3, 'Deleted user ID 7 and all related data.', '2025-10-24 21:44:11'),
(22, 3, 'Deleted user ID 6 and all related data.', '2025-10-24 21:44:14'),
(23, 3, 'Deleted user ID 4 and all related data.', '2025-10-24 21:44:18'),
(24, 3, 'Added new user: Ralph Jay Maano (ralphjay69@gmail.com) with ₱100', '2025-10-24 21:44:43'),
(25, 3, 'Forced logout for user ID 8', '2025-10-24 21:51:35'),
(26, 3, 'Forced logout for user ID 8', '2025-10-24 22:33:25'),
(27, 3, 'Deleted user ID 8 and all related data.', '2025-10-24 22:33:47'),
(28, 3, 'Added new user: Ralph Jay Maano (ralphjay69@gmail.com) with ₱100', '2025-10-24 22:34:01'),
(29, 3, 'Added new user: Carlo Romarez (nikky@gmail.com) with ₱120', '2025-10-24 22:34:10'),
(30, 3, 'Forced logout for user ID 10', '2025-10-24 22:37:27'),
(31, 3, 'Forced logout for user ID 9', '2025-10-24 22:37:31'),
(32, 3, 'Reset password for user ID 10 to default (123)', '2025-10-24 22:40:46'),
(33, 3, 'Forced logout for user ID 10', '2025-10-24 22:40:51'),
(34, 3, 'Forced logout for user ID 9', '2025-10-24 22:41:26'),
(35, 3, 'Forced logout for user ID 10', '2025-10-24 22:43:44'),
(36, 3, 'Forced logout for user ID 9', '2025-10-24 22:45:02'),
(37, 3, 'Forced logout for user ID 9', '2025-10-24 22:45:45'),
(38, 3, 'Forced logout for user ID 10', '2025-10-24 22:46:01'),
(39, 3, 'Archived user ID 9', '2025-10-24 22:46:26'),
(40, 3, 'Forced logout for user ID 10', '2025-10-24 22:51:38'),
(41, 3, 'Deleted advertisement image and link', '2025-10-24 22:51:54'),
(42, 3, 'Added new user: Nicole Catamco (jonel@gmail.com) with ₱100', '2025-10-24 22:52:32'),
(43, 3, 'Forced logout for user ID 11', '2025-10-24 22:53:59'),
(44, 3, 'Forced logout for user ID 10', '2025-10-24 22:56:50'),
(45, 3, 'Forced logout for user ID 11', '2025-10-24 22:56:53'),
(46, 3, 'Forced logout for user ID 10', '2025-10-24 22:57:23'),
(47, 3, 'Forced logout for user ID 11', '2025-10-24 22:57:43');

-- --------------------------------------------------------

--
-- Table structure for table `balance_logs`
--

CREATE TABLE `balance_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `change_type` enum('add','deduct','reset') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `computer_id` int(11) NOT NULL,
  `computer_name` varchar(50) NOT NULL,
  `type_id` int(11) NOT NULL,
  `status` enum('available','in-use','offline') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computers`
--

INSERT INTO `computers` (`computer_id`, `computer_name`, `type_id`, `status`) VALUES
(1, 'PC01', 1, 'available'),
(2, 'PC02', 1, 'available'),
(3, 'PC03', 1, 'available'),
(4, 'PC04', 2, 'available'),
(5, 'PC05', 2, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `computer_types`
--

CREATE TABLE `computer_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `rate_per_hour` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computer_types`
--

INSERT INTO `computer_types` (`type_id`, `type_name`, `rate_per_hour`) VALUES
(1, 'Basic', 29.00),
(2, 'VIP', 49.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `topup_id` int(11) NOT NULL,
  `payment_method` enum('cash','gcash','card') DEFAULT 'cash',
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin'),
(2, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `shop_name` varchar(100) DEFAULT 'My Computer Shop',
  `basic_rate` decimal(10,2) DEFAULT 29.00,
  `vip_rate` decimal(10,2) DEFAULT 49.00,
  `ad_image` varchar(255) DEFAULT NULL,
  `ad_link` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `shop_name`, `basic_rate`, `vip_rate`, `ad_image`, `ad_link`, `last_updated`) VALUES
(1, 'NETCAFE BY RALPH', 29.00, 49.00, NULL, NULL, '2025-10-24 22:51:54');

-- --------------------------------------------------------

--
-- Table structure for table `topups`
--

CREATE TABLE `topups` (
  `topup_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `topup_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topup_history`
--

CREATE TABLE `topup_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `role_id` int(11) NOT NULL,
  `active_pc_id` int(11) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `balance`, `role_id`, `active_pc_id`, `date_created`, `is_archived`) VALUES
(3, 'Admin User', 'admin@shop.com', '$2y$10$zMlBwDGoq7PQkmptkYNO8u2BC.xEAALozDrcCnsGipsAr1SiQMcyK', 0.00, 1, NULL, '2025-10-24 18:18:39', 0),
(9, 'Ralph Jay Maano', 'ralphjay69@gmail.com', '$2y$10$en2jt01yo9/DDukNwFmknusLEA6/jmh5XfQu74rvzzvO6VKdySAzi', 96.30, 2, NULL, '2025-10-24 22:34:01', 1),
(10, 'Carlo Romarez', 'nikky@gmail.com', '$2y$10$p//MDb13aBBw/pPPBeO.Kep24wZXtqTMWNwGBdwW2LIunySuheYTC', 114.94, 2, NULL, '2025-10-24 22:34:10', 0),
(11, 'Nicole Catamco', 'jonel@gmail.com', '$2y$10$HW6pcHfceoFhVWpUofiTFunIDNF.RmQAfBik0K7VH/yfBFRdc5iHi', 97.88, 2, NULL, '2025-10-24 22:52:32', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 0,
  `total_cost` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','ended','paused') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `computer_id`, `start_time`, `end_time`, `duration_minutes`, `total_cost`, `status`) VALUES
(40, 10, 4, '2025-10-25 06:35:12', '2025-10-25 06:35:17', 0, 0.00, 'ended'),
(41, 10, 4, '2025-10-25 06:35:21', '2025-10-25 06:35:23', 0, 0.00, 'ended'),
(42, 10, 4, '2025-10-25 06:36:21', '2025-10-25 06:37:27', 1, 0.82, 'ended'),
(43, 9, 1, '2025-10-25 06:36:35', '2025-10-25 06:37:31', 1, 0.48, 'ended'),
(44, 10, 4, '2025-10-25 06:40:08', '2025-10-25 06:40:51', 1, 0.82, 'ended'),
(45, 9, 1, '2025-10-25 06:40:10', '2025-10-25 06:41:26', 1, 0.48, 'ended'),
(46, 9, 1, '2025-10-25 06:43:01', '2025-10-25 06:43:04', 1, 0.48, 'ended'),
(47, 9, 1, '2025-10-25 06:43:16', '2025-10-25 06:45:02', 1, 0.48, 'ended'),
(48, 10, 4, '2025-10-25 06:43:26', '2025-10-25 06:43:44', 1, 0.82, 'ended'),
(49, 10, 4, '2025-10-25 06:45:21', '2025-10-25 06:46:01', 1, 0.82, 'ended'),
(50, 9, 1, '2025-10-25 06:45:31', '2025-10-25 06:45:45', 1, 0.48, 'ended'),
(51, 10, 1, '2025-10-25 06:46:07', '2025-10-25 06:51:38', 1, 0.48, 'ended'),
(52, 9, 4, '2025-10-25 06:46:42', '2025-10-25 06:46:47', 1, 0.82, 'ended'),
(53, 11, 4, '2025-10-25 06:52:40', '2025-10-25 06:53:59', 1, 0.82, 'ended'),
(54, 9, 1, '2025-10-25 06:52:56', '2025-10-25 06:53:21', 1, 0.48, 'ended'),
(55, 11, 4, '2025-10-25 06:55:57', '2025-10-25 06:56:53', 1, 0.82, 'ended'),
(56, 10, 1, '2025-10-25 06:56:07', '2025-10-25 06:56:50', 1, 0.48, 'ended'),
(57, 10, 4, '2025-10-25 06:56:57', '2025-10-25 06:57:23', 1, 0.82, 'ended'),
(58, 11, 1, '2025-10-25 06:57:14', '2025-10-25 06:57:43', 1, 0.48, 'ended');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `balance_logs`
--
ALTER TABLE `balance_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `computers`
--
ALTER TABLE `computers`
  ADD PRIMARY KEY (`computer_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `computer_types`
--
ALTER TABLE `computer_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `topup_id` (`topup_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `topups`
--
ALTER TABLE `topups`
  ADD PRIMARY KEY (`topup_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `topup_history`
--
ALTER TABLE `topup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `computer_id` (`computer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `balance_logs`
--
ALTER TABLE `balance_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `computers`
--
ALTER TABLE `computers`
  MODIFY `computer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `computer_types`
--
ALTER TABLE `computer_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `topups`
--
ALTER TABLE `topups`
  MODIFY `topup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topup_history`
--
ALTER TABLE `topup_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `balance_logs`
--
ALTER TABLE `balance_logs`
  ADD CONSTRAINT `balance_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `computers`
--
ALTER TABLE `computers`
  ADD CONSTRAINT `computers_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `computer_types` (`type_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`topup_id`) REFERENCES `topups` (`topup_id`);

--
-- Constraints for table `topups`
--
ALTER TABLE `topups`
  ADD CONSTRAINT `topups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `topup_history`
--
ALTER TABLE `topup_history`
  ADD CONSTRAINT `topup_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`computer_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
