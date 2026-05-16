<?php
// ============================================================
// db.php - Database Connection File
// University Parking Management System
// ============================================================

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'parking_management_system');

// Create connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection - stop execution if it fails
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8 for proper encoding
$conn->set_charset("utf8");
?>
