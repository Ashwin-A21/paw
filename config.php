<?php
// Secure session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "paw_rescue_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load CSRF helper
require_once __DIR__ . '/includes/csrf.php';
?>