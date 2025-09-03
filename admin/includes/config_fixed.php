<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'marry_mother_mercy_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Admin session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Admin credentials (you can change these)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'mercy2024'); // Change this password

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Function to require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Function to sanitize input
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

// Function to upload image
function uploadImage($file, $targetDir = '../images/') {
    // Create directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Generate unique filename to avoid conflicts
    $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $uniqueName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetFile = $targetDir . $uniqueName;
    
    // Check if image file is actually an image
    if (!getimagesize($file["tmp_name"])) {
        return "Error: File is not an image.";
    }
    
    // Check file size (5MB limit)
    if ($file["size"] > 5000000) {
        return "Error: File is too large. Maximum size is 5MB.";
    }
    
    // Allow certain file formats
    $allowedTypes = array("jpg", "jpeg", "png", "gif");
    if (!in_array($fileExtension, $allowedTypes)) {
        return "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
    }
    
    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $uniqueName; // Return just the filename
    } else {
        return "Error: Failed to upload file.";
    }
}
?>