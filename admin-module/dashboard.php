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
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="<?= ($adminRole === 'superadmin') ? 'theme-superadmin' : '' ?>">
    <div class="d-flex">
        <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4">
               
                
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');
            if(toggleBtn && sidebar && overlay) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                });
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }
        });
    </script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>



