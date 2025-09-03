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
session_start();

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
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        return "File is not an image.";
    }
    
    // Check file size (5MB limit)
    if ($file["size"] > 5000000) {
        return "Sorry, your file is too large.";
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
    
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return basename($file["name"]);
        } else {
            return "Sorry, there was an error uploading your file.";
        }
    }
}
?>