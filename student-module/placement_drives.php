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
                                        <h4 class="mb-1 text-dark"><?= htmlspecialchars($c['company_name']) ?> <span class="badge bg-primary fs-6 align-middle ms-1"><?= htmlspecialchars($c['batch_year'] ?? 'N/A') ?></span></h4>
                                    </div>
                                </div>
                                <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i><?= date('M d, Y', strtotime($c['created_at'])) ?></span>
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
                            
                            <?php if (!empty($c['job_description_text'])): ?>
                            <div class="mb-3 text-dark small" style="white-space: pre-line; background-color: #f9f9f9; padding: 10px; border-radius: 5px; border-left: 3px solid var(--accent-coral);">
                                <?= htmlspecialchars($c['job_description_text']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($c['document_path']): ?>
                            <div class="p-3 bg-light border rounded mb-4">
                                <h6 class="mb-2 text-dark"><i class="fa-solid fa-paperclip text-muted me-2"></i>Company Document</h6>
                                <a href="../admin-module/<?= htmlspecialchars($c['document_path']) ?>" class="btn btn-sm btn-outline-secondary w-100" target="_blank">
                                    <i class="fa-solid fa-file-pdf text-danger me-1"></i> View Document (PDF)
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($c['has_applied'] > 0): ?>
                                <button class="btn btn-secondary w-100 fw-bold" disabled><i class="fa-solid fa-check me-2"></i>Applied</button>
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

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

