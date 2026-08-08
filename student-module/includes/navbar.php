<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg top-navbar navbar-light">
    <div class="container">
        <a class="navbar-brand brand-text" href="dashboard.php">GEC Modasa <span>Placement</span></a>
        <button class="navbar-toggler border-0 px-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" onclick="let icon = this.querySelector('i'); if(icon.classList.contains('fa-bars')){icon.classList.replace('fa-bars', 'fa-xmark');}else{icon.classList.replace('fa-xmark', 'fa-bars');}">
            <i class="fa-solid fa-bars fs-2 text-dark"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?> fw-medium" href="dashboard.php">Profile</a>
                </li>
                <?php if (isset($isProfileComplete) && $isProfileComplete): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'placement_drives.php' ? 'active' : '' ?> fw-medium" href="placement_drives.php">Placement Drives</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'track_applications.php' ? 'active' : '' ?> fw-medium" href="track_applications.php">My Applications</a>
                </li>
                <?php endif; ?>
                
                <hr class="d-lg-none my-2 text-secondary">
                
                <li class="nav-item">
                    <span class="nav-link fw-bold" style="color: var(--primary-navy);">Hi, <?= htmlspecialchars($student['full_name'] ?? 'Student') ?></span>
                </li>
                <li class="nav-item mt-2 mt-lg-0 ms-lg-2">
                    <a href="logout.php" class="btn btn-outline-danger btn-sm w-100"><i
                            class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
