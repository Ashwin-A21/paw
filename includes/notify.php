<?php
// includes/notify.php — Helper to create notifications

/**
 * Create a notification for a user
 * @param mysqli $conn Database connection
 * @param int $userId Target user ID
 * @param string $type Notification type (comment, adoption_status, rescue_status, etc.)
 * @param string $message Notification message
 * @param string|null $link Optional link URL
 */
function createNotification($conn, $userId, $type, $message, $link = null)
{
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $type, $message, $link);
    $stmt->execute();
    $stmt->close();
}
?>