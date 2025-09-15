<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'applications.php' ? 'active' : ''; ?>" href="applications.php">
                    <i class="fas fa-file-alt"></i> Applications
                    <?php if (isset($stats['pending_applications']) && $stats['pending_applications'] > 0): ?>
                        <span class="badge bg-warning rounded-pill"><?php echo $stats['pending_applications']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>
</nav>