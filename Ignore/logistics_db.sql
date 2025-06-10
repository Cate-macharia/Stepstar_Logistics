-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 10:27 PM
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
  `from_location` varchar(50) NOT NULL,
  `to_location` varchar(50) NOT NULL,
  `distance_km` varchar(50) NOT NULL,
  `rate_per_ton` decimal(10,2) NOT NULL,
  `vat` decimal(5,2) NOT NULL DEFAULT 16.00,
  `total_rate` decimal(10,2) GENERATED ALWAYS AS (`rate_per_ton` + `rate_per_ton` * `vat` / 100) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `zone` varchar(100) DEFAULT NULL,
  `route_range` varchar(100) DEFAULT NULL,
  `base_rate` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manual_rates`
--

INSERT INTO `manual_rates` (`id`, `from_location`, `to_location`, `distance_km`, `rate_per_ton`, `vat`, `created_at`, `zone`, `route_range`, `base_rate`) VALUES
(249, '-', '-', '', 0.00, 100.52, '2025-06-10 20:25:59', 'A', '<15', '628.23'),
(250, '16', '29', '', 0.00, 100.20, '2025-06-10 20:25:59', 'B', '16-29', '626.23'),
(251, '30', '59', '', 0.00, 161.73, '2025-06-10 20:25:59', 'C', '30-59', '1010.83'),
(252, '60', '99', '', 0.00, 206.73, '2025-06-10 20:25:59', 'D', '60-99', '1292.07'),
(253, '100', '139', '', 0.00, 258.16, '2025-06-10 20:25:59', 'E', '100-139', '1613.49'),
(254, '140', '199', '', 0.00, 297.88, '2025-06-10 20:25:59', 'F', '140-199', '1861.76'),
(255, '200', '259', '', 0.00, 416.43, '2025-06-10 20:25:59', 'G', '200-259', '2602.68'),
(256, '260', '300', '', 0.00, 441.24, '2025-06-10 20:25:59', 'H', '260-300', '2757.73'),
(257, '301', '400', '', 0.00, 444.76, '2025-06-10 20:25:59', 'I', '301-400', '2779.78'),
(258, '401', '500', '', 0.00, 448.35, '2025-06-10 20:25:59', 'J', '401-500', '2802.2'),
(259, '501', '600', '', 0.00, 505.41, '2025-06-10 20:25:59', 'K', '501-600', '3158.84'),
(261, 'NKR', 'KSM DEPOT', '', 0.00, 327.67, '2025-06-10 20:25:59', 'M', 'NKR-KSM Depot', '2047.93'),
(262, 'NAKURU', 'SAGANA', '', 0.00, 416.48, '2025-06-10 20:25:59', 'N', 'NAKURU-SAGANA', '2603'),
(263, 'ELD', 'KISII', '', 0.00, 416.43, '2025-06-10 20:25:59', 'O', 'ELD-KISII', '2602.68'),
(264, 'ELD', 'KSM', '', 0.00, 288.53, '2025-06-10 20:25:59', 'P', 'ELD-KSM', '1803.31'),
(265, 'ELD', 'MIGORI', '', 0.00, 493.15, '2025-06-10 20:25:59', 'Q', 'ELD-MIGORI', '3082.17'),
(266, 'ELD', 'BONDO/BUSIA', '', 0.00, 332.93, '2025-06-10 20:25:59', 'R', 'ELD-BONDO/BUSIA', '2080.79'),
(267, 'NRB', 'KSM DEPOT', '', 0.00, 489.19, '2025-06-10 20:25:59', 'S', 'NRB-KSM Depot', '3057.45'),
(268, 'NRB', 'KAKAMEGA', '', 0.00, 489.19, '2025-06-10 20:25:59', 'T', 'NRB-KAKAMEGA', '3057.45'),
(269, 'NRB', 'ELD', '', 0.00, 444.80, '2025-06-10 20:25:59', 'U', 'NRB-ELD', '2780'),
(270, 'NRB', 'SAGANA DEPOT', '', 0.00, 206.72, '2025-06-10 20:25:59', 'V', 'NRB-SAGANA DEPOT', '1292'),
(271, 'NRB', 'MWINGI', '', 0.00, 350.45, '2025-06-10 20:25:59', 'W', 'NRB-MWINGI', '2190.3'),
(272, 'NRB', 'NYERI', '', 0.00, 332.93, '2025-06-10 20:25:59', 'X', 'NRB-NYERI', '2080.79'),
(273, 'NRB', 'NANYUKI', '', 0.00, 350.45, '2025-06-10 20:25:59', 'Y', 'NRB-NANYUKI', '2190.3'),
(274, 'NRB', 'EMBU', '', 0.00, 288.53, '2025-06-10 20:25:59', 'Z', 'NRB-EMBU', '1803.31'),
(275, 'NRB', 'MAUA', '', 0.00, 519.10, '2025-06-10 20:25:59', 'AA', 'NRB-MAUA', '3244.38'),
(276, 'NRB', 'GARISSA', '', 0.00, 474.72, '2025-06-10 20:25:59', 'AB', 'NRB-GARISSA', '2967.03'),
(277, 'NAKURU', 'ELDORET', '', 0.00, 163.83, '2025-06-10 20:25:59', 'NAK1', 'NAKURU-ELDORET', '1023.96'),
(278, 'NAKURU', 'NAIROBI', '', 0.00, 163.83, '2025-06-10 20:25:59', 'NAK2', 'NAKURU-NAIROBI', '1023.96'),
(279, 'ELDORET', 'NAIROBI', '', 0.00, 244.62, '2025-06-10 20:25:59', 'ELD1', 'ELDORET-NAIROBI', '1528.87');

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id` int(11) NOT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `distance_km` varchar(50) DEFAULT NULL,
  `rate_per_ton` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rates`
--

INSERT INTO `rates` (`id`, `from_location`, `to_location`, `distance_km`, `rate_per_ton`) VALUES
(1, 'zone', 'A', '', 0.00),
(2, 'zone', 'B', '', 0.00),
(3, 'zone', 'C', '', 0.00),
(4, 'zone', 'D', '', 0.00),
(5, 'zone', 'E', '', 0.00),
(6, 'zone', 'F', '', 0.00),
(7, 'zone', 'G', '', 0.00),
(8, 'zone', 'H', '', 0.00),
(9, 'zone', 'I', '', 0.00),
(10, 'zone', 'J', '', 0.00),
(11, 'zone', 'K', '', 0.00),
(12, 'zone', 'L', '', 0.00),
(13, 'route', '', 'ELDORET', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `driver_id` varchar(20) NOT NULL,
  `shipment_number` varchar(50) NOT NULL,
  `customer_source` varchar(100) DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `vehicle_reg` varchar(20) DEFAULT NULL,
  `net_weight` float DEFAULT NULL,
  `distance_km` float DEFAULT NULL,
  `rate_per_tonne` float DEFAULT NULL,
  `vat_percent` float DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `status` enum('Pending','In Progress','Delivered') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','MANAGER','DRIVER') NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `national_id`, `created_at`) VALUES
(4, 'Cate', 'machariacate5@gmail.com', '1234', 'MANAGER', '12345678', '2025-06-10 17:42:24'),
(5, 'Catherine', 'machariacatherine407@gmail.com', '9685', 'ADMIN', '39827754', '2025-06-10 17:43:05'),
(6, 'Martin', 'martin404@gmail.com', '2345', 'DRIVER', '23456789', '2025-06-10 17:43:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `manual_rates`
--
ALTER TABLE `manual_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_route` (`from_location`,`to_location`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_route` (`from_location`,`to_location`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipment_number` (`shipment_number`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `national_id` (`national_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `manual_rates`
--
ALTER TABLE `manual_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- AUTO_INCREMENT for table `rates`
--
ALTER TABLE `rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`national_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
