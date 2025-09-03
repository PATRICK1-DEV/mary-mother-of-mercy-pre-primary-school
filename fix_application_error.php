<?php
// Complete fix for application form submission error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Fixing Application Form Submission Error</h1>";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marry_mother_mercy_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✅ Connected to database successfully<br><br>";
    
    // Step 1: Check if database exists
    $db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($db_check->num_rows == 0) {
        echo "⚠️ Database '$dbname' does not exist. Creating it...<br>";
        if ($conn->query("CREATE DATABASE $dbname") === TRUE) {
            echo "✅ Database '$dbname' created successfully<br>";
            $conn->select_db($dbname);
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    } else {
        echo "✅ Database '$dbname' exists<br>";
        $conn->select_db($dbname);
    }
    
    // Step 2: Check if applications table exists
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
            signature_data TEXT,
            typed_signature VARCHAR(255),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table_sql) === TRUE) {
            echo "✅ Applications table created successfully<br>";
        } else {
            throw new Exception("Error creating applications table: " . $conn->error);
        }
    } else {
        echo "✅ Applications table exists<br>";
        
        // Step 3: Check if signature columns exist
        $columns_result = $conn->query("DESCRIBE applications");
        $columns = [];
        while ($row = $columns_result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $missing_columns = [];
        
        if (!in_array('signature_data', $columns)) {
            $missing_columns[] = 'signature_data';
        }
        
        if (!in_array('typed_signature', $columns)) {
            $missing_columns[] = 'typed_signature';
        }
        
        if (!empty($missing_columns)) {
            echo "⚠️ Missing signature columns. Adding them...<br>";
            
            foreach ($missing_columns as $column) {
                if ($column === 'signature_data') {
                    $sql = "ALTER TABLE applications ADD COLUMN signature_data TEXT NULL";
                } else {
                    $sql = "ALTER TABLE applications ADD COLUMN typed_signature VARCHAR(255) NULL";
                }
                
                if ($conn->query($sql) === TRUE) {
                    echo "✅ Added column: $column<br>";
                } else {
                    echo "❌ Error adding column $column: " . $conn->error . "<br>";
                }
            }
        } else {
            echo "✅ All signature columns exist<br>";
        }
    }
    
    // Step 4: Create uploads directory
    $upload_dir = 'uploads/signatures/';
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0777, true)) {
            echo "✅ Created uploads directory: $upload_dir<br>";
        } else {
            echo "❌ Failed to create uploads directory<br>";
        }
    } else {
        echo "✅ Uploads directory exists<br>";
    }
    
    // Step 5: Test form submission
    if ($_POST && isset($_POST['test_form'])) {
        echo "<h2>📝 Testing Form Submission</h2>";
        
        // Generate application number
        $application_no = 'MMM' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Get form data
        $class_to_join = $_POST['class_to_join'] ?? '';
        $student_name = $_POST['student_name'] ?? '';
        $student_surname = $_POST['student_surname'] ?? '';
        
        echo "Application No: $application_no<br>";
        echo "Class: $class_to_join<br>";
        echo "Student: $student_name $student_surname<br>";
        
        // Test database insertion with signature columns
        $sql = "INSERT INTO applications (application_no, class_to_join, student_name, student_surname, signature_data, typed_signature) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $signature_data = null;
            $typed_signature = null;
            
            $stmt->bind_param("ssssss", $application_no, $class_to_join, $student_name, $student_surname, $signature_data, $typed_signature);
            
            if ($stmt->execute()) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; color: #155724;'>";
                echo "✅ <strong>SUCCESS!</strong> Test application saved successfully!<br>";
                echo "🎉 Application Number: <strong>$application_no</strong><br>";
                echo "The form submission should now work correctly.";
                echo "</div>";
            } else {
                echo "❌ Database insertion failed: " . $stmt->error . "<br>";
            }
        } else {
            echo "❌ SQL preparation failed: " . $conn->error . "<br>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fix Application Form Error</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f8f9fa; 
            line-height: 1.6;
        }
        h1, h2 { color: #007bff; }
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
            color: #155724;
        }
        .next-steps h3 { color: #155724; margin-top: 0; }
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
    <h3>🧪 Test Database Fix</h3>
    <p>Submit this form to test if the database fix worked:</p>
    
    <form method="POST">
        <div class="form-group">
            <label>Class to Join:</label>
            <select name="class_to_join" required>
                <option value="">Select Class</option>
                <option value="Standard I">Standard I</option>
                <option value="Standard II">Standard II</option>
                <option value="Pre-Primary I">Pre-Primary I</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Student Name:</label>
            <input type="text" name="student_name" required>
        </div>
        
        <div class="form-group">
            <label>Student Surname:</label>
            <input type="text" name="student_surname" required>
        </div>
        
        <button type="submit" name="test_form">🚀 Test Database Fix</button>
    </form>
</div>

<div class="next-steps">
    <h3>✅ Next Steps</h3>
    <p>After running this fix:</p>
    <a href="index.html" target="_blank">Test Main Application Form</a>
    <a href="test_simple_form.html" target="_blank">Test Simple Form</a>
    <a href="admin/applications.php" target="_blank">View Applications</a>
</div>

<div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;">
    <h3>🔍 What This Fix Does</h3>
    <ul>
        <li>✅ Creates the database if it doesn't exist</li>
        <li>✅ Creates the applications table with all required columns</li>
        <li>✅ Adds missing signature_data and typed_signature columns</li>
        <li>✅ Creates the uploads/signatures directory</li>
        <li>✅ Tests the database insertion to verify it works</li>
    </ul>
    
    <h3>🎯 Common Causes of "An error occurred"</h3>
    <ul>
        <li><strong>Missing database columns:</strong> signature_data, typed_signature</li>
        <li><strong>Database connection issues:</strong> XAMPP MySQL not running</li>
        <li><strong>Table doesn't exist:</strong> applications table missing</li>
        <li><strong>Permission issues:</strong> uploads directory not writable</li>
        <li><strong>JavaScript errors:</strong> Invalid JSON response from server</li>
    </ul>
</div>

</body>
</html>