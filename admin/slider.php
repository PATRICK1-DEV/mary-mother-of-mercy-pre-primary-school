<?php
require_once 'includes/config.php';
requireAdminLogin();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_slide'])) {
        $title = sanitizeInput($_POST['title']);
        $caption = sanitizeInput($_POST['caption']);
        $sort_order = (int)$_POST['sort_order'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                // Image uploaded successfully
                $image_path = $upload_result;
                
                $stmt = $conn->prepare("INSERT INTO slider_images (title, image_path, caption, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $title, $image_path, $caption, $sort_order);
                
                if ($stmt->execute()) {
                    $success = "Slider image added successfully!";
                } else {
                    $error = "Error adding slider image to database.";
                }
            } else {
                $error = $upload_result;
            }
        } else {
            $error = "Please select an image file.";
        }
    }
    
    if (isset($_POST['update_slide'])) {
        $slide_id = (int)$_POST['slide_id'];
        $title = sanitizeInput($_POST['title']);
        $caption = sanitizeInput($_POST['caption']);
        $sort_order = (int)$_POST['sort_order'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if new image is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                // Delete old image
                $old_image_stmt = $conn->prepare("SELECT image_path FROM slider_images WHERE id = ?");
                $old_image_stmt->bind_param("i", $slide_id);
                $old_image_stmt->execute();
                $old_image = $old_image_stmt->get_result()->fetch_assoc();
                
                if ($old_image && file_exists('../images/' . $old_image['image_path'])) {
                    unlink('../images/' . $old_image['image_path']);
                }
                
                // Update with new image
                $stmt = $conn->prepare("UPDATE slider_images SET title = ?, image_path = ?, caption = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $title, $upload_result, $caption, $sort_order, $is_active, $slide_id);
            } else {
                $error = $upload_result;
            }
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE slider_images SET title = ?, caption = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $title, $caption, $sort_order, $is_active, $slide_id);
        }
        
        if (isset($stmt) && $stmt->execute()) {
            $success = "Slider image updated successfully!";
        } elseif (!isset($error)) {
            $error = "Error updating slider image.";
        }
    }
    
    if (isset($_POST['delete_slide'])) {
        $slide_id = (int)$_POST['slide_id'];
        
        // Get image path for deletion
        $image_stmt = $conn->prepare("SELECT image_path FROM slider_images WHERE id = ?");
        $image_stmt->bind_param("i", $slide_id);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result()->fetch_assoc();
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM slider_images WHERE id = ?");
        $delete_stmt->bind_param("i", $slide_id);
        
        if ($delete_stmt->execute()) {
            // Delete image file
            if ($image_result && file_exists('../images/' . $image_result['image_path'])) {
                unlink('../images/' . $image_result['image_path']);
            }
            $success = "Slider image deleted successfully!";
        } else {
            $error = "Error deleting slider image.";
        }
    }
}

// Get all slider images
$slider_images = $conn->query("SELECT * FROM slider_images ORDER BY sort_order ASC, id DESC");

