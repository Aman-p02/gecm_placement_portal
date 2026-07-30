<?php
/**
 * Placement Drives Page (Coming Soon)
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
    <title>Placement Drives - GEC Placement Portal</title>
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
                        <a class="nav-link active fw-medium" href="placement_drives.php">Placement Drives</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="track_applications.php">My Applications</a>
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
        <h3 class="mb-4">Available Placement Drives</h3>
        
        <div class="row g-4">
            <!-- Mock Company Card 1 -->
            <div class="col-md-6">
                <div class="custom-card h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1 text-dark">Tech Mahindra</h4>
                            <span class="badge bg-secondary">Software Developer</span>
                        </div>
                        <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i>2 days ago</span>
                    </div>
                    
                    <p class="text-muted mb-3 flex-grow-1">Hiring 2026 batch students. Excellent problem-solving skills and knowledge of web technologies required.</p>
                    
                    <div class="p-3 bg-light border rounded mb-4">
                        <h6 class="mb-2 text-dark"><i class="fa-solid fa-paperclip text-muted me-2"></i>Company Document</h6>
                        <p class="small text-muted mb-2">Contains salary breakdown, bond details, and criteria.</p>
                        <a href="#" class="btn btn-sm btn-outline-secondary w-100" target="_blank">
                            <i class="fa-solid fa-file-pdf text-danger me-1"></i> View Document (PDF)
                        </a>
                    </div>
                    
                    <button class="btn btn-accent w-100 fw-bold">Apply Now</button>
                </div>
            </div>

            <!-- Mock Company Card 2 -->
            <div class="col-md-6">
                <div class="custom-card h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1 text-dark">L&T Technology Services</h4>
                            <span class="badge bg-secondary">Embedded Engineer</span>
                        </div>
                        <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i>5 days ago</span>
                    </div>
                    
                    <p class="text-muted mb-3 flex-grow-1">Looking for EC/IC branch students with strong fundamentals in C programming and microcontrollers.</p>
                    
                    <div class="p-3 bg-light border rounded mb-4">
                        <h6 class="mb-2 text-dark"><i class="fa-solid fa-paperclip text-muted me-2"></i>Company Document</h6>
                        <p class="small text-muted mb-2">Official poster with requirements and 2-year bond agreement.</p>
                        <a href="#" class="btn btn-sm btn-outline-secondary w-100" target="_blank">
                            <i class="fa-solid fa-image text-primary me-1"></i> View Details (JPG)
                        </a>
                    </div>
                    
                    <button class="btn btn-accent w-100 fw-bold">Apply Now</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
