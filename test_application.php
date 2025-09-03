<?php
// Test script to verify application form functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Testing Application Form Setup</h2>";

// Test 1: Check if config file exists and loads
echo "<h3>1. Testing Configuration</h3>";
try {
    require_once 'admin/includes/config.php';
    echo "✅ Config file loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Config file error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check database connection
echo "<h3>2. Testing Database Connection</h3>";
if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
    exit;
} else {
    echo "✅ Database connected successfully<br>";
}

// Test 3: Check if applications table exists
echo "<h3>3. Testing Applications Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'applications'");
if ($result->num_rows == 0) {
    echo "❌ Applications table does not exist<br>";
    echo "<strong>Solution:</strong> <a href='admin/setup_database.php'>Run database setup</a><br>";
} else {
    echo "✅ Applications table exists<br>";
    
    // Check table structure
    $result = $conn->query("DESCRIBE applications");
    echo "<h4>Table Structure:</h4>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
}

// Test 4: Test form processing
echo "<h3>4. Testing Form Processing</h3>";
if ($_POST) {
    echo "<h4>Received POST Data:</h4>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test the actual processing
    try {
        $application_no = 'MMM' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $class_to_join = sanitizeInput($_POST['class_to_join'] ?? '');
        $student_name = sanitizeInput($_POST['student_name'] ?? '');
        $student_surname = sanitizeInput($_POST['student_surname'] ?? '');
        
        echo "<h4>Processed Data:</h4>";
        echo "Application No: $application_no<br>";
        echo "Class: $class_to_join<br>";
        echo "Student: $student_name $student_surname<br>";
        
        echo "✅ Form processing test successful<br>";
        
    } catch (Exception $e) {
        echo "❌ Form processing error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "No POST data received. Use the form below to test:<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Form Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3, h4 { color: #007bff; }
        .test-form { background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="test-form">
    <h3>🧪 Test Application Form</h3>
    <form method="POST">
        <div class="form-group">
            <label>Class to Join:</label>
            <select name="class_to_join" required>
                <option value="">Select Class</option>
                <option value="Baby Class">Baby Class</option>
                <option value="Pre-Primary I">Pre-Primary I</option>
                <option value="Pre-Primary II">Pre-Primary II</option>
                <option value="Standard I">Standard I</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Student First Name:</label>
            <input type="text" name="student_name" required>
        </div>
        
        <div class="form-group">
            <label>Student Surname:</label>
            <input type="text" name="student_surname" required>
        </div>
        
        <div class="form-group">
            <label>Father's Name:</label>
            <input type="text" name="father_name">
        </div>
        
        <div class="form-group">
            <label>Father's Phone:</label>
            <input type="tel" name="father_phone">
        </div>
        
        <button type="submit">Test Form Submission</button>
    </form>
</div>

<div style="margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 10px;">
    <h3>🚀 Next Steps:</h3>
    <p>If all tests pass:</p>
    <ul>
        <li><a href="index.html">Test the main application form</a></li>
        <li><a href="admin/applications.php">Check admin panel</a></li>
        <li><a href="admin/dashboard.php">View dashboard</a></li>
    </ul>
</div>

</body>
</html>