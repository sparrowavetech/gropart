-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2022 at 07:55 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gropart_update`
--

-- --------------------------------------------------------

--
-- Table structure for table `mp_stores`
--

CREATE TABLE `mp_stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `vendor_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `shop_category` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mp_stores`
--

INSERT INTO `mp_stores` (`id`, `name`, `email`, `phone`, `address`, `country`, `state`, `city`, `customer_id`, `logo`, `description`, `content`, `status`, `vendor_verified_at`, `created_at`, `updated_at`, `zip_code`, `company`, `is_verified`, `shop_category`) VALUES
(12, 'SB Marketing', 'abhinoody@gmail.com', '7357845792', 'Gol Choraha, Jhala Manna Circle', '1', '30', '36', 37, 'stores/logo-400-400.jpg', 'Our business depends on the convenience of the farmers. We provide almost all the necessities which are related to agriculture. All the necessary items are available in our shop. We deal in all types of tractor company genuine spare parts and service as well as earthmover machines genuine spare parts and agriculture machine spare parts and service.', '<p>Our business depends on the convenience of the farmers. We provide almost all the necessities which are related to agriculture. All the necessary items are available in our shop. We deal in all types of tractor company genuine spare parts and service as well as earthmover machines genuine spare parts and agriculture machine spare parts and service.</p><ul><li>We Believe in the quality of resources.</li><li>All resources will be of high quality.</li><li>We are associated with the country\'s most trusted and well-known companies.</li></ul>', 'published', NULL, '2022-05-21 17:44:53', '2022-11-09 18:48:44', NULL, NULL, 1, 'wholesaler');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mp_stores`
--
ALTER TABLE `mp_stores`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mp_stores`
--
ALTER TABLE `mp_stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
