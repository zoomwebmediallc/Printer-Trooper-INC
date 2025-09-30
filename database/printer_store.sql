-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 06:35 PM
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
-- Database: `printer_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `service` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(10) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `full_name`, `email`, `phone`, `service`, `date`, `time`, `message`, `created_at`) VALUES
(1, 'Sahiba Bano', 'sahiba.krp2000@gmail.com', '8595046293', 'consultation', '2025-09-26', '13:00', 'testing', '2025-09-24 08:30:32');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Inkjet Printers', 'High-quality inkjet printers for home and office use', '2025-09-24 05:46:48'),
(2, 'Laser Printers', 'Fast and efficient laser printers', '2025-09-24 05:46:48'),
(3, 'All-in-One Printers', 'Multifunction printers with scanning and copying', '2025-09-24 05:46:48'),
(4, 'Photo Printers', 'Specialized printers for photo printing', '2025-09-24 05:46:48'),
(5, 'Ink Cartridges', 'Reliable Printing Every Time', '2025-09-24 05:46:48');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(32) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(64) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `session_id`, `customer_email`, `total_amount`, `status`, `shipping_address`, `payment_method`, `stripe_payment_intent_id`, `order_date`) VALUES
(1, 'PS-20250924-CF8CE5', NULL, 'dcii4dghpvt69p5of50eu7ba3u', 'sahiba.krp2000@gmail.com', 229.99, 'processing', 'testing', 'stripe', 'pi_3SAlZ108O9gYqTfw1TNXJSJ7', '2025-09-24 05:48:29'),
(2, 'PS-20250924-A8C7C7', 1, '0rj1mb77agkpncmbs42rqe8pn7', 'sahiba.krp2000@gmail.com', 609.98, 'processing', 'Addresssssssssss', 'stripe', 'pi_3SAs6N08O9gYqTfw0PzWWsrg', '2025-09-24 12:47:20'),
(3, 'PS-20250925-1731D1', 1, 'arf1sb6jfrli34925jgqtcpsa2', 'sahiba.krp2000@gmail.com', 279.99, 'delivered', 'Sec 22 Noida near shivammarket dharam public school', 'stripe', 'pi_3SBAmz08O9gYqTfw0Ss2Goxd', '2025-09-25 08:44:34'),
(4, 'PS-20250925-A58F22', 1, 'arf1sb6jfrli34925jgqtcpsa2', 'sahiba.krp2000@gmail.com', 329.99, 'shipped', 'Sec 22 Noida near shivammarket dharam public school', 'stripe', 'pi_3SBBnB08O9gYqTfw1zmByIBg', '2025-09-25 09:48:50'),
(5, 'PS-20250925-1F6591', 1, 'arf1sb6jfrli34925jgqtcpsa2', 'sahiba.krp2000@gmail.com', 329.99, 'processing', 'Noidaeeeeee', 'stripe', 'pi_3SBD3Y08O9gYqTfw1sMSN4xT', '2025-09-25 11:09:49'),
(6, 'PS-20250925-1D6D6C', 2, 'i7he7us95njjhsoje1gvcnghqp', 'sahiba.krp2000@gmail.com', 263.99, 'processing', 'Sec 22 Noida near shivammarket dharam public school', 'stripe', 'pi_3SBFA908O9gYqTfw0zcx9EQc', '2025-09-25 13:24:46'),
(7, 'PS-20250925-B54706', 2, 'i7he7us95njjhsoje1gvcnghqp', 'sahiba.krp2000@gmail.com', 27.59, 'processing', 'Sec 22 Noida near shivammarket dharam public school', 'stripe', 'pi_3SBFE708O9gYqTfw1Tvdt8TG', '2025-09-25 13:28:52'),
(8, 'PS-20250926-85E67A', 1, 'tl8rlh97lib9ag732o8ub2gqit', 'sahiba.krp2000@gmail.com', 89.99, 'processing', 'Sec 22 Noida near shivammarket dharam public school', 'stripe', 'pi_3SBZ2Y08O9gYqTfw0sQAtWD0', '2025-09-26 10:38:15'),
(9, 'PS-20250926-384DF2', 1, 'tl8rlh97lib9ag732o8ub2gqit', 'sahiba.krp2000@gmail.com', 43.19, 'processing', 'Sec 22 Noida near shivammarket dharam public school', 'stripe', 'pi_3SBZ9w08O9gYqTfw1UQdeDfW', '2025-09-26 10:45:53'),
(10, 'PS-20250926-C556A6', 1, 'tl8rlh97lib9ag732o8ub2gqit', 'sahiba.krp2000@gmail.com', 1156.87, 'cancelled', 'Sec 22 Noida near shivammarket dharam public school', 'stripe', 'pi_3SBbFO08O9gYqTfw0gAh1fVn', '2025-09-26 12:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 6, 1, 229.99),
(2, 2, 23, 1, 329.99),
(3, 2, 22, 1, 279.99),
(4, 3, 22, 1, 279.99),
(5, 4, 23, 1, 329.99),
(6, 5, 23, 1, 329.99),
(7, 6, 23, 1, 329.99),
(8, 7, 20, 1, 27.59),
(9, 8, 41, 1, 89.99),
(10, 9, 19, 1, 43.19),
(11, 10, 31, 13, 88.99);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 1, '77db582e6b04f3f3d7f4c3160967a1eec969d4e17b7e3e84c044aacbc9ca2828', '2025-09-30 18:43:06', '2025-09-30 18:15:12', '2025-09-30 21:43:06'),
(2, 1, '0099ce29d1214335c0b537bf543d70dc7202085e17919ecaec502679022c63ef', '2025-09-30 18:45:49', '2025-09-30 18:16:50', '2025-09-30 21:45:49'),
(3, 1, '0b68071ef93c8251f69930883345ca9229495c90924a57e85551bf7fd348641e', '2025-09-30 18:51:27', NULL, '2025-09-30 21:51:27'),
(4, 3, '4792e5a07fe4ca34e247025ede13f1b1a4826ab85d3b2fd0ca175d13e3324de8', '2025-09-30 18:51:43', NULL, '2025-09-30 21:51:43'),
(5, 3, 'd1f22bcb1ce763d62539c43a1246d692ef0571fa5d52feeb7bf824894331a1b0', '2025-09-30 18:52:00', '2025-09-30 18:24:27', '2025-09-30 21:52:00'),
(6, 3, '89a6fbf39a14848c3a64d76c9f1f587fa66a12aa6de6edec76c24cdda24c91ee', '2025-09-30 19:00:28', '2025-09-30 18:30:55', '2025-09-30 22:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock_quantity`, `category_id`, `image_url`, `brand`, `model`, `specifications`, `created_at`, `updated_at`) VALUES
(1, 'Canon PIXMA TS3420', 'Canon PIXMA TS3420 Wireless All-in-One Inkjet Printer with print, copy, scan and mobile printing support. Compact design with borderless photo printing up to 5x7 inches.', 79.99, 25, 3, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-ts-3720.webp', 'Canon', 'PIXMA TS3420', 'Print, Copy, Scan | Wireless | Mobile Printing | 4800x1200 DPI', '2025-09-24 00:16:48', '2025-09-26 11:16:49'),
(2, 'HP - LaserJet M110w', 'Wireless Black and Laser Printer - White', 202.99, 75, 2, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-laserjet-m110w(2).webp', 'HP', 'LaserJet Pro M15w', 'Laser | Wireless | Mobile Printing | 600x600 DPI | 19 PPM', '2025-09-24 00:16:48', '2025-09-26 12:28:03'),
(3, 'Epson - EcoTank ET-2800', 'Epson - EcoTank ET-2800 Wireless All-in-One Supertank Inkjet Printer - White', 238.99, 20, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/epson-all-in-one.webp', 'Epson', 'C11CJ66202', 'Inkjet | All-in-One | Supertank | Wireless | 5760x1440 DPI', '2025-09-24 00:16:48', '2025-09-26 08:01:07'),
(4, 'Brother - HL-L2460DW Wireless', 'Brother - HL-L2460DW Wireless Black-and-White Refresh Subscription Eligible Laser Printer, Great for Home Offices - Gray', 214.99, 30, 2, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/brother-hl-l2420dw(4).webp', 'Brother', 'HL-L2460DW', 'Monochrome Laser | Wireless | Duplex Printing | 2400x600 DPI | 32 PPM', '2025-09-24 00:16:48', '2025-09-26 12:31:48'),
(5, 'Canon - SELPHY CP1500 Wireless', 'Compact Photo Printer - Black - Open Box - Fair', 214.99, 120, 4, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-selphy-cp1500.webp', 'Canon', '5540C002', 'Dye Sublimation | Photo Printing | Wireless | 300x300 DPI | 4x6 inch prints', '2025-09-24 00:16:48', '2025-09-26 08:07:59'),
(6, 'HP - OfficeJet Pro 9125e Wireless', 'All-in-one color inkjet printer for small businesses, AI-Enabled AiO Inkjet Printer w/ 3 Months of Instant Ink with HP+ (+ 1 Bonus Month w/ Code) - White', 262.99, 170, 3, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-officejetpro-9125e.webp', 'HP', 'OJ Pro 9125e /403X0A#B1H', 'Color Inkjet | All-in-One | Wireless | Duplex | 1200x1200 DPI | 22 PPM', '2025-09-24 00:16:48', '2025-09-26 08:07:55'),
(7, 'HP DeskJet 2855e', 'HP - DeskJet 2855e Wireless AI-Enabled AiO Inkjet Printer w/ 3 Mo. of Instant Ink (+ 1 Bonus Mo. w/ Code), Perfect for School - White', 70.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-printer-2855.webp', 'HP', 'DJ 2855E/588S5A#B1H', 'Networking : Wireless | Printer Type : All In One | Instant Ink : 3 months + 1 bonus month', '2025-09-22 13:00:00', '2025-09-26 08:09:11'),
(8, 'HP Envy Inspire 7955e', 'HP - Envy Inspire 7955e Wireless AI-Enabled AiO Inkjet Photo Printer w/ 3 Mo. of Instant Ink (+1 Bonus Month w/ Code) - White & Sandstone', 274.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-envy-7955e.webp', 'HP', 'ENVY Inspire 7955e/1W2Y8A#B1H', 'Networking : Wireless | Printer Type : All In One | Instant Ink : 3 months + 1 bonus month', '2025-09-22 13:00:00', '2025-09-26 08:19:36'),
(9, 'HP OfficeJet Pro 9125e', 'HP - OfficeJet Pro 9125e Wireless AI-Enabled AiO Inkjet Printer w/ 3 Months of Instant Ink with HP+ (+ 1 Bonus Month w/ Code) - White', 262.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-officejetpro-9125e.webp', 'HP', 'OJ Pro 9125e /403X0A#B1H', 'Networking : Wireless, Wired | Printer Type : All In One | Instant Ink : 3 months + 1 bonus month', '2025-09-22 13:00:00', '2025-09-26 08:19:44'),
(10, 'HP Envy 6165e', 'HP - Envy 6165e Wireless AI-Enabled AiO Inkjet Printer w/ 6 Mo. of Instant Ink (+ 1 Bonus Month w/ Code), Perfect for School - White', 106.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-envy-6165e.webp', 'HP', '714L6A#B1H', 'Networking : Wireless | Printer Type : All In One | Instant Ink : 6 months + 1 bonus month', '2025-09-22 13:00:00', '2025-09-26 08:19:53'),
(11, 'HP DeskJet 4255e', 'HP - DeskJet 4255e Wireless AI-Enabled AiO Inkjet Printer w/ 3 Mo. of Instant Ink (+ 1 Bonus Mo. w/ Code), Perfect for School - White', 94.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-4255e.webp', 'HP', 'DJ 4255E/588S6A#B1H', 'Networking : Wireless | Printer Type : All In One | Instant Ink : 3 months + 1 bonus month', '2025-09-22 13:00:00', '2025-09-26 08:20:09'),
(12, 'Canon PIXMA TR7020a', 'Canon - PIXMA TS7720 Wireless All-In-One Inkjet Printer - White', 94.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-7020a.webp', 'Canon', '4460C052', 'Networking : Wireless, Wired | Printer Type : Printer, Copier, Scanner, All In One', '2025-09-22 13:00:00', '2025-09-26 08:19:23'),
(13, 'Canon PIXMA TS6420a', 'Canon - PIXMA TS6420a Wireless All-In-One Inkjet Printer - Black', 168.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-6420a.webp', 'Canon', '4462C082', 'Networking : Wireless | Printer Type : Printer, Copier, Scanner, All In One', '2025-09-22 13:00:00', '2025-09-26 08:19:06'),
(14, 'Canon PIXMA TS7720', 'Canon - PIXMA TS7720 Wireless All-In-One Inkjet Printer - White', 95.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-7720.webp', 'Canon', '6256C002', 'Networking : Wireless, Wired | Printer Type : Printer, Scanner, Copier, All In One', '2025-09-22 13:00:00', '2025-09-26 08:19:13'),
(15, 'Canon - PIXMA TS3720', 'Canon - PIXMA TS3720 Wireless All-In-One Inkjet Printer - White', 103.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-ts-3720.webp', 'Canon', '6671C022', 'Networking : Wireless, Wired | Printer Type : Printer, Copier, Scanner, All In One', '2025-09-22 13:00:00', '2025-09-26 08:21:43'),
(16, 'Canon PIXMA TS202 Inkjet Printer', 'Compact and affordable inkjet printer for simple document printing. No wireless support.', 45.59, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-ts202.webp', 'Canon', '2319C002', 'Networking : Not Applicable | Printer Type: Printer', '2025-09-22 13:00:00', '2025-09-24 07:08:06'),
(17, 'Brother - HL-L2405W', 'Brother - HL-L2405W Wireless Black-and-White Refresh Subscription Eligible Laser Printer, Great for Home - Gray', 161.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/brother-l2405w.webp', 'Brother', 'HL-L2405W', 'Networking : Wireless | Printer Type : Printer', '2025-09-22 13:00:00', '2025-09-26 08:23:49'),
(18, 'Epson WorkForce Pro WF-3820', 'Epson - WorkForce Pro WF-3820 Wireless All-in-One Printer - Black\nProfessional-grade all-in-one printer with wireless and wired options. Great for business needs.', 119.99, 100, 4, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/epson-pro-wf-3820.webp', 'Epson', 'C11CJ07201', 'Networking : Wired, Wireless | Printer Type : All In One', '2025-09-22 13:00:00', '2025-09-26 08:25:39'),
(19, 'Canon 243 / CL-244 Value Pack', 'Includes black and color ink for vibrant photo and document prints. Smudge-resistant and easy to replace.', 43.19, 99, 5, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-234-cl-244.webp', 'Canon', '1287C006', '', '2025-09-22 13:00:00', '2025-09-26 10:45:53'),
(20, 'HP 67 Standard Capacity Ink Cartridge - Black', 'Standard black ink cartridge for everyday document printing.', 27.59, 99, 5, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-inkcartrages.webp', 'HP', '3YM56AN#140', '', '2025-09-22 13:00:00', '2025-09-25 13:28:52'),
(21, 'Canon PIXMA G3270 MegaTank', 'Canon - PIXMA MegaTank G3270 Wireless All-In-One SuperTank Inkjet Printer - BLACK', 297.99, 80, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-3260.webp', 'Canon', '5805C002', 'Networking : Wireless | Supertank | Print, Copy, Scan', '2025-09-24 01:30:00', '2025-09-26 09:20:03'),
(22, 'HP Smart Tank 7301', 'HP - Smart Tank 7301 Wireless AI-Enabled All-In-One Supertank Inkjet Printer with up to 2 Years of Ink Included - White & Slate', 538.99, 100, 3, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-smart-printer-5101.webp', 'HP', 'SMART TANK 7301/28B70A#B1H', 'Networking : Wireless | Supertank | Duplex', '2025-09-24 01:30:00', '2025-09-26 09:15:02'),
(23, 'Epson - EcoTank ET-3850', 'Epson - EcoTank ET-3850 All-in-One Supertank Inkjet Printer - White', 514.99, 76, 3, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/epson-ecotank-2800.webp', 'Epson', 'C11CJ61201', 'Networking : Wireless, Ethernet | Duplex | 5760x1440 DPI', '2025-09-24 01:30:00', '2025-09-26 09:16:34'),
(24, 'Brother - MFC-L2820DW', 'Brother - MFC-L2820DW Wireless Black-and-White Refresh Subscription Eligible All-In-One Laser Printer, Great for Home Offices - Gray', 319.99, 70, 2, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/brother-mfc-l2750w.webp', 'Brother', 'MFC-L2820DW', 'Monochrome Laser | Wireless | Duplex | 36 PPM', '2025-09-24 01:30:00', '2025-09-26 09:19:36'),
(25, 'Canon - imageCLASS MF451dw', 'Wireless Black-and- All-In-One Laser Printer - White High-speed laser all-in-one printer for office use.', 358.99, 50, 2, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-image-class-mf445dw.webp', 'Canon', '5161C013', 'Monochrome Laser | Wired & Wireless | Duplex | Touchscreen', '2025-09-24 01:30:00', '2025-09-26 07:20:59'),
(26, 'HP - LaserJet M140w', 'Wireless color laser printer with fast performance.', 166.99, 45, 2, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-color-laser-jet-pro-m255w.webp', 'HP', 'M140W/7MD72F#BGJ', 'Color Laser | Duplex | Wireless, Ethernet | 22 PPM', '2025-09-24 01:30:00', '2025-09-26 07:25:09'),
(27, 'Epson Expression Home XP - 4200', 'Compact wireless inkjet printer with scanner & copier.', 120.99, 100, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/epson-expression-home-xp-4100.webp', 'Epson', 'C11CK65201', 'Networking : Wireless | Print, Copy, Scan, \nAll In One', '2025-09-24 01:30:00', '2025-09-26 07:30:42'),
(28, 'Canon - PIXMA MegaTank G6020', 'MegaTank wireless all-in-one printer for business needs.', 599.99, 30, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-g6020.webp', 'Canon', 'GX6020', 'Inkjet | Supertank | Wireless | Duplex | 24 PPM', '2025-09-24 01:30:00', '2025-09-26 08:29:01'),
(29, 'Brother - HL-L3280CDW ', 'Brother - HL-L3280CDW Wireless Digital Color Printer with Laser Quality Output , Great for Home Offices - White', 412.99, 60, 2, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/brother-hl-l3280cdw.webp', 'Brother', 'HL-L3290CDW', 'Color Laser | Wireless | Duplex | 25 PPM', '2025-09-24 01:30:00', '2025-09-26 08:33:04'),
(30, 'HP - Smart Tank 7602 Wireless', 'HP - Smart Tank 7602 Wireless AI-Enabled AiO Supertank Inkjet Printer w/ up to 2 Years of Ink Included, Perfect for School - Dark Surf Blue', 413.99, 75, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-smart-tank-7602.webp', 'HP', 'HP SMART TANK 7602/28B98A#B1H', 'Wireless | Cloud & Mobile Printing | Voice Assistant Compatible', '2025-09-24 01:30:00', '2025-09-26 08:34:54'),
(31, 'Canon - PIXMA TR4720', 'Canon - PIXMA TR4720 Wireless All-In-One Inkjet Printer - Black', 88.99, 37, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-tr4720(31).webp', 'Canon', '5074C002', 'Wireless | Portable | 9600x2400 DPI', '2025-09-24 01:30:00', '2025-09-26 12:59:40'),
(32, 'Epson - EcoTank ET-2980', 'Epson - EcoTank ET-2980 Wireless All-in-One Color Supertank Inkjet Printer - White', 799.99, 20, 4, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/epson-ecotank-et-2980(32).webp', 'Epson', 'C11CL41202', 'Photo Printing | Wireless | 5760x1440 DPI', '2025-09-24 01:30:00', '2025-09-26 11:47:25'),
(33, 'HP Sprocket Photo Printer', 'HP - Sprocket Select Portable Instant Photo Printer 2.3x3.4 and Zink Paper Bundle - Eclipse', 142.99, 120, 4, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-sprocket-select(33).webp', 'HP', 'Sprocket', 'Photo Printer | Bluetooth | 2x3 inch Prints', '2025-09-24 01:30:00', '2025-09-26 11:47:45'),
(34, 'Canon - PIXMA PRO-200S', 'Professional Wireless Inkjet Photo Printer - Black', 599.99, 25, 4, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-pro-200s(34).webp', 'Canon', 'Pro-200', 'Photo Printer | Wireless | 4800x2400 DPI', '2025-09-24 01:30:00', '2025-09-26 11:48:00'),
(35, 'Brother - INKvestment MFC-J1215W', 'Brother - INKvestment MFC-J1215W Wireless All-in-One Inkjet Printer with up to 1-Year of Ink In-box - White/Gray', 130.99, 40, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/brother-inkvestment-mfc-j1215w(35).webp', 'Brother', 'MFCJ1215W', 'Inkjet | Wireless | Duplex | INKvestment Tank', '2025-09-24 01:30:00', '2025-09-26 11:48:14'),
(36, 'HP - Envy 6555e Wireless', 'Bonus ink offer\nHP - Envy 6555e Wireless AI-Enabled All-in-One Inkjet Printer with 3 Months of Instant Ink (+ 1 Bonus Month w/ Code) - White', 155.99, 67, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-envy-6555e(36).webp', 'HP', 'ENVY 6555E/714N5A#B1H', 'Large Format | Wireless | 24-inch Wide Prints', '2025-09-24 01:30:00', '2025-09-26 11:49:34'),
(37, 'Epson - WorkForce WF-110', 'Epson - WorkForce WF-110 Wireless Inkjet Printer - Black\nPortable wireless inkjet printer for professionals on the go.', 249.99, 30, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/epson-workforce-wf-110(37).webp', 'Epson', 'WF-110', 'Portable | Wireless | Rechargeable Battery', '2025-09-24 01:30:00', '2025-09-26 11:49:50'),
(38, 'Canon - PIXMA iP8720', 'Canon - PIXMA iP8720 Wireless Photo Printer - Black', 256.99, 100, 2, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-pixma-ip8720(38).webp', 'Canon', '8746B002', 'Photo Printer | Pigment Ink | 13x19-inch Prints', '2025-09-24 01:30:00', '2025-09-26 11:50:03'),
(39, 'Brother - TN830 Standard-Yield', 'Brother - TN830 Standard-Yield Toner Cartridge - Black', 68.99, 35, 1, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/brother-tn830(39).webp', 'Brother', 'TN-830', 'Monochrome Laser | Wireless | Duplex | 36 PPM', '2025-09-24 01:30:00', '2025-09-26 11:50:15'),
(40, 'HP - LaserJet M234sdw', 'HP - LaserJet M234sdw Wireless Black-and-White Laser Printer - White & Slate', 298.99, 25, 3, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-laserjet-m234sdw(40).webp', 'HP', 'M234SDW/6GX01F#BGJ', 'Page Yield: Up to 1,200 pages | Output: Crisp, precise prints | \nUse: Professional & personal documents', '2025-09-24 01:30:00', '2025-09-26 12:24:02'),
(41, 'Canon - PG 275XL & CL276XL 2-Pack ', 'A complete set of ink for select compatible PIXMA printers. The PG-275 XL Black Ink Cartridge produces crisp, sharp black text for all your documents. The CL-276 XL Color Ink Cartridge produces colorful photos and images. Combined with Canon photo paper this ink protects your photos from fading for longer, thanks to the ChromaLife100 System. Genuine Canon inks provide peak performance that is specifically designed for compatible Canon printers.', 89.99, 47, 5, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/canon-ink-cartridges-275.webp', 'Canon', '4981C008', 'Extra large ink volume per cartridge — Black:11.9ml / Color:12.6ml', '2025-09-24 01:30:00', '2025-09-26 12:20:04'),
(42, 'HP - 61XL High-Yield Ink Cartridge - Black', 'Easily print vivid color documents, reports and letters. You get a great value when you choose a high-capacity cartridge designed for frequent printing.', 76.99, 58, 5, 'https://printerproducts.s3.ap-southeast-2.amazonaws.com/hp-ink-cartridges-61xl.webp', 'HP', 'CH563WN#140', 'Package Type : Single | Ink Type : Dye |Cartridge Color(s) : Black', '0000-00-00 00:00:00', '2025-09-25 11:03:20');

-- --------------------------------------------------------

--
-- Table structure for table `saved_cards`
--

CREATE TABLE `saved_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `brand` varchar(32) NOT NULL,
  `last4` varchar(4) NOT NULL,
  `exp_month` tinyint(3) UNSIGNED NOT NULL,
  `exp_year` smallint(5) UNSIGNED NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_cards`
--

INSERT INTO `saved_cards` (`id`, `user_id`, `brand`, `last4`, `exp_month`, `exp_year`, `is_default`, `created_at`) VALUES
(1, 1, 'mastercard', '4111', 12, 2045, 1, '2025-09-25 09:33:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stripe_customer_id` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `address`, `created_at`, `stripe_customer_id`) VALUES
(1, 'sahiba', 'sahiba@wishgeekstechserve.com', '$2y$10$R1weOq33TSvS2ZXiOvpPN.p2UWXOuELQnS.5pJh8v.53iUIgVjTPy', 'Sahiba', 'Bano', '8595046293', 'Noida', '2025-09-24 09:55:49', 'cus_T7Qe4F2tmjlh7R'),
(2, 'Saaaahii', 'priyanka@wishgeekstechserve.com', '$2y$10$Z0RzEQViK8dLMcgop9PAC.ndboYzTm8sMDsCqUYODLKvdZFVFb3fu', 'Saahi', 'Bnnnn', '67687878889', 'gyuiiuiui', '2025-09-25 13:13:53', 'cus_T7U8e95Kqvr2aS'),
(3, 'admin_user', 'sahiba.krp2000@gmail.com', '$2y$10$6uW8yrIy3T74suBG5WckreRfE15dTyZXTM61TrTgZrl/rZwdfetEW', 'admin', 'user', '6765544475', 'NNnnnn', '2025-09-30 15:42:16', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_cart_session` (`session_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_orders_email` (`customer_email`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `saved_cards`
--
ALTER TABLE `saved_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `saved_cards`
--
ALTER TABLE `saved_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
