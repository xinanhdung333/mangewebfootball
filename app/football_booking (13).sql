-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 04:02 AM
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
-- Database: `football_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_price` decimal(12,2) DEFAULT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled','expired') NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `field_id`, `booking_date`, `start_time`, `end_time`, `total_price`, `status`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-04-16', '16:09:00', '18:09:00', 18000000.00, 'completed', NULL, '2026-04-16 07:10:06', '2026-04-16 12:16:47');

-- --------------------------------------------------------

--
-- Table structure for table `booking_payments`
--

CREATE TABLE `booking_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `momo_order_id` varchar(255) DEFAULT NULL,
  `momo_trans_id` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_payments`
--

INSERT INTO `booking_payments` (`id`, `booking_id`, `momo_order_id`, `momo_trans_id`, `amount`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'booking_1_69e08b4f21e94', '4724141823', 18000000.00, 'success', '2026-04-16 07:11:22', '2026-04-16 07:10:06', '2026-04-16 07:11:22');

-- --------------------------------------------------------

--
-- Table structure for table `booking_services`
--

CREATE TABLE `booking_services` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_services`
--

INSERT INTO `booking_services` (`id`, `booking_id`, `service_id`, `quantity`) VALUES
(1, 1, 3, 1),
(2, 1, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_intents`
--

CREATE TABLE `chatbot_intents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `priority` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chatbot_intents`
--

INSERT INTO `chatbot_intents` (`id`, `name`, `priority`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'hello', 0, 1, '2026-04-16 08:17:28', '2026-04-16 08:17:28');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_keywords`
--

CREATE TABLE `chatbot_keywords` (
  `id` int(11) NOT NULL,
  `intent_id` int(11) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chatbot_keywords`
--

