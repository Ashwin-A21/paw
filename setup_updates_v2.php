<?php
include 'config.php';

// Users Table Updates
$updates = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS lives_saved INT DEFAULT 0",
    "ALTER TABLE blogs ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'",
    // Ensure existing blogs are approved if they were published
    "UPDATE blogs SET status = 'approved' WHERE is_published = 1 AND status = 'pending'"
];

foreach ($updates as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Successfully executed: $sql<br>";
    } else {
        echo "Error executing: $sql - " . $conn->error . "<br>";
    }
}

echo "Additional database updates completed.";
?>