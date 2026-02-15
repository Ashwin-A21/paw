-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 15, 2026 at 07:03 AM
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
  `application_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 'How to Prepare Your Home for a New Pet', 'prepare-home-new-pet', 'Bringing a new pet home is exciting! Here are some tips to prepare:\n\n1. Pet-proof your home by removing hazardous items\n2. Set up a comfortable sleeping area\n3. Stock up on food, treats, and toys\n4. Schedule a vet visit\n5. Be patient during the adjustment period\n\nRemember, your new pet may need time to settle in. Give them love and patience!', 1, 'Admin', NULL, 0, '2026-02-02 05:26:50', 'rejected'),
(3, 'duck found missing', NULL, 'duck u ', 3, 'Sarah Volunteer', '1771130501_credits~bugsfreeelife.jpg', 1, '2026-02-12 10:50:39', 'approved'),
(4, 'sf', NULL, 'sdfdf', 1, 'Admin', '', 1, '2026-02-12 11:09:39', 'approved'),
(5, 'sdfsd samoosa', NULL, 'dsf3rsf', 3, 'Sarah G Volunteer', '1771050906_download (8).jpg', 1, '2026-02-14 06:35:06', 'approved');

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
(2, 1, 4, 'nicee', '2026-02-15 05:38:06');

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
(6, 'bogra', 'dog', 'Siberian Husky', '6', 'Male', 'The Siberian Husky is a breed of medium-sized working sled dog. The breed belongs to the Spitz genetic family. It is recognizable by its thickly furred double coat, erect triangular ears, and distinctive markings, and is smaller than the similar-looking Alaskan Malamute', '1771130092_images.webp', 'Available', 1, '2026-02-15 04:34:52'),
(7, 'chommu', 'dog', 'street dog ', '7', 'Male', 'cutiepie chommu , keralas one and only chommu', '1771133524_download (11).jpg', 'Available', 3, '2026-02-15 05:32:04');

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
(2, 5, 'Ash', '897654235', 'near the old abounded house pet stuck under the pipeline', 12.70606400, 74.90422900, 'its an emergency , urgent help needed', NULL, 'Medium', '', 'In Progress', NULL, '2026-02-02 07:17:42', '2026-02-12 10:55:34'),
(3, NULL, 'test', '786523545', 'xsfcsdf', 28.42341000, 76.98806800, 'sdfsf', NULL, 'Medium', '1771050797_download (9).jpg', 'Reported', NULL, '2026-02-14 06:33:17', '2026-02-14 06:33:17');

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
(1, 3, 'volunteer', '', 'Individual', '1771133427_download.png', 'Approved', '2026-02-15 05:30:27');

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
  `role` enum('user','admin','volunteer','rescuer') DEFAULT 'user',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `lives_saved` int(11) DEFAULT 0,
  `organization_type` enum('Individual','Charity','Organization','Trust') DEFAULT NULL,
  `organization_name` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `address`, `role`, `is_verified`, `created_at`, `gender`, `dob`, `profile_image`, `lives_saved`, `organization_type`, `organization_name`) VALUES
(1, 'Admin', 'admin@paw.com', '1234', '2345678', NULL, 'admin', 1, '2026-02-02 05:26:50', '', '0000-00-00', 'https://api.dicebear.com/9.x/toon-head/svg?seed=Charlie', 0, NULL, NULL),
(2, 'John User', 'john@example.com', '1234', NULL, NULL, 'user', 1, '2026-02-02 05:26:50', NULL, NULL, NULL, 0, NULL, NULL),
(3, 'Sarah G Volunteer', 'sarah@volunteer.com', '12345', '987654321', NULL, 'volunteer', 1, '2026-02-02 05:26:50', '', '0000-00-00', 'https://api.dicebear.com/9.x/toon-head/svg?seed=Aneka', 6, 'Individual', ''),
(4, 'Mike Rescuer', 'mike@rescuer.com', '1234', NULL, NULL, 'rescuer', 1, '2026-02-02 05:26:50', NULL, NULL, NULL, 8, NULL, NULL),
(5, 'Ash lord', 'ash@gmail.com', '12345', '9897654321', NULL, 'user', 1, '2026-02-02 05:27:38', '', '2002-10-21', 'https://api.dicebear.com/9.x/adventurer/svg?seed=Aneka', 9, NULL, NULL);

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
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rescue_reports`
--
ALTER TABLE `rescue_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role_requests`
--
ALTER TABLE `role_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`rescue_report_id`) REFERENCES `rescue_reports` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
