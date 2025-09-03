<?php
// Debug application form issues step by step
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Application Form Debug Tool</h1>";

// Step 1: Check if this script can run
echo "<h2>Step 1: Basic PHP Test</h2>";
echo "✅ PHP is working<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br><br>";

// Step 2: Test database connection
echo "<h2>Step 2: Database Connection Test</h2>";
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marry_mother_mercy_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✅ Database connected successfully<br>";
    echo "Database: $dbname<br><br>";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "<strong>Solution:</strong> Make sure XAMPP MySQL is running<br><br>";
    exit;
}

// Step 3: Check if applications table exists
echo "<h2>Step 3: Applications Table Check</h2>";
$result = $conn->query("SHOW TABLES LIKE 'applications'");
if ($result->num_rows == 0) {
    echo "❌ Applications table does not exist<br>";
    echo "<strong>Creating table now...</strong><br>";
    
    $create_table = "CREATE TABLE applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_no VARCHAR(50) UNIQUE,
        class_to_join VARCHAR(100),
        student_name VARCHAR(255) NOT NULL,
        student_middle_name VARCHAR(255),
        student_surname VARCHAR(255),
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
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table) === TRUE) {
        echo "✅ Applications table created successfully<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
        exit;
    }
} else {
    echo "✅ Applications table exists<br>";
}

// Step 4: Test the process_application.php script
echo "<h2>Step 4: Testing Form Processing</h2>";

if ($_POST && isset($_POST['test_submit'])) {
    echo "<h3>📝 Processing Test Form Submission</h3>";
    
    // Simulate the process_application.php logic
    $application_no = 'MMM' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $class_to_join = $_POST['class_to_join'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $student_surname = $_POST['student_surname'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $father_phone = $_POST['father_phone'] ?? '';
    
    echo "Application No: $application_no<br>";
    echo "Class: $class_to_join<br>";
    echo "Student: $student_name $student_surname<br>";
    echo "Father: $father_name ($father_phone)<br><br>";
    
    // Test database insertion
    $sql = "INSERT INTO applications (application_no, class_to_join, student_name, student_surname, father_name, father_phone) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssssss", $application_no, $class_to_join, $student_name, $student_surname, $father_name, $father_phone);
        
        if ($stmt->execute()) {
            echo "✅ <strong>SUCCESS!</strong> Test application saved to database<br>";
            echo "🎉 Application Number: <strong>$application_no</strong><br>";
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; color: #155724;'>";
            echo "<strong>Great! The form processing is working correctly.</strong><br>";
            echo "The issue might be with the JavaScript or the main form submission.";
            echo "</div>";
        } else {
            echo "❌ Database insertion failed: " . $stmt->error . "<br>";
        }
    } else {
        echo "❌ SQL preparation failed: " . $conn->error . "<br>";
    }
}

// Step 5: Check existing applications
echo "<h2>Step 5: Current Applications in Database</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM applications");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "📊 Total applications: $count<br>";
    
    if ($count > 0) {
        echo "<h3>Recent Applications:</h3>";
        $recent = $conn->query("SELECT application_no, student_name, student_surname, class_to_join, submitted_at FROM applications ORDER BY submitted_at DESC LIMIT 3");
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

// Step 6: Test the actual process_application.php file
echo "<h2>Step 6: Testing process_application.php File</h2>";
if (file_exists('process_application.php')) {
    echo "✅ process_application.php file exists<br>";
    
    // Test if it's accessible
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/process_application.php';
    echo "File URL: <a href='$url' target='_blank'>$url</a><br>";
    
} else {
    echo "❌ process_application.php file not found<br>";
    echo "<strong>Solution:</strong> Make sure the file exists in the root directory<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Form Debug</title>
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
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
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
    </style>
</head>
<body>

<div class="test-form">
    <h3>🧪 Test Form Submission</h3>
    <p>Use this form to test if the database insertion works:</p>
    
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
        
        <button type="submit" name="test_submit">🚀 Test Database Insertion</button>
    </form>
</div>

<div class="info">
    <h3>🔍 Troubleshooting Checklist:</h3>
    <ol>
        <li><strong>Database Connection:</strong> ✅ Should be working if you see this page</li>
        <li><strong>Applications Table:</strong> ✅ Should be created automatically</li>
        <li><strong>Test Form Above:</strong> Try submitting to test database insertion</li>
        <li><strong>Main Form:</strong> Go to <a href="index.html">index.html</a> and test the modal form</li>
        <li><strong>Browser Console:</strong> Press F12 → Console to check for JavaScript errors</li>
        <li><strong>Network Tab:</strong> Press F12 → Network to see if form is submitting</li>
    </ol>
</div>

<div class="next-steps">
    <h3>✅ Next Steps:</h3>
    <p>After testing the form above:</p>
    <a href="index.html" target="_blank">Test Main Form</a>
    <a href="admin/login.php" target="_blank">Admin Login</a>
    <a href="admin/applications.php" target="_blank">View Applications</a>
    <a href="process_application.php" target="_blank">Test Processing Script</a>
</div>

<div class="info">
    <h3>🐛 Common Issues & Solutions:</h3>
    <ul>
        <li><strong>Form not submitting:</strong> Check browser console for JavaScript errors</li>
        <li><strong>500 Error:</strong> Check if process_application.php has syntax errors</li>
        <li><strong>No response:</strong> Check if XAMPP Apache and MySQL are running</li>
        <li><strong>Database errors:</strong> Make sure the applications table exists</li>
        <li><strong>JavaScript errors:</strong> Check if jQuery and Bootstrap are loading</li>
    </ul>
</div>

</body>
</html>