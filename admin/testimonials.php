<?php
require_once 'includes/config.php';
requireAdminLogin();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_testimonial'])) {
        $name = sanitizeInput($_POST['name']);
        $position = sanitizeInput($_POST['position']);
        $message = sanitizeInput($_POST['message']);
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                $image_path = $upload_result;
            } else {
                $error = $upload_result;
            }
        }
        
        if (!isset($error)) {
            $stmt = $conn->prepare("INSERT INTO testimonials (name, position, message, image_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $position, $message, $image_path);
            
            if ($stmt->execute()) {
                $success = "Testimonial added successfully!";
            } else {
                $error = "Error adding testimonial to database.";
            }
        }
    }
    
    if (isset($_POST['update_testimonial'])) {
        $testimonial_id = (int)$_POST['testimonial_id'];
        $name = sanitizeInput($_POST['name']);
        $position = sanitizeInput($_POST['position']);
        $message = sanitizeInput($_POST['message']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if new image is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                // Delete old image
                $old_image_stmt = $conn->prepare("SELECT image_path FROM testimonials WHERE id = ?");
                $old_image_stmt->bind_param("i", $testimonial_id);
                $old_image_stmt->execute();
                $old_image = $old_image_stmt->get_result()->fetch_assoc();
                
                if ($old_image && $old_image['image_path'] && file_exists('../images/' . $old_image['image_path'])) {
                    unlink('../images/' . $old_image['image_path']);
                }
                
                // Update with new image
                $stmt = $conn->prepare("UPDATE testimonials SET name = ?, position = ?, message = ?, image_path = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssssii", $name, $position, $message, $upload_result, $is_active, $testimonial_id);
            } else {
                $error = $upload_result;
            }
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE testimonials SET name = ?, position = ?, message = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("sssii", $name, $position, $message, $is_active, $testimonial_id);
        }
        
        if (isset($stmt) && $stmt->execute()) {
            $success = "Testimonial updated successfully!";
        } elseif (!isset($error)) {
            $error = "Error updating testimonial.";
        }
    }
    
    if (isset($_POST['delete_testimonial'])) {
        $testimonial_id = (int)$_POST['testimonial_id'];
        
        // Get image path for deletion
        $image_stmt = $conn->prepare("SELECT image_path FROM testimonials WHERE id = ?");
        $image_stmt->bind_param("i", $testimonial_id);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result()->fetch_assoc();
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $delete_stmt->bind_param("i", $testimonial_id);
        
        if ($delete_stmt->execute()) {
            // Delete image file
            if ($image_result && $image_result['image_path'] && file_exists('../images/' . $image_result['image_path'])) {
                unlink('../images/' . $image_result['image_path']);
            }
            $success = "Testimonial deleted successfully!";
        } else {
            $error = "Error deleting testimonial.";
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
    $where_conditions[] = "(name LIKE ? OR position LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get testimonials
$query = "SELECT * FROM testimonials $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$testimonials = $stmt->get_result();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
    FROM testimonials $where_clause";

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
    <title>Testimonials Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .testimonial-photo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e9ecef;
        }
        .testimonial-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .testimonial-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .message-preview {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .testimonial-quote {
            position: relative;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            margin: 15px 0;
        }
        .testimonial-quote::before {
            content: '"';
            font-size: 4rem;
            color: #007bff;
            position: absolute;
            top: -10px;
            left: 15px;
            font-family: serif;
        }
        .testimonial-quote::after {
            content: '"';
            font-size: 4rem;
            color: #007bff;
            position: absolute;
            bottom: -30px;
            right: 15px;
            font-family: serif;
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
                    <h1 class="h2"><i class="fas fa-comments"></i> Testimonials Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                            <i class="fas fa-plus"></i> Add New Testimonial
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
                                            Total Testimonials
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['total']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-comments fa-2x text-gray-300"></i>
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
                                            Active Testimonials
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
                                            Hidden Testimonials
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

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
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
                                       placeholder="Search by name, position, or message..." value="<?php echo htmlspecialchars($search); ?>">
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
                    </div>
                </div>

                <!-- Testimonials List -->
                <div class="row">
                    <?php if ($testimonials && $testimonials->num_rows > 0): ?>
                        <?php while ($testimonial = $testimonials->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card testimonial-card h-100">
                                    <div class="position-relative">
                                        <span class="badge status-badge bg-<?php echo $testimonial['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $testimonial['is_active'] ? 'Active' : 'Hidden'; ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <?php if ($testimonial['image_path']): ?>
                                                <img src="../images/<?php echo htmlspecialchars($testimonial['image_path']); ?>" 
                                                     class="testimonial-photo me-3" 
                                                     alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                                            <?php else: ?>
                                                <div class="testimonial-photo me-3 bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="card-title mb-1"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                                <small class="text-primary"><?php echo htmlspecialchars($testimonial['position']); ?></small>
                                            </div>
                                        </div>
                                        
                                        <div class="testimonial-quote">
                                            <p class="message-preview mb-0">
                                                <?php echo htmlspecialchars($testimonial['message']); ?>
                                            </p>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?>
                                            </small>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="editTestimonial(<?php echo htmlspecialchars(json_encode($testimonial)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteTestimonial(<?php echo $testimonial['id']; ?>, '<?php echo htmlspecialchars($testimonial['name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No testimonials found</h5>
                                <p class="text-muted">Add your first testimonial to get started.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                                    <i class="fas fa-plus"></i> Add First Testimonial
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tips Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-lightbulb"></i> Testimonial Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Photo Guidelines:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Professional or friendly photos work best</li>
                                    <li><i class="fas fa-check text-success"></i> Square format (1:1 ratio) preferred</li>
                                    <li><i class="fas fa-check text-success"></i> Clear, well-lit photos</li>
                                    <li><i class="fas fa-check text-success"></i> Minimum 200x200px resolution</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Content Best Practices:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Keep testimonials authentic and genuine</li>
                                    <li><i class="fas fa-check text-success"></i> Include specific details about experience</li>
                                    <li><i class="fas fa-check text-success"></i> Mention person's relationship to school</li>
                                    <li><i class="fas fa-check text-success"></i> Keep messages concise but meaningful</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Testimonial Modal -->
    <div class="modal fade" id="addTestimonialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Add New Testimonial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position/Relationship <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="position" name="position" 
                                           placeholder="e.g., Parent, Former Student, Teacher" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Photo (Optional)</label>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text">Recommended: Square format, at least 200x200px</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Photo Preview</label>
                                    <div id="imagePreview" class="border rounded p-3 text-center" style="min-height: 150px;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                        <p class="text-muted mt-2">Photo preview will appear here</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Testimonial Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" 
                                      placeholder="Write the testimonial message here..." required></textarea>
                            <div class="form-text">Share specific experiences and positive feedback about the school</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_testimonial" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Testimonial
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Testimonial Modal -->
    <div class="modal fade" id="editTestimonialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Testimonial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editTestimonialForm">
                    <input type="hidden" name="testimonial_id" id="edit_testimonial_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_position" class="form-label">Position/Relationship <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_position" name="position" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_image" class="form-label">Change Photo (Optional)</label>
                                    <input type="file" class="form-control" id="edit_image" name="image" 
                                           accept="image/*" onchange="previewEditImage(this)">
                                    <div class="form-text">Leave empty to keep current photo</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                        <label class="form-check-label" for="edit_is_active">
                                            Active (visible on website)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Current/Preview Photo</label>
                                    <div id="editImagePreview" class="border rounded p-3 text-center" style="min-height: 150px;">
                                        <img id="currentPhoto" src="" alt="Current photo" class="img-fluid image-preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_message" class="form-label">Testimonial Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_message" name="message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_testimonial" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Testimonial
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTestimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the testimonial from "<span id="deleteTestimonialName"></span>"?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="testimonial_id" id="delete_testimonial_id">
                        <button type="submit" name="delete_testimonial" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Testimonial
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        function editTestimonial(testimonial) {
            document.getElementById('edit_testimonial_id').value = testimonial.id;
            document.getElementById('edit_name').value = testimonial.name;
            document.getElementById('edit_position').value = testimonial.position;
            document.getElementById('edit_message').value = testimonial.message;
            document.getElementById('edit_is_active').checked = testimonial.is_active == 1;
            
            const currentPhoto = document.getElementById('currentPhoto');
            if (testimonial.image_path) {
                currentPhoto.src = '../images/' + testimonial.image_path;
                currentPhoto.style.display = 'block';
            } else {
                document.getElementById('editImagePreview').innerHTML = '<i class="fas fa-user fa-3x text-muted"></i><p class="text-muted mt-2">No photo uploaded</p>';
            }
            
            new bootstrap.Modal(document.getElementById('editTestimonialModal')).show();
        }

        function deleteTestimonial(id, name) {
            document.getElementById('delete_testimonial_id').value = id;
            document.getElementById('deleteTestimonialName').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteTestimonialModal')).show();
        }

        // Reset forms when modals are hidden
        document.getElementById('addTestimonialModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('#addTestimonialModal form').reset();
            document.getElementById('imagePreview').innerHTML = '<i class="fas fa-user fa-3x text-muted"></i><p class="text-muted mt-2">Photo preview will appear here</p>';
        });

        document.getElementById('editTestimonialModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('editTestimonialForm').reset();
        });
    </script>
</body>
</html>