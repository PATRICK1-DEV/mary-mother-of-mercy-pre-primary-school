<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
requireAdminLogin();

// Simple gallery management without complex features
$success = '';
$error = '';

// Handle simple form submissions
if ($_POST) {
    if (isset($_POST['add_image'])) {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            
            if (strpos($upload_result, 'Error') === false && strpos($upload_result, 'Sorry') === false) {
                $stmt = $conn->prepare("INSERT INTO gallery_images (title, image_path, description) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $title, $upload_result, $description);
                
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
}

// Get gallery images
$gallery_images = $conn->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
$total_images = $conn->query("SELECT COUNT(*) as count FROM gallery_images")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Simple Version</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-photo-video"></i> Gallery Management (Simple)</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
                        <i class="fas fa-plus"></i> Add New Image
                    </button>
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

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Images</h5>
                                <h2 class="text-primary"><?php echo $total_images; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gallery Images -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-images"></i> Gallery Images
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($gallery_images && $gallery_images->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($image = $gallery_images->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card">
                                            <img src="../images/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                 class="card-img-top" style="height: 200px; object-fit: cover;" 
                                                 alt="<?php echo htmlspecialchars($image['title']); ?>">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h6>
                                                <p class="card-text text-muted small">
                                                    <?php echo htmlspecialchars($image['description']); ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($image['created_at'])); ?>
                                                </small>
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
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Image Modal -->
    <div class="modal fade" id="addImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image *</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_image" class="btn btn-primary">Add Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>