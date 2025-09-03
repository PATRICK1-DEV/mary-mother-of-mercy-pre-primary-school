<?php
// Step-by-step fix for gallery 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Gallery 500 Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .step { background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Gallery 500 Error</h1>
        <p>Let's fix the gallery.php HTTP 500 error step by step:</p>

        <?php
        $steps_completed = 0;
        $total_steps = 6;

        // Step 1: Check database connection
        echo '<div class="step">';
        echo '<h3>Step 1: Database Connection</h3>';
        try {
            require_once 'includes/config.php';
            if ($conn->connect_error) {
                throw new Exception($conn->connect_error);
            }
            echo '<div class="success">✅ Database connected successfully</div>';
            $steps_completed++;
        } catch (Exception $e) {
            echo '<div class="error">❌ Database connection failed: ' . $e->getMessage() . '</div>';
            echo '<p><strong>Fix:</strong> Make sure XAMPP MySQL is running</p>';
        }
        echo '</div>';

        // Step 2: Check gallery_images table
        echo '<div class="step">';
        echo '<h3>Step 2: Gallery Images Table</h3>';
        try {
            $result = $conn->query("SHOW TABLES LIKE 'gallery_images'");
            if ($result->num_rows == 0) {
                throw new Exception("Table does not exist");
            }
            echo '<div class="success">✅ gallery_images table exists</div>';
            $steps_completed++;
        } catch (Exception $e) {
            echo '<div class="error">❌ gallery_images table missing</div>';
            echo '<p><strong>Fix:</strong> <a href="update_database.php" class="btn btn-warning">Create Table</a></p>';
        }
        echo '</div>';

        // Step 3: Check images directory
        echo '<div class="step">';
        echo '<h3>Step 3: Images Directory</h3>';
        $images_dir = '../images/';
        if (!is_dir($images_dir)) {
            if (mkdir($images_dir, 0755, true)) {
                echo '<div class="success">✅ Images directory created</div>';
                $steps_completed++;
            } else {
                echo '<div class="error">❌ Failed to create images directory</div>';
            }
        } else {
            echo '<div class="success">✅ Images directory exists</div>';
            $steps_completed++;
        }
        echo '</div>';

        // Step 4: Check file permissions
        echo '<div class="step">';
        echo '<h3>Step 4: File Permissions</h3>';
        if (is_writable($images_dir)) {
            echo '<div class="success">✅ Images directory is writable</div>';
            $steps_completed++;
        } else {
            echo '<div class="error">❌ Images directory is not writable</div>';
            echo '<p><strong>Fix:</strong> Set directory permissions to 755</p>';
        }
        echo '</div>';

        // Step 5: Check PHP extensions
        echo '<div class="step">';
        echo '<h3>Step 5: PHP Extensions</h3>';
        $required_extensions = ['mysqli', 'gd', 'fileinfo'];
        $missing_extensions = [];
        
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing_extensions[] = $ext;
            }
        }
        
        if (empty($missing_extensions)) {
            echo '<div class="success">✅ All required PHP extensions are loaded</div>';
            $steps_completed++;
        } else {
            echo '<div class="error">❌ Missing PHP extensions: ' . implode(', ', $missing_extensions) . '</div>';
            echo '<p><strong>Fix:</strong> Enable these extensions in php.ini</p>';
        }
        echo '</div>';

        // Step 6: Test simple query
        echo '<div class="step">';
        echo '<h3>Step 6: Database Query Test</h3>';
        try {
            $result = $conn->query("SELECT COUNT(*) as count FROM gallery_images");
            if (!$result) {
                throw new Exception($conn->error);
            }
            $count = $result->fetch_assoc()['count'];
            echo '<div class="success">✅ Database query successful (Found ' . $count . ' images)</div>';
            $steps_completed++;
        } catch (Exception $e) {
            echo '<div class="error">❌ Database query failed: ' . $e->getMessage() . '</div>';
        }
        echo '</div>';

        // Summary
        echo '<div class="step">';
        echo '<h3>📊 Summary</h3>';
        echo "<p>Completed: $steps_completed / $total_steps steps</p>";
        
        if ($steps_completed == $total_steps) {
            echo '<div class="success">🎉 All checks passed! Gallery should work now.</div>';
            echo '<p><a href="gallery_simple.php" class="btn btn-success">Try Simple Gallery</a>';
            echo ' <a href="gallery.php" class="btn">Try Full Gallery</a></p>';
        } else {
            echo '<div class="error">⚠️ Some issues need to be fixed before gallery will work.</div>';
            echo '<p><a href="update_database.php" class="btn btn-warning">Fix Database Issues</a></p>';
        }
        echo '</div>';

        if (isset($conn)) {
            $conn->close();
        }
        ?>

        <div class="step">
            <h3>🔍 Additional Troubleshooting</h3>
            <p>If gallery.php still shows 500 error after fixing above issues:</p>
            <ul>
                <li><a href="debug_gallery.php" class="btn">Run Debug Script</a></li>
                <li><a href="test_gallery.php" class="btn">Run Test Script</a></li>
                <li><a href="gallery_simple.php" class="btn btn-success">Use Simple Gallery</a></li>
            </ul>
            
            <h4>Common Causes of 500 Errors:</h4>
            <ul>
                <li>PHP memory limit too low (increase to 256M)</li>
                <li>PHP execution time limit</li>
                <li>Missing PHP extensions</li>
                <li>File permission issues</li>
                <li>Database connection problems</li>
            </ul>
        </div>
    </div>
</body>
</html>