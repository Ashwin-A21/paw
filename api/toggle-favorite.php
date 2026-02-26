<?php
// api/toggle-favorite.php — AJAX endpoint for favoriting pets
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$petId = (int) ($_POST['pet_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

if ($petId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid pet ID']);
    exit;
}

// Check if already favorited
$stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND pet_id = ?");
$stmt->bind_param("ii", $userId, $petId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove favorite
    $delStmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND pet_id = ?");
    $delStmt->bind_param("ii", $userId, $petId);
    $delStmt->execute();
    $delStmt->close();
    echo json_encode(['success' => true, 'favorited' => false]);
} else {
    // Add favorite
    $insStmt = $conn->prepare("INSERT INTO favorites (user_id, pet_id) VALUES (?, ?)");
    $insStmt->bind_param("ii", $userId, $petId);
    $insStmt->execute();
    $insStmt->close();
    echo json_encode(['success' => true, 'favorited' => true]);
}

$stmt->close();
?>