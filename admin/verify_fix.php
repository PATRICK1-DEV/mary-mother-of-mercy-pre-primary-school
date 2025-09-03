<?php
// Quick verification that everything is fixed
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Verifying Gallery Fix</h2>";

$all_good = true;

// Check 1: Database connection
echo "<h3>1. Database Connection</h3>";
try {
    require_once 'includes/config.php';
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    echo "✅ Database connected<br>";
} catch (Exception $e) {
    echo "❌ Database failed: " . $e->getMessage() . "<br>";
    $all_good = false;
}

// Check 2: Gallery table
echo "<h3>2. Gallery Images Table</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM gallery_images");
    if (!$result) {
        throw new Exception("Table query failed");
    }
    $count = $result->fetch_assoc()['count'];
    echo "✅ gallery_images table exists with $count images<br>";
} catch (Exception $e) {
    echo "❌ Gallery table issue: " . $e->getMessage() . "<br>";
    $all_good = false;
}

// Check 3: Images directory
echo "<h3>3. Images Directory</h3>";
$images_dir = '../images/';
if (is_dir($images_dir) && is_writable($images_dir)) {
    echo "✅ Images directory exists and is writable<br>";
} else {
    echo "❌ Images directory issue<br>";
    $all_good = false;
}

// Check 4: Test a simple gallery query
echo "<h3>4. Gallery Query Test</h3>";
try {
    $stmt = $conn->prepare("SELECT * FROM gallery_images ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    echo "✅ Gallery query successful (" . $result->num_rows . " results)<br>";
} catch (Exception $e) {
    echo "❌ Gallery query failed: " . $e->getMessage() . "<br>";
    $all_good = false;
}

// Final result
echo "<br><h2>📊 Final Result</h2>";
if ($all_good) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; color: #155724;'>";
    echo "<h3>🎉 SUCCESS! All issues are fixed!</h3>";
    echo "<p>Gallery.php should now work without 500 errors.</p>";
    echo "</div>";
    
    echo "<br><div style='text-align: center;'>";
    echo "<a href='gallery.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; display: inline-block; font-weight: bold;'>🚀 Test Gallery Now</a>";
    echo "<a href='gallery_simple.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; display: inline-block; font-weight: bold;'>📱 Simple Gallery</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
    echo "<h3>⚠️ Some issues still need fixing</h3>";
    echo "<p>Please run the database fix script first.</p>";
    echo "</div>";
    
    echo "<br><div style='text-align: center;'>";
    echo "<a href='fix_database_now.php' style='background: #ffc107; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; display: inline-block; font-weight: bold;'>🔧 Fix Database</a>";
    echo "</div>";
}

if (isset($conn)) {
    $conn->close();
}
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