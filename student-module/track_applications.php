<?php
/**
 * Track Applications Page (Coming Soon / UI Mock)
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';

// Secure the page
require_login();

$studentId = $_SESSION['student_id'];

// Fetch basic student info for navbar
$stmt = $pdo->prepare("SELECT full_name FROM tbl_students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - GEC Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5" style="margin-top: 2rem;">
        <h3 class="mb-4">My Applications</h3>
        
        <div class="custom-card mb-4 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Tata Consultancy Services (TCS)</h5>
                    <p class="text-muted mb-0 small">Applied on: 12 Aug 2026</p>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2">In Progress</span>
            </div>
            <hr class="text-muted my-3">
            <p class="mb-0"><strong>Status:</strong> Cleared Aptitude Test. Pending Technical Interview (Round 2).</p>
        </div>

        <div class="custom-card mb-4 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Infosys</h5>
                    <p class="text-muted mb-0 small">Applied on: 05 Aug 2026</p>
                </div>
                <span class="badge bg-danger px-3 py-2">Rejected</span>
            </div>
            <hr class="text-muted my-3">
            <p class="mb-0"><strong>Status:</strong> Not selected in the HR Round.</p>
        </div>

        <div class="custom-card mb-4 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Cognizant</h5>
                    <p class="text-muted mb-0 small">Applied on: 01 Aug 2026</p>
                </div>
                <span class="badge bg-success px-3 py-2">Selected</span>
            </div>
            <hr class="text-muted my-3">
            <p class="mb-0"><strong>Status:</strong> Congratulations! You have been selected. Offer letter sent to email.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
