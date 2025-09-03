<?php
// Setup contact_info table
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Setting up Contact Info Table</h2>";

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

// Create contact_info table
echo "<h3>Creating contact_info table...</h3>";
$contact_table_sql = "CREATE TABLE IF NOT EXISTS contact_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    value TEXT NOT NULL,
    label VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($contact_table_sql) === TRUE) {
    echo "✅ contact_info table created successfully<br>";
} else {
    echo "❌ Error creating contact_info table: " . $conn->error . "<br>";
}

// Check if contact_info table is empty and add sample data
$check_contact = $conn->query("SELECT COUNT(*) as count FROM contact_info");
$contact_count = $check_contact->fetch_assoc()['count'];

if ($contact_count == 0) {
    echo "<br><h3>Adding default contact information...</h3>";
    
    $default_contacts = [
        ['address', 'MJIMPYA RELINI, P. O. BOX 12986, DAR ES SALAAM, TANZANIA', 'School Address'],
        ['phone', '0784168758', 'Primary Phone'],
        ['phone', '0674120346', 'Secondary Phone'],
        ['email', 'motherofmercyprimaryschool@gmail.com', 'Primary Email'],
        ['email', 'marrymotherofmercy@gmail.com', 'Secondary Email'],
        ['hours', 'Monday - Friday: 7:00 AM - 4:00 PM', 'School Hours'],
        ['hours', 'Saturday: 8:00 AM - 12:00 PM', 'Saturday Hours']
    ];
    
    $stmt = $conn->prepare("INSERT INTO contact_info (type, value, label) VALUES (?, ?, ?)");
    
    foreach ($default_contacts as $contact) {
        $stmt->bind_param("sss", $contact[0], $contact[1], $contact[2]);
        if ($stmt->execute()) {
            echo "✅ Added: " . $contact[2] . " - " . $contact[1] . "<br>";
        } else {
            echo "❌ Failed to add: " . $contact[2] . "<br>";
        }
    }
} else {
    echo "<br>ℹ️ Contact info already has $contact_count entries<br>";
}

// Verify table structure
echo "<br><h3>Verifying table structure...</h3>";
$result = $conn->query("DESCRIBE contact_info");
if ($result) {
    echo "✅ Table structure is valid<br>";
    echo "<h4>Table Columns:</h4>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "❌ Error checking table structure: " . $conn->error . "<br>";
}

echo "<br><h2>🎉 Contact Info Setup Complete!</h2>";
echo "<p>The contact_info table has been created and populated with default school contact information.</p>";

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
h2, h3, h4 {
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
</style>

<div style="margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 10px;">
    <h3>🚀 Next Steps:</h3>
    <a href="contact_info.php" class="btn btn-success">Go to Contact Info Management</a>
    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
</div>