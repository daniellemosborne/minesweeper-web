-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 06, 2024 at 11:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_accounts`
--

-- --------------------------------------------------------

--
-- Table structure for table `game_stats`
--

CREATE TABLE `game_stats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `games_played` int(11) DEFAULT 0,
  `games_won` int(11) DEFAULT 0,
  `time_played` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_stats`
--

INSERT INTO `game_stats` (`id`, `user_id`, `games_played`, `games_won`, `time_played`) VALUES
(14, 5, 20, 10, 2000),
(15, 7, 70, 50, 8000),
(16, 18, 88, 1, 148),
(17, 10, 2, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `created_at`) VALUES
(5, 'patrick', 'star', 'user@gmail.com', '$2y$10$9V6QYWffTfsE2SeKft7v5OHYG8Ue78MilgI1aLaKc16YKQKuLcSJe', '2024-12-03 21:52:42'),
(7, 'john', 'doe', 'test@example.com', 'password123', '2024-12-03 22:10:36'),
(10, 'Danielle', 'Osborne', 'danielle.osborne11@gmail.com', '$2y$10$iZ6nGzg4g/PfZIQtscgxZufVK/5lm1bWWSOL6qryhyUazBYPo4waa', '2024-12-03 22:57:42'),
(12, 'bob', 'bob', 'bob@yahoo.com', '$2y$10$hcJiLHYeKNzut9v2Ogl1E.aypvKrF3YA.neLkY6tvu2Bx0ribXf5K', '2024-12-03 23:06:18'),
(15, 'First', 'Last', 'email@example.com', '$2y$10$s8U3/OJqNlSt22TBuJV5/.pM4/SuzUJ9hPubolONX2wH1INgfL7/a', '2024-12-05 00:00:16'),
(16, 'Billy', 'Joe', 'joe@email.com', '$2y$10$nAq.ttM2yfDTEPm68lShg.6F2iooRI4GepDULcU6EadWOWxJcwfp.', '2024-12-05 04:19:55'),
(17, 'billy', 'bob', 'example@example.com', '$2y$10$SeJzBHe70FtDgbTKC58dV.CxjsSTrW1uI8T/7EB0Xj4M8YfvLa.L2', '2024-12-06 01:58:33'),
(18, 'help', 'me', 'help@gmail.com', '$2y$10$G9MqW3fVMWtdsUnKt9KmkexgUXWJfTPByjlOFhYl15pgOJCOZlgSe', '2024-12-06 02:49:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `game_stats`
--
ALTER TABLE `game_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `email_3` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `game_stats`
--
ALTER TABLE `game_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `game_stats`
--
ALTER TABLE `game_stats`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
