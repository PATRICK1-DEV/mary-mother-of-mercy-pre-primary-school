<?php
require_once 'includes/config.php';
requireAdminLogin();

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$app_id) {
    header('Location: applications.php');
    exit();
}

// Get application details
$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();

if (!$application) {
    header('Location: applications.php');
    exit();
}

// Handle status update
if ($_POST && isset($_POST['update_status'])) {
    $new_status = sanitizeInput($_POST['status']);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    $update_stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $app_id);
    
    if ($update_stmt->execute()) {
        $success = "Application status updated successfully!";
        $application['status'] = $new_status;
    } else {
        $error = "Error updating application status.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .application-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-card {
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 1rem;
            padding: 8px 16px;
        }
        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
                    <h1 class="h2">
                        <i class="fas fa-file-alt"></i> Application Details
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <a href="applications.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Application Header -->
                <div class="application-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <?php echo htmlspecialchars($application['student_name'] . ' ' . $application['student_surname']); ?>
                            </h3>
                            <p class="mb-1">
                                <i class="fas fa-id-card"></i> Application No: 
                                <strong><?php echo $application['application_no'] ?: 'Not assigned'; ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-calendar"></i> Submitted: 
                                <?php echo date('F d, Y \a\t g:i A', strtotime($application['submitted_at'])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge status-badge bg-<?php echo $application['status'] === 'pending' ? 'warning' : ($application['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                <?php echo ucfirst($application['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Student Information -->
                    <div class="col-md-6">
                        <div class="card info-card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-user"></i> Student Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Full Name:</strong></td>
                                        <td><?php echo htmlspecialchars($application['student_name'] . ' ' . $application['student_middle_name'] . ' ' . $application['student_surname']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Class to Join:</strong></td>
                                        <td><?php echo htmlspecialchars($application['class_to_join']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sex:</strong></td>
                                        <td><?php echo ucfirst($application['sex']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date of Birth:</strong></td>
                                        <td><?php echo $application['date_of_birth'] ? date('F d, Y', strtotime($application['date_of_birth'])) : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Place of Birth:</strong></td>
                                        <td><?php echo htmlspecialchars($application['place_of_birth']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nationality:</strong></td>
                                        <td><?php echo htmlspecialchars($application['nationality']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tribe:</strong></td>
                                        <td><?php echo htmlspecialchars($application['tribe']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Religion:</strong></td>
                                        <td><?php echo htmlspecialchars($application['religion']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Denomination:</strong></td>
                                        <td><?php echo htmlspecialchars($application['denomination']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Previous School -->
                        <div class="card info-card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-school"></i> Previous School
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>School Name:</strong></td>
                                        <td><?php echo htmlspecialchars($application['previous_school']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Previous Class:</strong></td>
                                        <td><?php echo htmlspecialchars($application['previous_class']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Parent Information -->
                    <div class="col-md-6">
                        <div class="card info-card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-users"></i> Parent Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <h6 class="text-primary">Father's Information</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td><?php echo htmlspecialchars($application['father_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Occupation:</strong></td>
                                        <td><?php echo htmlspecialchars($application['father_occupation']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo htmlspecialchars($application['father_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Workplace:</strong></td>
                                        <td><?php echo htmlspecialchars($application['father_workplace']); ?></td>
                                    </tr>
                                </table>

                                <h6 class="text-primary mt-3">Mother's Information</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td><?php echo htmlspecialchars($application['mother_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Occupation:</strong></td>
                                        <td><?php echo htmlspecialchars($application['mother_occupation']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo htmlspecialchars($application['mother_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Workplace:</strong></td>
                                        <td><?php echo htmlspecialchars($application['mother_workplace']); ?></td>
                                    </tr>
                                </table>

                                <?php if ($application['guardian_name']): ?>
                                <h6 class="text-primary mt-3">Guardian's Information</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td><?php echo htmlspecialchars($application['guardian_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Occupation:</strong></td>
                                        <td><?php echo htmlspecialchars($application['guardian_occupation']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo htmlspecialchars($application['guardian_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Workplace:</strong></td>
                                        <td><?php echo htmlspecialchars($application['guardian_workplace']); ?></td>
                                    </tr>
                                </table>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card info-card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-address-book"></i> Contact Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>P.O. Box:</strong></td>
                                        <td><?php echo htmlspecialchars($application['postal_box']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Place:</strong></td>
                                        <td><?php echo htmlspecialchars($application['postal_place']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Update Form -->
                <div class="card mt-4 no-print">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-edit"></i> Update Application Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $application['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Notes (Optional)</label>
                                    <input type="text" class="form-control" id="notes" name="notes" placeholder="Add any notes...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" name="update_status" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>