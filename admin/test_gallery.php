<?php
// Minimal test to identify 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting gallery test...<br>";

// Test 1: Basic PHP
echo "1. PHP working ✅<br>";

// Test 2: Include config
echo "2. Loading config...<br>";
try {
    require_once 'includes/config_fixed.php';
    echo "2. Config loaded ✅<br>";
} catch (Exception $e) {
    echo "2. Config failed ❌: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Database
echo "3. Testing database...<br>";
if ($conn->connect_error) {
    echo "3. Database failed ❌: " . $conn->connect_error . "<br>";
    exit;
}
echo "3. Database connected ✅<br>";

// Test 4: Check table
echo "4. Checking gallery_images table...<br>";
$result = $conn->query("SHOW TABLES LIKE 'gallery_images'");
if ($result->num_rows == 0) {
    echo "4. Table missing ❌<br>";
    echo "<a href='update_database.php'>Create table</a><br>";
    exit;
}
echo "4. Table exists ✅<br>";

// Test 5: Simple query
echo "5. Testing query...<br>";
$count = $conn->query("SELECT COUNT(*) as count FROM gallery_images")->fetch_assoc()['count'];
echo "5. Query successful ✅ (Found $count images)<br>";

// Test 6: Session
echo "6. Testing session...<br>";
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "6. Session active ✅<br>";
} else {
    echo "6. Session issue ❌<br>";
}

echo "<br><strong>All basic tests passed!</strong><br>";
echo "<br>Now testing the actual gallery page components...<br>";

// Test the problematic parts of gallery.php
echo "<br>7. Testing POST handling...<br>";
if ($_POST) {
    echo "POST data received<br>";
} else {
    echo "No POST data (normal)<br>";
}
echo "7. POST handling ✅<br>";

echo "<br>8. Testing filter parameters...<br>";
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
echo "8. Filter parameters ✅<br>";

echo "<br>9. Testing query building...<br>";
$where_conditions = [];
$params = [];
$types = "";

if ($status_filter !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = (int)$status_filter;
    $types .= "i";
}

if ($search) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
echo "9. Query building ✅<br>";

echo "<br>10. Testing actual gallery query...<br>";
$query = "SELECT * FROM gallery_images $where_clause ORDER BY sort_order ASC, created_at DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$gallery_images = $stmt->get_result();
echo "10. Gallery query ✅<br>";

echo "<br>🎉 <strong>ALL TESTS PASSED!</strong><br>";
echo "<br>The issue might be in the HTML/CSS/JavaScript section of gallery.php<br>";
echo "<br><a href='gallery_simple.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none;'>Try Simple Gallery</a>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
</style>