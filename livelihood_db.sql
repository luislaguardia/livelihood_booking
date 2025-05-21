-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 08:16 AM
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
-- Database: `livelihood_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service` varchar(255) NOT NULL,
  `booking_date` date NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `worker_name` varchar(255) DEFAULT NULL,
  `skill` varchar(255) DEFAULT NULL,
  `earnings` decimal(10,2) DEFAULT 0.00,
  `price` decimal(10,2) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `worker_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service`, `booking_date`, `status`, `worker_name`, `skill`, `earnings`, `price`, `customer_name`, `worker_id`) VALUES
(43, 45, 'Plumbing', '2025-05-15', 'Pending', 'Alren Samonte', 'Plumbing', 0.00, 1000.00, '123', 2);

-- --------------------------------------------------------

--
-- Table structure for table `booking_history`
--

CREATE TABLE `booking_history` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `worker_name` varchar(255) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `status` enum('Cancelled','Approved') DEFAULT NULL,
  `cancelled_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(2, 'eubenelle ', 'ben@gmail.com', 'hi', '2025-05-09 20:25:27'),
(4, 'eubenelle ', 'ben@gmail.com', 'hi', '2025-05-09 20:27:17'),
(5, 'eubenelle ', 'ben@gmail.com', 'hi', '2025-05-09 20:27:31'),
(6, '213', '213@gmail.com', 'i need help', '2025-05-10 03:43:04'),
(7, '213', '213@gmail.com', '213', '2025-05-10 03:44:08'),
(8, '213', '213@gmail.com', 'help', '2025-05-10 03:47:55');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `message`, `created_at`, `email`) VALUES
(10, '213', 'need more improvement', '2025-05-10 03:46:49', '');

-- --------------------------------------------------------

--
-- Table structure for table `pending_admins`
--

CREATE TABLE `pending_admins` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`) VALUES
(1, 'Plumbing'),
(2, 'Electrical'),
(3, 'Carpentry'),
(4, 'Welder');

-- --------------------------------------------------------

--
-- Table structure for table `skilled_workers`
--

CREATE TABLE `skilled_workers` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `contact_info` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `skill` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skilled_workers`
--

INSERT INTO `skilled_workers` (`id`, `fullname`, `skills`, `contact_info`, `photo`, `name`, `skill`, `image_path`, `price`, `contact_number`, `status`, `category`) VALUES
(2, 'Alren Samonte', '', '09198765432', 'alren.jpg', 'Alren', 'Plumbing', NULL, 1000.00, NULL, '', NULL),
(3, 'Jazmin', '', '09122334455', 'jazmin.jpg', 'Jazmin', 'Electrical,Mason,Welder', NULL, 645.00, NULL, '', NULL),
(4, 'Ellen Grace', 'Carpentry, Plumbing', '123-456-7890', 'ellen.jpg', 'Ellen', 'carpentry', NULL, 500.00, NULL, '', NULL),
(7, 'Eubenelle Billete', '', NULL, 'Eubenlle.jpg', NULL, 'Programmer,electrician', NULL, 300.00, NULL, '', NULL),
(9, 'Joseph Villanueva', '', NULL, 'Joseph.jpg', NULL, 'Hitman,NinJA Hokage', NULL, 645.00, NULL, '', NULL),
(10, 'Ralph Lauren Espiritu', NULL, NULL, NULL, NULL, 'Drifter,Mason', NULL, 600.00, NULL, '', NULL),
(11, 'Howard', NULL, NULL, NULL, NULL, NULL, NULL, 500.00, NULL, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trash_history`
--

CREATE TABLE `trash_history` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `worker_name` varchar(255) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `cancelled_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) DEFAULT 'Trash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `email`, `created_at`, `role`, `is_approved`, `approved_by`, `status`) VALUES
(45, '123', '$2y$10$tYVFJKdVG9Q1eWI5MpMOhOuHoFqF77aeDfKxy5/m37Yeo8WWR/73q', '123@gmail.com', '2025-05-15 01:10:30', 'customer', 0, NULL, 'Pending'),
(46, 'admin', '$2y$10$h1OlMnnnVjT8VwzyAwfhy.L2J7epVtWjmbrep1oNHOkTQ9mVjbeGe', 'admin@gmail.com', '2025-05-15 01:10:53', 'admin', 1, NULL, 'Pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_ibfk_1` (`user_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- Indexes for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pending_admins`
--
ALTER TABLE `pending_admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skilled_workers`
--
ALTER TABLE `skilled_workers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trash_history`
--
ALTER TABLE `trash_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `booking_history`
--
ALTER TABLE `booking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pending_admins`
--
ALTER TABLE `pending_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `skilled_workers`
--
ALTER TABLE `skilled_workers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `trash_history`
--
ALTER TABLE `trash_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `skilled_workers` (`id`);

--
-- Constraints for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD CONSTRAINT `booking_history_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `trash_history`
--
ALTER TABLE `trash_history`
  ADD CONSTRAINT `trash_history_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
