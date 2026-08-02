<?php
/**
 * Track Applications Page (Coming Soon / UI Mock)
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';

// Secure the page
require_login();
require_profile_completion($pdo);

$studentId = $_SESSION['student_id'];

// Fetch basic student info for navbar
$stmt = $pdo->prepare("SELECT full_name FROM tbl_students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

// Fetch applications
$stmt = $pdo->prepare("
    SELECT a.status, a.round_details, a.applied_at, c.company_name, c.logo_path
    FROM tbl_applications a
    JOIN tbl_companies c ON a.company_id = c.company_id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$studentId]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - GEC Modasa Placement Portal</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar navbar-light">
        <div class="container">
            <a class="navbar-brand brand-text" href="dashboard.php">GEC Modasa <span>Placement</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-4">
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="dashboard.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="placement_drives.php">Placement Drives</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-medium" href="track_applications.php">My Applications</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3 ms-auto mt-3 mt-lg-0">
                    <span class="fw-medium text-dark">Hi, <?= htmlspecialchars($student['full_name']) ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm"><i
                            class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5" style="margin-top: 2rem;">
        <h3 class="mb-4">My Applications</h3>

        <?php if (empty($applications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-file-invoice fs-1 mb-3"></i>
                <h5>You haven't applied to any companies yet.</h5>
                <p>Go to <a href="placement_drives.php" style="color: var(--accent-coral);">Placement Drives</a> to find
                    opportunities.</p>
            </div>
        <?php else: ?>
            <?php foreach ($applications as $app):
                $borderClass = 'border-secondary';
                $badgeClass = 'bg-secondary';
                if ($app['status'] === 'Applied' || $app['status'] === 'In Progress') {
                    $borderClass = 'border-warning';
                    $badgeClass = 'bg-warning text-dark';
                } elseif ($app['status'] === 'Rejected') {
                    $borderClass = 'border-danger';
                    $badgeClass = 'bg-danger';
                } elseif ($app['status'] === 'Selected') {
                    $borderClass = 'border-success';
                    $badgeClass = 'bg-success';
                }
                ?>
                <div class="custom-card mb-4 border-start border-4 <?= $borderClass ?>">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex gap-3 align-items-center">
                            <?php if ($app['logo_path']): ?>
                                <img src="../admin-module/<?= htmlspecialchars($app['logo_path']) ?>" alt="Logo"
                                    style="width: 40px; height: 40px; object-fit: contain; border-radius: 6px;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-building text-muted fs-5"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($app['company_name']) ?></h5>
                                <p class="text-muted mb-0 small">Applied on:
                                    <?= date('d M Y, h:i A', strtotime($app['applied_at'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge <?= $badgeClass ?> px-3 py-2 mb-1"><?= htmlspecialchars($app['status']) ?></span>
                            <?php if (!empty($app['round_details'])): ?>
                                <div class="small text-muted mt-1" style="max-width: 250px;">
                                    <?= htmlspecialchars($app['round_details']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
