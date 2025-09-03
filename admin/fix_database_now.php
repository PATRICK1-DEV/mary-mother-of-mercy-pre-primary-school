<?php
// Direct database fix script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Fixing Database Issues Now</h2>";

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

// Create gallery_images table
echo "<h3>Creating gallery_images table...</h3>";
$gallery_table_sql = "CREATE TABLE IF NOT EXISTS gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    image_path VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($gallery_table_sql) === TRUE) {
    echo "✅ gallery_images table created successfully<br>";
} else {
    echo "❌ Error creating gallery_images table: " . $conn->error . "<br>";
}

// Create testimonials table
echo "<h3>Creating testimonials table...</h3>";
$testimonials_table_sql = "CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    message TEXT NOT NULL,
    image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($testimonials_table_sql) === TRUE) {
    echo "✅ testimonials table created successfully<br>";
} else {
    echo "❌ Error creating testimonials table: " . $conn->error . "<br>";
}

// Check if gallery_images table is empty and add sample data
$check_gallery = $conn->query("SELECT COUNT(*) as count FROM gallery_images");
$gallery_count = $check_gallery->fetch_assoc()['count'];

if ($gallery_count == 0) {
    echo "<br><h3>Adding sample gallery images...</h3>";
    
    $sample_images = [
        ["School Building", "school-2-1200x500-1.jpeg", "Our beautiful school building", 1],
        ["Students Activities", "Picnict-of-Uncity-1-scaled-1.jpeg", "Students enjoying outdoor activities", 2],
        ["School Events", "Picnict-of-Uncity-3-scaled-1.jpeg", "Special school events and celebrations", 3],
        ["Learning Environment", "Picnict-of-Uncity-4-scaled-1.jpeg", "Our conducive learning environment", 4]
    ];
    
    $stmt = $conn->prepare("INSERT INTO gallery_images (title, image_path, description, sort_order) VALUES (?, ?, ?, ?)");
    
    foreach ($sample_images as $image) {
        $stmt->bind_param("sssi", $image[0], $image[1], $image[2], $image[3]);
        if ($stmt->execute()) {
            echo "✅ Added: " . $image[0] . "<br>";
        } else {
            echo "❌ Failed to add: " . $image[0] . "<br>";
        }
    }
} else {
    echo "<br>ℹ️ Gallery already has $gallery_count images<br>";
}

// Check if testimonials table is empty and add sample data
$check_testimonials = $conn->query("SELECT COUNT(*) as count FROM testimonials");
$testimonials_count = $check_testimonials->fetch_assoc()['count'];

if ($testimonials_count == 0) {
    echo "<br><h3>Adding sample testimonials...</h3>";
    
    $sample_testimonials = [
        ["John Mwalimu", "Parent", "Mary Mother of Mercy School has provided excellent education for my child. The teachers are dedicated and caring.", "teacher-1.jpg"],
        ["Grace Kimani", "Parent", "I am very satisfied with the quality of education and moral values taught at this school.", "teacher-2.jpg"],
        ["Peter Msigwa", "Parent", "The school has a wonderful environment for learning and character development.", "teacher-3.jpg"]
    ];
    
    $stmt = $conn->prepare("INSERT INTO testimonials (name, position, message, image_path) VALUES (?, ?, ?, ?)");
    
    foreach ($sample_testimonials as $testimonial) {
        $stmt->bind_param("ssss", $testimonial[0], $testimonial[1], $testimonial[2], $testimonial[3]);
        if ($stmt->execute()) {
            echo "✅ Added testimonial: " . $testimonial[0] . "<br>";
        } else {
            echo "❌ Failed to add testimonial: " . $testimonial[0] . "<br>";
        }
    }
} else {
    echo "<br>ℹ️ Testimonials already has $testimonials_count entries<br>";
}

// Verify tables exist
echo "<br><h3>Verifying tables...</h3>";
$tables_to_check = ['gallery_images', 'testimonials', 'applications', 'teachers', 'slider_images', 'website_content'];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ $table table exists<br>";
    } else {
        echo "❌ $table table missing<br>";
    }
}

echo "<br><h2>🎉 Database Fix Complete!</h2>";
echo "<p>All required tables have been created and sample data added.</p>";

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f8f9fa;
}
h2, h3 {
    color: #007bff;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 10px 5px;
    text-decoration: none;
    border-radius: 5px;
    color: white;
    font-weight: bold;
}
.btn-success {
    background: #28a745;
}
.btn-primary {
    background: #007bff;
}
.btn-warning {
    background: #ffc107;
    color: #212529;
}
</style>

<div style="margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 10px;">
    <h3>🚀 Next Steps:</h3>
    <a href="fix_gallery_500.php" class="btn btn-warning">Re-run Diagnostic</a>
    <a href="gallery_simple.php" class="btn btn-success">Test Simple Gallery</a>
    <a href="gallery.php" class="btn btn-primary">Test Full Gallery</a>
    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
</div>