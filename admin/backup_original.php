<?php
require_once 'includes/config.php';
requireAdminLogin();

// Create backups directory if it doesn't exist
$backup_dir = '../backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_backup'])) {
        $backup_type = sanitizeInput($_POST['backup_type']);
        $include_images = isset($_POST['include_images']);
        
        $timestamp = date('Y-m-d_H-i-s');
        $backup_filename = "backup_{$backup_type}_{$timestamp}";
        
        try {
            if ($backup_type === 'database' || $backup_type === 'full') {
                // Create database backup
                $db_backup_file = $backup_dir . $backup_filename . '_database.sql';
                $result = createDatabaseBackup($db_backup_file);
                
                if ($result !== true) {
                    throw new Exception($result);
                }
            }
            
            if ($backup_type === 'files' || $backup_type === 'full') {
                // Create files backup
                $files_backup_file = $backup_dir . $backup_filename . '_files.zip';
                $result = createFilesBackup($files_backup_file, $include_images);
                
                if ($result !== true) {
                    throw new Exception($result);
                }
            }
            
            if ($backup_type === 'full') {
                // Create combined backup
                $full_backup_file = $backup_dir . $backup_filename . '_full.zip';
                $result = createFullBackup($full_backup_file, $backup_filename, $include_images);
                
                if ($result !== true) {
                    throw new Exception($result);
                }
            }
            
            $success = "Backup created successfully!";
            
        } catch (Exception $e) {
            $error = "Backup failed: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_backup'])) {
        $backup_file = sanitizeInput($_POST['backup_file']);
        $full_path = $backup_dir . $backup_file;
        
        if (file_exists($full_path) && strpos(realpath($full_path), realpath($backup_dir)) === 0) {
            if (unlink($full_path)) {
                $success = "Backup deleted successfully!";
            } else {
                $error = "Failed to delete backup file.";
            }
        } else {
            $error = "Backup file not found or invalid.";
        }
    }
    
    if (isset($_POST['restore_database'])) {
        $backup_file = sanitizeInput($_POST['backup_file']);
        $full_path = $backup_dir . $backup_file;
        
        if (file_exists($full_path) && strpos(realpath($full_path), realpath($backup_dir)) === 0) {
            try {
                $result = restoreDatabaseBackup($full_path);
                if ($result === true) {
                    $success = "Database restored successfully!";
                } else {
                    $error = "Database restore failed: " . $result;
                }
            } catch (Exception $e) {
                $error = "Database restore failed: " . $e->getMessage();
            }
        } else {
            $error = "Backup file not found or invalid.";
        }
    }
}

// Get list of backup files
$backup_files = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && !is_dir($backup_dir . $file)) {
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => filemtime($backup_dir . $file),
                'type' => getBackupType($file)
            ];
        }
    }
    
    // Sort by date (newest first)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Helper functions
function createDatabaseBackup($filename) {
    global $conn;
    
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    $sql_dump = "-- Database Backup\n";
    $sql_dump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $sql_dump .= "-- Database: " . DB_NAME . "\n\n";
    
    $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        // Get table structure
        $result = $conn->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_array();
        
        $sql_dump .= "-- Table structure for table `$table`\n";
        $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql_dump .= $row[1] . ";\n\n";
        
        // Get table data
        $result = $conn->query("SELECT * FROM `$table`");
        if ($result->num_rows > 0) {
            $sql_dump .= "-- Dumping data for table `$table`\n";
            
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $sql_dump .= "INSERT INTO `$table` VALUES (";
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $conn->real_escape_string($value) . "'";
                    }
                }
                $sql_dump .= implode(', ', $values) . ");\n";
            }
            $sql_dump .= "\n";
        }
    }
    
    $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    if (file_put_contents($filename, $sql_dump) === false) {
        return "Failed to write database backup file.";
    }
    
    return true;
}

function createFilesBackup($filename, $include_images = true) {
    $zip = new ZipArchive();
    
    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
        return "Cannot create zip file.";
    }
    
    // Add admin files
    addDirectoryToZip($zip, '../admin/', 'admin/', ['backups']);
    
    // Add main website files
    $main_files = ['index.html', 'about.html', 'contact.html', 'courses.html', 'teacher.html', 'process_application.php'];
    foreach ($main_files as $file) {
        if (file_exists('../' . $file)) {
            $zip->addFile('../' . $file, $file);
        }
    }
    
    // Add CSS, JS, and other assets
    if (is_dir('../css/')) {
        addDirectoryToZip($zip, '../css/', 'css/');
    }
    if (is_dir('../js/')) {
        addDirectoryToZip($zip, '../js/', 'js/');
    }
    if (is_dir('../fonts/')) {
        addDirectoryToZip($zip, '../fonts/', 'fonts/');
    }
    
    // Add images if requested
    if ($include_images && is_dir('../images/')) {
        addDirectoryToZip($zip, '../images/', 'images/');
    }
    
    $zip->close();
    return true;
}