INSERT INTO `chatbot_keywords` (`id`, `intent_id`, `keyword`, `created_at`, `updated_at`) VALUES
(1, 1, 'hello', '2026-04-16 08:17:51', '2026-04-16 08:17:51'),
(2, 1, 'chào', '2026-04-16 08:20:20', '2026-04-16 08:20:20');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_logs`
--

CREATE TABLE `chatbot_logs` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `matched_intent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_responses`
--

CREATE TABLE `chatbot_responses` (
  `id` int(11) NOT NULL,
  `intent_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chatbot_responses`
--

INSERT INTO `chatbot_responses` (`id`, `intent_id`, `response_text`, `created_at`, `updated_at`) VALUES
(1, 1, 'chịu chết', '2026-04-16 08:20:05', '2026-04-16 08:20:05'),
(2, 1, '??', '2026-04-16 08:20:26', '2026-04-16 08:20:26');

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` tinyint(4) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `user_id`, `booking_id`, `service_id`, `message`, `rating`, `created_at`) VALUES
(1, 1, NULL, 3, 'ĐẸP', 5, '2025-12-08 08:26:09'),
(2, 1, NULL, 3, 'ĐẸP', 5, '2025-12-08 08:27:29'),
(3, 1, 5, NULL, 'sân đẹp', 5, '2025-12-08 08:47:57'),
(4, 1, NULL, 5, 'ĐẸP', 5, '2025-12-11 21:05:52'),
(5, 1, 4, NULL, 'sân đẹp', 4, '2025-12-12 00:34:33'),
(6, 0, NULL, 4, 'ok', 2, '2025-12-17 11:39:46'),
(7, 0, NULL, 5, 'ok', 4, '2025-12-17 11:54:26'),
(8, 0, 13, NULL, 'ok', 5, '2025-12-17 12:10:28'),
(9, 0, NULL, 2, 'rất ok', 4, '2025-12-27 11:15:26'),
(10, 0, NULL, 6, 'có bán kèm xixa cho anh độ mixi là vui rồi', 5, '2025-12-27 17:55:30'),
(11, 0, NULL, 3, 'uống dở như nước mưa', 1, '2025-12-27 17:56:12'),
(12, 1, NULL, 20, 'OK', 4, '2026-03-17 19:30:30');

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `name`, `location`, `description`, `price_per_hour`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Old Trafford', 'England', 'là một dự án xây dựng sân vận động mới với sức chứa 100.000 chỗ ngồi thay thế cho sân Old Trafford hiện tại, dự kiến hoàn thành vào khoảng năm 2030. Sân mới này sẽ có kiến trúc hiện đại, được thiết kế bởi công ty kiến trúc Foster + Partners, và sẽ có sức chứa lớn hơn cả sân vận động Wembley', 9000000.00, '1764788597_8153.webp', 'active', '2025-12-03 05:03:17', '2025-12-03 05:03:17'),
(2, 'Santiago Bernabéu', 'Spain', 'Sân vận động Santiago Bernabéu (tiếng Tây Ban Nha: Estadio Santiago Bernabéu; [esˈtaðjo sanˈtjaɣo βeɾnaˈβew] ⓘ) là một sân vận động bóng đá ở Madrid, Tây Ban Nha. Với sức chứa chỗ ngồi là 78.297 chỗ,[2] sân vận động có sức chứa chỗ ngồi lớn thứ hai cho một sân vận động bóng đá ở Tây Ban Nha. Đây là sân nhà của Real Madrid kể từ khi hoàn thành vào năm 1947.[5]', 8000000.00, '1764788705_9976.jpg', 'active', '2025-12-03 05:05:05', '2025-12-03 05:19:58'),
(3, 'Etihad Stadium', 'England', 'Sân vận động Thành phố Manchester (tiếng Anh: City of Manchester Stadium; thường được viết tắt là CoMS), còn được gọi là Sân vận động Etihad vì lý do tài trợ,[2] là một sân vận động ở Manchester, Anh. Đây là sân nhà của Manchester City F.C., với sức chứa bóng đá cho các trận đấu trong nước là 55.017 chỗ ngồi,[1] khiến sân trở thành sân vận động lớn thứ sáu ở Premier League và lớn thứ mười ở Vương quốc Anh.[3]', 10000000.00, '1764788882_2814.jpg', 'active', '2025-12-03 05:08:02', '2025-12-03 05:26:52'),
(4, 'Emirates vô địch EPL 2025', 'England', 'Sân vận động Emirates (tiếng Anh: Emirates Stadium, được biết đến với tên gọi Ashburton Grove trước khi bán quyền đặt tên và có tên gọi là Sân vận động Arsenal trong các giải đấu của UEFA) là một sân vận động bóng đá ở Highbury, Luân Đôn, Anh. Đây là sân nhà của câu lạc bộ Arsenal.[1][2][3][4][5] Với sức chứa 60.704 chỗ ngồi, Emirates là sân vận động bóng đá lớn thứ tư ở nước Anh sau Sân vận động Wembley, Old Trafford và Sân vận động Tottenham Hotspur.', 7000000.00, '1764788999_4021.webp', 'active', '2025-12-03 05:09:59', '2025-12-26 20:13:19'),
(5, 'Camp Nou', 'Spain', 'Camp Nou (phát âm tiếng Catalunya: [ˌkamˈnɔw], có nghĩa là sân mới, thường được gọi bằng tiếng Anh là Nou Camp), được đặt tên là Spotify Camp Nou vì lý do tài trợ, là sân nhà của câu lạc bộ La Liga FC Barcelona kể từ khi hoàn thành vào năm 1957. Với sức chứa hiện tại 99,354,[8] đó là sân vận động có sức chứa lớn nhất ở Tây Ban Nha và châu Âu, và sân vận động bóng đá lớn thứ hai trên thế giới.', 9000000.00, '1764789153_4240.jpg', 'active', '2025-12-03 05:12:33', '2025-12-03 05:12:33'),
(6, 'Wembley', 'England', 'Sân vận động Wembley (tiếng Anh: Wembley Stadium), được đặt tên là Sân vận động Wembley được kết nối bởi EE vì lý do tài trợ, là một sân vận động bóng đá ở Wembley, Luân Đôn, được khai trương vào năm 2007, trên nền đất của sân vận động Wembley cũ, đã bị phá hủy từ năm 2002 đến 2003.[8][9] Sân vận động thuộc sở hữu của Hiệp hội bóng đá Anh (FA), thông qua công ty con Wembley National Stadium Ltd (WNSL). Đây cũng là nơi FA đặt trụ sở làm việc chính của mình. Với 90.000 chỗ ngồi, đây là sân vận động bóng đá lớn nhất nước Anh, sân vận động lớn thứ sáu thế giới và sân vận động lớn thứ hai ở châu Âu.[10]', 6000000.00, '1764789293_5752.jpg', 'active', '2025-12-03 05:14:53', '2025-12-03 05:14:53'),
(7, 'Allianz Arena', 'Germany', 'Allianz Arena là một sân vận động bóng đá ở Munich, Bavaria, Đức, với sức chứa 70.000 chỗ ngồi cho các trận đấu quốc tế và 75.000 chỗ ngồi cho các trận đấu trong nước. Được biết đến rộng rãi với mặt ngoài bằng các tấm nhựa ETFE bơm hơi, đây là sân vận động đầu tiên trên thế giới có mặt ngoài đổi màu hoàn toàn. Tọa lạc tại số 25 Werner-Heisenberg-Allee ở rìa phía bắc của quận Schwabing-Freimann của Munich trên Fröttmaning Heath, đây là sân vận động lớn thứ hai ở Đức sau Westfalenstadion ở Dortmund.', 9000000.00, '1764789935_3867.jpg', 'active', '2025-12-03 05:25:35', '2025-12-03 05:25:35'),
(8, 'Anfield Stadium', 'England', 'Anfield là một sân vận động đạt chuẩn UEFA, và đã tổ chức nhiều trận đấu quốc tế, bao gồm cả những trận của đội tuyển Anh. Sân cũng được sử dụng ở VCK Euro 1996. Ban đầu sân cũng còn được sử dụng trong nhiều hoạt động, chẳng hạn như những trận boxing và tennis.', 7000000.00, '1764790143_8460.jpg', 'active', '2025-12-03 05:29:03', '2025-12-03 05:29:03'),
(9, 'Allianz Stadium,', 'Italia', 'Allianz Arena ([ʔali̯ˈants ʔaˌʁeːnaː]) là một sân vận động bóng đá ở München, Bayern, Đức. Sân có sức chứa 70.000 chỗ ngồi cho các trận đấu quốc tế và 75.000 ...', 8000000.00, '1764790561_1985.jpg', 'active', '2025-12-03 05:36:01', '2025-12-03 05:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_04_06_012746_create_booking_services_table', 1),
(2, '2026_04_06_012746_create_bookings_table', 1),
(3, '2026_04_06_012746_create_cart_items_table', 1),
(4, '2026_04_06_012746_create_cart_table', 1),
(5, '2026_04_06_012746_create_chatbot_intents_table', 1),
(6, '2026_04_06_012746_create_chatbot_keywords_table', 1),
(7, '2026_04_06_012746_create_chatbot_logs_table', 1),
(8, '2026_04_06_012746_create_chatbot_responses_table', 1),
(9, '2026_04_06_012746_create_feedbacks_table', 1),
(10, '2026_04_06_012746_create_fields_table', 1),
(11, '2026_04_06_012746_create_order_items_table', 1),
(12, '2026_04_06_012746_create_orders_table', 1),
(13, '2026_04_06_012746_create_payments_table', 1),
(14, '2026_04_06_012746_create_services_table', 1),
(15, '2026_04_06_012746_create_user_spending_table', 1),
(16, '2026_04_06_012746_create_users_table', 1),
(17, '2026_04_13_155407_create_booking_payments_table', 1),
(18, '2026_04_14_142851_create_password_reset_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('pending','confirmed','processing','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','processing','completed','cancelled') DEFAULT 'pending',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('2311062825@hunre.edu.vn', '$2y$12$NJByL7ZZOWyJnYh7vVToguPjBKTqL1rpe4cjA3CzsmvpESTv5NWWa', '2026-04-16 08:04:26');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `momo_order_id` varchar(255) DEFAULT NULL,
  `momo_trans_id` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(250) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `status`, `created_at`, `updated_at`, `image`, `quantity`) VALUES
