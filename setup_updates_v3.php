<?php
include 'config.php';

// Add missing columns to users table
$userUpdates = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS gender VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS dob DATE DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified INT DEFAULT 0", // Ensure this exists too as int
    "ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'volunteer', 'rescuer') DEFAULT 'user'" // Ensure role includes new types if needed
];

foreach ($userUpdates as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Successfully executed: $sql<br>";
    } else {
        // Ignore duplicate column errors if "IF NOT EXISTS" fails on some MySQL versions (though it should work)
        echo "Note (might be already existing): $sql - " . $conn->error . "<br>";
    }
}

echo "Database schema update completed. Please refresh your page.";
?>