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
    SELECT a.status, a.round_details, a.applied_at, a.attendance, a.round_1, a.round_2, a.round_3, a.round_4, a.round_5, c.company_name, c.logo_path
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
            <button class="navbar-toggler border-0 px-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" onclick="let icon = this.querySelector('i'); if(icon.classList.contains('fa-bars')){icon.classList.replace('fa-bars', 'fa-xmark');}else{icon.classList.replace('fa-xmark', 'fa-bars');}">
                <i class="fa-solid fa-bars fs-2 text-dark"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="dashboard.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="placement_drives.php">Placement Drives</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-medium" href="track_applications.php">My Applications</a>
                    </li>
                    
                    <hr class="d-lg-none my-2 text-secondary">
                    
                    <li class="nav-item">
                        <span class="nav-link fw-bold" style="color: var(--primary-navy);">Hi, <?= htmlspecialchars($student['full_name']) ?></span>
                    </li>
                    <li class="nav-item mt-2 mt-lg-0 ms-lg-2">
                        <a href="logout.php" class="btn btn-outline-danger btn-sm w-100"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-4">
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
                                    style="width: 50px; height: 50px; object-fit: contain; border-radius: 6px;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-building text-muted fs-4"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($app['company_name']) ?></h5>
                                <p class="text-muted mb-2 small">Applied on:
                                    <?= date('d M Y, h:i A', strtotime($app['applied_at'])) ?>
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($app['attendance'] === 'P'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle"><i class="fa-solid fa-user-check me-1"></i> Present</span>
                                    <?php elseif ($app['attendance'] === 'A'): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle"><i class="fa-solid fa-user-xmark me-1"></i> Absent</span>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $rounds = [
                                        1 => $app['round_1'],
                                        2 => $app['round_2'],
                                        3 => $app['round_3'],
                                        4 => $app['round_4'],
                                        5 => $app['round_5'],
                                    ];
                                    foreach ($rounds as $i => $res): 
                                        if ($res === 'Y'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" title="Round <?= $i ?> Cleared">R<?= $i ?> <i class="fa-solid fa-check"></i></span>
                                        <?php elseif ($res === 'N'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle" title="Round <?= $i ?> Failed">R<?= $i ?> <i class="fa-solid fa-xmark"></i></span>
                                        <?php endif; 
                                    endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-md-end text-start mt-3 mt-md-0">
                            <span class="badge <?= $badgeClass ?> px-3 py-2 mb-2 fs-6"><?= htmlspecialchars($app['status']) ?></span>
                            <?php if (!empty($app['round_details'])): ?>
                                <div class="small text-muted mt-1" style="max-width: 250px;">
                                    <strong class="text-dark">Remarks:</strong> <span class="fst-italic"><?= nl2br(htmlspecialchars($app['round_details'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/footer.php'; ?>
</body>

</html>