// Get statistics
$total_slides = $conn->query("SELECT COUNT(*) as count FROM slider_images")->fetch_assoc()['count'];
$active_slides = $conn->query("SELECT COUNT(*) as count FROM slider_images WHERE is_active = 1")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .slider-preview {
            max-width: 200px;
            max-height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        .slide-card {
            transition: all 0.3s ease;
        }
        .slide-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .drag-handle {
            cursor: move;
            color: #6c757d;
        }
        .drag-handle:hover {
            color: #007bff;
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
                    <h1 class="h2"><i class="fas fa-images"></i> Slider Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                            <i class="fas fa-plus"></i> Add New Slide
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
                                            Total Slides
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $total_slides; ?>
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
                                            Active Slides
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $active_slides; ?>
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
                        <div class="card border-left-info">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Recommended
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            3-5 Slides
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-lightbulb fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slider Images List -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> Current Slider Images
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($slider_images && $slider_images->num_rows > 0): ?>
                            <div class="row" id="slider-container">
                                <?php while ($slide = $slider_images->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4 mb-4" data-slide-id="<?php echo $slide['id']; ?>">
                                        <div class="card slide-card h-100">
                                            <div class="position-relative">
                                                <img src="../images/<?php echo htmlspecialchars($slide['image_path']); ?>" 
                                                     class="card-img-top slider-preview" 
                                                     alt="<?php echo htmlspecialchars($slide['title']); ?>">
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <span class="badge bg-<?php echo $slide['is_active'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $slide['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </div>
                                                <div class="position-absolute top-0 start-0 p-2">
                                                    <i class="fas fa-grip-vertical drag-handle"></i>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($slide['title']); ?></h6>
                                                <p class="card-text text-muted small">
                                                    <?php echo htmlspecialchars($slide['caption']); ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">Order: <?php echo $slide['sort_order']; ?></small>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="editSlide(<?php echo htmlspecialchars(json_encode($slide)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteSlide(<?php echo $slide['id']; ?>, '<?php echo htmlspecialchars($slide['title']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No slider images found</h5>
                                <p class="text-muted">Add your first slider image to get started.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                                    <i class="fas fa-plus"></i> Add First Slide
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-lightbulb"></i> Slider Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Image Guidelines:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Recommended size: 1200x500 pixels</li>
                                    <li><i class="fas fa-check text-success"></i> Format: JPG, PNG, or GIF</li>
                                    <li><i class="fas fa-check text-success"></i> Maximum file size: 5MB</li>
                                    <li><i class="fas fa-check text-success"></i> Use high-quality images</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Best Practices:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Keep 3-5 slides for optimal performance</li>
                                    <li><i class="fas fa-check text-success"></i> Use descriptive titles and captions</li>
                                    <li><i class="fas fa-check text-success"></i> Order slides by importance</li>
                                    <li><i class="fas fa-check text-success"></i> Test on mobile devices</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Slide Modal -->
    <div class="modal fade" id="addSlideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Add New Slider Image
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
                                    <label for="caption" class="form-label">Caption</label>
                                    <textarea class="form-control" id="caption" name="caption" rows="3" 
                                              placeholder="Optional caption text..."></textarea>
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
                                    <div class="form-text">Max size: 5MB. Recommended: 1200x500px</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Preview</label>
                                    <div id="imagePreview" class="border rounded p-3 text-center" style="min-height: 150px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                        <p class="text-muted mt-2">Image preview will appear here</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_slide" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Slide
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Slide Modal -->
    <div class="modal fade" id="editSlideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Slider Image
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editSlideForm">
                    <input type="hidden" name="slide_id" id="edit_slide_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_caption" class="form-label">Caption</label>
                                    <textarea class="form-control" id="edit_caption" name="caption" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
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
                                    <label for="edit_image" class="form-label">Change Image (Optional)</label>
                                    <input type="file" class="form-control" id="edit_image" name="image" 
                                           accept="image/*" onchange="previewEditImage(this)">
                                    <div class="form-text">Leave empty to keep current image</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Current/Preview Image</label>
                                    <div id="editImagePreview" class="border rounded p-3 text-center" style="min-height: 150px;">
                                        <img id="currentImage" src="" alt="Current image" class="img-fluid" style="max-height: 120px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_slide" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Slide
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteSlideModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the slide "<span id="deleteSlideTitle"></span>"?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="slide_id" id="delete_slide_id">
                        <button type="submit" name="delete_slide" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Slide
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
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 120px; border-radius: 8px;">`;
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewEditImage(input) {
            const preview = document.getElementById('editImagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 120px; border-radius: 8px;">`;
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        function editSlide(slide) {
            document.getElementById('edit_slide_id').value = slide.id;
            document.getElementById('edit_title').value = slide.title;
            document.getElementById('edit_caption').value = slide.caption;
            document.getElementById('edit_sort_order').value = slide.sort_order;
            document.getElementById('edit_is_active').checked = slide.is_active == 1;
            document.getElementById('currentImage').src = '../images/' + slide.image_path;
            
            new bootstrap.Modal(document.getElementById('editSlideModal')).show();
        }

        function deleteSlide(id, title) {
            document.getElementById('delete_slide_id').value = id;
            document.getElementById('deleteSlideTitle').textContent = title;
            
            new bootstrap.Modal(document.getElementById('deleteSlideModal')).show();
        }

        // Reset forms when modals are hidden
        document.getElementById('addSlideModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('#addSlideModal form').reset();
            document.getElementById('imagePreview').innerHTML = '<i class="fas fa-image fa-3x text-muted"></i><p class="text-muted mt-2">Image preview will appear here</p>';
        });

        document.getElementById('editSlideModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('editSlideForm').reset();
        });
    </script>
</body>
</html>