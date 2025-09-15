<?php
require_once 'includes/config.php';
requireAdminLogin();

// Handle status updates
if ($_POST && isset($_POST['update_status'])) {
    $app_id = (int)$_POST['app_id'];
    $new_status = sanitizeInput($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $app_id);
    
    if ($stmt->execute()) {
        $success = "Application status updated successfully!";
    } else {
        $error = "Error updating application status.";
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];
$types = "";

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($search) {
    $where_conditions[] = "(student_name LIKE ? OR student_surname LIKE ? OR application_no LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get applications
$query = "SELECT * FROM applications $where_clause ORDER BY submitted_at DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$applications = $stmt->get_result();

// Get statistics for current filter
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications $where_clause";

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
    <title>Applications - Admin Panel</title>
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
        
        /* Export Button Styling */
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
        
        /* Alert Styling */
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-color: var(--primary-blue);
            color: var(--primary-maroon);
            border-left: 4px solid var(--primary-blue);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-color: var(--primary-maroon);
            color: var(--primary-maroon);
            border-left: 4px solid var(--primary-maroon);
        }
        
        /* Statistics Cards Styling */
        .card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
        }
        
        /* Card Border Colors using School Theme */
        .border-left-primary {
            border-left: 5px solid var(--primary-blue) !important;
            background: linear-gradient(135deg, rgba(44,172,238,0.05), rgba(255,255,255,1));
        }
        
        .border-left-warning {
            border-left: 5px solid #ffc107 !important;
            background: linear-gradient(135deg, rgba(255,193,7,0.05), rgba(255,255,255,1));
        }
        
        .border-left-success {
            border-left: 5px solid var(--primary-maroon) !important;
            background: linear-gradient(135deg, rgba(128,0,0,0.05), rgba(255,255,255,1));
        }
        
        .border-left-danger {
            border-left: 5px solid #dc3545 !important;
            background: linear-gradient(135deg, rgba(220,53,69,0.05), rgba(255,255,255,1));
        }
        
        /* Card Text Colors */
        .text-primary {
            color: var(--primary-blue) !important;
        }
        
        .text-warning {
            color: #e67e22 !important;
        }
        
        .text-success {
            color: var(--primary-maroon) !important;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .text-gray-800 {
            color: #2c3e50 !important;
            font-weight: 800;
        }
        
        /* Statistics Numbers Enhancement */
        .h5 {
            font-size: 2rem !important;
            font-weight: 800 !important;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Text Uppercase Styling */
        .text-uppercase {
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 0.8rem;
        }
        
        /* Filter Card Styling */
        .card-body {
            padding: 25px;
            position: relative;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 15px;
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
        
        /* Primary Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon)) !important;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-maroon), var(--primary-blue)) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(44,172,238,0.4);
        }
        
        /* Applications Table Card */
        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon)) !important;
            border-bottom: none;
            padding: 20px 25px;
        }
        
        .card-header h6 {
            color: var(--white) !important;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .card-header i {
            margin-right: 8px;
        }
        
        /* Table Styling */
        .table {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--light-blue), rgba(248,249,250,1));
            color: var(--primary-maroon);
            font-weight: 700;
            border: none;
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: var(--light-blue);
            transform: scale(1.005);
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        
        /* Status Select Styling */
        .form-select-sm {
            padding: 6px 10px;
            font-size: 0.85rem;
            border-radius: 6px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-select-sm:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.1rem rgba(44,172,238,0.25);
        }
        
        /* Action Buttons */
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
            margin-right: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(220,53,69,0.4);
        }
        
        /* Student Name Styling */
        .table tbody td strong {
            color: var(--primary-maroon);
            font-weight: 700;
        }
        
        .text-muted {
            color: #6c757d !important;
            font-style: italic;
        }
        
        /* Phone Number Styling */
        .table tbody td i.fa-phone {
            color: var(--primary-blue);
            margin-right: 5px;
        }
        
        /* Main Content Area */
        main {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            min-height: 100vh;
            padding-top: 20px;
        }
        
        /* Add subtle pattern to cards */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, rgba(44,172,238,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(20px, -20px);
        }
        
        /* No Data Message */
        .text-center {
            color: var(--primary-maroon);
            font-weight: 600;
            font-style: italic;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .card {
                margin-bottom: 20px;
            }
            
            .h5 {
                font-size: 1.5rem !important;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.75rem;
            }
        }
        
        /* Animation for page load */
        .card {
            animation: slideInUp 0.6s ease-out;
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        
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
        
        /* Filter Form Enhancement */
        .row.g-3 {
            align-items: end;
        }
        
        /* Table Hover Effects */
        .table tbody tr:hover td {
            color: var(--primary-maroon);
        }
        
        .table tbody tr:hover .btn {
            transform: scale(1.05);
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
                    <h1 class="h2"><i class="fas fa-file-alt"></i> Applications Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportApplications()">
                            <i class="fas fa-download"></i> Export CSV
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
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-warning">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-success">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['approved']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-left-danger">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['rejected']; ?></div>
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
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search by name or application number..." value="<?php echo htmlspecialchars($search); ?>">
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

                <!-- Applications Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> Applications List
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>App No</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Parent Contact</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($applications && $applications->num_rows > 0): ?>
                                        <?php while ($app = $applications->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $app['application_no'] ?: 'N/A'; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($app['student_name'] . ' ' . $app['student_surname']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($app['student_middle_name']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($app['class_to_join']); ?></td>
                                                <td>
                                                    <?php if ($app['father_phone']): ?>
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($app['father_phone']); ?><br>
                                                    <?php endif; ?>
                                                    <?php if ($app['mother_phone']): ?>
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($app['mother_phone']); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="approved" <?php echo $app['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                            <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                        </select>
                                                        <input type="hidden" name="update_status" value="1">
                                                    </form>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($app['submitted_at'])); ?></td>
                                                <td>
                                                    <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteApplication(<?php echo $app['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No applications found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteApplication(id) {
            if (confirm('Are you sure you want to delete this application?')) {
                window.location.href = 'delete_application.php?id=' + id;
            }
        }

        function exportApplications() {
            window.location.href = 'export_applications.php<?php echo $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>';
        }
    </script>
</body>
</html>