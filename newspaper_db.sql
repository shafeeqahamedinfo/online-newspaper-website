-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 24, 2025 at 03:34 PM
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
-- Database: `newspaper_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `full_name`, `created_at`) VALUES
(1, 'shafeeq', '$2y$10$XJSTzr3KcjpIxJnUfrhR3u55nZpz14E.kYJx3PYCAFxynpJodgO9y', 'admin@email.com', 'Administrator', '2025-12-24 12:25:57'),
(2, 'SHAFEEQAHAMED', '$2y$10$XJSTzr3KcjpIxJnUfrhR3u55nZpz14E.kYJx3PYCAFxynpJodgO9y', 'mshafeeqahamed5@gmail.com', 'staff', '2025-12-24 12:25:57');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_breaking` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','published') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `excerpt`, `content`, `category_id`, `author`, `featured_image`, `is_featured`, `is_breaking`, `views`, `published_at`, `status`) VALUES
(5, 'palani baba', 'palani-baba', '', '________________________________________\r\n📌 PALANI BABA – COMPLETE TIMELINE (Story Converted to Timeline Format)\r\n________________________________________\r\n🔹 Early Life (1950 – Childhood)\r\n•	14 Nov 1950 – Born in Palani, to Muhammadhali & Kadija Devi Inayar.\r\n•	Birth name: Ahamad ali.\r\n•	Grew up in New Ayakudi, 4 km from Palani.\r\n•	Studied in Lawrence School, Ooty (beginner in English).\r\n•	After his parents passed away, raised by his sister (mother-in-law mentioned).\r\n•	Continued studies at ITO Higher Education School.\r\n•	Aged 14, moved for studies and stayed in hostel.\r\n________________________________________\r\n🔹 College Life – Rise as a Student Leader\r\n•	Became a leader during college era.\r\n•	Spoke in Shariah meetings.\r\n•	Spoke in Muslim League under Naina Muhammad’s leadership.\r\n•	Studied 10 years in Delhi.\r\n•	Became fluent in many languages; known for wisdom.\r\n•	His speeches became widely popular.\r\n________________________________________\r\n🔹 Public Speaking Career\r\n•	Delivered 13,207 public speeches in his lifetime.\r\n•	Topics:\r\no	Islamic rights\r\no	Muslim community empowerment\r\no	Freedom & justice\r\no	Anti-oppression\r\n•	Famous quote:\r\n“I feel proud to be a prisoner of India rather than the president of India.”\r\n________________________________________\r\n🔹 Activism & Protest Involvement (1970s–1980s)\r\n•	Participated in several Tamil Nadu political & social movements.\r\n•	1981 – Meenakshipuram Conversion Issue\r\n•	1982 – Mandai Kadu Protest\r\n•	Opposed rising RSS activities in Tamil Nadu.\r\n•	Questioned MGR publicly on RSS issue.\r\n•	Became known as a powerful legal activist.\r\n________________________________________\r\n🔹 Relationship with MGR & DMK (1980s)\r\n•	Sometimes supported by MGR, sometimes opposed.\r\n•	Spoke for the rights of low-caste Hindus, Harijans, and Muslims.\r\n•	MGR used Baba to mobilize communities.\r\n•	Later DMK opposed him, arrested him under National Protection Act.\r\n•	Baba said:\r\n“MGR stabbed my heart, Karunanidhi stabbed my back.”\r\n________________________________________\r\n🔹 Legal Cases & Jail Terms\r\nTotals:\r\n•	136 laws slapped against him\r\n•	125 times arrested / jailed\r\n•	4 National Security Act (NSA) detentions\r\n•	1-time complete ban\r\n•	Known for being a Special Class Prisoner.\r\n•	Arrested often during protests; released the same day.\r\n________________________________________\r\n🔹 Major Political Interventions\r\n•	Fought for Kaveri Water rights for Tamil Nadu.\r\n•	Fought for Bhopal Gas Tragedy victims in India and abroad.\r\n•	Formed Indian Jihad Committee to unite oppressed communities.\r\n•	Helped many legally and financially.\r\n________________________________________\r\n🔹 His Political Philosophy\r\n•	“These are horses I rode, not the paths I walked. I will change parties but not my principles.”\r\n•	Attempted to unite:\r\no	Oppressed Hindus\r\no	Dalits\r\no	Muslims\r\no	Other backward communities\r\n________________________________________\r\n🔹 Sri Lankan Tamil Issue\r\n•	Supported Sri Lankan Tamils during civil conflict.\r\n•	Arrested and jailed in Chennai & Coimbatore for speaking out.\r\n________________________________________\r\n🔹 Later Years – Community Mobilisation\r\n•	Encouraged Muslims to unite under one Jamaat.\r\n•	Formed plans for winning 60 MLAs through united Muslim vote banks.\r\n•	Travelled across Tamil Nadu organizing youth.\r\n________________________________________\r\n🔹 Assassination (1997)\r\n•	28 January 1997 – Assassinated in Pollachi.\r\n•	Attacked with aruval (machetes) while returning from Dhanapal’s house.\r\n•	Fell on the road with multiple injuries.\r\n•	Attackers escaped in an Ambassador car.\r\n•	Tamil Nadu was shocked; happened during Ramadhan month.\r\n________________________________________\r\n🔹 Aftermath\r\n•	Large crowds gathered for final rites at ITO School grounds.\r\n•	Even a Brahmin whom Baba once helped said his life went dark after Baba’s death.\r\n•	His humanitarian acts remembered across communities.\r\n________________________________________\r\n🔹 Legacy & Social Impact\r\n•	Fought not against humanity, but for humanity.\r\n•	Attempted to transform Muslims into a united, educated, empowered society.\r\n•	Promoted education:\r\no	Encouraged Muslims to join 60 Islamic area\r\no	Asked them to become MLAs\r\n•	Tried to build a strong political identity for the community.\r\n________________________________________\r\n', 1, 'Administrator', '', 1, 0, 10, '2025-12-24 13:56:59', 'published'),
(8, 's', 's', 's', 's', 4, 'Administrator', 'uploads/1766586609_palani-baba-0c21bab1-0e4b-45b7-9d95-5c08bdd4c0e-resize-750.jpeg', 0, 0, 0, '2025-12-24 14:30:09', 'published');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Politics', 'politics', '2025-12-24 12:31:21'),
(2, 'Business', 'business', '2025-12-24 12:31:21'),
(3, 'Technology', 'technology', '2025-12-24 12:31:21'),
(4, 'Sports', 'sports', '2025-12-24 12:31:21'),
(5, 'Entertainment', 'entertainment', '2025-12-24 12:31:21'),
(6, 'Health', 'health', '2025-12-24 12:31:21'),
(7, 'Education', 'education', '2025-12-24 12:31:21'),
(8, 'World', 'world', '2025-12-24 12:31:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_articles_categories` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `fk_articles_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
