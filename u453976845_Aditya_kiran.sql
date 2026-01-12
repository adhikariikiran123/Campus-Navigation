-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 12, 2026 at 12:27 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u453976845_Aditya_kiran`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'aditya jyala', 'adijyala1100@gmail.com', 'hello aditya jyala this side', '2025-11-16 18:04:32'),
(2, 'aditya', 'adi@gmail.com', 'hello', '2025-11-17 09:34:01'),
(3, 'aditya', 'jyaladi1100@gmail.com', 'hello world', '2025-12-05 19:45:02'),
(4, 'aditya', 'jyaladi1100@gmail.com', 'hello world', '2025-12-05 19:49:06'),
(5, 'Kiran Adhikari', 'kiranadhikari621@gmail.com', 'Hi', '2025-12-05 19:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `reset_token` varchar(128) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `dob`, `student_id`, `gender`, `name`, `email`, `password_hash`, `is_active`, `reset_token`, `reset_expires`, `created_at`) VALUES
(4, 'aditya', 'jyala', '2005-06-15', '23151138', 'Male', 'aditya jyala', 'adijyala1100@gmail.com', '$2y$10$LLMLRknmMDeGdqawCrrrbuO8MbMZUca3OYF/l4.7nndyQcDsQSbuG', 1, NULL, NULL, '2025-11-17 07:38:08'),
(5, 'Kiran', 'Adhikari', '2004-11-15', '23042267', 'Female', 'Kiran Adhikari', 'kiranadhikari621@gmail.com', '$2y$10$WFQC4Dn58PAG6NPETUgEb.PJOAvmthZqcP07FaKubO9NxiiIGbN0q', 1, NULL, NULL, '2025-12-05 19:51:41'),
(6, 'yash', 'chandola', '2025-11-30', '23041132', 'Male', 'yash chandola', 'yashchandola12@gmail.com', '$2y$10$aR/.LOjcTW6UAV3z6rbdGeOPERczbHI4VwxqOMuK69qHIvZpzOkii', 1, NULL, NULL, '2025-12-06 01:09:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD UNIQUE KEY `uq_student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
