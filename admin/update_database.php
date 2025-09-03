<?php
// Database update script - Run this to add missing tables

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marry_mother_mercy_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Update Script</h2>";

// Create missing tables
$tables = [
    // Gallery images table
    "CREATE TABLE IF NOT EXISTS gallery_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        image_path VARCHAR(255) NOT NULL,
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Testimonials table
    "CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        position VARCHAR(255),
        message TEXT NOT NULL,
        image_path VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table created/verified successfully<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
}

// Insert some sample gallery images if table is empty
$check_gallery = $conn->query("SELECT COUNT(*) as count FROM gallery_images");
$gallery_count = $check_gallery->fetch_assoc()['count'];

if ($gallery_count == 0) {
    echo "<br><h3>Adding Sample Gallery Images</h3>";
    
    $sample_images = [
        "INSERT INTO gallery_images (title, image_path, description, sort_order) VALUES 
        ('School Building', 'school-2-1200x500-1.jpeg', 'Our beautiful school building', 1),
        ('Students Activities', 'Picnict-of-Uncity-1-scaled-1.jpeg', 'Students enjoying outdoor activities', 2),
        ('School Events', 'Picnict-of-Uncity-3-scaled-1.jpeg', 'Special school events and celebrations', 3),
        ('Learning Environment', 'Picnict-of-Uncity-4-scaled-1.jpeg', 'Our conducive learning environment', 4)"
    ];
    
    foreach ($sample_images as $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "✅ Sample gallery images added<br>";
        } else {
            echo "❌ Error adding sample images: " . $conn->error . "<br>";
        }
    }
}

// Insert some sample testimonials if table is empty
$check_testimonials = $conn->query("SELECT COUNT(*) as count FROM testimonials");
$testimonials_count = $check_testimonials->fetch_assoc()['count'];

if ($testimonials_count == 0) {
    echo "<br><h3>Adding Sample Testimonials</h3>";
    
    $sample_testimonials = [
        "INSERT INTO testimonials (name, position, message, image_path) VALUES 
        ('John Mwalimu', 'Parent', 'Mary Mother of Mercy School has provided excellent education for my child. The teachers are dedicated and caring.', 'teacher-1.jpg'),
        ('Grace Kimani', 'Parent', 'I am very satisfied with the quality of education and moral values taught at this school.', 'teacher-2.jpg'),
        ('Peter Msigwa', 'Parent', 'The school has a wonderful environment for learning and character development.', 'teacher-3.jpg')"
    ];
    
    foreach ($sample_testimonials as $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "✅ Sample testimonials added<br>";
        } else {
            echo "❌ Error adding sample testimonials: " . $conn->error . "<br>";
        }
    }
}

echo "<br><strong>✅ Database update completed successfully!</strong><br>";
echo "<br><a href='gallery.php' class='btn btn-primary'>Go to Gallery Management</a>";
echo " | <a href='dashboard.php' class='btn btn-secondary'>Go to Dashboard</a>";

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
    margin: 5px;
    text-decoration: none;
    border-radius: 5px;
    color: white;
}
.btn-primary {
    background: #007bff;
}
.btn-secondary {
    background: #6c757d;
}
</style>