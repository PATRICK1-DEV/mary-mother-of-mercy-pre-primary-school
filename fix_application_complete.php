<?php
// Complete Application Form Fix Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Complete Application Form Fix</h1>";
echo "<p>This script will diagnose and fix all application form issues.</p>";

$issues_found = [];
$fixes_applied = [];

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marry_mother_mercy_db";

// Step 1: Test database connection
echo "<h2>Step 1: Database Connection Test</h2>";
try {
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✅ MySQL connection successful<br>";
    
    // Check if database exists
    $db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($db_check->num_rows == 0) {
        echo "⚠️ Database '$dbname' does not exist. Creating it...<br>";
        if ($conn->query("CREATE DATABASE $dbname") === TRUE) {
            echo "✅ Database '$dbname' created successfully<br>";
            $fixes_applied[] = "Created database '$dbname'";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    } else {
        echo "✅ Database '$dbname' exists<br>";
    }
    
    // Select the database
    $conn->select_db($dbname);
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "<strong>Solution:</strong> Make sure XAMPP MySQL is running<br>";
    $issues_found[] = "Database connection failed";
    exit;
}

// Step 2: Check and create applications table
echo "<h2>Step 2: Applications Table Setup</h2>";
$table_check = $conn->query("SHOW TABLES LIKE 'applications'");
if ($table_check->num_rows == 0) {
    echo "⚠️ Applications table does not exist. Creating it...<br>";
    
    $create_table_sql = "CREATE TABLE applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_no VARCHAR(50) UNIQUE NOT NULL,
        class_to_join VARCHAR(100),
        student_name VARCHAR(255) NOT NULL,
        student_middle_name VARCHAR(255),
        student_surname VARCHAR(255) NOT NULL,
        sex ENUM('male', 'female'),
        date_of_birth DATE,
        place_of_birth VARCHAR(255),
        nationality VARCHAR(100),
        tribe VARCHAR(100),
        religion VARCHAR(100),
        denomination VARCHAR(100),
        previous_school VARCHAR(255),
        previous_class VARCHAR(100),
        father_name VARCHAR(255),
        father_occupation VARCHAR(255),
        father_phone VARCHAR(50),
        father_workplace VARCHAR(255),
        mother_name VARCHAR(255),
        mother_occupation VARCHAR(255),
        mother_phone VARCHAR(50),
        mother_workplace VARCHAR(255),
        guardian_name VARCHAR(255),
        guardian_occupation VARCHAR(255),
        guardian_phone VARCHAR(50),
        guardian_workplace VARCHAR(255),
        postal_box VARCHAR(100),
        postal_place VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "✅ Applications table created successfully<br>";
        $fixes_applied[] = "Created applications table";
    } else {
        echo "❌ Error creating applications table: " . $conn->error . "<br>";
        $issues_found[] = "Failed to create applications table";
    }
} else {
    echo "✅ Applications table exists<br>";
    
    // Check table structure
    $columns = $conn->query("DESCRIBE applications");
    echo "Table columns: ";
    $column_names = [];
    while ($row = $columns->fetch_assoc()) {
        $column_names[] = $row['Field'];
    }
    echo implode(', ', $column_names) . "<br>";
}

// Step 3: Test form processing
echo "<h2>Step 3: Form Processing Test</h2>";
if ($_POST && isset($_POST['test_form'])) {
    echo "<h3>📝 Processing Test Submission</h3>";
    
    // Generate application number
    $application_no = 'MMM' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Get form data
    $class_to_join = $_POST['class_to_join'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $student_surname = $_POST['student_surname'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $father_phone = $_POST['father_phone'] ?? '';
    
    echo "Application No: $application_no<br>";
    echo "Class: $class_to_join<br>";
    echo "Student: $student_name $student_surname<br>";
    echo "Father: $father_name ($father_phone)<br>";
    
    // Test database insertion
    $sql = "INSERT INTO applications (application_no, class_to_join, student_name, student_surname, father_name, father_phone) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssssss", $application_no, $class_to_join, $student_name, $student_surname, $father_name, $father_phone);
        
        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; color: #155724;'>";
            echo "✅ <strong>SUCCESS!</strong> Test application saved successfully!<br>";
            echo "🎉 Application Number: <strong>$application_no</strong><br>";
            echo "The database connection and form processing are working correctly.";
            echo "</div>";
            $fixes_applied[] = "Verified form processing works";
        } else {
            echo "❌ Database insertion failed: " . $stmt->error . "<br>";
            $issues_found[] = "Database insertion failed";
        }
    } else {
        echo "❌ SQL preparation failed: " . $conn->error . "<br>";
        $issues_found[] = "SQL preparation failed";
    }
}

// Step 4: Check process_application.php file
echo "<h2>Step 4: Process Application File Check</h2>";
if (file_exists('process_application.php')) {
    echo "✅ process_application.php file exists<br>";
    
    // Check file size and basic content
    $file_size = filesize('process_application.php');
    echo "File size: $file_size bytes<br>";
    
    if ($file_size < 100) {
        echo "⚠️ File seems too small, might be corrupted<br>";
        $issues_found[] = "process_application.php file too small";
    }
} else {
    echo "❌ process_application.php file not found<br>";
    $issues_found[] = "process_application.php file missing";
}

