<?php
include 'config.php';

// Users Table Updates
$updates = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT 'default_user.png'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS gender ENUM('Male', 'Female', 'Other') DEFAULT 'Other'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS dob DATE",
    "ALTER TABLE users MODIFY COLUMN is_verified TINYINT(1) DEFAULT 0",

    // Blogs Table Updates
    // We want a status enum for better workflow control. 
    // If 'is_published' exists, we should probably migrate it or keep it in sync. 
    // For now, let's add 'status' and default it based on is_published.
    "ALTER TABLE blogs ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'",
    "UPDATE blogs SET status = IF(is_published = 1, 'approved', 'pending') WHERE status = 'pending'"
];

foreach ($updates as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Successfully executed: $sql<br>";
    } else {
        echo "Error executing: $sql - " . $conn->error . "<br>";
    }
}

echo "Database updates completed.";
?>