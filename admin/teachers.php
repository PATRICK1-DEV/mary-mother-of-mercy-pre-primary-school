<?php
require_once 'includes/config.php';
requireAdminLogin();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_teacher'])) {
        $name = sanitizeInput($_POST['name']);
        $position = sanitizeInput($_POST['position']);
        $bio = sanitizeInput($_POST['bio']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        
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
            $stmt = $conn->prepare("INSERT INTO teachers (name, position, bio, email, phone, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $position, $bio, $email, $phone, $image_path);
            
            if ($stmt->execute()) {
                $success = "Teacher added successfully!";
            } else {
                $error = "Error adding teacher to database.";
            }
        }
    }
    
    if (isset($_POST['update_teacher'])) {
        $teacher_id = (int)$_POST['teacher_id'];
        $name = sanitizeInput($_POST['name']);
        $position = sanitizeInput($_POST['position']);
        $bio = sanitizeInput($_POST['bio']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if new image is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                // Delete old image
                $old_image_stmt = $conn->prepare("SELECT image_path FROM teachers WHERE id = ?");
                $old_image_stmt->bind_param("i", $teacher_id);
                $old_image_stmt->execute();
                $old_image = $old_image_stmt->get_result()->fetch_assoc();
                
                if ($old_image && $old_image['image_path'] && file_exists('../images/' . $old_image['image_path'])) {
                    unlink('../images/' . $old_image['image_path']);
                }
                
                // Update with new image
                $stmt = $conn->prepare("UPDATE teachers SET name = ?, position = ?, bio = ?, email = ?, phone = ?, image_path = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssssssii", $name, $position, $bio, $email, $phone, $upload_result, $is_active, $teacher_id);
            } else {
                $error = $upload_result;
            }
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE teachers SET name = ?, position = ?, bio = ?, email = ?, phone = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("sssssii", $name, $position, $bio, $email, $phone, $is_active, $teacher_id);
        }
        
        if (isset($stmt) && $stmt->execute()) {
            $success = "Teacher updated successfully!";
        } elseif (!isset($error)) {
            $error = "Error updating teacher.";
        }
    }
    
    if (isset($_POST['delete_teacher'])) {
        $teacher_id = (int)$_POST['teacher_id'];
        
        // Get image path for deletion
        $image_stmt = $conn->prepare("SELECT image_path FROM teachers WHERE id = ?");
        $image_stmt->bind_param("i", $teacher_id);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result()->fetch_assoc();
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
        $delete_stmt->bind_param("i", $teacher_id);
        
        if ($delete_stmt->execute()) {
            // Delete image file
            if ($image_result && $image_result['image_path'] && file_exists('../images/' . $image_result['image_path'])) {
                unlink('../images/' . $image_result['image_path']);
            }
            $success = "Teacher deleted successfully!";
        } else {
            $error = "Error deleting teacher.";
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
    $where_conditions[] = "(name LIKE ? OR position LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get teachers
$query = "SELECT * FROM teachers $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$teachers = $stmt->get_result();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
    FROM teachers $where_clause";

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
    <title>Teachers Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .teacher-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e9ecef;
        }
        .teacher-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .teacher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .teacher-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .bio-preview {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e9ecef;
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
                    <h1 class="h2"><i class="fas fa-users"></i> Teachers Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                            <i class="fas fa-plus"></i> Add New Teacher
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
                                            Total Teachers
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['total']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                            Active Teachers
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['active']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                            Inactive Teachers
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['inactive']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-times fa-2x text-gray-300"></i>
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
                                    <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search by name, position, or email..." value="<?php echo htmlspecialchars($search); ?>">
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

                <!-- Teachers Grid -->
                <div class="row">
                    <?php if ($teachers && $teachers->num_rows > 0): ?>
                        <?php while ($teacher = $teachers->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card teacher-card h-100">
                                    <div class="position-relative">
                                        <?php if ($teacher['image_path']): ?>
                                            <img src="../images/<?php echo htmlspecialchars($teacher['image_path']); ?>" 
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($teacher['name']); ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-user fa-4x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="badge status-badge bg-<?php echo $teacher['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $teacher['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($teacher['name']); ?></h5>
                                        <h6 class="card-subtitle mb-2 text-primary"><?php echo htmlspecialchars($teacher['position']); ?></h6>
                                        
                                        <?php if ($teacher['bio']): ?>
                                            <p class="card-text bio-preview text-muted">
                                                <?php echo htmlspecialchars($teacher['bio']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="contact-info mb-3">
                                            <?php if ($teacher['email']): ?>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($teacher['email']); ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if ($teacher['phone']): ?>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($teacher['phone']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Added: <?php echo date('M d, Y', strtotime($teacher['created_at'])); ?>
                                            </small>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="editTeacher(<?php echo htmlspecialchars(json_encode($teacher)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteTeacher(<?php echo $teacher['id']; ?>, '<?php echo htmlspecialchars($teacher['name']); ?>')">
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
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No teachers found</h5>
                                <p class="text-muted">Add your first teacher to get started.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                                    <i class="fas fa-plus"></i> Add First Teacher
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tips Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-lightbulb"></i> Teacher Profile Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Photo Guidelines:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Professional headshot preferred</li>
                                    <li><i class="fas fa-check text-success"></i> Square format (1:1 ratio) works best</li>
                                    <li><i class="fas fa-check text-success"></i> High resolution (at least 400x400px)</li>
                                    <li><i class="fas fa-check text-success"></i> Clear, well-lit photos</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Profile Best Practices:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Include full name and position</li>
                                    <li><i class="fas fa-check text-success"></i> Write engaging bio (2-3 sentences)</li>
                                    <li><i class="fas fa-check text-success"></i> Add contact information if appropriate</li>
                                    <li><i class="fas fa-check text-success"></i> Keep profiles updated regularly</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Add New Teacher
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
                                    <label for="position" class="form-label">Position/Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="position" name="position" 
                                           placeholder="e.g., Mathematics Teacher, Head Teacher" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text">Recommended: Square format, at least 400x400px</div>
                                </div>
                                
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
                            <label for="bio" class="form-label">Biography</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" 
                                      placeholder="Brief description about the teacher's background, experience, and teaching philosophy..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_teacher" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Teacher Modal -->
    <div class="modal fade" id="editTeacherModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Teacher
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editTeacherForm">
                    <input type="hidden" name="teacher_id" id="edit_teacher_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_position" class="form-label">Position/Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_position" name="position" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="edit_email" name="email">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="edit_phone" name="phone">
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
                                    <label for="edit_image" class="form-label">Change Photo (Optional)</label>
                                    <input type="file" class="form-control" id="edit_image" name="image" 
                                           accept="image/*" onchange="previewEditImage(this)">
                                    <div class="form-text">Leave empty to keep current photo</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Current/Preview Photo</label>
                                    <div id="editImagePreview" class="border rounded p-3 text-center" style="min-height: 150px;">
                                        <img id="currentPhoto" src="" alt="Current photo" class="img-fluid image-preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_bio" class="form-label">Biography</label>
                            <textarea class="form-control" id="edit_bio" name="bio" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_teacher" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the teacher "<span id="deleteTeacherName"></span>"?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="teacher_id" id="delete_teacher_id">
                        <button type="submit" name="delete_teacher" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Teacher
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

        function editTeacher(teacher) {
            document.getElementById('edit_teacher_id').value = teacher.id;
            document.getElementById('edit_name').value = teacher.name;
            document.getElementById('edit_position').value = teacher.position;
            document.getElementById('edit_bio').value = teacher.bio || '';
            document.getElementById('edit_email').value = teacher.email || '';
            document.getElementById('edit_phone').value = teacher.phone || '';
            document.getElementById('edit_is_active').checked = teacher.is_active == 1;
            
            const currentPhoto = document.getElementById('currentPhoto');
            if (teacher.image_path) {
                currentPhoto.src = '../images/' + teacher.image_path;
                currentPhoto.style.display = 'block';
            } else {
                document.getElementById('editImagePreview').innerHTML = '<i class="fas fa-user fa-3x text-muted"></i><p class="text-muted mt-2">No photo uploaded</p>';
            }
            
            new bootstrap.Modal(document.getElementById('editTeacherModal')).show();
        }

        function deleteTeacher(id, name) {
            document.getElementById('delete_teacher_id').value = id;
            document.getElementById('deleteTeacherName').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteTeacherModal')).show();
        }

        // Reset forms when modals are hidden
        document.getElementById('addTeacherModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('#addTeacherModal form').reset();
            document.getElementById('imagePreview').innerHTML = '<i class="fas fa-user fa-3x text-muted"></i><p class="text-muted mt-2">Photo preview will appear here</p>';
        });

        document.getElementById('editTeacherModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('editTeacherForm').reset();
        });
    </script>
</body>
</html>