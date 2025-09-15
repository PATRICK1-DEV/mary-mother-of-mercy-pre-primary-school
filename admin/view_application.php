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
        /* Color Variables - Matching School Website */
        :root {
            --primary-blue: rgb(44, 172, 238);
            --primary-maroon: maroon;
            --white: white;
            --light-blue: rgba(44, 172, 238, 0.1);
            --light-maroon: rgba(128, 0, 0, 0.1);
            --dark-blue: rgba(44, 172, 238, 0.8);
            --dark-maroon: rgba(128, 0, 0, 0.8);
        }
        
        /* Page Header Styling */
        .border-bottom {
            border-bottom: 3px solid var(--primary-blue) !important;
        }
        
        .h2 {
            color: var(--primary-maroon) !important;
            font-weight: 700;
        }
        
        .h2 i {
            color: var(--primary-blue);
            margin-right: 10px;
        }
        
        /* Toolbar Buttons */
        .btn-outline-secondary {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon));
            border-color: var(--primary-blue);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(44,172,238,0.3);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-maroon);
            color: var(--primary-maroon);
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, var(--primary-maroon), var(--primary-blue));
            border-color: var(--primary-maroon);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(128,0,0,0.3);
        }
        
        /* Alert Styling */
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-color: var(--primary-blue);
            color: var(--primary-maroon);
            border-left: 4px solid var(--primary-blue);
            border-radius: 10px;
        }
        
        /* Application Header */
        .application-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon));
            color: var(--white);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(44,172,238,0.3);
        }
        
        .application-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .application-header h3 {
            font-weight: 700;
            font-size: 1.8rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            position: relative;
            z-index: 2;
        }
        
        .application-header p {
            position: relative;
            z-index: 2;
            opacity: 0.95;
        }
        
        .application-header i {
            margin-right: 8px;
        }
        
        /* Status Badge */
        .status-badge {
            font-size: 1.1rem;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .bg-warning {
            background: linear-gradient(135deg, #ffc107, #e67e22) !important;
        }
        
        .bg-success {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
        }
        
        .bg-danger {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
        }
        
        /* Information Cards */
        .info-card {
            border: none;
            border-radius: 15px;
            margin-bottom: 25px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(44,172,238,0.15);
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon));
        }
        
        /* Card Headers */
        .card-header {
            background: linear-gradient(135deg, var(--light-blue), rgba(248,249,250,1)) !important;
            border-bottom: 2px solid var(--primary-blue);
            padding: 20px 25px;
        }
        
        .card-header h6 {
            color: var(--primary-maroon) !important;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .card-header i {
            color: var(--primary-blue);
            margin-right: 8px;
        }
        
        /* Card Body */
        .card-body {
            padding: 25px;
            background: linear-gradient(135deg, rgba(255,255,255,1), rgba(248,249,250,0.5));
        }
        
        /* Table Styling */
        .table {
            margin-bottom: 0;
        }
        
        .table td {
            padding: 12px 0;
            border: none;
            vertical-align: middle;
        }
        
        .table td:first-child {
            color: var(--primary-maroon);
            font-weight: 600;
            width: 40%;
        }
        
        .table td:last-child {
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* Section Headers in Parent Info */
        .text-primary {
            color: var(--primary-blue) !important;
            font-weight: 700;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 5px;
            margin-bottom: 15px !important;
            display: inline-block;
        }
        
        /* Signature Section */
        .signature-display {
            border: 2px solid var(--primary-blue) !important;
            border-radius: 12px !important;
            padding: 20px !important;
            background: linear-gradient(135deg, rgba(255,255,255,1), var(--light-blue)) !important;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .signature-display:hover {
            box-shadow: 0 5px 15px rgba(44,172,238,0.2);
        }
        
        .typed-signature-display {
            border: 2px solid var(--primary-maroon) !important;
            border-radius: 12px !important;
            padding: 25px !important;
            background: linear-gradient(135deg, rgba(255,255,255,1), var(--light-maroon)) !important;
            font-family: 'Brush Script MT', cursive;
            font-size: 28px !important;
            text-align: center;
            color: var(--primary-maroon) !important;
            transition: all 0.3s ease;
        }
        
        .typed-signature-display:hover {
            box-shadow: 0 5px 15px rgba(128,0,0,0.2);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-maroon);
            margin-bottom: 10px;
        }
        
        /* No Signature Message */
        .text-center .fa-signature {
            color: var(--primary-blue) !important;
        }
        
        .text-muted {
            color: #6c757d !important;
            font-style: italic;
        }
        
        /* Status Update Form */
        .card.mt-4 .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon)) !important;
            border-bottom: none;
        }
        
        .card.mt-4 .card-header h6 {
            color: var(--white) !important;
        }
        
        .card.mt-4 .card-header i {
            color: var(--white);
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(44,172,238,0.25);
            transform: translateY(-1px);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-maroon);
            margin-bottom: 8px;
        }
        
        /* Primary Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon)) !important;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-maroon), var(--primary-blue)) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(44,172,238,0.4);
        }
        
        /* Main Content Area */
        main {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            min-height: 100vh;
            padding-top: 20px;
        }
        
        /* Print Styles */
        @media print {
            .no-print { display: none !important; }
            .card { 
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
            .application-header {
                background: #f8f9fa !important;
                color: #000 !important;
            }
            .card-header {
                background: #f8f9fa !important;
                color: #000 !important;
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .application-header {
                padding: 20px;
                text-align: center;
            }
            
            .application-header h3 {
                font-size: 1.5rem;
            }
            
            .status-badge {
                font-size: 0.9rem;
                padding: 8px 16px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .table td:first-child {
                width: 45%;
                font-size: 0.9rem;
            }
        }
        
        /* Animation for page load */
        .info-card {
            animation: slideInUp 0.6s ease-out;
        }
        
        .info-card:nth-child(1) { animation-delay: 0.1s; }
        .info-card:nth-child(2) { animation-delay: 0.2s; }
        .info-card:nth-child(3) { animation-delay: 0.3s; }
        .info-card:nth-child(4) { animation-delay: 0.4s; }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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

                <!-- Signature Section -->
                <div class="card info-card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-signature"></i> Parent/Guardian Signature
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($application['signature_data']) || !empty($application['typed_signature'])): ?>
                            <?php if (!empty($application['signature_data'])): ?>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Drawn/Uploaded Signature:</strong></label>
                                    <div class="signature-display" style="border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; background: white; text-align: center;">
                                        <img src="<?php echo htmlspecialchars($application['signature_data']); ?>" 
                                             alt="Parent/Guardian Signature" 
                                             style="max-width: 100%; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($application['typed_signature'])): ?>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Typed Signature:</strong></label>
                                    <div class="typed-signature-display" style="border: 2px solid #e9ecef; border-radius: 8px; padding: 20px; background: white; font-family: 'Brush Script MT', cursive; font-size: 24px; text-align: center; color: #000;">
                                        <?php echo htmlspecialchars($application['typed_signature']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-signature fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No signature provided</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                This signature was provided by the parent/guardian during the application submission.
                            </small>
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
    <script>
        // Add print-specific styling for signatures
        window.addEventListener('beforeprint', function() {
            const signatureImages = document.querySelectorAll('.signature-display img');
            signatureImages.forEach(img => {
                img.style.maxHeight = '100px';
                img.style.border = '1px solid #000';
            });
            
            const typedSignatures = document.querySelectorAll('.typed-signature-display');
            typedSignatures.forEach(sig => {
                sig.style.border = '1px solid #000';
                sig.style.fontSize = '18px';
            });
        });
        
        window.addEventListener('afterprint', function() {
            const signatureImages = document.querySelectorAll('.signature-display img');
            signatureImages.forEach(img => {
                img.style.maxHeight = '150px';
                img.style.border = '1px solid #ddd';
            });
            
            const typedSignatures = document.querySelectorAll('.typed-signature-display');
            typedSignatures.forEach(sig => {
                sig.style.border = '2px solid #e9ecef';
                sig.style.fontSize = '24px';
            });
        });
    </script>
</body>
</html>