<?php
/**
 * Manage Admins (Superadmin Only)
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';

// Secure the page - Superadmin Only
require_superadmin();

$adminName = $_SESSION['admin_name'];
$adminRole = $_SESSION['admin_role'];

$error = '';
$success = '';

if (isset($_SESSION['page_success'])) {
    $success = $_SESSION['page_success'];
    unset($_SESSION['page_success']);
}
if (isset($_SESSION['page_error'])) {
    $error = $_SESSION['page_error'];
    unset($_SESSION['page_error']);
}

// Handle Delete Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_admin') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    $deleteId = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    
    if ($deleteId) {
        $stmt = $pdo->prepare("DELETE FROM tbl_admins WHERE admin_id = ? AND role = 'subadmin'");
        if ($stmt->execute([$deleteId])) {
            $_SESSION['page_success'] = "Sub-admin removed successfully.";
        } else {
            $_SESSION['page_error'] = "Failed to remove sub-admin.";
        }
        header("Location: manage_admins.php");
        exit;
    }
}

// Fetch all subadmins
$stmt = $pdo->query("SELECT * FROM tbl_admins WHERE role = 'subadmin' ORDER BY created_at DESC");
$subadmins = $stmt->fetchAll();

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - GEC Placement</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="<?= ($adminRole === 'superadmin') ? 'theme-superadmin' : '' ?>">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <h4 class="text-center mb-4 px-3" style="color: var(--accent-coral);">GEC Admin</h4>
            <a href="dashboard.php"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <a href="manage_admins.php" class="active"><i class="fa-solid fa-users-gear me-2"></i> Manage Admins</a>
            <a href="all_students.php"><i class="fa-solid fa-user-graduate me-2"></i> All Students</a>
            <a href="manage_companies.php"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
            <a href="view_applicants.php"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-muted">Manage Sub-Admins</h5>
                <div>
                    <span class="fw-medium me-3 text-dark">Hi, <?= htmlspecialchars($adminName) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($adminRole)) ?></span>
                </div>
            </div>

            <div class="container-fluid p-4">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="custom-card border-top border-4 border-warning">
                    <h5 class="mb-4" style="color: var(--primary-navy);"><i class="fa-solid fa-user-shield me-2"></i>Registered Sub-Admins</h5>
                    
                    <?php if (empty($subadmins)): ?>
                        <p class="text-muted">No sub-admins registered yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Department / Branch</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Joined On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subadmins as $admin): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($admin['full_name']) ?></strong></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($admin['branch']) ?></span></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($admin['email']) ?></small></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($admin['phone_number']) ?></small></td>
                                            <td><?= date('d M Y', strtotime($admin['created_at'])) ?></td>
                                            <td>
                                                <form action="manage_admins.php" method="POST" onsubmit="return confirm('Are you sure you want to remove this admin?');">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="action" value="delete_admin">
                                                    <input type="hidden" name="delete_id" value="<?= $admin['admin_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>