function createFullBackup($filename, $backup_name, $include_images = true) {
    $zip = new ZipArchive();
    
    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
        return "Cannot create full backup zip file.";
    }
    
    // Add database backup
    $db_file = '../backups/' . $backup_name . '_database.sql';
    if (file_exists($db_file)) {
        $zip->addFile($db_file, 'database.sql');
    }
    
    // Add files backup
    $files_file = '../backups/' . $backup_name . '_files.zip';
    if (file_exists($files_file)) {
        $zip->addFile($files_file, 'files.zip');
    }
    
    // Add readme
    $readme = "Mary Mother of Mercy School - Full Backup\n";
    $readme .= "Created: " . date('Y-m-d H:i:s') . "\n\n";
    $readme .= "Contents:\n";
    $readme .= "- database.sql: Complete database backup\n";
    $readme .= "- files.zip: Website files backup\n\n";
    $readme .= "To restore:\n";
    $readme .= "1. Extract files.zip to your web directory\n";
    $readme .= "2. Import database.sql to your MySQL database\n";
    $readme .= "3. Update database connection settings if needed\n";
    
    $zip->addFromString('README.txt', $readme);
    
    $zip->close();
    return true;
}

function addDirectoryToZip($zip, $source_dir, $zip_dir, $exclude = []) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        $file_path = $file->getRealPath();
        $relative_path = $zip_dir . substr($file_path, strlen(realpath($source_dir)) + 1);
        
        // Check if this path should be excluded
        $should_exclude = false;
        foreach ($exclude as $exclude_dir) {
            if (strpos($relative_path, $exclude_dir) === 0) {
                $should_exclude = true;
                break;
            }
        }
        
        if ($should_exclude) {
            continue;
        }
        
        if ($file->isDir()) {
            $zip->addEmptyDir($relative_path);
        } else {
            $zip->addFile($file_path, $relative_path);
        }
    }
}

function restoreDatabaseBackup($filename) {
    global $conn;
    
    $sql = file_get_contents($filename);
    if ($sql === false) {
        return "Cannot read backup file.";
    }
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            if (!$conn->query($statement)) {
                return "SQL Error: " . $conn->error;
            }
        }
    }
    
    return true;
}

