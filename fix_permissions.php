<?php
// Fix permissions for XAMPP on macOS
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Fix Permissions for XAMPP on macOS</h1>";

$project_root = dirname(__FILE__);
$uploads_dir = $project_root . '/uploads';
$signatures_dir = $uploads_dir . '/signatures';

echo "<p><strong>Project Root:</strong> $project_root</p>";
echo "<p><strong>Current User:</strong> " . get_current_user() . "</p>";
echo "<p><strong>PHP User:</strong> " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'Unknown') . "</p>";

// Check current permissions
echo "<h2>📋 Current Permissions</h2>";

if (file_exists($project_root)) {
    $perms = fileperms($project_root);
    echo "Project root permissions: " . substr(sprintf('%o', $perms), -4) . "<br>";
    echo "Project root writable: " . (is_writable($project_root) ? "✅ Yes" : "❌ No") . "<br>";
} else {
    echo "❌ Project root not found<br>";
}

if (file_exists($uploads_dir)) {
    $perms = fileperms($uploads_dir);
    echo "Uploads directory permissions: " . substr(sprintf('%o', $perms), -4) . "<br>";
    echo "Uploads directory writable: " . (is_writable($uploads_dir) ? "✅ Yes" : "❌ No") . "<br>";
} else {
    echo "⚠️ Uploads directory doesn't exist<br>";
}

// Try to create directories with different approaches
echo "<h2>🛠️ Attempting to Fix Permissions</h2>";

// Method 1: Try to create with different permissions
if (!file_exists($uploads_dir)) {
    echo "Attempting to create uploads directory...<br>";
    
    // Try different permission levels
    $permission_levels = [0777, 0755, 0775, 0766];
    
    foreach ($permission_levels as $perm) {
        if (@mkdir($uploads_dir, $perm, true)) {
            echo "✅ Created uploads directory with permissions " . sprintf('%o', $perm) . "<br>";
            break;
        } else {
            echo "❌ Failed to create with permissions " . sprintf('%o', $perm) . "<br>";
        }
    }
}

if (!file_exists($signatures_dir)) {
    echo "Attempting to create signatures directory...<br>";
    
    foreach ($permission_levels as $perm) {
        if (@mkdir($signatures_dir, $perm, true)) {
            echo "✅ Created signatures directory with permissions " . sprintf('%o', $perm) . "<br>";
            break;
        } else {
            echo "❌ Failed to create signatures directory with permissions " . sprintf('%o', $perm) . "<br>";
        }
    }
}

// Test file creation
echo "<h2>📝 Testing File Creation</h2>";

$test_file = $project_root . '/test_write.txt';
if (@file_put_contents($test_file, 'test')) {
    echo "✅ Can write files in project root<br>";
    @unlink($test_file);
} else {
    echo "❌ Cannot write files in project root<br>";
}

if (file_exists($uploads_dir)) {
    $test_upload_file = $uploads_dir . '/test_upload.txt';
    if (@file_put_contents($test_upload_file, 'test')) {
        echo "✅ Can write files in uploads directory<br>";
        @unlink($test_upload_file);
    } else {
        echo "❌ Cannot write files in uploads directory<br>";
    }
}

if (file_exists($signatures_dir)) {
    $test_sig_file = $signatures_dir . '/test_signature.txt';
    if (@file_put_contents($test_sig_file, 'test')) {
        echo "✅ Can write files in signatures directory<br>";
        @unlink($test_sig_file);
    } else {
        echo "❌ Cannot write files in signatures directory<br>";
    }
}

// Check XAMPP configuration
echo "<h2>🔍 XAMPP Configuration Check</h2>";

$xampp_htdocs = '/Applications/XAMPP/xamppfiles/htdocs';
if (file_exists($xampp_htdocs)) {
    echo "✅ XAMPP htdocs found<br>";
    echo "XAMPP htdocs writable: " . (is_writable($xampp_htdocs) ? "✅ Yes" : "❌ No") . "<br>";
} else {
    echo "❌ XAMPP htdocs not found<br>";
}

// Get system info
echo "<h2>💻 System Information</h2>";
echo "Operating System: " . PHP_OS . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Fix Permissions - XAMPP macOS</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f8f9fa; 
            line-height: 1.6;
        }
        h1, h2 { color: #007bff; }
        .solution-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            color: #856404;
        }
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
        .command {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 10px;
            font-family: monospace;
            margin: 10px 0;
        }
        .step {
            background: white;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 5px 5px 0;
        }
    </style>
</head>
<body>

<div class="solution-box">
    <h3>🛠️ macOS Permission Solutions</h3>
    <p>Here are several ways to fix the permission issue on macOS:</p>
</div>

<div class="step">
    <h3>Solution 1: Terminal Commands (Recommended)</h3>
    <p>Open Terminal and run these commands:</p>
    <div class="command">
        cd /Applications/XAMPP/xamppfiles/htdocs/marry_mother_of_mercy<br>
        sudo mkdir -p uploads/signatures<br>
        sudo chmod -R 777 uploads<br>
        sudo chown -R _www:_www uploads
    </div>
    <p><strong>Note:</strong> You'll need to enter your macOS password when prompted.</p>
</div>

<div class="step">
    <h3>Solution 2: Alternative Directory Location</h3>
    <p>Create the uploads directory in a location that doesn't require special permissions:</p>
    <div class="command">
        mkdir -p ~/Desktop/marry_mother_uploads/signatures<br>
        chmod 777 ~/Desktop/marry_mother_uploads/signatures
    </div>
    <p>Then update your PHP scripts to use this path instead.</p>
</div>

<div class="step">
    <h3>Solution 3: Disable Signature File Saving</h3>
    <p>Temporarily disable file saving and store signatures as base64 in the database only.</p>
    <p>This avoids the file permission issue entirely.</p>
</div>

<div class="step">
    <h3>Solution 4: Use XAMPP Manager</h3>
    <p>1. Open XAMPP Control Panel</p>
    <p>2. Stop Apache</p>
    <p>3. In Terminal, run:</p>
    <div class="command">
        sudo /Applications/XAMPP/xamppfiles/xampp security
    </div>
    <p>4. Restart Apache</p>
</div>

<div class="success-box">
    <h3>✅ Quick Test</h3>
    <p>After applying any solution, test by running:</p>
    <a href="test_upload_fix.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Test Upload Fix</a>
</div>

<div class="error-box">
    <h3>⚠️ If Nothing Works</h3>
    <p>Use the version without file uploads:</p>
    <a href="process_application_no_files.php" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Use No-File Version</a>
</div>

</body>
</html>