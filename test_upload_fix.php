<?php
// Test upload directory fix
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Testing Upload Directory Fix</h1>";

$project_root = dirname(__FILE__);
$uploads_dir = $project_root . '/uploads';
$signatures_dir = $uploads_dir . '/signatures';

echo "<p><strong>Testing from:</strong> $project_root</p>";

// Test 1: Check if directories exist
echo "<h2>📁 Directory Existence Test</h2>";

if (file_exists($uploads_dir)) {
    echo "✅ uploads/ directory exists<br>";
    echo "Permissions: " . substr(sprintf('%o', fileperms($uploads_dir)), -4) . "<br>";
    echo "Writable: " . (is_writable($uploads_dir) ? "✅ Yes" : "❌ No") . "<br>";
} else {
    echo "❌ uploads/ directory does not exist<br>";
    
    // Try to create it
    echo "Attempting to create uploads/ directory...<br>";
    if (@mkdir($uploads_dir, 0777, true)) {
        echo "✅ Successfully created uploads/ directory<br>";
    } else {
        echo "❌ Failed to create uploads/ directory<br>";
    }
}

if (file_exists($signatures_dir)) {
    echo "✅ uploads/signatures/ directory exists<br>";
    echo "Permissions: " . substr(sprintf('%o', fileperms($signatures_dir)), -4) . "<br>";
    echo "Writable: " . (is_writable($signatures_dir) ? "✅ Yes" : "❌ No") . "<br>";
} else {
    echo "❌ uploads/signatures/ directory does not exist<br>";
    
    // Try to create it
    echo "Attempting to create uploads/signatures/ directory...<br>";
    if (@mkdir($signatures_dir, 0777, true)) {
        echo "✅ Successfully created uploads/signatures/ directory<br>";
    } else {
        echo "❌ Failed to create uploads/signatures/ directory<br>";
    }
}

// Test 2: Try to write a test file
echo "<h2>📝 File Writing Test</h2>";

$test_content = "Test file created at " . date('Y-m-d H:i:s');
$test_file = $project_root . '/test_write.txt';

if (@file_put_contents($test_file, $test_content)) {
    echo "✅ Can write files in project root<br>";
    @unlink($test_file);
} else {
    echo "❌ Cannot write files in project root<br>";
}

if (file_exists($uploads_dir)) {
    $test_upload_file = $uploads_dir . '/test_upload.txt';
    if (@file_put_contents($test_upload_file, $test_content)) {
        echo "✅ Can write files in uploads/ directory<br>";
        @unlink($test_upload_file);
    } else {
        echo "❌ Cannot write files in uploads/ directory<br>";
    }
}

if (file_exists($signatures_dir)) {
    $test_sig_file = $signatures_dir . '/test_signature.txt';
    if (@file_put_contents($test_sig_file, $test_content)) {
        echo "✅ Can write files in uploads/signatures/ directory<br>";
        @unlink($test_sig_file);
    } else {
        echo "❌ Cannot write files in uploads/signatures/ directory<br>";
    }
}

// Test 3: Test signature file creation
echo "<h2>🖊️ Signature File Test</h2>";

if (file_exists($signatures_dir) && is_writable($signatures_dir)) {
    $signature_filename = 'MMM2024_test_signature.png';
    $signature_path = $signatures_dir . '/' . $signature_filename;
    
    // Create a simple test image (1x1 pixel PNG)
    $test_image_data = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    
    if (@file_put_contents($signature_path, $test_image_data)) {
        echo "✅ Successfully created test signature file<br>";
        echo "File path: $signature_path<br>";
        echo "File size: " . filesize($signature_path) . " bytes<br>";
        @unlink($signature_path);
    } else {
        echo "❌ Failed to create test signature file<br>";
    }
} else {
    echo "⚠️ Signatures directory not available for testing<br>";
}

// Test 4: Database test
echo "<h2>🗄️ Database Connection Test</h2>";

try {
    $conn = new mysqli("localhost", "root", "", "marry_mother_mercy_db");
    
    if ($conn->connect_error) {
        echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ Database connection successful<br>";
        
        // Test table creation
        $create_table = "CREATE TABLE IF NOT EXISTS test_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_no VARCHAR(50),
            test_data TEXT
        )";
        
        if ($conn->query($create_table)) {
            echo "✅ Can create tables<br>";
            
            // Test data insertion
            $test_app_no = 'TEST' . time();
            $insert_sql = "INSERT INTO test_applications (application_no, test_data) VALUES ('$test_app_no', 'test')";
            
            if ($conn->query($insert_sql)) {
                echo "✅ Can insert data<br>";
                
                // Clean up
                $conn->query("DELETE FROM test_applications WHERE application_no = '$test_app_no'");
            } else {
                echo "❌ Cannot insert data: " . $conn->error . "<br>";
            }
        } else {
            echo "❌ Cannot create tables: " . $conn->error . "<br>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Fix Test Results</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f8f9fa; 
            line-height: 1.6;
        }
        h1, h2 { color: #007bff; }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            color: #155724;
        }
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            color: #721c24;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            color: #856404;
        }
        .test-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .test-buttons a {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .test-buttons a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<?php
// Determine overall status
$uploads_working = file_exists($uploads_dir) && is_writable($uploads_dir);
$signatures_working = file_exists($signatures_dir) && is_writable($signatures_dir);

if ($uploads_working && $signatures_working) {
    echo '<div class="success-box">';
    echo '<h3>🎉 All Tests Passed!</h3>';
    echo '<p>File uploads should now work correctly. You can use the original application form with signature uploads.</p>';
    echo '</div>';
} elseif ($uploads_working) {
    echo '<div class="warning-box">';
    echo '<h3>⚠️ Partial Success</h3>';
    echo '<p>Uploads directory works, but signatures directory has issues. You can still save signatures as base64 in the database.</p>';
    echo '</div>';
} else {
    echo '<div class="error-box">';
    echo '<h3>❌ Upload Issues Remain</h3>';
    echo '<p>File uploads are still not working. Use the no-file version of the application processor.</p>';
    echo '</div>';
}
?>

<div class="test-buttons">
    <h3>🚀 Next Steps</h3>
    
    <?php if ($uploads_working && $signatures_working): ?>
        <a href="index.html">Test Main Application Form</a>
        <a href="debug_live_form.html">Test All Scripts</a>
    <?php else: ?>
        <a href="debug_live_form.html">Test No-File Version</a>
        <a href="fix_permissions.php">View Permission Solutions</a>
    <?php endif; ?>
    
    <a href="process_application_no_files.php" style="background: #28a745;">Use No-File Processor</a>
</div>

<div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;">
    <h3>🔧 Manual Fix Commands</h3>
    <p>If tests still fail, run these commands in Terminal:</p>
    <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">
cd /Applications/XAMPP/xamppfiles/htdocs/marry_mother_of_mercy
sudo mkdir -p uploads/signatures
sudo chmod -R 777 uploads
sudo chown -R _www:_www uploads</pre>
    
    <h3>🎯 Alternative Solution</h3>
    <p>If file permissions can't be fixed, use the no-file version:</p>
    <ol>
        <li>Rename your current process_application.php to process_application_backup.php</li>
        <li>Rename process_application_no_files.php to process_application.php</li>
        <li>Test the application form - it will work without file uploads</li>
    </ol>
</div>

</body>
</html>