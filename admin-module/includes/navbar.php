<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = 'Dashboard Overview';
switch ($currentPage) {
    case 'all_students.php': $pageTitle = 'Students Directory'; break;
    case 'manage_companies.php': $pageTitle = 'Manage Companies'; break;
    case 'view_applicants.php': $pageTitle = 'View Applicants'; break;
    case 'reports.php': $pageTitle = 'Reports'; break;
    case 'manage_activities.php':
    case 'edit_activity.php': $pageTitle = 'Manage Activities'; break;
    case 'manage_admins.php': $pageTitle = 'Manage Admins'; break;
}
?>
<!-- Sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" style="width: 250px;">
    <h4 class="text-center mb-4 px-3" style="color: var(--accent-coral);">GEC Admin</h4>
    
    <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
    
    <?php if (isset($adminRole) && $adminRole === 'superadmin'): ?>
        <a href="manage_admins.php" class="<?= $currentPage == 'manage_admins.php' ? 'active' : '' ?>"><i class="fa-solid fa-users-gear me-2"></i> Manage Admins</a>
    <?php endif; ?>
    <a href="all_students.php" class="<?= $currentPage == 'all_students.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-graduate me-2"></i> All Students</a>
    <a href="manage_companies.php" class="<?= $currentPage == 'manage_companies.php' ? 'active' : '' ?>"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
    <a href="view_applicants.php" class="<?= $currentPage == 'view_applicants.php' ? 'active' : '' ?>"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
    
    <a href="reports.php" class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie me-2"></i> Reports</a>
    <a href="manage_activities.php" class="<?= in_array($currentPage, ['manage_activities.php', 'edit_activity.php']) ? 'active' : '' ?>"><i class="fa-solid fa-list-check me-2"></i> Manage Activities</a>
    <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="flex-grow-1">
    <div class="topbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-outline-secondary me-3" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
            <h5 class="m-0 text-muted"><?= $pageTitle ?></h5>
        </div>
        <div>
            <span class="fw-medium me-3 text-dark">Hi, <?= htmlspecialchars($adminName ?? 'Admin') ?></span>
            <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($adminRole ?? '')) ?> <?= !empty($adminBranch) ? '- ' . htmlspecialchars($adminBranch) : '' ?></span>
        </div>
    </div>
