<?php
require_once 'includes/config.php';
requireAdminLogin();

// Get statistics
$stats = [];
$stats['total_applications'] = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'] ?? 0;
$stats['pending_applications'] = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
$stats['approved_applications'] = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'approved'")->fetch_assoc()['count'] ?? 0;

// Get recent applications
$recent_applications = $conn->query("SELECT * FROM applications ORDER BY submitted_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mary Mother of Mercy</title>
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
        
        /* Dashboard Header Styling */
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
        
        /* Statistics Cards Styling */
        .card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
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
        
        .text-gray-800 {
            color: #2c3e50 !important;
            font-weight: 800;
        }
        
        .text-gray-300 {
            color: rgba(44,172,238,0.3) !important;
        }
        
        /* Card Icons Enhancement */
        .card .fas {
            transition: all 0.3s ease;
        }
        
        .card:hover .fas {
            transform: scale(1.1);
            color: var(--primary-blue) !important;
        }
        
        /* Recent Applications Card */
        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon)) !important;
            border-bottom: none;
            padding: 20px;
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
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: var(--light-blue);
            transform: scale(1.01);
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        
        /* Badge Styling */
        .badge {
            padding: 8px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 20px;
        }
        
        .bg-warning {
            background: linear-gradient(135deg, #ffc107, #e67e22) !important;
        }
        
        .bg-success {
            background: linear-gradient(135deg, var(--primary-maroon), #8B0000) !important;
        }
        
        .bg-danger {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
        }
        
        /* Action Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-maroon)) !important;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-maroon), var(--primary-blue)) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(44,172,238,0.4);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        
        /* Main Content Area */
        main {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            min-height: 100vh;
            padding-top: 20px;
        }
        
        /* Card Body Enhancement */
        .card-body {
            padding: 25px;
            position: relative;
        }
        
        /* Add subtle pattern to cards */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(44,172,238,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        /* Statistics Numbers Enhancement */
        .h5 {
            font-size: 2.5rem !important;
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .card {
                margin-bottom: 20px;
            }
            
            .h5 {
                font-size: 2rem !important;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
        
        /* Animation for page load */
        .card {
            animation: slideInUp 0.6s ease-out;
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        
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
        
        /* View All Applications Button */
        .text-center .btn-primary {
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 25px;
            margin-top: 20px;
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
                    <h1 class="h2"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4 justify-content-center">
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Applications
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['total_applications']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Applications
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['pending_applications']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Approved Applications
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['approved_applications']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> Recent Applications
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Application No</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_applications && $recent_applications->num_rows > 0): ?>
                                        <?php while ($app = $recent_applications->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $app['application_no'] ?: 'N/A'; ?></td>
                                                <td><?php echo $app['student_name'] . ' ' . $app['student_surname']; ?></td>
                                                <td><?php echo $app['class_to_join']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $app['status'] === 'pending' ? 'warning' : ($app['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                                        <?php echo ucfirst($app['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($app['submitted_at'])); ?></td>
                                                <td>
                                                    <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No applications found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="applications.php" class="btn btn-primary">
                                <i class="fas fa-list"></i> View All Applications
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>