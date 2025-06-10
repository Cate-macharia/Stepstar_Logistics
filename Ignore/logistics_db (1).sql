-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 02:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
-- Table structure for table `manual_rates`
--

CREATE TABLE `manual_rates` (
  `id` int(11) NOT NULL,
  `zone` varchar(5) DEFAULT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `route_range` varchar(100) DEFAULT NULL,
  `base_rate` decimal(10,2) DEFAULT NULL,
  `vat` decimal(10,2) DEFAULT NULL,
  `total_rate` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manual_rates`
--

INSERT INTO `manual_rates` (`id`, `zone`, `from_location`, `to_location`, `route_range`, `base_rate`, `vat`, `total_rate`, `created_at`) VALUES
(13, 'A', 'NULL', 'NULL', '<15', 628.23, 100.52, 728.75, '2025-06-09 12:30:45'),
(14, 'B', NULL, NULL, '16-29', 626.23, 100.20, 726.43, '2025-06-09 12:37:22'),
(15, 'C', NULL, NULL, '30-59', 1010.83, 161.73, 1172.56, '2025-06-09 12:37:55'),
(16, 'D', NULL, NULL, '60-99', 1292.07, 206.73, 1498.80, '2025-06-09 12:38:20'),
(17, 'E', NULL, NULL, '100-139', 1613.49, 258.16, 1871.65, '2025-06-09 12:38:58'),
(18, 'O', 'ELD', 'KISII', 'ELD - KISII', 2602.68, 416.43, 3019.11, '2025-06-09 12:40:10'),
(19, 'U', 'NRB', 'ELD', 'NRB - ELD', 2780.00, 444.80, 3224.80, '2025-06-09 12:40:44'),
(20, 'F', NULL, NULL, '140-199', 1861.76, 297.88, 2159.64, '2025-06-09 12:42:13'),
(21, 'G', NULL, NULL, '200-259', 2602.68, 416.43, 3019.11, '2025-06-09 12:42:31'),
(22, 'L', NULL, NULL, '>600', 3464.53, 554.32, 4018.85, '2025-06-09 12:42:55');

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id` int(11) NOT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `base_rate` decimal(10,2) DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT NULL,
  `vat_percent` decimal(5,2) DEFAULT 16.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `distance_km` decimal(10,2) DEFAULT NULL,
  `rate_per_tonne` decimal(10,2) DEFAULT NULL,
  `vat_percent` decimal(5,2) DEFAULT 16.00,
  `amount` decimal(10,2) DEFAULT NULL,
  `vehicle_reg` varchar(100) DEFAULT NULL,
  `net_weight` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `driver_id`, `shipment_number`, `customer_source`, `pickup_date`, `from_location`, `to_location`, `status`, `created_at`, `distance_km`, `rate_per_tonne`, `vat_percent`, `amount`, `vehicle_reg`, `net_weight`) VALUES
(0, '123456', '112345', 'FARMCARE', '2025-06-04', 'MOMBASA', 'ELD', 'Pending', '2025-06-04 09:30:11', NULL, NULL, 16.00, NULL, NULL, NULL),
(0, '123456', '11345', 'FARMCARE', '2025-06-04', 'MOMBASA', 'NAKURU', 'Pending', '2025-06-04 09:30:32', NULL, NULL, 16.00, NULL, NULL, NULL),
(0, '23456', '11345', 'UNGA LTD', '2025-06-10', 'NRB', 'ELD', 'Pending', '2025-06-10 12:37:17', 0.00, 2780.00, 16.00, 40310.00, 'KAZ 637Z', '12.5');

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
(0, 'Catherine M ', 'machariacatherine407@gmail.com', '9685', '', '2025-06-09 08:52:36', '39827754'),
(0, 'Cate', 'machariacate5@gmail.com', '1234', 'MANAGER', '2025-06-09 08:52:50', '123456'),
(0, 'Martin', 'martin404@gmail.com', '2345', 'DRIVER', '2025-06-09 08:53:02', '23456');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `manual_rates`
--
ALTER TABLE `manual_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `manual_rates`
--
ALTER TABLE `manual_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `rates`
--
ALTER TABLE `rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
