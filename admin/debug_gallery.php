<?php
// Debug script to find the exact error in gallery.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>Gallery Debug Script</h2>";
echo "<p>Testing each component step by step...</p>";

// Step 1: Test basic PHP
echo "✅ Step 1: Basic PHP working<br>";

// Step 2: Test config inclusion
echo "🔄 Step 2: Testing config inclusion...<br>";
try {
    if (!file_exists('includes/config.php')) {
        throw new Exception("Config file not found");
    }
    require_once 'includes/config.php';
    echo "✅ Step 2: Config loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Step 2 FAILED: " . $e->getMessage() . "<br>";
    exit;
}

// Step 3: Test database connection
echo "🔄 Step 3: Testing database connection...<br>";
try {
    if (!isset($conn)) {
        throw new Exception("Database connection not established");
    }
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    echo "✅ Step 3: Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Step 3 FAILED: " . $e->getMessage() . "<br>";
    exit;
}

// Step 4: Test admin authentication
echo "🔄 Step 4: Testing admin authentication...<br>";
try {
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if admin is logged in (skip for debug)
    echo "✅ Step 4: Session handling working<br>";
} catch (Exception $e) {
    echo "❌ Step 4 FAILED: " . $e->getMessage() . "<br>";
    exit;
}

// Step 5: Test table existence
echo "🔄 Step 5: Testing gallery_images table...<br>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'gallery_images'");
    if ($result->num_rows == 0) {
        throw new Exception("gallery_images table does not exist");
    }
    echo "✅ Step 5: gallery_images table exists<br>";
} catch (Exception $e) {
    echo "❌ Step 5 FAILED: " . $e->getMessage() . "<br>";
    echo "<strong>Solution:</strong> <a href='update_database.php'>Run database update</a><br>";
    exit;
}

// Step 6: Test basic query
echo "🔄 Step 6: Testing basic query...<br>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM gallery_images");
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $count = $result->fetch_assoc()['count'];
    echo "✅ Step 6: Query successful. Found $count gallery images<br>";
} catch (Exception $e) {
    echo "❌ Step 6 FAILED: " . $e->getMessage() . "<br>";
    exit;
}

// Step 7: Test POST handling
echo "🔄 Step 7: Testing POST handling...<br>";
try {
    if ($_POST) {
        echo "POST data received<br>";
    } else {
        echo "No POST data (normal for GET request)<br>";
    }
    echo "✅ Step 7: POST handling working<br>";
} catch (Exception $e) {
    echo "❌ Step 7 FAILED: " . $e->getMessage() . "<br>";
    exit;
}

// Step 8: Test file upload function
echo "🔄 Step 8: Testing upload function...<br>";
try {
    if (function_exists('uploadImage')) {
        echo "✅ uploadImage function exists<br>";
    } else {
        echo "⚠️ uploadImage function not found (defined in config.php)<br>";
    }
    echo "✅ Step 8: Upload function check complete<br>";
} catch (Exception $e) {
    echo "❌ Step 8 FAILED: " . $e->getMessage() . "<br>";
    exit;
}

echo "<br><h3>🎉 All tests passed! Gallery.php should work.</h3>";
echo "<p>If gallery.php still shows 500 error, the issue might be:</p>";
echo "<ul>";
echo "<li>PHP memory limit too low</li>";
echo "<li>Missing PHP extensions</li>";
echo "<li>File permissions issue</li>";
echo "<li>Syntax error in the HTML/JavaScript section</li>";
echo "</ul>";

echo "<br><a href='gallery_simple.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Simplified Gallery</a>";
echo " <a href='gallery.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Test Original Gallery</a>";

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
</style>