<?php
// Complete setup script for Mary Mother of Mercy School website
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Mary Mother of Mercy School - Complete Setup</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .warning { color: orange; font-weight: bold; }
    .section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #007bff; }
    .step { margin: 10px 0; padding: 10px; background: white; border-radius: 4px; }
    .nav-links { background: #007bff; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; }
    .nav-links a { color: white; text-decoration: none; margin: 0 15px; font-weight: bold; }
    .nav-links a:hover { text-decoration: underline; }
</style>";

$setup_steps = [];
$errors = [];

try {
    // Step 1: Database Connection Test
    echo "<div class='section'>";
    echo "<h2>🔧 Step 1: Database Setup</h2>";
    
    $conn = new mysqli('localhost', 'root', '', '');
    if ($conn->connect_error) {
        throw new Exception("Cannot connect to MySQL: " . $conn->connect_error);
    }
    echo "<div class='step success'>✅ MySQL connection successful</div>";
    
    // Create database
    $database = 'marry_mother_mercy_db';
    $conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8 COLLATE utf8_general_ci");
    $conn->select_db($database);
    echo "<div class='step success'>✅ Database '$database' created/selected</div>";
    
    // Create tables
    $tables = [
        'applications' => "
            CREATE TABLE IF NOT EXISTS `applications` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `application_no` varchar(50) DEFAULT NULL,
                `class_to_join` varchar(100) NOT NULL,
                `student_name` varchar(255) NOT NULL,
                `student_middle_name` varchar(255) DEFAULT NULL,
                `student_surname` varchar(255) NOT NULL,
                `sex` varchar(10) DEFAULT NULL,
                `date_of_birth` date DEFAULT NULL,
                `place_of_birth` varchar(255) DEFAULT NULL,
                `nationality` varchar(100) DEFAULT NULL,
                `tribe` varchar(100) DEFAULT NULL,
                `religion` varchar(100) DEFAULT NULL,
                `denomination` varchar(100) DEFAULT NULL,
                `previous_school` varchar(255) DEFAULT NULL,
                `previous_class` varchar(100) DEFAULT NULL,
                `father_name` varchar(255) DEFAULT NULL,
                `father_occupation` varchar(255) DEFAULT NULL,
                `father_phone` varchar(50) DEFAULT NULL,
                `father_workplace` varchar(255) DEFAULT NULL,
                `mother_name` varchar(255) DEFAULT NULL,
                `mother_occupation` varchar(255) DEFAULT NULL,
                `mother_phone` varchar(50) DEFAULT NULL,
                `mother_workplace` varchar(255) DEFAULT NULL,
                `guardian_name` varchar(255) DEFAULT NULL,
                `guardian_occupation` varchar(255) DEFAULT NULL,
                `guardian_phone` varchar(50) DEFAULT NULL,
                `guardian_workplace` varchar(255) DEFAULT NULL,
                `postal_box` varchar(100) DEFAULT NULL,
                `postal_place` varchar(255) DEFAULT NULL,
                `signature_data` longtext DEFAULT NULL,
                `typed_signature` varchar(255) DEFAULT NULL,
                `status` varchar(20) DEFAULT 'pending',
                `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `application_no` (`application_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        'teachers' => "
            CREATE TABLE IF NOT EXISTS `teachers` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `subject` varchar(255) NOT NULL,
                `qualification` text,
                `experience` varchar(100),
                `image` varchar(255),
                `bio` text,
                `is_active` tinyint(1) DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        'content' => "
            CREATE TABLE IF NOT EXISTS `content` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `section` varchar(100) NOT NULL,
                `title` varchar(255),
                `content` text,
                `image` varchar(255),
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `section` (`section`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        'gallery' => "
            CREATE TABLE IF NOT EXISTS `gallery` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `image` varchar(255) NOT NULL,
                `category` varchar(100),
                `is_active` tinyint(1) DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        
        'testimonials' => "
            CREATE TABLE IF NOT EXISTS `testimonials` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `role` varchar(255),
                `message` text NOT NULL,
                `image` varchar(255),
                `rating` int(1) DEFAULT 5,
                `status` enum('active','inactive') DEFAULT 'active',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    ];
    
    foreach ($tables as $table_name => $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "<div class='step success'>✅ Table '$table_name' created successfully</div>";
        } else {
            echo "<div class='step error'>❌ Error creating table '$table_name': " . $conn->error . "</div>";
            $errors[] = "Table creation failed: $table_name";
        }
    }
    echo "</div>";
    
    // Step 2: Directory Structure
    echo "<div class='section'>";
    echo "<h2>📁 Step 2: Directory Structure</h2>";
    
    $directories = [
        'admin/backups',
        'uploads/signatures',
        'images'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<div class='step success'>✅ Created directory: $dir</div>";
            } else {
                echo "<div class='step error'>❌ Failed to create directory: $dir</div>";
                $errors[] = "Directory creation failed: $dir";
            }
        } else {
            echo "<div class='step info'>ℹ️ Directory already exists: $dir</div>";
        }
        
        if (is_writable($dir)) {
            echo "<div class='step success'>✅ Directory is writable: $dir</div>";
        } else {
            echo "<div class='step warning'>⚠️ Directory is not writable: $dir</div>";
        }
    }
    echo "</div>";
    
    // Step 3: PHP Extensions Check
    echo "<div class='section'>";
    echo "<h2>🔌 Step 3: PHP Extensions</h2>";
    
    $required_extensions = [
        'mysqli' => 'Database connectivity',
        'zip' => 'Backup functionality',
        'gd' => 'Image processing',
        'json' => 'JSON processing'
    ];
    
    foreach ($required_extensions as $ext => $purpose) {
        if (extension_loaded($ext)) {
            echo "<div class='step success'>✅ $ext extension loaded ($purpose)</div>";
        } else {
            echo "<div class='step error'>❌ $ext extension missing ($purpose)</div>";
            $errors[] = "Missing PHP extension: $ext";
        }
    }
    echo "</div>";
    
    // Step 4: File Permissions
    echo "<div class='section'>";
    echo "<h2>🔐 Step 4: File Permissions</h2>";
    
    $files_to_check = [
        'process_application.php' => 'Application processing',
        'admin/backup.php' => 'Backup functionality',
        'admin/includes/config.php' => 'Configuration'
    ];
    
    foreach ($files_to_check as $file => $purpose) {
        if (file_exists($file)) {
            echo "<div class='step success'>✅ File exists: $file ($purpose)</div>";
            if (is_readable($file)) {
                echo "<div class='step success'>✅ File is readable: $file</div>";
            } else {
                echo "<div class='step error'>❌ File is not readable: $file</div>";
                $errors[] = "File not readable: $file";
            }
        } else {
            echo "<div class='step error'>❌ File missing: $file ($purpose)</div>";
            $errors[] = "File missing: $file";
        }
    }
    echo "</div>";
    
    // Step 5: Sample Data
    echo "<div class='section'>";
    echo "<h2>📊 Step 5: Sample Data</h2>";
    
    // Check if we need to add sample data
    $teacher_count = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'];
    if ($teacher_count == 0) {
        $sample_teachers = [
            "('Sister Mary Catherine', 'Principal & Administration', 'Masters in Education, 20+ years experience', '20+ years', NULL, 'Dedicated educator and administrator committed to excellence in education.', 1)",
            "('Mr. John Smith', 'Mathematics & Science', 'BSc Mathematics, MSc Education', '10 years', NULL, 'Passionate about making mathematics and science accessible to all students.', 1)",
            "('Mrs. Sarah Johnson', 'English & Literature', 'BA English, MA Literature', '8 years', NULL, 'Inspiring students to develop strong communication and critical thinking skills.', 1)",
            "('Ms. Grace Mwalimu', 'Kiswahili & Social Studies', 'BA Education, Diploma in Kiswahili', '12 years', NULL, 'Promoting cultural values and national identity through language and social studies.', 1)"
        ];
        
        foreach ($sample_teachers as $teacher_data) {
            $sql = "INSERT INTO teachers (name, subject, qualification, experience, image, bio, is_active) VALUES $teacher_data";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='step success'>✅ Sample teacher added</div>";
            }
        }
    } else {
        echo "<div class='step info'>ℹ️ Teachers table already has data ($teacher_count records)</div>";
    }
    
    // Add sample content
    $content_count = $conn->query("SELECT COUNT(*) as count FROM content")->fetch_assoc()['count'];
    if ($content_count == 0) {
        $sample_content = [
            "('about', 'About Mary Mother of Mercy School', 'Mary Mother of Mercy Pre & Primary School is a Catholic institution dedicated to providing quality education in a nurturing Christian environment. Founded in 2011, we have been serving the community of Mjimpya Relini with excellence in education.', NULL)",
            "('mission', 'Our Mission', 'To uplift and integrate pupils into a bright future providing quality education, discipline, and moral values in a Christian environment.', NULL)",
            "('vision', 'Our Vision', 'To build a society based on positive cultural, moral and spiritual values for a peaceful and prosperous world.', NULL)"
        ];
        
        foreach ($sample_content as $content_data) {
            $sql = "INSERT INTO content (section, title, content, image) VALUES $content_data";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='step success'>✅ Sample content added</div>";
            }
        }
    } else {
        echo "<div class='step info'>ℹ️ Content table already has data ($content_count records)</div>";
    }
    echo "</div>";
    
    // Step 6: System Status
    echo "<div class='section'>";
    echo "<h2>📈 Step 6: System Status</h2>";
    
    $app_count = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
    $pending_count = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")->fetch_assoc()['count'];
    
    echo "<div class='step info'>📊 Total Applications: $app_count</div>";
    echo "<div class='step info'>⏳ Pending Applications: $pending_count</div>";
    echo "<div class='step info'>👥 Total Teachers: $teacher_count</div>";
    echo "<div class='step info'>📝 Content Sections: $content_count</div>";
    
    $disk_space = disk_free_space('.');
    echo "<div class='step info'>💾 Available Disk Space: " . formatBytes($disk_space) . "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>❌ Setup Error: " . $e->getMessage() . "</div>";
    $errors[] = $e->getMessage();
}

