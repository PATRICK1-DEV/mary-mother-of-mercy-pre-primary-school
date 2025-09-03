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
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'content.php' ? 'active' : ''; ?>" href="content.php">
                    <i class="fas fa-edit"></i> Website Content
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'slider.php' ? 'active' : ''; ?>" href="slider.php">
                    <i class="fas fa-images"></i> Slider Images
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : ''; ?>" href="teachers.php">
                    <i class="fas fa-users"></i> Teachers
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>" href="gallery.php">
                    <i class="fas fa-photo-video"></i> Gallery
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''; ?>" href="testimonials.php">
                    <i class="fas fa-comments"></i> Testimonials
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Settings</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact_info.php' ? 'active' : ''; ?>" href="contact_info.php">
                    <i class="fas fa-address-book"></i> Contact Info
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'active' : ''; ?>" href="backup.php">
                    <i class="fas fa-download"></i> Backup
                </a>
            </li>
        </ul>
    </div>
</nav>