<?php
// Application Form Processing Script - No File Uploads Required
error_reporting(0); // Hide all errors from output
ini_set('display_errors', 0);

// Set content type for JSON response
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
}

// Function to sanitize input
function sanitizeInput($data) {
    if ($data === null || $data === '') {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to generate application number
function generateApplicationNumber() {
    return 'MMM' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method');
    }
    
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "marry_mother_mercy_db";
    
    // Create database connection
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        sendResponse(false, 'Database connection failed. Please make sure XAMPP MySQL is running.');
    }
    
    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
    
    // Create table if it doesn't exist (without file-related columns)
    $create_table = "CREATE TABLE IF NOT EXISTS applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_no VARCHAR(50) UNIQUE,
        class_to_join VARCHAR(100),
        student_name VARCHAR(255),
        student_middle_name VARCHAR(255),
        student_surname VARCHAR(255),
        sex VARCHAR(10),
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
        signature_data LONGTEXT,
        typed_signature VARCHAR(255),
        status VARCHAR(20) DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($create_table);
    
    // Validate required fields
    if (empty($_POST['class_to_join']) || empty($_POST['student_name']) || empty($_POST['student_surname'])) {
        sendResponse(false, 'Please fill in all required fields: Class to Join, Student Name, and Student Surname');
    }
    
    // Generate unique application number
    $application_no = generateApplicationNumber();
    
    // Check if application number exists and generate new one if needed
    $check_result = $conn->query("SELECT id FROM applications WHERE application_no = '$application_no'");
    while ($check_result && $check_result->num_rows > 0) {
        $application_no = generateApplicationNumber();
        $check_result = $conn->query("SELECT id FROM applications WHERE application_no = '$application_no'");
    }
    
    // Handle signature data (store as base64 in database, no file saving)
    $signature_data = '';
    if (isset($_POST['signature_data']) && !empty($_POST['signature_data'])) {
        $signature_data = $_POST['signature_data'];
        // Keep as base64 string, don't save to file
    }
    
    // Prepare data for insertion
    $data = [
        'application_no' => $application_no,
        'class_to_join' => sanitizeInput($_POST['class_to_join']),
        'student_name' => sanitizeInput($_POST['student_name']),
        'student_middle_name' => sanitizeInput($_POST['student_middle_name'] ?? ''),
        'student_surname' => sanitizeInput($_POST['student_surname']),
        'sex' => sanitizeInput($_POST['sex'] ?? ''),
        'date_of_birth' => sanitizeInput($_POST['date_of_birth'] ?? ''),
        'place_of_birth' => sanitizeInput($_POST['place_of_birth'] ?? ''),
        'nationality' => sanitizeInput($_POST['nationality'] ?? ''),
        'tribe' => sanitizeInput($_POST['tribe'] ?? ''),
        'religion' => sanitizeInput($_POST['religion'] ?? ''),
        'denomination' => sanitizeInput($_POST['denomination'] ?? ''),
        'previous_school' => sanitizeInput($_POST['previous_school'] ?? ''),
        'previous_class' => sanitizeInput($_POST['previous_class'] ?? ''),
        'father_name' => sanitizeInput($_POST['father_name'] ?? ''),
        'father_occupation' => sanitizeInput($_POST['father_occupation'] ?? ''),
        'father_phone' => sanitizeInput($_POST['father_phone'] ?? ''),
        'father_workplace' => sanitizeInput($_POST['father_workplace'] ?? ''),
        'mother_name' => sanitizeInput($_POST['mother_name'] ?? ''),
        'mother_occupation' => sanitizeInput($_POST['mother_occupation'] ?? ''),
        'mother_phone' => sanitizeInput($_POST['mother_phone'] ?? ''),
        'mother_workplace' => sanitizeInput($_POST['mother_workplace'] ?? ''),
        'guardian_name' => sanitizeInput($_POST['guardian_name'] ?? ''),
        'guardian_occupation' => sanitizeInput($_POST['guardian_occupation'] ?? ''),
        'guardian_phone' => sanitizeInput($_POST['guardian_phone'] ?? ''),
        'guardian_workplace' => sanitizeInput($_POST['guardian_workplace'] ?? ''),
        'postal_box' => sanitizeInput($_POST['postal_box'] ?? ''),
        'postal_place' => sanitizeInput($_POST['postal_place'] ?? ''),
        'signature_data' => $signature_data,
        'typed_signature' => sanitizeInput($_POST['typed_signature'] ?? '')
    ];
    
    // Convert empty date to NULL
    if (empty($data['date_of_birth'])) {
        $data['date_of_birth'] = null;
    }
    
    // Build SQL query
    $columns = implode(', ', array_keys($data));
    $placeholders = str_repeat('?,', count($data) - 1) . '?';
    $sql = "INSERT INTO applications ($columns) VALUES ($placeholders)";
    
    // Prepare and execute
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendResponse(false, 'Database preparation error. Please try again.');
    }
    
    // Create type string for bind_param
    $types = str_repeat('s', count($data));
    $values = array_values($data);
    
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        // Success response
        sendResponse(true, 'Application submitted successfully!', [
            'application_no' => $application_no,
            'student_name' => $data['student_name'] . ' ' . $data['student_surname'],
            'class' => $data['class_to_join'],
            'note' => 'Signature saved in database (no file upload required)'
        ]);
    } else {
        sendResponse(false, 'Failed to save application. Please try again.');
    }
    
} catch (Exception $e) {
    sendResponse(false, 'An error occurred while processing your application. Please try again.');
} catch (Error $e) {
    sendResponse(false, 'A system error occurred. Please try again.');
}
?>