function getBackupType($filename) {
    if (strpos($filename, '_database.sql') !== false) {
        return 'database';
    } elseif (strpos($filename, '_files.zip') !== false) {
        return 'files';
    } elseif (strpos($filename, '_full.zip') !== false) {
        return 'full';
    } else {
        return 'unknown';
    }
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .backup-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .backup-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .backup-type-badge {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        .backup-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 15px;
        }
        .backup-icon.database { background: linear-gradient(135deg, #007bff, #0056b3); }
        .backup-icon.files { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .backup-icon.full { background: linear-gradient(135deg, #6f42c1, #5a32a3); }
        .backup-icon.unknown { background: linear-gradient(135deg, #6c757d, #545b62); }
        
        .create-backup-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }
        
        .backup-stats {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .progress-custom {
            height: 25px;
            border-radius: 15px;
            background: #e9ecef;
        }
        
        .progress-bar-custom {
            border-radius: 15px;
            background: linear-gradient(90deg, #007bff, #0056b3);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-download"></i> Backup & Restore</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                            <i class="fas fa-plus"></i> Create New Backup
                        </button>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Backup Statistics -->
                <div class="backup-stats">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo count($backup_files); ?></h4>
                                <small class="text-muted">Total Backups</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">
                                    <?php 
                                    $total_size = 0;
                                    foreach ($backup_files as $file) {
                                        $total_size += $file['size'];
                                    }
                                    echo formatFileSize($total_size);
                                    ?>
                                </h4>
                                <small class="text-muted">Total Size</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">
                                    <?php echo !empty($backup_files) ? date('M d, Y', $backup_files[0]['date']) : 'Never'; ?>
                                </h4>
                                <small class="text-muted">Last Backup</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">
                                    <?php 
                                    $disk_free = disk_free_space($backup_dir);
                                    echo formatFileSize($disk_free);
                                    ?>
                                </h4>
                                <small class="text-muted">Free Space</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create Backup Section -->
                <div class="create-backup-section">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-shield-alt"></i> Backup Your Data
                            </h4>
                            <p class="mb-3">Regular backups protect your school's important data. Choose the type of backup you need:</p>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-database text-primary"></i> <strong>Database Only:</strong> Student applications, teachers, content</li>
                                <li class="mb-2"><i class="fas fa-folder text-success"></i> <strong>Files Only:</strong> Website files, images, documents</li>
                                <li class="mb-2"><i class="fas fa-archive text-purple"></i> <strong>Full Backup:</strong> Complete website and database</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                Create Backup
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Existing Backups -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history"></i> Existing Backups
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($backup_files)): ?>
                            <div class="row">
                                <?php foreach ($backup_files as $backup): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card backup-card h-100">
                                            <div class="position-relative">
                                                <span class="badge backup-type-badge bg-<?php 
                                                    echo $backup['type'] === 'database' ? 'primary' : 
                                                        ($backup['type'] === 'files' ? 'success' : 
                                                        ($backup['type'] === 'full' ? 'purple' : 'secondary')); 
                                                ?>">
                                                    <?php echo ucfirst($backup['type']); ?>
                                                </span>
                                            </div>
                                            <div class="card-body text-center">
                                                <div class="backup-icon <?php echo $backup['type']; ?> mx-auto">
                                                    <i class="fas fa-<?php 
                                                        echo $backup['type'] === 'database' ? 'database' : 
                                                            ($backup['type'] === 'files' ? 'folder' : 
                                                            ($backup['type'] === 'full' ? 'archive' : 'file')); 
                                                    ?>"></i>
                                                </div>
                                                
                                                <h6 class="card-title"><?php echo htmlspecialchars($backup['name']); ?></h6>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y g:i A', $backup['date']); ?>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-hdd"></i> <?php echo formatFileSize($backup['size']); ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="btn-group w-100" role="group">
                                                    <a href="../backups/<?php echo urlencode($backup['name']); ?>" 
                                                       class="btn btn-sm btn-outline-primary" download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    
                                                    <?php if ($backup['type'] === 'database' || strpos($backup['name'], '_database.sql') !== false): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="restoreBackup('<?php echo htmlspecialchars($backup['name']); ?>')">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No backups found</h5>
                                <p class="text-muted">Create your first backup to protect your data.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                                    <i class="fas fa-plus"></i> Create First Backup
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Backup Tips -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-lightbulb"></i> Backup Best Practices
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Backup Schedule:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Create backups before major updates</li>
                                    <li><i class="fas fa-check text-success"></i> Weekly database backups recommended</li>
                                    <li><i class="fas fa-check text-success"></i> Monthly full backups for safety</li>
                                    <li><i class="fas fa-check text-success"></i> Store backups in multiple locations</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Security Tips:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Download important backups locally</li>
                                    <li><i class="fas fa-check text-success"></i> Test restore process regularly</li>
                                    <li><i class="fas fa-check text-success"></i> Keep backup files secure</li>
                                    <li><i class="fas fa-check text-success"></i> Delete old backups to save space</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Backup Modal -->
    <div class="modal fade" id="createBackupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Create New Backup
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="backupForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="backup_type" class="form-label">Backup Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="backup_type" name="backup_type" required>
                                <option value="">Select backup type</option>
                                <option value="database">Database Only</option>
                                <option value="files">Files Only</option>
                                <option value="full">Full Backup (Database + Files)</option>
                            </select>
                            <div class="form-text">
                                <small><strong>Database:</strong> Applications, teachers, content data</small><br>
                                <small><strong>Files:</strong> Website files, admin panel, assets</small><br>
                                <small><strong>Full:</strong> Complete backup of everything</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_images" name="include_images" checked>
                                <label class="form-check-label" for="include_images">
                                    Include images folder
                                </label>
                                <div class="form-text">Uncheck to exclude images and reduce backup size</div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Backup creation may take a few minutes depending on the size of your data.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_backup" class="btn btn-primary" id="createBackupBtn">
                            <i class="fas fa-save"></i> Create Backup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Database Restore
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This will replace all current database data with the backup data.
                    </div>
                    <p>Are you sure you want to restore the database from this backup?</p>
                    <p class="text-muted"><strong>Backup:</strong> <span id="restoreBackupName"></span></p>
                    <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="backup_file" id="restore_backup_file">
                        <button type="submit" name="restore_database" class="btn btn-warning">
                            <i class="fas fa-undo"></i> Restore Database
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this backup?</p>
                    <p class="text-muted"><strong>Backup:</strong> <span id="deleteBackupName"></span></p>
                    <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="backup_file" id="delete_backup_file">
                        <button type="submit" name="delete_backup" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function restoreBackup(filename) {
            document.getElementById('restore_backup_file').value = filename;
            document.getElementById('restoreBackupName').textContent = filename;
            new bootstrap.Modal(document.getElementById('restoreModal')).show();
        }

        function deleteBackup(filename) {
            document.getElementById('delete_backup_file').value = filename;
            document.getElementById('deleteBackupName').textContent = filename;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Show loading state during backup creation
        document.getElementById('backupForm').addEventListener('submit', function() {
            const btn = document.getElementById('createBackupBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
            btn.disabled = true;
        });

        // Reset form when modal is hidden
        document.getElementById('createBackupModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('backupForm').reset();
            const btn = document.getElementById('createBackupBtn');
            btn.innerHTML = '<i class="fas fa-save"></i> Create Backup';
            btn.disabled = false;
        });
    </script>
</body>
</html>