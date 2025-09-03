<?php
// Simple error checking script for gallery.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Gallery.php Error Check</h2>";

// Check if config file exists
if (!file_exists('includes/config.php')) {
    echo "❌ Error: includes/config.php file not found<br>";
    exit;
} else {
    echo "✅ Config file found<br>";
}

// Try to include config
try {
    require_once 'includes/config.php';
    echo "✅ Config file loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading config: " . $e->getMessage() . "<br>";
    exit;
}

// Check database connection
if (!isset($conn)) {
    echo "❌ Error: Database connection not established<br>";
    exit;
} else {
    echo "✅ Database connection established<br>";
}

// Check if gallery_images table exists
$result = $conn->query("SHOW TABLES LIKE 'gallery_images'");
if ($result->num_rows == 0) {
    echo "❌ Error: gallery_images table does not exist<br>";
    echo "<strong>Solution:</strong> Run the update_database.php script first<br>";
    echo "<a href='update_database.php'>Click here to update database</a><br>";
} else {
    echo "✅ gallery_images table exists<br>";
}

// Check table structure
$result = $conn->query("DESCRIBE gallery_images");
if ($result) {
    echo "✅ Table structure is valid<br>";
    echo "<h3>Table Columns:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "❌ Error checking table structure: " . $conn->error . "<br>";
}

// Check if images directory exists and is writable
$images_dir = '../images/';
if (!is_dir($images_dir)) {
    echo "❌ Error: Images directory does not exist<br>";
    if (mkdir($images_dir, 0755, true)) {
        echo "✅ Images directory created<br>";
    } else {
        echo "❌ Failed to create images directory<br>";
    }
} else {
    echo "✅ Images directory exists<br>";
}

if (!is_writable($images_dir)) {
    echo "❌ Warning: Images directory is not writable<br>";
} else {
    echo "✅ Images directory is writable<br>";
}

echo "<br><strong>If all checks pass, gallery.php should work correctly.</strong><br>";
echo "<br><a href='gallery.php'>Test Gallery Page</a>";
echo " | <a href='dashboard.php'>Go to Dashboard</a>";

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f8f9fa;
}
h2, h3 {
    color: #007bff;
}
a {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    text-decoration: none;
    border-radius: 5px;
    color: white;
    background: #007bff;
}
</style>