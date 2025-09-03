<?php
require_once 'includes/config.php';
requireAdminLogin();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_image'])) {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $sort_order = (int)$_POST['sort_order'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                // Image uploaded successfully
                $image_path = $upload_result;
                
                $stmt = $conn->prepare("INSERT INTO gallery_images (title, image_path, description, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $title, $image_path, $description, $sort_order);
                
                if ($stmt->execute()) {
                    $success = "Gallery image added successfully!";
                } else {
                    $error = "Error adding image to database.";
                }
            } else {
                $error = $upload_result;
            }
        } else {
            $error = "Please select an image file.";
        }
    }
    
    if (isset($_POST['update_image'])) {
        $image_id = (int)$_POST['image_id'];
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $sort_order = (int)$_POST['sort_order'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if new image is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                // Delete old image
                $old_image_stmt = $conn->prepare("SELECT image_path FROM gallery_images WHERE id = ?");
                $old_image_stmt->bind_param("i", $image_id);
                $old_image_stmt->execute();
                $old_image = $old_image_stmt->get_result()->fetch_assoc();
                
                if ($old_image && file_exists('../images/' . $old_image['image_path'])) {
                    unlink('../images/' . $old_image['image_path']);
                }
                
                // Update with new image
                $stmt = $conn->prepare("UPDATE gallery_images SET title = ?, image_path = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $title, $upload_result, $description, $sort_order, $is_active, $image_id);
            } else {
                $error = $upload_result;
            }
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE gallery_images SET title = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $title, $description, $sort_order, $is_active, $image_id);
        }
        
        if (isset($stmt) && $stmt->execute()) {
            $success = "Gallery image updated successfully!";
        } elseif (!isset($error)) {
            $error = "Error updating gallery image.";
        }
    }
    
    if (isset($_POST['delete_image'])) {
        $image_id = (int)$_POST['image_id'];
        
        // Get image path for deletion
        $image_stmt = $conn->prepare("SELECT image_path FROM gallery_images WHERE id = ?");
        $image_stmt->bind_param("i", $image_id);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result()->fetch_assoc();
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
        $delete_stmt->bind_param("i", $image_id);
        
        if ($delete_stmt->execute()) {
            // Delete image file
            if ($image_result && file_exists('../images/' . $image_result['image_path'])) {
                unlink('../images/' . $image_result['image_path']);
            }
            $success = "Gallery image deleted successfully!";
        } else {
            $error = "Error deleting gallery image.";
        }
    }
    
    if (isset($_POST['bulk_action'])) {
        $action = sanitizeInput($_POST['action']);
        $selected_images = $_POST['selected_images'] ?? [];
        
        if (!empty($selected_images)) {
            $placeholders = str_repeat('?,', count($selected_images) - 1) . '?';
            
            if ($action === 'activate') {
                $stmt = $conn->prepare("UPDATE gallery_images SET is_active = 1 WHERE id IN ($placeholders)");
                $stmt->bind_param(str_repeat('i', count($selected_images)), ...$selected_images);
                if ($stmt->execute()) {
                    $success = count($selected_images) . " images activated successfully!";
                }
            } elseif ($action === 'deactivate') {
                $stmt = $conn->prepare("UPDATE gallery_images SET is_active = 0 WHERE id IN ($placeholders)");
                $stmt->bind_param(str_repeat('i', count($selected_images)), ...$selected_images);
                if ($stmt->execute()) {
                    $success = count($selected_images) . " images deactivated successfully!";
                }
            } elseif ($action === 'delete') {
                // Get image paths for deletion
                $stmt = $conn->prepare("SELECT image_path FROM gallery_images WHERE id IN ($placeholders)");
                $stmt->bind_param(str_repeat('i', count($selected_images)), ...$selected_images);
                $stmt->execute();
                $images_to_delete = $stmt->get_result();
                
                // Delete from database
                $delete_stmt = $conn->prepare("DELETE FROM gallery_images WHERE id IN ($placeholders)");
                $delete_stmt->bind_param(str_repeat('i', count($selected_images)), ...$selected_images);
                
                if ($delete_stmt->execute()) {
                    // Delete image files
                    while ($img = $images_to_delete->fetch_assoc()) {
                        if (file_exists('../images/' . $img['image_path'])) {
                            unlink('../images/' . $img['image_path']);
                        }
                    }
                    $success = count($selected_images) . " images deleted successfully!";
                }
            }
        } else {
            $error = "Please select at least one image.";
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];
$types = "";

if ($status_filter !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = (int)$status_filter;
    $types .= "i";
}

if ($search) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get gallery images
$query = "SELECT * FROM gallery_images $where_clause ORDER BY sort_order ASC, created_at DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$gallery_images = $stmt->get_result();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
    FROM gallery_images $where_clause";

$stats_stmt = $conn->prepare($stats_query);
if (!empty($params)) {
    $stats_stmt->bind_param($types, ...$params);
}
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .gallery-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .gallery-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .gallery-card:hover .gallery-thumbnail {
            transform: scale(1.05);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .select-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
        }
        .image-preview {
            max-width: 300px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        .bulk-actions {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 15px;
            transform: translateY(100%);
            transition: all 0.3s ease;
        }
        .gallery-card:hover .gallery-overlay {
            transform: translateY(0);
        }
        .masonry-grid {
            column-count: 3;
            column-gap: 1rem;
        }
        .masonry-item {
            break-inside: avoid;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .masonry-grid {
                column-count: 2;
            }
        }
        @media (max-width: 576px) {
            .masonry-grid {
                column-count: 1;
            }
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
                    <h1 class="h2"><i class="fas fa-photo-video"></i> Gallery Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleView()">
                                <i class="fas fa-th" id="viewIcon"></i> <span id="viewText">Grid View</span>
                            </button>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
                            <i class="fas fa-plus"></i> Add New Image
                        </button>
                    </div>
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

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Images
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['total']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-images fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-left-success">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Active Images
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['active']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-eye fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-left-warning">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Hidden Images
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['inactive']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-eye-slash fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Bulk Actions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Filter by Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Hidden</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search by title or description..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Bulk Actions -->
                        <div class="bulk-actions" id="bulkActions" style="display: none;">
                            <form method="POST" id="bulkForm">
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <label for="bulkAction" class="form-label">Bulk Action</label>
                                        <select class="form-select" id="bulkAction" name="action" required>
                                            <option value="">Select Action</option>
                                            <option value="activate">Activate Selected</option>
                                            <option value="deactivate">Hide Selected</option>
                                            <option value="delete">Delete Selected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <span id="selectedCount">0 images selected</span>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="bulk_action" class="btn btn-warning">
                                            <i class="fas fa-bolt"></i> Apply Action
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                                            <i class="fas fa-times"></i> Clear
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Gallery Images -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-images"></i> Gallery Images
                        </h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                Select All
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($gallery_images && $gallery_images->num_rows > 0): ?>
                            <div class="row" id="galleryGrid">
                                <?php while ($image = $gallery_images->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4 mb-4 gallery-item">
                                        <div class="card gallery-card h-100">
                                            <div class="position-relative">
                                                <img src="../images/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                     class="gallery-thumbnail" 
                                                     alt="<?php echo htmlspecialchars($image['title']); ?>"
                                                     onclick="viewImage('<?php echo htmlspecialchars($image['image_path']); ?>', '<?php echo htmlspecialchars($image['title']); ?>')">
                                                
                                                <div class="select-checkbox">
                                                    <input type="checkbox" class="form-check-input image-select" 
                                                           value="<?php echo $image['id']; ?>" onchange="updateSelection()">
                                                </div>
                                                
                                                <span class="badge status-badge bg-<?php echo $image['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $image['is_active'] ? 'Active' : 'Hidden'; ?>
                                                </span>
                                                
                                                <div class="gallery-overlay">
                                                    <h6 class="text-white mb-1"><?php echo htmlspecialchars($image['title']); ?></h6>
                                                    <?php if ($image['description']): ?>
                                                        <p class="text-white-50 small mb-2">
                                                            <?php echo htmlspecialchars(substr($image['description'], 0, 100)); ?>
                                                            <?php echo strlen($image['description']) > 100 ? '...' : ''; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-light" 
                                                                onclick="editImage(<?php echo htmlspecialchars(json_encode($image)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteImage(<?php echo $image['id']; ?>, '<?php echo htmlspecialchars($image['title']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h6>
                                                <?php if ($image['description']): ?>
                                                    <p class="card-text text-muted small">
                                                        <?php echo htmlspecialchars(substr($image['description'], 0, 80)); ?>
                                                        <?php echo strlen($image['description']) > 80 ? '...' : ''; ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">Order: <?php echo $image['sort_order']; ?></small>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($image['created_at'])); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No gallery images found</h5>
                                <p class="text-muted">Add your first gallery image to get started.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
                                    <i class="fas fa-plus"></i> Add First Image
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-lightbulb"></i> Gallery Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Image Guidelines:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> High-quality images (at least 800px wide)</li>
                                    <li><i class="fas fa-check text-success"></i> Various aspect ratios work well</li>
                                    <li><i class="fas fa-check text-success"></i> Format: JPG, PNG, or GIF</li>
                                    <li><i class="fas fa-check text-success"></i> Maximum file size: 5MB</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Best Practices:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Use descriptive titles and descriptions</li>
                                    <li><i class="fas fa-check text-success"></i> Organize with sort order numbers</li>
                                    <li><i class="fas fa-check text-success"></i> Show school activities and events</li>
                                    <li><i class="fas fa-check text-success"></i> Keep gallery updated regularly</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Image Modal -->
    <div class="modal fade" id="addImageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Add New Gallery Image
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" 
                                              placeholder="Optional description of the image..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                           value="0" min="0">
                                    <div class="form-text">Lower numbers appear first</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/*" required onchange="previewImage(this)">
                                    <div class="form-text">Max size: 5MB. Recommended: High quality images</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Preview</label>
                                    <div id="imagePreview" class="border rounded p-3 text-center" style="min-height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                        <p class="text-muted mt-2">Image preview will appear here</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_image" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Image Modal -->
    <div class="modal fade" id="editImageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Gallery Image
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editImageForm">
                    <input type="hidden" name="image_id" id="edit_image_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                        <label class="form-check-label" for="edit_is_active">
                                            Active (visible in gallery)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_image" class="form-label">Change Image (Optional)</label>
                                    <input type="file" class="form-control" id="edit_image" name="image" 
                                           accept="image/*" onchange="previewEditImage(this)">
                                    <div class="form-text">Leave empty to keep current image</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Current/Preview Image</label>
                                    <div id="editImagePreview" class="border rounded p-3 text-center" style="min-height: 200px;">
                                        <img id="currentImage" src="" alt="Current image" class="img-fluid image-preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_image" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the image "<span id="deleteImageTitle"></span>"?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="image_id" id="delete_image_id">
                        <button type="submit" name="delete_image" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Image
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="viewImageModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewImageTitle">Image Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="viewImageSrc" src="" alt="Image preview" class="img-fluid" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedImages = [];

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid image-preview">`;
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewEditImage(input) {
            const preview = document.getElementById('editImagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid image-preview">`;
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        function editImage(image) {
            document.getElementById('edit_image_id').value = image.id;
            document.getElementById('edit_title').value = image.title;
            document.getElementById('edit_description').value = image.description || '';
            document.getElementById('edit_sort_order').value = image.sort_order;
            document.getElementById('edit_is_active').checked = image.is_active == 1;
            document.getElementById('currentImage').src = '../images/' + image.image_path;
            
            new bootstrap.Modal(document.getElementById('editImageModal')).show();
        }

        function deleteImage(id, title) {
            document.getElementById('delete_image_id').value = id;
            document.getElementById('deleteImageTitle').textContent = title;
            
            new bootstrap.Modal(document.getElementById('deleteImageModal')).show();
        }

        function viewImage(imagePath, title) {
            document.getElementById('viewImageSrc').src = '../images/' + imagePath;
            document.getElementById('viewImageTitle').textContent = title;
            
            new bootstrap.Modal(document.getElementById('viewImageModal')).show();
        }

        function updateSelection() {
            const checkboxes = document.querySelectorAll('.image-select:checked');
            selectedImages = Array.from(checkboxes).map(cb => cb.value);
            
            document.getElementById('selectedCount').textContent = selectedImages.length + ' images selected';
            document.getElementById('bulkActions').style.display = selectedImages.length > 0 ? 'block' : 'none';
            
            // Update hidden inputs for bulk form
            const bulkForm = document.getElementById('bulkForm');
            bulkForm.querySelectorAll('input[name="selected_images[]"]').forEach(input => input.remove());
            
            selectedImages.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_images[]';
                input.value = id;
                bulkForm.appendChild(input);
            });
        }

        function clearSelection() {
            document.querySelectorAll('.image-select').forEach(cb => cb.checked = false);
            document.getElementById('selectAll').checked = false;
            updateSelection();
        }

        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.image-select');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelection();
        });

        function toggleView() {
            const grid = document.getElementById('galleryGrid');
            const icon = document.getElementById('viewIcon');
            const text = document.getElementById('viewText');
            
            if (grid.classList.contains('masonry-grid')) {
                grid.classList.remove('masonry-grid');
                grid.classList.add('row');
                icon.className = 'fas fa-th-large';
                text.textContent = 'Masonry View';
            } else {
                grid.classList.remove('row');
                grid.classList.add('masonry-grid');
                icon.className = 'fas fa-th';
                text.textContent = 'Grid View';
            }
        }

        // Reset forms when modals are hidden
        document.getElementById('addImageModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('#addImageModal form').reset();
            document.getElementById('imagePreview').innerHTML = '<i class="fas fa-image fa-3x text-muted"></i><p class="text-muted mt-2">Image preview will appear here</p>';
        });

        document.getElementById('editImageModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('editImageForm').reset();
        });

        // Confirm bulk delete
        document.getElementById('bulkForm').addEventListener('submit', function(e) {
            const action = document.getElementById('bulkAction').value;
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete the selected images? This action cannot be undone.')) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>