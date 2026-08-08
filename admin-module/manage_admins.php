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
        <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?> <button
                            type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?> <button
                            type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="custom-card border-top border-4 border-warning">
                    <h5 class="mb-4" style="color: var(--primary-navy);">
                    </h5>

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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subadmins as $admin): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($admin['full_name']) ?></strong></td>
                                            <td><span
                                                    class="badge bg-secondary"><?= htmlspecialchars($admin['branch']) ?></span>
                                            </td>
                                            <td><small class="text-muted"><?= htmlspecialchars($admin['email']) ?></small></td>
                                            <td><small
                                                    class="text-muted"><?= htmlspecialchars($admin['phone_number']) ?></small>
                                            </td>
                                            <td><?= date('d M Y', strtotime($admin['created_at'])) ?></td>
                                            <td>

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');
            if (toggleBtn && sidebar && overlay) {
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