(1, 'thuốc lá', NULL, 15000.00, 'active', '2025-12-26 15:32:51', '2025-12-26 15:32:51', '1766813571_5110.webp', 9999),
(2, 'Nước lavile', NULL, 10000.00, 'active', '2025-12-03 05:37:40', '2025-12-26 15:38:54', '1764790660_8183.jpg', 992),
(3, 'Redbull', NULL, 15000.00, 'active', '2025-12-03 05:38:22', '2026-04-16 07:10:06', '1764790702_4073.jpg', 9989),
(4, 'Thuê quần áo', NULL, 70000.00, 'active', '2025-12-03 05:39:45', '2026-04-16 07:10:06', '1764790785_4823.webp', 997),
(5, 'Khăn lạnh', NULL, 5000.00, 'active', '2025-12-03 05:41:13', '2025-12-26 15:38:54', '1764790873_2369.avif', 999),
(6, 'Phục vụ nước', NULL, 500000.00, 'active', '2025-12-03 05:47:10', '2025-12-26 15:38:54', '1764791230_3408.jpg', 971),
(11, 'đào duy cao cấp', NULL, 1000000.00, 'active', '2026-03-17 18:21:44', '2026-03-17 18:21:44', '1773822104_4477.jpg', 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `avt` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin','boss') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `avt`, `email`, `phone`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'tiengion', NULL, 'phampham2411@gmail.com', '0327688190', '$2y$12$66/k9JS..XrVT/KJR.iYcOVtyLH8NvvCvFSvw5K2y4TP40OMk6Lhm', 'user', '2026-04-16 07:08:30', '2026-04-16 07:08:30'),
(2, 'HẢI NGU', NULL, '2311062825@hunre.edu.vn', '07658972634', '$2y$12$dOm09951kOLT1OrXIE3jIekriK8ok8oHjd6JQntKXl5Tg.IVXhxNC', 'user', '2026-04-16 07:13:12', '2026-04-16 07:13:12'),
(3, 'TIEN', NULL, '2311062573@hunre.edu.vn', '', '$2y$12$6McaFLFL1vg46JraRsiLBOLNlYCbbJTzeBCfjfOfbjhChVACgglwO', 'admin', '2026-04-16 08:14:50', '2026-04-16 08:14:50');

-- --------------------------------------------------------

--
-- Table structure for table `user_spending`
--

CREATE TABLE `user_spending` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_booking` decimal(10,2) DEFAULT 0.00,
  `total_services` decimal(10,2) DEFAULT 0.00,
  `total_spent` decimal(10,2) GENERATED ALWAYS AS (`total_booking` + `total_services`) STORED,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_field` (`field_id`);

--
-- Indexes for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_payments_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `booking_services`
--
ALTER TABLE `booking_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking` (`booking_id`),
  ADD KEY `idx_service` (`service_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart` (`cart_id`),
  ADD KEY `idx_service` (`service_id`);

--
-- Indexes for table `chatbot_intents`
--
ALTER TABLE `chatbot_intents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chatbot_keywords`
--
ALTER TABLE `chatbot_keywords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intent_id` (`intent_id`),
  ADD KEY `idx_keyword` (`keyword`);

--
-- Indexes for table `chatbot_logs`
--
ALTER TABLE `chatbot_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chatbot_responses`
--
ALTER TABLE `chatbot_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intent_id` (`intent_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_booking` (`booking_id`),
  ADD KEY `idx_service` (`service_id`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_cart` (`cart_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_service` (`service_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_spending`
--
ALTER TABLE `user_spending`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_payments`
--
ALTER TABLE `booking_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_services`
--
ALTER TABLE `booking_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chatbot_intents`
--
ALTER TABLE `chatbot_intents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chatbot_keywords`
--
ALTER TABLE `chatbot_keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chatbot_logs`
--
ALTER TABLE `chatbot_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chatbot_responses`
--
ALTER TABLE `chatbot_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD CONSTRAINT `booking_payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
