-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 07, 2026 at 02:11 AM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database_msa`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT '0',
  `size_color_quantities` json DEFAULT NULL,
  `images` json DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `variant_skus` json DEFAULT NULL,
  `information` json DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_quantities` json DEFAULT NULL,
  `color` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `sku`, `category`, `price`, `stock`, `size_color_quantities`, `images`, `status`, `created_at`, `variant_skus`, `information`, `size`, `size_quantities`, `color`) VALUES
('1690239', 'sample5', 'COL-SAM-J04R', 'collections', 88.00, 2, '[]', '[\"69a5878917f6e.png\"]', 'Low Stock', '2026-03-02 12:50:17', '[]', '{\"brand\": \"how\"}', '', '[]', '[]'),
('2064173', 'sample4', 'ACC-SAM-AQMK', 'accessories', 799.00, 3, '[]', '[\"69a0fcedb7e60.png\"]', 'Low Stock', '2026-02-27 02:09:49', '[]', 'null', '', '[]', '[]'),
('3126415', 'sticker', 'ACC-STI-7JX9', 'accessories', 80.00, 5, '{\"\": {\"red\": {\"sku\": \"ACC-STI-7JX9-RED\", \"quantity\": 3}, \"blue\": {\"sku\": \"ACC-STI-7JX9-BLUE\", \"quantity\": 1}, \"black\": {\"sku\": \"ACC-STI-7JX9-BLACK\", \"quantity\": 1}}}', '[\"699d851464f38.png\"]', 'Low Stock', '2026-02-24 11:01:40', '{\"blue\": \"ACC-STI-7JX9-BLUE\"}', '{\"brand\": \"\", \"material\": \"\", \"dimensions\": \"\", \"product_info\": \"\"}', '', '{\"\": 3}', '[\"blue\", \"black\", \"red\"]'),
('3687304', 'sample7', 'SHO-SAM-OZ9M', 'Shoes', 799.00, 7, '{\"39\": {\"blue\": {\"sku\": \"SHO-SAM-OZ9M-39-BLUE\", \"quantity\": 3}}, \"40\": {\"red\": {\"sku\": \"SHO-SAM-OZ9M-40-RED\", \"quantity\": 4}}, \"41\": [], \"42\": [], \"43\": [], \"44\": [], \"45\": [], \"46\": [], \"47\": []}', '[\"69a102c239afd.png\"]', 'Low Stock', '2026-02-27 02:34:42', '{\"40-red\": \"SHO-SAM-OZ9M-40-RED\", \"39-blue\": \"SHO-SAM-OZ9M-39-BLUE\"}', '[]', '39,40,41,47,42,43,44,45,46', '{\"39\": 3, \"40\": 4, \"41\": 3}', '[\"blue\", \"red\"]'),
('5771129', 'sapatos1', 'SHO-SAP-KU3D', 'Shoes', 799.00, 0, '{\"39\": {\"black\": {\"sku\": \"SHO-SAP-KU3D-39-BLACK\", \"quantity\": 0}}}', '[\"69a5922640c68.png\"]', 'Low Stock', '2026-03-02 13:35:34', '{\"39-black\": \"SHO-SAP-KU3D-39-BLACK\"}', 'null', '39', '{\"39\": 0}', '[\"black\"]'),
('6605820', 'sample1', 'ACC-SAM-W79A', 'accessories', 799.00, 19, '{\"\": {\"red\": {\"sku\": \"ACC-SAM-W79A-RED\", \"quantity\": 4}, \"blue\": {\"sku\": \"ACC-SAM-W79A-BLUE\", \"quantity\": 1}, \"black\": {\"sku\": \"ACC-SAM-W79A-BLACK\", \"quantity\": 1}, \"brown\": {\"sku\": \"ACC-SAM-W79A-BROWN\", \"quantity\": 2}, \"green\": {\"sku\": \"ACC-SAM-W79A-GREEN\", \"quantity\": 1}, \"white\": {\"sku\": \"ACC-SAM-W79A-WHITE\", \"quantity\": 3}, \"orange\": {\"sku\": \"ACC-SAM-W79A-ORANGE\", \"quantity\": 2}, \"violet\": {\"sku\": \"ACC-SAM-W79A-VIOLET\", \"quantity\": 4}, \"yellow\": {\"sku\": \"ACC-SAM-W79A-YELLOW\", \"quantity\": 1}}}', '[\"699d5b2ae4a19.png\"]', 'In Stock', '2026-02-24 08:02:50', '{\"red\": \"ACC-SAM-W79A-RED\", \"blue\": \"ACC-SAM-W79A-BLUE\", \"black\": \"ACC-SAM-W79A-BLACK\", \"brown\": \"ACC-SAM-W79A-BROWN\", \"green\": \"ACC-SAM-W79A-GREEN\", \"white\": \"ACC-SAM-W79A-WHITE\", \"orange\": \"ACC-SAM-W79A-ORANGE\", \"violet\": \"ACC-SAM-W79A-VIOLET\", \"yellow\": \"ACC-SAM-W79A-YELLOW\"}', '{\"brand\": \"Nikee\", \"material\": \"Leather\", \"dimensions\": \"30 x 50 cm\", \"product_info\": \"\"}', '', '{\"\": 1}', '[\"red\", \"blue\", \"black\", \"brown\", \"green\", \"white\", \"orange\", \"violet\", \"yellow\"]'),
('8547129', 'first', 'ACC-FIR-G7IV', 'accessories', 88.00, 3, '[]', '[\"69a598f1718bd.png\"]', 'Low Stock', '2026-03-02 14:04:33', '[]', 'null', '', '[]', '[]'),
('9970763', 'sapatos', 'SHO-SAP-3JOX', 'Shoes', 799.00, 3, '{\"39\": {\"black\": {\"sku\": \"SHO-SAP-3JOX-39-BLACK\", \"quantity\": 0}}, \"40\": {\"black\": {\"sku\": \"SHO-SAP-3JOX-40-BLACK\", \"quantity\": 1}}}', '[\"69a109577ad16.png\"]', 'Low Stock', '2026-03-01 07:36:53', '{\"41-red\": \"SHO-SAP-3JOX-41-RED\", \"39-black\": \"SHO-SAP-3JOX-39-BLACK\", \"40-black\": \"SHO-SAP-3JOX-40-BLACK\"}', '[]', '39,40', '{\"39\": 0, \"40\": 1, \"41\": 2}', '[\"black\"]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
