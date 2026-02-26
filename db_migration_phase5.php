<?php
// db_migration_phase5.php — Run once to add owner approval columns to adoption_applications
include 'config.php';

echo "<h2>Phase 5 Database Migrations — Adoption Approval System</h2>";

// 1. Add owner_response column
$sql1 = "ALTER TABLE `adoption_applications` 
         ADD COLUMN IF NOT EXISTS `owner_response` ENUM('Pending','Deal','No Deal') DEFAULT 'Pending' AFTER `admin_notes`";
if ($conn->query($sql1)) {
    echo "<p>✅ owner_response column added/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

// 2. Add owner_notes column
$sql2 = "ALTER TABLE `adoption_applications` 
         ADD COLUMN IF NOT EXISTS `owner_notes` TEXT DEFAULT NULL AFTER `owner_response`";
if ($conn->query($sql2)) {
    echo "<p>✅ owner_notes column added/exists</p>";
} else {
    echo "<p>❌ Error: " . $conn->error . "</p>";
}

echo "<br><p><strong>Phase 5 migrations complete!</strong> You can delete this file now.</p>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
?>