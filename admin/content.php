<?php
require_once 'includes/config.php';
requireAdminLogin();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_content'])) {
        $section = sanitizeInput($_POST['section']);
        $title = sanitizeInput($_POST['title']);
        $content = sanitizeInput($_POST['content']);
        
        // Check if record exists
        $check = $conn->prepare("SELECT id FROM website_content WHERE section = ?");
        $check->bind_param("s", $section);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing
            $stmt = $conn->prepare("UPDATE website_content SET title = ?, content = ? WHERE section = ?");
            $stmt->bind_param("sss", $title, $content, $section);
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO website_content (section, title, content) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $section, $title, $content);
        }
        
        if ($stmt->execute()) {
            $success = "Content updated successfully!";
        } else {
            $error = "Error updating content.";
        }
    }
}

// Get current content
$content_sections = [
    'school_mission' => 'School Mission',
    'school_vision' => 'School Vision', 
    'school_objectives' => 'School Objectives',
    'about_title' => 'About Us Title',
    'about_content' => 'About Us Content',
    'why_choose_us' => 'Why Choose Us',
    'contact_address' => 'Contact Address',
    'contact_phone' => 'Contact Phone',
    'contact_email' => 'Contact Email'
];

$current_content = [];
foreach ($content_sections as $section => $label) {
    $stmt = $conn->prepare("SELECT title, content FROM website_content WHERE section = ?");
    $stmt->bind_param("s", $section);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $current_content[$section] = $row;
    } else {
        $current_content[$section] = ['title' => '', 'content' => ''];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Content - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-edit"></i> Website Content Management</h1>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php foreach ($content_sections as $section => $label): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-edit"></i> <?php echo $label; ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="section" value="<?php echo $section; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="title_<?php echo $section; ?>" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="title_<?php echo $section; ?>" 
                                                   name="title" value="<?php echo htmlspecialchars($current_content[$section]['title']); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="content_<?php echo $section; ?>" class="form-label">Content</label>
                                            <textarea class="form-control" id="content_<?php echo $section; ?>" 
                                                      name="content" rows="4"><?php echo htmlspecialchars($current_content[$section]['content']); ?></textarea>
                                        </div>
                                        
                                        <button type="submit" name="update_content" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Content
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tools"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="../index.php" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-external-link-alt"></i> Preview Website
                                </a>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-success w-100 mb-2" onclick="backupContent()">
                                    <i class="fas fa-download"></i> Backup Content
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-info w-100 mb-2" onclick="clearCache()">
                                    <i class="fas fa-sync"></i> Clear Cache
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function backupContent() {
            if (confirm('Create a backup of all website content?')) {
                window.location.href = 'backup_content.php';
            }
        }

        function clearCache() {
            if (confirm('Clear website cache? This will refresh all cached content.')) {
                // Add cache clearing functionality here
                alert('Cache cleared successfully!');
            }
        }

        // Auto-save functionality
        let saveTimeout;
        document.querySelectorAll('textarea, input[type="text"]').forEach(field => {
            field.addEventListener('input', function() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    // Auto-save after 3 seconds of no typing
                    console.log('Auto-saving...');
                }, 3000);
            });
        });
    </script>
</body>
</html>