<?php
// Complete fix for application form issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Fixing Application Form Issues</h2>";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marry_mother_mercy_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to database successfully<br><br>";

// Step 1: Check if applications table exists
echo "<h3>Step 1: Checking Applications Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'applications'");
if ($result->num_rows == 0) {
    echo "❌ Applications table does not exist. Creating it now...<br>";
    
    // Create applications table
    $create_table_sql = "CREATE TABLE applications (
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
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "✅ Applications table created successfully<br>";
    } else {
        echo "❌ Error creating applications table: " . $conn->error . "<br>";
    }
} else {
    echo "✅ Applications table exists<br>";
}

// Step 2: Test form processing
echo "<h3>Step 2: Testing Form Processing</h3>";
if ($_POST) {
    echo "<h4>📝 Processing Test Submission</h4>";
    
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
    
    // Try to insert into database
    $sql = "INSERT INTO applications (application_no, class_to_join, student_name, student_surname, father_name, father_phone) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $application_no, $class_to_join, $student_name, $student_surname, $father_name, $father_phone);
    
    if ($stmt->execute()) {
        echo "✅ <strong>SUCCESS!</strong> Application saved to database<br>";
        echo "🎉 Application Number: <strong>$application_no</strong><br>";
    } else {
        echo "❌ Database error: " . $stmt->error . "<br>";
    }
}

// Step 3: Check existing applications
echo "<h3>Step 3: Checking Existing Applications</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM applications");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "📊 Total applications in database: $count<br>";
    
    if ($count > 0) {
        echo "<h4>Recent Applications:</h4>";
        $recent = $conn->query("SELECT application_no, student_name, student_surname, class_to_join, submitted_at FROM applications ORDER BY submitted_at DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>App No</th><th>Student Name</th><th>Class</th><th>Submitted</th></tr>";
        while ($row = $recent->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['application_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['student_name'] . ' ' . $row['student_surname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['class_to_join']) . "</td>";
            echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Form Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        h2, h3, h4 { color: #007bff; }
        .test-form { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 14px; }
        button { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        table { margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>

<div class="test-form">
    <h3>🧪 Test Application Form Submission</h3>
    <p>Use this form to test if the application system is working:</p>
    
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
        
        <button type="submit">🚀 Test Application Submission</button>
    </form>
</div>

<div class="info">
    <h3>🔍 Troubleshooting Steps:</h3>
    <ol>
        <li><strong>Test this form first</strong> - If this works, the database is fine</li>
        <li><strong>Check the main form</strong> - Go to <a href="index.html">index.html</a> and try the modal form</li>
        <li><strong>Check browser console</strong> - Look for JavaScript errors (F12 → Console)</li>
        <li><strong>Check network tab</strong> - See if the form is actually submitting (F12 → Network)</li>
        <li><strong>Check admin panel</strong> - Go to <a href="admin/applications.php">admin/applications.php</a> to see submissions</li>
    </ol>
</div>

<div class="success">
    <h3>✅ Next Steps:</h3>
    <ul>
        <li><a href="index.html" target="_blank">Test Main Application Form</a></li>
        <li><a href="admin/login.php" target="_blank">Login to Admin Panel</a></li>
        <li><a href="admin/applications.php" target="_blank">View Applications</a></li>
        <li><a href="process_application.php" target="_blank">Check Processing Script</a></li>
    </ul>
</div>

</body>
</html>