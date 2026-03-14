<?php
require 'config.php';

$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL, ADD COLUMN IF NOT EXISTS reset_expiry DATETIME NULL";

if ($conn->query($sql) === TRUE) {
    echo "Database updated successfully";
} else {
    echo "Error updating database: " . $conn->error;
}
$conn->close();
?>
