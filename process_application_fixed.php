<?php
// Fixed Application Form Processing Script
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output, log them instead

// Set content type for JSON response
header('Content-Type: application/json');

// Function to send JSON response and exit
function sendResponse($success, $message, $data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data);
    
    echo json_encode($response);
    exit;
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to generate application number
function generateApplicationNumber() {
    return 'MMM' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method. Only POST requests are allowed.');
    }
    
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "marry_mother_mercy_db";
    
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        sendResponse(false, 'Database connection failed. Please try again later.');
    }
    
    // Set charset
    $conn->set_charset("utf8");
    
    // Validate required fields
    $required_fields = ['class_to_join', 'student_name', 'student_surname'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        sendResponse(false, 'Missing required fields: ' . implode(', ', $missing_fields));
    }
    
    // Generate unique application number
    $application_no = generateApplicationNumber();
    
    // Ensure application number is unique
    $check_stmt = $conn->prepare("SELECT id FROM applications WHERE application_no = ?");
    if (!$check_stmt) {
        error_log("Prepare failed: " . $conn->error);
        sendResponse(false, 'Database error occurred. Please try again.');
    }
    
    $check_stmt->bind_param("s", $application_no);
    $check_stmt->execute();
    
    while ($check_stmt->get_result()->num_rows > 0) {
        $application_no = generateApplicationNumber();
        $check_stmt->bind_param("s", $application_no);
        $check_stmt->execute();
    }
    
    // Handle signature data
    $signature_data = null;
    if (isset($_POST['signature_data']) && !empty($_POST['signature_data'])) {
        $signature_data = $_POST['signature_data'];
        
        // If it's a base64 image, save it to a file
        if (strpos($signature_data, 'data:image') === 0) {
            $signature_dir = 'uploads/signatures/';
            if (!file_exists($signature_dir)) {
                if (!mkdir($signature_dir, 0777, true)) {
                    error_log("Failed to create signature directory");
                    // Continue without saving signature file
                }
            }
            
            if (file_exists($signature_dir) && is_writable($signature_dir)) {
                $signature_filename = $application_no . '_signature_' . time() . '.png';
                $signature_path = $signature_dir . $signature_filename;
                
                // Extract base64 data and save
                $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature_data));
                if ($image_data && file_put_contents($signature_path, $image_data)) {
                    $signature_data = $signature_filename;
                } else {
                    error_log("Failed to save signature file");
                    // Keep original signature data
                }
            }
        }
    }
    
    // Handle uploaded signature file
    if (isset($_FILES['signature_file']) && $_FILES['signature_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/signatures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['signature_file']['name'], PATHINFO_EXTENSION);
        $signature_filename = $application_no . '_signature_' . time() . '.' . $file_extension;
        $signature_path = $upload_dir . $signature_filename;
        
        if (move_uploaded_file($_FILES['signature_file']['tmp_name'], $signature_path)) {
            $signature_data = $signature_filename;
        } else {
            error_log("Failed to move uploaded signature file");
        }
    }
    
    // Sanitize all input data
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
    
    // Check if signature columns exist, if not create them
    $columns_check = $conn->query("SHOW COLUMNS FROM applications LIKE 'signature_data'");
    if ($columns_check->num_rows == 0) {
        $conn->query("ALTER TABLE applications ADD COLUMN signature_data VARCHAR(255) NULL");
        $conn->query("ALTER TABLE applications ADD COLUMN typed_signature VARCHAR(255) NULL");
    }
    
    // Prepare SQL statement
    $sql = "INSERT INTO applications (
        application_no, class_to_join, student_name, student_middle_name, student_surname,
        sex, date_of_birth, place_of_birth, nationality, tribe, religion, denomination,
        previous_school, previous_class, father_name, father_occupation, father_phone,
        father_workplace, mother_name, mother_occupation, mother_phone, mother_workplace,
        guardian_name, guardian_occupation, guardian_phone, guardian_workplace,
        postal_box, postal_place, signature_data, typed_signature, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("SQL preparation failed: " . $conn->error);
        sendResponse(false, 'Database error occurred. Please try again.');
    }
    
    // Bind parameters
    $stmt->bind_param("ssssssssssssssssssssssssssssss", 
        $data['application_no'], $data['class_to_join'], $data['student_name'], 
        $data['student_middle_name'], $data['student_surname'], $data['sex'], 
        $data['date_of_birth'], $data['place_of_birth'], $data['nationality'], 
        $data['tribe'], $data['religion'], $data['denomination'], 
        $data['previous_school'], $data['previous_class'], $data['father_name'], 
        $data['father_occupation'], $data['father_phone'], $data['father_workplace'], 
        $data['mother_name'], $data['mother_occupation'], $data['mother_phone'], 
        $data['mother_workplace'], $data['guardian_name'], $data['guardian_occupation'], 
        $data['guardian_phone'], $data['guardian_workplace'], $data['postal_box'], 
        $data['postal_place'], $data['signature_data'], $data['typed_signature']
    );
    
    // Execute the statement
    if ($stmt->execute()) {
        // Success response
        sendResponse(true, 'Application submitted successfully!', [
            'application_no' => $application_no,
            'student_name' => $data['student_name'] . ' ' . $data['student_surname'],
            'class' => $data['class_to_join']
        ]);
        
    } else {
        error_log("Database insertion failed: " . $stmt->error);
        sendResponse(false, 'Failed to save application. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Application processing error: " . $e->getMessage());
    sendResponse(false, 'An unexpected error occurred. Please try again.');
} catch (Error $e) {
    error_log("Fatal error in application processing: " . $e->getMessage());
    sendResponse(false, 'A system error occurred. Please try again.');
}

// Close connections if they exist
if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();
?>