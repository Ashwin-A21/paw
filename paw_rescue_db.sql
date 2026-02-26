-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 12:56 PM
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
-- Database: `paw_rescue_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoption_applications`
--

CREATE TABLE `adoption_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL,
  `owner_response` enum('Pending','Deal','No Deal') DEFAULT 'Pending',
  `owner_notes` text DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_applications`
--

INSERT INTO `adoption_applications` (`id`, `user_id`, `pet_id`, `message`, `status`, `admin_notes`, `owner_response`, `owner_notes`, `application_date`) VALUES
(1, 1, 7, 'i would like to adopt chommu if its not yet adopted ,plsss\r\n. Finaly price 5K\r\n', 'Approved', NULL, 'Deal', 'make sure to treat it well', '2026-02-26 10:39:09'),
(2, 3, 6, 'i love husky a lot ,and i promise to take goodcare', 'Pending', NULL, 'Deal', 'hope u treat him well', '2026-02-26 11:50:19');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `content` text NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `author` varchar(100) DEFAULT 'Admin',
  `image` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `slug`, `content`, `author_id`, `author`, `image`, `is_published`, `created_at`, `status`) VALUES
(1, 'Why Adopt a Pet?', 'why-adopt-a-pet', 'Adopting a pet saves two lives: the one you adopt and the one who takes their place in the shelter. Every year, millions of animals end up in shelters. By adopting, you give them a second chance at happiness while also gaining a loyal companion.\n\nAdopted pets are often already vaccinated, spayed/neutered, and microchipped, saving you time and money. Plus, shelters can help match you with a pet that fits your lifestyle.\n\nMake a difference today – adopt, don\'t shop!', 1, 'Admin', NULL, 1, '2026-02-02 05:26:50', 'approved'),
(2, 'How to Prepare Your Home for a New Pet ', 'prepare-home-new-pet', 'Bringing a new pet home is exciting! Here are some tips to prepare:\r\n\r\n1. Pet-proof your home by removing hazardous items\r\n2. Set up a comfortable sleeping area\r\n3. Stock up on food, treats, and toys\r\n4. Schedule a vet visit\r\n5. Be patient during the adjustment period\r\n\r\nRemember, your new pet may need time to settle in. Give them love and patience!', 1, 'Admin', '1771146134_#vibzztime #memes.jpg', 1, '2026-02-02 05:26:50', 'rejected'),
(3, 'duck found missing', NULL, 'duck u ', 3, 'Sarah Volunteer', '1771130501_credits~bugsfreeelife.jpg', 1, '2026-02-12 10:50:39', 'approved'),
(4, 'sf', NULL, 'sdfdf', 1, 'Admin', '', 1, '2026-02-12 11:09:39', 'approved'),
(5, 'sdfsd samoosa', NULL, 'dsf3rsf', 3, 'Sarah G Volunteer', '1771050906_download (8).jpg', 1, '2026-02-14 06:35:06', 'approved'),
(6, 'Doge after one sniff of \'Fresh Mountain Breeze', NULL, 'They said the new Ariel scent was uplifting. They didn\'t warn him it would literally lift him to another dimension. Current status: eyes at half-mast, tail optional, existential crisis included.\r\n\r\nStarted as \'let\'s do whites together\'… ended with him face-down in his own powder masterpiece like a tiny Scarface. Somebody get this dog some milk and a therapist.', 3, 'Sarah G Volunteer', '1771147297_#vibzztime #memes.jpg', 1, '2026-02-15 09:19:36', 'approved'),
(7, 'Kya be… Atankwadi? (Dogesh edition)', NULL, 'Puddle mein selfie le raha tha boss, par expression dekh – jaise bol raha ho \'tu mera area mein kya kar raha hai be?\'. Dogesh ne aaj ragging ka new level unlock kar diya. Who hurt you bhai? 🥶 #StreetDogSavage #KyabeAtankwadi #IndianDogMemes', 5, 'MeowMeow trust ', '1771150473_8163_____.jpg', 1, '2026-02-15 10:14:33', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `entity_type` enum('pet','rescue') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `entity_type`, `entity_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 'rescue', 2, 1, 'it wass a great and thrilling experience , poor dog was stuck but sucessfully rescued by our team', '2026-02-15 09:27:42'),
(2, 'rescue', 2, 1, 'heheheh', '2026-02-15 09:28:20'),
(3, 'rescue', 2, 5, 'great job mate', '2026-02-15 09:30:51'),
(4, 'rescue', 4, 5, 'druggie doggie', '2026-02-15 09:53:58'),
(5, 'pet', 7, 5, 'looks cute btw', '2026-02-15 09:57:32'),
(6, 'pet', 6, 5, 'Damnn.. i need this fellow', '2026-02-15 09:58:02'),
(7, 'pet', 7, 1, 'the best patti', '2026-02-26 10:00:02'),
(8, 'pet', 7, 1, 'sarahh is the dog adopted ??', '2026-02-26 10:38:32'),
(9, 'pet', 7, 3, 'not yet , u can contact me for the deal', '2026-02-26 11:27:20'),
(10, 'pet', 6, 1, 'up for sale', '2026-02-26 11:48:48'),
(11, 'pet', 6, 5, 'sad for me ...', '2026-02-26 11:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(100) DEFAULT NULL,
  `donor_email` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `message` text DEFAULT NULL,
  `payment_status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `donated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `pet_id`, `created_at`) VALUES
(1, 1, 6, '2026-02-26 10:56:56'),
(2, 1, 7, '2026-02-26 10:56:58'),
(3, 1, 3, '2026-02-26 10:57:05'),
(4, 3, 1, '2026-02-26 11:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `rating`, `message`, `created_at`) VALUES
(1, 3, 4, 'nicee', '2026-02-15 05:33:31'),
(3, 1, 4, 'nicee', '2026-02-15 06:05:23'),
(4, 5, 5, 'great website for bringing out newer innovation and idea into existence .', '2026-02-15 08:55:29'),
(5, 5, 5, 'Wonderfull website .. made a lot of donation money ..heheheh', '2026-02-15 10:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 3, 'comment', 'Admin commented on your pet \"chommu\"', 'pet-details.php?id=7#comments', 1, '2026-02-26 10:38:32'),
(2, 1, 'adoption_application', 'Sarah G Volunteer wants to adopt your pet \"bogra\"! Review their application.', 'manage-applications.php', 0, '2026-02-26 11:50:19'),
(3, 1, 'adoption_deal', '🎉 Great news! Your adoption application for \"chommu\" has been accepted! It\'s a Deal!', 'pet-details.php?id=7', 0, '2026-02-26 11:50:46'),
(4, 3, 'adoption_deal', '🎉 Great news! Your adoption application for \"bogra\" has been accepted! It\'s a Deal!', 'pet-details.php?id=6', 0, '2026-02-26 11:54:02'),
(5, 5, 'pet_adopted_commenter', '🐾 Good news! \"bogra\" was just adopted by Sarah G Volunteer. View their info and your old comments.', 'pet-details.php?id=6', 1, '2026-02-26 11:54:02'),
(6, 1, 'comment', 'MeowMeow trust  commented on your pet \"bogra\"', 'pet-details.php?id=6#comments', 0, '2026-02-26 11:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('dog','cat','bird','other') NOT NULL,
  `breed` varchar(50) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default_pet.jpg',
  `status` enum('Available','Adopted','Pending') DEFAULT 'Available',
  `added_by` int(11) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `name`, `type`, `breed`, `age`, `gender`, `description`, `image`, `status`, `added_by`, `added_at`) VALUES
(1, 'Bella', 'dog', 'Labrador Retriever', '2 years', 'Female', 'Bella is a friendly and energetic Labrador who loves playing fetch and swimming. She is great with kids and other pets.', '1771130316_images (1).webp', 'Available', 1, '2026-02-02 05:26:50'),
(3, 'Arjun', 'dog', 'German Shepherd', '3 years', 'Male', 'Arjun is a loyal and intelligent German Shepherd. He has been trained in basic commands and is very protective.', '1771130365_german_shepherd_dog_guide.avif', 'Available', 1, '2026-02-02 05:26:50'),
(4, 'Luna', 'cat', 'Persian', '2 years', 'Female', 'Luna is a beautiful Persian cat with a fluffy white coat. She enjoys quiet environments and gentle handling.', '1771130405_Persian_in_Cat_Cafe.jpg', 'Available', 1, '2026-02-02 05:26:50'),
(5, 'Max', 'dog', 'Golden Retriever', '4 years', 'Male', 'Max is a gentle giant who loves everyone he meets. Perfect for families looking for a loyal companion.', '1771130471_Untitled_design-40.jpg', 'Available', 1, '2026-02-02 05:26:50'),
(6, 'bogra', 'dog', 'Siberian Husky', '6', 'Male', 'The Siberian Husky is a breed of medium-sized working sled dog. The breed belongs to the Spitz genetic family. It is recognizable by its thickly furred double coat, erect triangular ears, and distinctive markings, and is smaller than the similar-looking Alaskan Malamute', '1771130092_images.webp', 'Adopted', 1, '2026-02-15 04:34:52'),
(7, 'chommu', 'dog', 'street dog ', '7', 'Male', 'cutiepie chommu , keralas one and only chommu', '1771133524_download (11).jpg', 'Adopted', 3, '2026-02-15 05:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `pet_images`
--

CREATE TABLE `pet_images` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_medical_records`
--

CREATE TABLE `pet_medical_records` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `record_type` enum('Vaccination','Checkup','Surgery','Treatment','Other') NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `vet_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rescue_reports`
--

CREATE TABLE `rescue_reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `reporter_name` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `description` text NOT NULL,
  `animal_type` varchar(50) DEFAULT NULL,
  `urgency` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Reported','Assigned','In Progress','Rescued','Closed') DEFAULT 'Reported',
  `assigned_to` int(11) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rescue_reports`
--

INSERT INTO `rescue_reports` (`id`, `reporter_id`, `reporter_name`, `contact_phone`, `location`, `latitude`, `longitude`, `description`, `animal_type`, `urgency`, `image`, `status`, `assigned_to`, `reported_at`, `updated_at`) VALUES
(1, NULL, 'Anonymous', '9876543210', 'Near City Park, Main Street', NULL, NULL, 'Injured stray dog found near the park. Appears to have a leg injury and is limping.', 'Dog', 'High', NULL, 'Rescued', 4, '2026-02-02 05:26:50', '2026-02-11 11:41:41'),
(2, 5, 'Ash', '897654235', 'near the old abounded house pet stuck under the pipeline', 12.70606400, 74.90422900, 'its an emergency , urgent help needed', NULL, 'Medium', '', 'Rescued', NULL, '2026-02-02 07:17:42', '2026-02-15 09:01:23'),
(3, NULL, 'test', '786523545', 'xsfcsdf', 28.42341000, 76.98806800, 'sdfsf', NULL, 'Medium', '1771050797_download (9).jpg', 'Rescued', NULL, '2026-02-14 06:33:17', '2026-02-26 09:57:12'),
(4, 5, 'Meow Meow ', '9876543223', 'as marked ', 28.63002500, 77.22498200, 'dog is high on drugs ', NULL, 'Critical', '1771147929_#vibzztime #memes.jpg', 'Reported', NULL, '2026-02-15 09:32:09', '2026-02-15 09:32:09'),
(5, NULL, 'Ash123', '987654324343', 'hurry up', 28.61587100, 77.22410200, 'urgent', NULL, 'Critical', '', 'Reported', NULL, '2026-02-26 11:26:14', '2026-02-26 11:26:14');

-- --------------------------------------------------------

--
-- Table structure for table `role_requests`
--

CREATE TABLE `role_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requested_role` varchar(50) NOT NULL,
  `organization_name` varchar(150) DEFAULT NULL,
  `organization_type` enum('Individual','Charity','Organization','Trust') DEFAULT NULL,
  `document_proof` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_requests`
--

INSERT INTO `role_requests` (`id`, `user_id`, `requested_role`, `organization_name`, `organization_type`, `document_proof`, `status`, `created_at`) VALUES
(1, 3, 'volunteer', '', 'Individual', '1771133427_download.png', 'Approved', '2026-02-15 05:30:27'),
(2, 5, 'organization', 'Meoww', 'Individual', '1771145783_😏.jpg', 'Approved', '2026-02-15 08:56:23');

-- --------------------------------------------------------

--
-- Table structure for table `success_stories`
--

CREATE TABLE `success_stories` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `story` text NOT NULL,
  `before_image` varchar(255) DEFAULT NULL,
  `after_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `rescue_report_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `status` enum('Assigned','In Progress','Completed','Cancelled') DEFAULT 'Assigned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('user','admin','volunteer','rescuer','organization') DEFAULT 'user',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `lives_saved` int(11) DEFAULT 0,
  `organization_name` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `address`, `role`, `is_verified`, `created_at`, `gender`, `dob`, `profile_image`, `lives_saved`, `organization_name`) VALUES
(1, 'Admin', 'admin@paw.com', '1234', '2345678', NULL, 'admin', 1, '2026-02-02 05:26:50', '', '0000-00-00', 'https://api.dicebear.com/9.x/toon-head/svg?seed=Charlie', 3, NULL),
(2, 'John User', 'john@example.com', '1234', NULL, NULL, 'user', 1, '2026-02-02 05:26:50', NULL, NULL, NULL, 3, NULL),
(3, 'Sarah G Volunteer', 'sarah@volunteer.com', '12345', '987654321', NULL, 'volunteer', 1, '2026-02-02 05:26:50', '', '0000-00-00', 'https://api.dicebear.com/9.x/toon-head/svg?seed=Aneka', 6, ''),
(4, 'Mike Rescuer', 'mike@rescuer.com', '1234', NULL, NULL, 'rescuer', 1, '2026-02-02 05:26:50', NULL, NULL, NULL, 8, NULL),
(5, 'MeowMeow trust ', 'ash@gmail.com', '12345', '9897654321', 'Kerala 671324', 'organization', 1, '2026-02-02 05:27:38', '', '2002-10-21', 'https://api.dicebear.com/9.x/toon-head/svg?seed=Charlie', 10, 'Meoww');

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_shifts`
--

CREATE TABLE `volunteer_shifts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `hours` decimal(4,1) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`pet_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `pet_images`
--
ALTER TABLE `pet_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `pet_medical_records`
--
ALTER TABLE `pet_medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `rescue_reports`
--
ALTER TABLE `rescue_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `role_requests`
--
ALTER TABLE `role_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `success_stories`
--
ALTER TABLE `success_stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `rescue_report_id` (`rescue_report_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `volunteer_shifts`
--
ALTER TABLE `volunteer_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pet_images`
--
ALTER TABLE `pet_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_medical_records`
--
ALTER TABLE `pet_medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rescue_reports`
--
ALTER TABLE `rescue_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_requests`
--
ALTER TABLE `role_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `success_stories`
--
ALTER TABLE `success_stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `volunteer_shifts`
--
ALTER TABLE `volunteer_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD CONSTRAINT `adoption_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adoption_applications_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blogs`
--
ALTER TABLE `blogs`
  ADD CONSTRAINT `blogs_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pet_images`
--
ALTER TABLE `pet_images`
  ADD CONSTRAINT `pet_images_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_medical_records`
--
ALTER TABLE `pet_medical_records`
  ADD CONSTRAINT `pet_medical_records_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rescue_reports`
--
ALTER TABLE `rescue_reports`
  ADD CONSTRAINT `rescue_reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rescue_reports_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_requests`
--
ALTER TABLE `role_requests`
  ADD CONSTRAINT `role_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `success_stories`
--
ALTER TABLE `success_stories`
  ADD CONSTRAINT `success_stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `success_stories_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`rescue_report_id`) REFERENCES `rescue_reports` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `volunteer_shifts`
--
ALTER TABLE `volunteer_shifts`
  ADD CONSTRAINT `volunteer_shifts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
