<?php
/**
 * Admin Dashboard
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';

// Secure the page
require_admin_login();

$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];
$adminRole = $_SESSION['admin_role'];
$adminBranch = $_SESSION['admin_branch'];

// Fetch stats based on role
$stats = [];
if ($adminRole === 'superadmin') {
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_admins WHERE role = 'subadmin'");
    $stats['subadmins'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_companies");
    $stats['companies'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_students");
    $stats['students'] = $stmt->fetchColumn();
} else {
    // Sub admin sees their branch stats
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT c.company_id) 
        FROM tbl_companies c
        JOIN tbl_company_branches cb ON c.company_id = cb.company_id
        WHERE cb.branch_name = ?
    ");
    $stmt->execute([$adminBranch]);
    $stats['companies'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_students WHERE branch = ?");
    $stmt->execute([$adminBranch]);
    $stats['students'] = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GEC Placement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            <?php if ($adminRole === 'superadmin'): ?>
            --primary-navy: #000000; /* Pure Black */
            --accent-coral: #F1C40F; /* Gold Accent */
            <?php else: ?>
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
            <?php endif; ?>
        }
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: var(--primary-navy); color: white; padding-top: 20px; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; display: block; padding: 12px 20px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--accent-coral); }
        .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid var(--accent-coral); }
        .stat-card h3 { color: var(--primary-navy); font-weight: 700; margin: 0; }
        .topbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <h4 class="text-center mb-4 px-3" style="color: var(--accent-coral);">GEC Admin</h4>
            
            <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            
            <?php if ($adminRole === 'superadmin'): ?>
                <a href="manage_admins.php"><i class="fa-solid fa-users-gear me-2"></i> Manage Admins</a>
            <?php endif; ?>
            <a href="all_students.php"><i class="fa-solid fa-user-graduate me-2"></i> All Students</a>
            <a href="manage_companies.php"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
            <a href="view_applicants.php"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            
            <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-muted">Dashboard Overview</h5>
                <div>
                    <span class="fw-medium me-3 text-dark">Hi, <?= htmlspecialchars($adminName) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($adminRole)) ?> <?= $adminBranch ? '- ' . htmlspecialchars($adminBranch) : '' ?></span>
                </div>
            </div>

            <div class="container-fluid p-4">
                <h3 class="mb-4" style="color: var(--primary-navy);">Welcome to Admin Panel</h3>
                
                <div class="row g-4">
                    <?php if ($adminRole === 'superadmin'): ?>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <p class="text-muted mb-1">Total Sub-Admins</p>
                            <h3><?= $stats['subadmins'] ?></h3>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-md-4">
                        <div class="stat-card">
                            <p class="text-muted mb-1">Companies (<?= $adminRole === 'superadmin' ? 'Total' : 'Your Branch' ?>)</p>
                            <h3><?= $stats['companies'] ?></h3>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stat-card">
                            <p class="text-muted mb-1">Students (<?= $adminRole === 'superadmin' ? 'Total' : 'Your Branch' ?>)</p>
                            <h3><?= $stats['students'] ?></h3>
                        </div>
                    </div>
                </div>

                <div class="mt-5 bg-white p-4 rounded shadow-sm border-top border-4 border-warning">
                    <h5 class="mb-3">Quick Actions</h5>
                    <div class="d-flex gap-3">
                        <a href="manage_companies.php" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Add New Company</a>
                        <a href="view_applicants.php" class="btn btn-outline-secondary"><i class="fa-solid fa-list me-1"></i> Check Applications</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
