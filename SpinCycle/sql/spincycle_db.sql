-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2026 at 04:10 PM
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
-- Database: `spincycle_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `laundry_orders`
--

CREATE TABLE `laundry_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `service_type` varchar(100) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `status` enum('Received','Washing','Ironing','Completed') DEFAULT 'Received',
  `total_cost` decimal(10,2) NOT NULL,
  `walk_in_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laundry_orders`
--

INSERT INTO `laundry_orders` (`id`, `user_id`, `order_date`, `service_type`, `weight`, `special_instructions`, `status`, `total_cost`, `walk_in_name`) VALUES
(1, 1, '2026-02-10 04:02:59', 'Washing', 5.00, '', 'Completed', 10.00, NULL),
(6, NULL, '2026-02-10 12:32:33', 'Washing', 10.00, '', 'Received', 20.00, 'Dwayne'),
(7, 3, '2026-02-10 12:42:00', 'Washing', 30.00, 'None', 'Completed', 60.00, NULL),
(15, NULL, '2026-02-10 17:32:28', 'Washing', 7.00, '', 'Received', 420.00, 'Mc Romer'),
(16, NULL, '2026-02-10 20:20:34', 'Dry Cleaning', 7.00, '', 'Received', 630.00, 'Alonso'),
(19, 3, '2026-02-11 14:42:30', 'Washing', 5.00, '', 'Received', 300.00, NULL),
(20, 3, '2026-02-11 14:42:48', 'Dry Cleaning', 5.00, '', 'Washing', 450.00, NULL),
(21, 3, '2026-02-11 14:42:54', 'Ironing', 5.00, '', 'Washing', 350.00, NULL),
(22, NULL, '2026-02-11 14:55:07', 'Washing', 10.00, '', 'Completed', 600.00, 'Richmond'),
(23, 3, '2026-02-11 15:00:39', 'Washing', 5.00, '', 'Washing', 300.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `price_range` varchar(20) DEFAULT NULL,
  `min_price` decimal(10,2) NOT NULL,
  `max_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `price_range`, `min_price`, `max_price`) VALUES
(1, 'Washing', '5.00', 50.00, 70.00),
(2, 'Dry Cleaning', '10.00', 80.00, 100.00),
(3, 'Ironing', '8.00', 60.00, 80.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'Sebastian', '$2y$10$4mhJ3CpocrGwdU/SgWlLOuJuDdooDpG9v7FdkvFkU7gjXF99NjvCW', 'jondwaynesebastian52@gmail.com', 'user', '2026-02-10 03:59:52'),
(2, 'admin', '$2y$10$G.Jhkqyu3FkKrVVqnMAMAexw4n8Ia5kFxJjj7yyWySGXBeMOC4Ifi', 'admin@example.com', 'admin', '2026-02-10 04:08:09'),
(3, 'Elain', '$2y$10$SzzOhXHU9hAKY/ejZIQFUOQcM6PrnCjaI9aXsu8eYWOIplEzS.3zG', 'Elainsamson.02@gmail.com', 'user', '2026-02-10 12:41:29'),
(4, 'Joshua Tolentino', '$2y$10$BVDG/RB/VhZXwQFfQL3rQeDgS.9UUgDDU.dAs9WUBsNlqX/05.eNC', 'joshuatolentino06@gmail.com', 'user', '2026-02-10 19:44:53'),
(6, 'Blue Simon', '$2y$10$oabeDsfjrAI2TcDlbj9qWOpJnwiYMuEeuUpzLZmLquHjCRq8anrmS', 'bluesimon@gmail.com', 'user', '2026-02-10 19:47:38'),
(7, 'Kevin', '$2y$10$3nFBSVG1zzz9l8RMmVePXuMNgi/hQNxPhq72kmjWwDmJTTV0zYZRO', 'kevingamboa@gmail.com', 'user', '2026-02-10 19:50:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `laundry_orders`
--
ALTER TABLE `laundry_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `laundry_orders`
--
ALTER TABLE `laundry_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laundry_orders`
--
ALTER TABLE `laundry_orders`
  ADD CONSTRAINT `laundry_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
