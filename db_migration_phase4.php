<?php
// db_migration_phase4.php — Run once to create Phase 4 tables
include 'config.php';

echo "<h2>Phase 4 Database Migrations</h2>";

// 1. Favorites table
$sql1 = "CREATE TABLE IF NOT EXISTS `favorites` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `pet_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_fav` (`user_id`, `pet_id`),
    KEY `user_id` (`user_id`),
    KEY `pet_id` (`pet_id`),
    CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql1)) {
    echo "<p>✅ favorites table created/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

// 2. Notifications table
$sql2 = "CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `type` varchar(50) NOT NULL,
    `message` text NOT NULL,
    `link` varchar(255) DEFAULT NULL,
    `is_read` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql2)) {
    echo "<p>✅ notifications table created/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

// 3. Pet images table (for gallery)
$sql3 = "CREATE TABLE IF NOT EXISTS `pet_images` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `pet_id` int(11) NOT NULL,
    `image` varchar(255) NOT NULL,
    `sort_order` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `pet_id` (`pet_id`),
    CONSTRAINT `pet_images_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql3)) {
    echo "<p>✅ pet_images table created/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

// 4. Success stories table
$sql4 = "CREATE TABLE IF NOT EXISTS `success_stories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `pet_id` int(11) DEFAULT NULL,
    `user_id` int(11) NOT NULL,
    `title` varchar(150) NOT NULL,
    `story` text NOT NULL,
    `before_image` varchar(255) DEFAULT NULL,
    `after_image` varchar(255) DEFAULT NULL,
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `pet_id` (`pet_id`),
    CONSTRAINT `success_stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `success_stories_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql4)) {
    echo "<p>✅ success_stories table created/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

// 5. Volunteer shifts table
$sql5 = "CREATE TABLE IF NOT EXISTS `volunteer_shifts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `date` date NOT NULL,
    `hours` decimal(4,1) NOT NULL,
    `description` text DEFAULT NULL,
    `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `volunteer_shifts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql5)) {
    echo "<p>✅ volunteer_shifts table created/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

// 6. Pet medical records table
$sql6 = "CREATE TABLE IF NOT EXISTS `pet_medical_records` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `pet_id` int(11) NOT NULL,
    `record_type` enum('Vaccination','Checkup','Surgery','Treatment','Other') NOT NULL,
    `description` text NOT NULL,
    `date` date NOT NULL,
    `vet_name` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `pet_id` (`pet_id`),
    CONSTRAINT `pet_medical_records_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql6)) {
    echo "<p>✅ pet_medical_records table created/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

echo "<br><p><strong>All migrations complete!</strong> You can delete this file now.</p>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
?>