<?php
/**
 * View Applicants
 * Allows admins to view students who applied and update their status.
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';
require_once __DIR__ . '/../includes/mailer.php';

// Secure the page
require_admin_login();

$adminRole = $_SESSION['admin_role'];
$adminBranch = $_SESSION['admin_branch'];
$adminName = $_SESSION['admin_name'];

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

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    
    $applicationId = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    $status = sanitize_input($_POST['status'] ?? '');
    $roundDetails = sanitize_input($_POST['round_details'] ?? '');
    
    $validStatuses = ['Applied', 'In Progress', 'Selected', 'Rejected'];
    
    if ($applicationId && in_array($status, $validStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE tbl_applications SET status = ?, round_details = ? WHERE application_id = ?");
            $stmt->execute([$status, $roundDetails, $applicationId]);
            
            // Fetch info for email
            $infoStmt = $pdo->prepare("
                SELECT s.email, s.full_name, c.company_name 
                FROM tbl_applications a
                JOIN tbl_students s ON a.student_id = s.student_id
                JOIN tbl_companies c ON a.company_id = c.company_id
                WHERE a.application_id = ?
            ");
            $infoStmt->execute([$applicationId]);
            $info = $infoStmt->fetch();
            
            if ($info) {
                sendStatusUpdateEmail($info['email'], $info['full_name'], $info['company_name'], $status);
            }
            
            $_SESSION['page_success'] = "Application status updated successfully.";
        } catch (Exception $e) {
            $_SESSION['page_error'] = "Error updating status.";
        }
    } else {
        $_SESSION['page_error'] = "Invalid status update.";
    }
    header("Location: view_applicants.php");
    exit;
}

// Fetch Applications Based on Role
$filterBranch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING) ?: '';

if ($adminRole === 'superadmin') {
    // Superadmin sees all applications, or filtered by branch
    if ($filterBranch) {
        $stmt = $pdo->prepare("
            SELECT a.application_id, a.status, a.round_details, a.applied_at,
                   s.full_name as student_name, s.enrollment_no, s.branch as student_branch,
                   p.resume_path, p.profile_pic,
                   c.company_name, c.batch_year
            FROM tbl_applications a
            JOIN tbl_students s ON a.student_id = s.student_id
            LEFT JOIN tbl_student_profile p ON s.student_id = p.student_id
            JOIN tbl_companies c ON a.company_id = c.company_id
            WHERE s.branch = ?
            ORDER BY a.applied_at DESC
        ");
        $stmt->execute([$filterBranch]);
    } else {
        $stmt = $pdo->prepare("
            SELECT a.application_id, a.status, a.round_details, a.applied_at,
                   s.full_name as student_name, s.enrollment_no, s.branch as student_branch,
                   p.resume_path, p.profile_pic,
                   c.company_name, c.batch_year
            FROM tbl_applications a
            JOIN tbl_students s ON a.student_id = s.student_id
            LEFT JOIN tbl_student_profile p ON s.student_id = p.student_id
            JOIN tbl_companies c ON a.company_id = c.company_id
            ORDER BY a.applied_at DESC
        ");
        $stmt->execute();
    }
} else {
    // Subadmin sees applications where the student's branch matches the subadmin's branch
    // (Meaning: they only manage students from their own department)
    $stmt = $pdo->prepare("
        SELECT a.application_id, a.status, a.round_details, a.applied_at,
               s.full_name as student_name, s.enrollment_no, s.branch as student_branch,
               p.resume_path, p.profile_pic,
               c.company_name, c.batch_year
        FROM tbl_applications a
        JOIN tbl_students s ON a.student_id = s.student_id
        LEFT JOIN tbl_student_profile p ON s.student_id = p.student_id
        JOIN tbl_companies c ON a.company_id = c.company_id
        WHERE s.branch = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$adminBranch]);
}
$applications = $stmt->fetchAll();

// Get distinct companies for the filter dropdown
$distinctCompanies = array_unique(array_column($applications, 'company_name'));
sort($distinctCompanies);

// Get distinct batch years
$distinctBatches = array_unique(array_column($applications, 'batch_year'));
sort($distinctBatches);

// Additional PHP-based Filtering
$filterCompany = filter_input(INPUT_GET, 'company', FILTER_SANITIZE_STRING) ?: '';
$filterStatus = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) ?: '';
$filterBatch = filter_input(INPUT_GET, 'batch_year', FILTER_VALIDATE_INT) ?: '';

if ($filterCompany || $filterStatus || $filterBatch) {
    $applications = array_filter($applications, function($app) use ($filterCompany, $filterStatus, $filterBatch) {
        if ($filterCompany && $app['company_name'] !== $filterCompany) return false;
        if ($filterStatus && $app['status'] !== $filterStatus) return false;
        if ($filterBatch && $app['batch_year'] != $filterBatch) return false;
        return true;
    });
}

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=applicants_export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    // Output headers
    fputcsv($output, ['Applicant Name', 'Enrollment No', 'Branch', 'Company', 'Batch Year', 'Status', 'Applied At', 'Round Details']);
    
    foreach ($applications as $app) {
        fputcsv($output, [
            $app['student_name'],
            $app['enrollment_no'],
            $app['student_branch'],
            $app['company_name'],
            $app['batch_year'],
            $app['status'],
            date('d M Y, h:i A', strtotime($app['applied_at'])),
            $app['round_details'] ?: 'N/A'
        ]);
    }
    
    fclose($output);
    exit;
}
// Get distinct branches for superadmin filter dropdown
$distinctBranches = [];
if ($adminRole === 'superadmin') {
    $stmtB = $pdo->query("SELECT DISTINCT branch FROM tbl_students ORDER BY branch ASC");
    $distinctBranches = $stmtB->fetchAll(PDO::FETCH_COLUMN);
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applicants - GEC Placement</title>
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
            <?php if ($adminRole === 'superadmin'): ?>
                <a href="manage_admins.php"><i class="fa-solid fa-users-gear me-2"></i> Manage Admins</a>
            <?php endif; ?>
            <a href="all_students.php"><i class="fa-solid fa-user-graduate me-2"></i> All Students</a>
            <a href="manage_companies.php"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
            <a href="view_applicants.php" class="active"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-muted">Applicant Tracking</h5>
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
                    <div class="mb-4 bg-light p-3 rounded border">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <h5 class="m-0" style="color: var(--primary-navy);"><i class="fa-solid fa-user-graduate me-2"></i><?= $adminRole === 'superadmin' ? 'All Applicants' : 'Applicants in ' . htmlspecialchars($adminBranch) ?></h5>
                            </div>
                            <div class="col-md-7">
                                <form action="" method="GET" class="row g-2">
                                    <?php if ($adminRole === 'superadmin'): ?>
                                    <div class="col-auto">
                                        <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Branches</option>
                                            <?php foreach($distinctBranches as $db): ?>
                                                <option value="<?= htmlspecialchars($db) ?>" <?= $filterBranch === $db ? 'selected' : '' ?>><?= htmlspecialchars($db) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>

                                    <div class="col-auto">
                                        <select name="company" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Companies</option>
                                            <?php foreach($distinctCompanies as $dc): ?>
                                                <option value="<?= htmlspecialchars($dc) ?>" <?= $filterCompany === $dc ? 'selected' : '' ?>><?= htmlspecialchars($dc) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <select name="batch_year" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Batches</option>
                                            <?php foreach($distinctBatches as $dbatch): ?>
                                                <?php if($dbatch): ?>
                                                    <option value="<?= htmlspecialchars($dbatch) ?>" <?= $filterBatch == $dbatch ? 'selected' : '' ?>><?= htmlspecialchars($dbatch) ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Statuses</option>
                                            <option value="Applied" <?= $filterStatus === 'Applied' ? 'selected' : '' ?>>Applied</option>
                                            <option value="In Progress" <?= $filterStatus === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Selected" <?= $filterStatus === 'Selected' ? 'selected' : '' ?>>Selected</option>
                                            <option value="Rejected" <?= $filterStatus === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <noscript><div class="col-auto"><button type="submit" class="btn btn-sm btn-secondary">Filter</button></div></noscript>
                                </form>
                            </div>
                            
                            <div class="col-md-2 text-end">
                                <?php
                                    $exportParams = $_GET;
                                    $exportParams['export'] = 'csv';
                                    $exportUrl = '?' . http_build_query($exportParams);
                                ?>
                                <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn btn-sm btn-success shadow-sm w-100 mb-2">
                                    <i class="fa-solid fa-file-excel me-1"></i> Export to Excel
                                </a>
                                <span class="badge bg-white text-dark border w-100 py-1">Total: <?= count($applications) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($applications)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 mb-3"></i>
                            <h5>No applications received yet.</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Enrollment No.</th>
                                        <th>Company</th>
                                        <th>Resume</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): 
                                        $badgeClass = 'bg-secondary';
                                        if ($app['status'] === 'Applied' || $app['status'] === 'In Progress') $badgeClass = 'bg-warning text-dark';
                                        if ($app['status'] === 'Selected') $badgeClass = 'bg-success';
                                        if ($app['status'] === 'Rejected') $badgeClass = 'bg-danger';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <?php if ($app['profile_pic']): ?>
                                                        <img src="../student-module/<?= htmlspecialchars($app['profile_pic']) ?>" alt="Pic" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                                    <?php else: ?>
                                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                                            <?= strtoupper(substr($app['student_name'], 0, 1)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong class="d-block"><?= htmlspecialchars($app['student_name']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= htmlspecialchars($app['enrollment_no']) ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($app['company_name']) ?> <span class="badge bg-primary ms-1"><?= htmlspecialchars($app['batch_year'] ?? 'N/A') ?></span></div>
                                                <div class="small text-muted"><i class="fa-regular fa-clock me-1"></i><?= date('d M, h:i A', strtotime($app['applied_at'])) ?></div>
                                            </td>
                                            <td>
                                                <?php if ($app['resume_path']): ?>
                                                    <a href="../student-module/<?= htmlspecialchars($app['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-pdf me-1"></i> View</a>
                                                <?php else: ?>
                                                    <span class="text-muted small">No Resume</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($app['status']) ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#updateModal<?= $app['application_id'] ?>">
                                                    Update
                                                </button>
                                                
                                                <!-- Update Modal -->
                                                <div class="modal fade" id="updateModal<?= $app['application_id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Update Application Status</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="view_applicants.php" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Student</label>
                                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($app['student_name']) ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Update Status</label>
                                                                        <select name="status" class="form-select">
                                                                            <option value="Applied" <?= $app['status'] == 'Applied' ? 'selected' : '' ?>>Applied</option>
                                                                            <option value="In Progress" <?= $app['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                                            <option value="Selected" <?= $app['status'] == 'Selected' ? 'selected' : '' ?>>Selected</option>
                                                                            <option value="Rejected" <?= $app['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Round Details / Feedback</label>
                                                                        <textarea name="round_details" class="form-control" rows="3"><?= htmlspecialchars($app['round_details']) ?></textarea>
                                                                        <small class="text-muted">e.g. Cleared Aptitude test. Pending HR Round.</small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-accent">Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
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


