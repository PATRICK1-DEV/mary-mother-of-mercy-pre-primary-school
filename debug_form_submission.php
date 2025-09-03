<?php
// Debug form submission issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Form Submission Debug Tool</h1>";

// Test 1: Check if this script can run
echo "<h2>Step 1: Basic PHP Test</h2>";
echo "✅ PHP is working<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br><br>";

// Test 2: Check database connection
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
}

// Test 3: Check if applications table exists and has correct structure
echo "<h2>Step 3: Applications Table Check</h2>";
try {
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
            signature_data VARCHAR(255),
            typed_signature VARCHAR(255),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table) === TRUE) {
            echo "✅ Applications table created successfully<br>";
        } else {
            echo "❌ Error creating table: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ Applications table exists<br>";
        
        // Check if signature columns exist
        $columns_result = $conn->query("DESCRIBE applications");
        $columns = [];
        while ($row = $columns_result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        if (!in_array('signature_data', $columns)) {
            echo "⚠️ Adding signature_data column...<br>";
            $conn->query("ALTER TABLE applications ADD COLUMN signature_data VARCHAR(255) NULL");
        }
        
        if (!in_array('typed_signature', $columns)) {
            echo "⚠️ Adding typed_signature column...<br>";
            $conn->query("ALTER TABLE applications ADD COLUMN typed_signature VARCHAR(255) NULL");
        }
        
        echo "✅ Table structure verified<br>";
    }
} catch (Exception $e) {
    echo "❌ Table check failed: " . $e->getMessage() . "<br>";
}

// Test 4: Test process_application.php directly
echo "<h2>Step 4: Testing process_application.php</h2>";
if (file_exists('process_application.php')) {
    echo "✅ process_application.php file exists<br>";
    
    // Test with sample data
    if ($_POST && isset($_POST['test_submission'])) {
        echo "<h3>📝 Processing Test Submission</h3>";
        
        // Simulate form data
        $_POST['class_to_join'] = 'Standard I';
        $_POST['student_name'] = 'Test';
        $_POST['student_surname'] = 'Student';
        
        // Capture output from process_application.php
        ob_start();
        include 'process_application.php';
        $output = ob_get_clean();
        
        echo "<h4>Raw Output from process_application.php:</h4>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        echo htmlspecialchars($output);
        echo "</pre>";
        
        // Try to decode as JSON
        $json_data = json_decode($output, true);
        if ($json_data) {
            echo "<h4>Parsed JSON Response:</h4>";
            echo "<pre style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
            print_r($json_data);
            echo "</pre>";
        } else {
            echo "<h4>❌ Output is not valid JSON</h4>";
            echo "This might be causing the 'An error occurred' message.<br>";
        }
    }
} else {
    echo "❌ process_application.php file not found<br>";
}

// Test 5: Check uploads directory
echo "<h2>Step 5: Uploads Directory Check</h2>";
$upload_dir = 'uploads/signatures/';
if (!file_exists($upload_dir)) {
    echo "⚠️ Creating uploads directory...<br>";
    if (mkdir($upload_dir, 0777, true)) {
        echo "✅ Created uploads/signatures/ directory<br>";
    } else {
        echo "❌ Failed to create uploads directory<br>";
    }
} else {
    echo "✅ uploads/signatures/ directory exists<br>";
}

// Test 6: Check file permissions
echo "<h2>Step 6: File Permissions Check</h2>";
if (is_writable($upload_dir)) {
    echo "✅ uploads/signatures/ directory is writable<br>";
} else {
    echo "❌ uploads/signatures/ directory is not writable<br>";
    echo "Try running: chmod 777 uploads/signatures/<br>";
}

if (is_readable('process_application.php')) {
    echo "✅ process_application.php is readable<br>";
} else {
    echo "❌ process_application.php is not readable<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Submission Debug</title>
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
        .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-warning { background: #fff3cd; color: #856404; }
        .alert-info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>

<div class="test-form">
    <h3>🧪 Test Form Submission</h3>
    <p>Click the button below to test the form processing:</p>
    
    <form method="POST">
        <button type="submit" name="test_submission">🚀 Test process_application.php</button>
    </form>
</div>

<div class="test-form">
    <h3>🔧 JavaScript Test Form</h3>
    <p>This form tests the actual JavaScript submission process:</p>
    
    <form id="testForm">
        <div class="form-group">
            <label>Class to Join:</label>
            <select name="class_to_join" required>
                <option value="">Select Class</option>
                <option value="Standard I">Standard I</option>
                <option value="Standard II">Standard II</option>
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
        
        <button type="submit">Submit Test Form</button>
    </form>
    
    <div id="testResult" style="margin-top: 20px;"></div>
</div>

<div class="alert alert-info">
    <h3>🔍 Common Issues & Solutions</h3>
    <ul>
        <li><strong>Database not running:</strong> Start XAMPP MySQL service</li>
        <li><strong>Table doesn't exist:</strong> Run the test above to create it</li>
        <li><strong>PHP errors:</strong> Check if process_application.php has syntax errors</li>
        <li><strong>Permission issues:</strong> Make sure uploads directory is writable</li>
        <li><strong>JavaScript errors:</strong> Check browser console (F12)</li>
    </ul>
</div>

<script>
document.getElementById('testForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const resultDiv = document.getElementById('testResult');
    resultDiv.innerHTML = '<div class="alert alert-info">🔄 Testing form submission...</div>';
    
    const formData = new FormData(this);
    
    fetch('process_application.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        
        resultDiv.innerHTML = `
            <div class="alert alert-info">
                <h4>📄 Raw Response:</h4>
                <pre style="background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto;">${text}</pre>
            </div>
        `;
        
        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            
            if (data.success) {
                resultDiv.innerHTML += `
                    <div class="alert alert-success">
                        <h4>✅ Success!</h4>
                        <p><strong>Message:</strong> ${data.message}</p>
                        <p><strong>Application No:</strong> ${data.application_no}</p>
                    </div>
                `;
            } else {
                resultDiv.innerHTML += `
                    <div class="alert alert-danger">
                        <h4>❌ Error</h4>
                        <p><strong>Message:</strong> ${data.message}</p>
                    </div>
                `;
            }
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            resultDiv.innerHTML += `
                <div class="alert alert-danger">
                    <h4>❌ JSON Parse Error</h4>
                    <p>The server response is not valid JSON. This is likely causing the "An error occurred" message.</p>
                    <p><strong>Parse Error:</strong> ${parseError.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <h4>❌ Network Error</h4>
                <p><strong>Error:</strong> ${error.message}</p>
                <p>This could be causing the "An error occurred" message.</p>
            </div>
        `;
    });
});
</script>

</body>
</html>