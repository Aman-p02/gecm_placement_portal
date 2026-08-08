<?php
/**
 * Placement Drives Page (Coming Soon)
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';

// Secure the page
require_login();
require_profile_completion($pdo);

$studentId = $_SESSION['student_id'];

// Fetch basic student info and branch
$stmt = $pdo->prepare("SELECT full_name, branch FROM tbl_students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

$studentBranch = $student['branch'];

// Check if student is already placed
$stmtCheckPlaced = $pdo->prepare("SELECT COUNT(*) FROM tbl_applications WHERE student_id = ? AND status = 'Selected'");
$stmtCheckPlaced->execute([$studentId]);
$isPlaced = $stmtCheckPlaced->fetchColumn() > 0;

// Handle application submission
$success = '';
$error = '';

if (isset($_SESSION['page_success'])) {
    $success = $_SESSION['page_success'];
    unset($_SESSION['page_success']);
}
if (isset($_SESSION['page_error'])) {
    $error = $_SESSION['page_error'];
    unset($_SESSION['page_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    $companyId = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
    
    if ($companyId) {
        if ($isPlaced) {
            $_SESSION['page_error'] = "You cannot apply because you are already placed in a company.";
            header("Location: placement_drives.php");
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO tbl_applications (student_id, company_id) VALUES (?, ?)");
            $stmt->execute([$studentId, $companyId]);
            $_SESSION['page_success'] = "Successfully applied!";
            header("Location: placement_drives.php");
            exit;
        } catch (PDOException $e) {
            // 23000 is integrity constraint violation (duplicate key)
            if ($e->getCode() == 23000) {
                $_SESSION['page_error'] = "You have already applied to this company.";
            } else {
                $_SESSION['page_error'] = "An error occurred while applying.";
            }
            header("Location: placement_drives.php");
            exit;
        }
    }
}

// Fetch available companies for the student's branch
// Also check if the student has already applied
$stmt = $pdo->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM tbl_applications a WHERE a.student_id = ? AND a.company_id = c.company_id) as has_applied
    FROM tbl_companies c
    JOIN tbl_company_branches cb ON c.company_id = cb.company_id
    WHERE cb.branch_name = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$studentId, $studentBranch]);
$companies = $stmt->fetchAll();

$distinctBatches = array_unique(array_column($companies, 'batch_year'));
sort($distinctBatches);

$filterBatch = filter_input(INPUT_GET, 'batch_year', FILTER_VALIDATE_INT) ?: '';
if ($filterBatch) {
    $companies = array_filter($companies, function($c) use ($filterBatch) {
        return $c['batch_year'] == $filterBatch;
    });
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Drives - GEC Placement Portal</title>
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
                        <a class="nav-link active fw-medium" href="placement_drives.php">Placement Drives</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="track_applications.php">My Applications</a>
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
        <h3 class="mb-4">Available Placement Drives</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (empty($companies)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-3"></i>
                    <h5>No companies available right now</h5>
                    <p>Check back later when placement drives are added for your branch.</p>
                </div>
            <?php else: ?>
                <?php foreach ($companies as $c): ?>
                    <div class="col-md-6">
                        <div class="custom-card h-100 d-flex flex-column border-top border-4 border-warning">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex gap-3 align-items-center">
                                    <?php if ($c['logo_path']): ?>
                                        <img src="../admin-module/<?= htmlspecialchars($c['logo_path']) ?>" alt="Logo" style="width: 50px; height: 50px; object-fit: contain; border-radius: 8px;">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fa-solid fa-building text-muted fs-4"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="mb-0 text-dark fw-bold"><?= htmlspecialchars($c['company_name']) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $deadlineTimestamp = strtotime($c['last_date_to_apply'] . ' 23:59:59');
                                $isExpired = $deadlineTimestamp < time();
                                
                                $diff = $deadlineTimestamp - time();
                                $daysLeft = ceil($diff / (60 * 60 * 24));
                                
                                $daysText = '';
                                if (!$isExpired) {
                                    if ($daysLeft > 1) {
                                        $daysText = "<span class='badge bg-warning text-dark ms-2'>$daysLeft days left</span>";
                                    } elseif ($daysLeft == 1) {
                                        $daysText = "<span class='badge bg-warning text-dark ms-2'>1 day left</span>";
                                    } elseif ($daysLeft == 0) {
                                        $daysText = "<span class='badge bg-danger ms-2'>Ends today</span>";
                                    }
                                }
                            ?>
                            <p class="text-muted mb-3 flex-grow-1">Last Date to Apply: <strong class="<?= $isExpired ? 'text-danger' : 'text-success' ?>"><?= date('d M Y', strtotime($c['last_date_to_apply'])) ?></strong> <?= $daysText ?></p>
                            
                            <div class="d-flex gap-2 mb-3">
                                <?php if (!empty($c['job_description_text'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#jobDescModal<?= $c['company_id'] ?>">
                                        <i class="fa-solid fa-file-lines me-1"></i> Job Details
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($c['document_path']): ?>
                                    <a href="../admin-module/<?= htmlspecialchars($c['document_path']) ?>" class="btn btn-sm btn-outline-secondary flex-grow-1" target="_blank">
                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i> Document
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($c['has_applied'] > 0): ?>
                                <button class="btn btn-secondary w-100 fw-bold" disabled><i class="fa-solid fa-check me-2"></i>Applied</button>
                            <?php elseif ($isPlaced): ?>
                                <button class="btn btn-success w-100 fw-bold" disabled><i class="fa-solid fa-briefcase me-2"></i>Already Placed</button>
                            <?php elseif ($isExpired): ?>
                                <button class="btn btn-danger w-100 fw-bold" disabled>Deadline Passed</button>
                            <?php else: ?>
                                <form action="placement_drives.php" method="POST" class="mt-auto">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="action" value="apply">
                                    <input type="hidden" name="company_id" value="<?= htmlspecialchars($c['company_id']) ?>">
                                    <button type="submit" class="btn btn-accent w-100 fw-bold">Apply Now</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Job Description Modals -->
    <?php if (!empty($companies)): ?>
        <?php foreach ($companies as $c): ?>
            <?php if (!empty($c['job_description_text'])): ?>
            <div class="modal fade" id="jobDescModal<?= $c['company_id'] ?>" tabindex="-1" aria-labelledby="jobDescModalLabel<?= $c['company_id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="jobDescModalLabel<?= $c['company_id'] ?>">Job Details - <?= htmlspecialchars($c['company_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <?= $c['job_description_text'] ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

