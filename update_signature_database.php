<?php
// Database update script to add signature functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Database Update for Signature Functionality</h1>";

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
    
    // Check if signature columns exist
    $result = $conn->query("DESCRIBE applications");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $updates_needed = [];
    
    // Check for signature_data column
    if (!in_array('signature_data', $columns)) {
        $updates_needed[] = "ADD COLUMN signature_data VARCHAR(255) NULL COMMENT 'Signature file name or base64 data'";
    }
    
    // Check for typed_signature column
    if (!in_array('typed_signature', $columns)) {
        $updates_needed[] = "ADD COLUMN typed_signature VARCHAR(255) NULL COMMENT 'Typed signature name'";
    }
    
    // Check for official use columns
    if (!in_array('admission_decision', $columns)) {
        $updates_needed[] = "ADD COLUMN admission_decision ENUM('ADMITTED', 'NOT ADMITTED', 'PENDING', 'CONDITIONAL') NULL COMMENT 'Official admission decision'";
    }
    
    if (!in_array('decision_date', $columns)) {
        $updates_needed[] = "ADD COLUMN decision_date DATE NULL COMMENT 'Date of admission decision'";
    }
    
    if (!in_array('official_comments', $columns)) {
        $updates_needed[] = "ADD COLUMN official_comments TEXT NULL COMMENT 'Official comments and notes'";
    }
    
    if (!in_array('head_master_name', $columns)) {
        $updates_needed[] = "ADD COLUMN head_master_name VARCHAR(255) NULL COMMENT 'Head Master/Mistress name'";
    }
    
    if (!in_array('head_master_signature', $columns)) {
        $updates_needed[] = "ADD COLUMN head_master_signature VARCHAR(255) NULL COMMENT 'Head Master/Mistress signature file'";
    }
    
    if (!in_array('academic_master_name', $columns)) {
        $updates_needed[] = "ADD COLUMN academic_master_name VARCHAR(255) NULL COMMENT 'Academic Master/Mistress name'";
    }
    
    if (!in_array('academic_master_signature', $columns)) {
        $updates_needed[] = "ADD COLUMN academic_master_signature VARCHAR(255) NULL COMMENT 'Academic Master/Mistress signature file'";
    }
    
    if (empty($updates_needed)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
        echo "✅ <strong>All signature columns already exist!</strong><br>";
        echo "No database updates needed.";
        echo "</div>";
    } else {
        echo "<h2>📝 Applying Database Updates</h2>";
        
        foreach ($updates_needed as $update) {
            $sql = "ALTER TABLE applications " . $update;
            echo "Executing: " . htmlspecialchars($sql) . "<br>";
            
            if ($conn->query($sql) === TRUE) {
                echo "✅ Success<br><br>";
            } else {
                echo "❌ Error: " . $conn->error . "<br><br>";
            }
        }
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 20px 0;'>";
        echo "✅ <strong>Database updates completed!</strong><br>";
        echo "The applications table now supports signature functionality.";
        echo "</div>";
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/signatures/';
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0777, true)) {
            echo "✅ Created uploads/signatures/ directory<br>";
        } else {
            echo "❌ Failed to create uploads/signatures/ directory<br>";
        }
    } else {
        echo "✅ uploads/signatures/ directory already exists<br>";
    }
    
    // Show current table structure
    echo "<h2>📊 Current Applications Table Structure</h2>";
    $result = $conn->query("DESCRIBE applications");
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Field</th><th style='padding: 8px;'>Type</th><th style='padding: 8px;'>Null</th><th style='padding: 8px;'>Key</th><th style='padding: 8px;'>Default</th><th style='padding: 8px;'>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Update - Signature Functionality</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f8f9fa; 
            line-height: 1.6;
        }
        h1, h2 { color: #007bff; }
        table { margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;">
        <h3>🎉 What's New</h3>
        <ul>
            <li><strong>Interactive Signature Section:</strong> Users can now draw, upload, or type their signatures</li>
            <li><strong>Official Use Section:</strong> Admin can record admission decisions and signatures</li>
            <li><strong>File Upload Support:</strong> Signature images are saved securely</li>
            <li><strong>Multiple Signature Methods:</strong> Draw with mouse/touch, upload image, or type name</li>
            <li><strong>Database Integration:</strong> All signature data is stored in the database</li>
        </ul>
        
        <h3>🔗 Next Steps</h3>
        <p>After running this update:</p>
        <ol>
            <li><a href="index.html">Test the updated application form</a></li>
            <li><a href="signature_fix.html">View the signature demo</a></li>
            <li><a href="admin/applications.php">Check admin panel for signature display</a></li>
        </ol>
    </div>
</body>
</html>