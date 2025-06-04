-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 10:49 AM
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
-- Database: `logistics_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `driver_id` varchar(20) NOT NULL,
  `shipment_number` varchar(100) DEFAULT NULL,
  `customer_source` varchar(100) DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `status` enum('Pending','In Progress','Delivered') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `driver_id`, `shipment_number`, `customer_source`, `pickup_date`, `from_location`, `to_location`, `status`, `created_at`) VALUES
(2, '39827754', '1234567', 'Unga Limited', '2025-06-03', 'Eldoret', 'Nakuru', 'Pending', '2025-06-03 20:07:58'),
(3, '39827754', '234568', 'Unga Limited', '2025-06-03', 'Eldoret', 'Nakuru', 'Pending', '2025-06-03 20:11:59'),
(4, '39827754', '14567', 'Farmcare', '2025-06-03', 'nakuru', 'Nairobi', 'Pending', '2025-06-03 20:49:04'),
(5, '12345678', '123478', 'Farmcare', '2025-06-03', 'nakuru', 'Nairobi', 'Pending', '2025-06-03 20:49:45'),
(6, '23456789', '1234567', 'Unga Limited', '2025-06-03', 'Eldoret', 'Nakuru', 'Pending', '2025-06-03 20:59:06'),
(7, '12345678', '123567', 'Farmcare', '2025-06-03', 'Eldoret', 'Nakuru', 'Pending', '2025-06-03 21:00:01'),
(8, '23456789', '234578', 'Farmcare', '2025-06-04', 'Mombasa', 'eldoret', 'Pending', '2025-06-04 06:31:05');

-- --------------------------------------------------------

--
-- Table structure for table `shipment_items`
--

CREATE TABLE `shipment_items` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `weight_kg` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('DRIVER','MANAGER') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `national_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `national_id`) VALUES
(5, 'Cate', 'machariacatherine407@gmail.com', '$2y$10$q.ovi2ZQYUoPsGnjBYbG5eGWQuRlUXr3mlLFPWzggjxOB.eeYL0Ky', 'MANAGER', '2025-06-03 20:56:52', '39827754'),
(6, 'Martin', 'martin404@gmail.com', '$2y$10$mFJGOifmpW.GcEkNOi3b6et0yN7FabUj96ZBfuP48/UXeBSI46Hle', 'DRIVER', '2025-06-03 20:57:33', '12345678'),
(7, 'Macharia', 'macharia1234@gmail.com', '$2y$10$V/eVbUQIr2F0L0VwXMXNEOnjZ9VzfH1O8E7G8EJIsEzuz3JKsmn/i', 'DRIVER', '2025-06-03 20:57:49', '23456789');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_id` (`shipment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shipment_items`
--
ALTER TABLE `shipment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD CONSTRAINT `shipment_items_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