// Step 5: Check existing applications
echo "<h2>Step 5: Current Applications</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM applications");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "📊 Total applications in database: $count<br>";
    
    if ($count > 0) {
        echo "<h3>Recent Applications:</h3>";
        $recent = $conn->query("SELECT application_no, student_name, student_surname, class_to_join, submitted_at FROM applications ORDER BY submitted_at DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>App No</th><th style='padding: 8px;'>Student</th><th style='padding: 8px;'>Class</th><th style='padding: 8px;'>Date</th></tr>";
        while ($row = $recent->fetch_assoc()) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['application_no']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['student_name'] . ' ' . $row['student_surname']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['class_to_join']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['submitted_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

$conn->close();

// Summary
echo "<h2>📊 Fix Summary</h2>";
if (empty($issues_found)) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; color: #155724;'>";
    echo "<h3>🎉 All Systems Working!</h3>";
    echo "<p>No issues found. The application form should be working correctly.</p>";
    if (!empty($fixes_applied)) {
        echo "<p><strong>Fixes applied:</strong></p><ul>";
        foreach ($fixes_applied as $fix) {
            echo "<li>$fix</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
    echo "<h3>⚠️ Issues Found</h3>";
    echo "<ul>";
    foreach ($issues_found as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Form Fix</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f8f9fa; 
            line-height: 1.6;
        }
        h1, h2, h3 { color: #007bff; }
        .test-form { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 20px 0; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .form-group { margin-bottom: 15px; }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
            color: #495057; 
        }
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ced4da; 
            border-radius: 5px; 
            font-size: 14px; 
            box-sizing: border-box;
        }
        button { 
            background: #007bff; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
        }
        button:hover { background: #0056b3; }
        .next-steps {
            background: #d4edda;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .next-steps h3 {
            color: #155724;
            margin-top: 0;
        }
        .next-steps a {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .troubleshooting {
            background: #d1ecf1;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            color: #0c5460;
        }
    </style>
</head>
<body>

<div class="test-form">
    <h3>🧪 Test Database Connection & Form Processing</h3>
    <p>Submit this form to test if the database connection and form processing work:</p>
    
    <form method="POST">
        <div class="form-group">
            <label>Class to Join: *</label>
            <select name="class_to_join" required>
                <option value="">Select Class</option>
                <option value="Baby Class">Baby Class</option>
                <option value="Pre-Primary I">Pre-Primary I</option>
                <option value="Pre-Primary II">Pre-Primary II</option>
                <option value="Standard I">Standard I</option>
                <option value="Standard II">Standard II</option>
                <option value="Standard III">Standard III</option>
                <option value="Standard IV">Standard IV</option>
                <option value="Standard V">Standard V</option>
                <option value="Standard VI">Standard VI</option>
                <option value="Standard VII">Standard VII</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Student First Name: *</label>
            <input type="text" name="student_name" placeholder="Enter student's first name" required>
        </div>
        
        <div class="form-group">
            <label>Student Surname: *</label>
            <input type="text" name="student_surname" placeholder="Enter student's surname" required>
        </div>
        
        <div class="form-group">
            <label>Father's Name:</label>
            <input type="text" name="father_name" placeholder="Enter father's full name">
        </div>
        
        <div class="form-group">
            <label>Father's Phone:</label>
            <input type="tel" name="father_phone" placeholder="Enter father's phone number">
        </div>
        
        <button type="submit" name="test_form">🚀 Test Form Submission</button>
    </form>
</div>

<div class="next-steps">
    <h3>✅ Next Steps:</h3>
    <p>After running this fix script:</p>
    <a href="index.html" target="_blank">Test Main Application Form</a>
    <a href="admin/login.php" target="_blank">Admin Login</a>
    <a href="admin/applications.php" target="_blank">View Applications</a>
    <a href="process_application.php" target="_blank">Test Processing Script</a>
</div>

<div class="troubleshooting">
    <h3>🔍 If Main Form Still Doesn't Work:</h3>
    <ol>
        <li><strong>Check Browser Console:</strong> Press F12 → Console tab for JavaScript errors</li>
        <li><strong>Check Network Tab:</strong> Press F12 → Network tab to see if form submits</li>
        <li><strong>Test process_application.php:</strong> Visit it directly to check for errors</li>
        <li><strong>Check XAMPP:</strong> Make sure Apache and MySQL are running</li>
        <li><strong>Clear Browser Cache:</strong> Refresh the page with Ctrl+F5</li>
    </ol>
    
    <h4>Common JavaScript Errors:</h4>
    <ul>
        <li><strong>jQuery not loaded:</strong> Check if jQuery library is loading</li>
        <li><strong>Bootstrap not loaded:</strong> Check if Bootstrap JS is loading</li>
        <li><strong>Form ID mismatch:</strong> Make sure form ID matches JavaScript</li>
        <li><strong>CORS issues:</strong> Make sure you're accessing via localhost</li>
    </ul>
</div>

</body>
</html>