// Summary
echo "<div class='section'>";
echo "<h2>📋 Setup Summary</h2>";

if (empty($errors)) {
    echo "<div class='step success'>🎉 Setup completed successfully! Your Mary Mother of Mercy School website is ready to use.</div>";
} else {
    echo "<div class='step warning'>⚠️ Setup completed with " . count($errors) . " issues:</div>";
    foreach ($errors as $error) {
        echo "<div class='step error'>❌ $error</div>";
    }
}
echo "</div>";

// Navigation Links
echo "<div class='nav-links'>";
echo "<h3>🚀 Quick Access Links</h3>";
echo "<a href='../index.html'>🏠 Main Website</a>";
echo "<a href='login.php'>🔐 Admin Login</a>";
echo "<a href='dashboard.php'>📊 Dashboard</a>";
echo "<a href='backup.php'>💾 Backup System</a>";
echo "<a href='test_backup_system.php'>🧪 Test Backup</a>";
echo "<a href='check_database_status.php'>🔍 Database Status</a>";
echo "</div>";

// Admin Credentials
echo "<div class='section'>";
echo "<h2>🔑 Admin Access</h2>";
echo "<div class='step info'><strong>Username:</strong> admin</div>";
echo "<div class='step info'><strong>Password:</strong> mercy2024</div>";
echo "<div class='step warning'>⚠️ Please change the default password after first login!</div>";
echo "</div>";

// Helper function
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
?>