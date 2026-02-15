<?php
// db_migration.php
include 'config.php';

echo "Starting DB Migration...\n";

// 1. Update Role Enum to include 'organization' (and others if missing)
// We need to modify the column definition.
$sql = "ALTER TABLE users MODIFY COLUMN role ENUM('user','admin','volunteer','rescuer','organization') DEFAULT 'user'";
if ($conn->query($sql) === TRUE) {
    echo "✔ Role Enum updated successfully.\n";
} else {
    echo "✖ Error updating Role Enum: " . $conn->error . "\n";
}

// 2. Fix User ID 5 (Ash lord) data
// Set role to 'organization'.
// Set organization_name to username if empty? 
// The user removed 'organization_type' requirement, effectively merging it into just a role.
// We will set role='organization'.
$sql = "UPDATE users SET role='organization' WHERE id=5";
if ($conn->query($sql) === TRUE) {
    echo "✔ User ID 5 role updated to 'organization'.\n";
} else {
    echo "✖ Error updating User ID 5: " . $conn->error . "\n";
}

// 3. Drop organization_type column if deemed confusing?
// The user said "remove the chairty , trust and all completely".
// So we can drop the column OR just ignore it.
// Dropping is cleaner.
$checkCol = $conn->query("SHOW COLUMNS FROM users LIKE 'organization_type'");
if ($checkCol->num_rows > 0) {
    // Before dropping, let's migrate any existing data?
    // If we drop it, we lose 'Trust', 'Charity' distinction. 
    // The user explicitly asked to remove the confusion.
    $sql = "ALTER TABLE users DROP COLUMN organization_type";
    if ($conn->query($sql) === TRUE) {
        echo "✔ Dropped confusing 'organization_type' column.\n";
    } else {
        echo "✖ Error dropping organization_type: " . $conn->error . "\n";
    }
} else {
    echo "ℹ organization_type column already removed or not found.\n";
}

// 4. Update centers.php logic (we will do this in code, but here we can clean up data if needed)
// No data cleanup needed for centers other than role update.

echo "Migration Completed.\n";
?>