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

// Handle CSV Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_status') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_FILES['status_csv']) && $_FILES['status_csv']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['status_csv']['name'], PATHINFO_EXTENSION));
        if ($ext === 'csv') {
            $file = fopen($_FILES['status_csv']['tmp_name'], 'r');
            $headers = fgetcsv($file); // Read headers
            
            if ($headers) {
                // Strip BOM from the first header if present
                $headers[0] = preg_replace('/^[\xef\xbb\xbf]+/', '', $headers[0]);
                
                // Find column indices
                $appIdIdx = array_search('Application ID', $headers);
                $statusIdx = array_search('Status', $headers);
                $attIdx = array_search('Attendance (P/A)', $headers);
                $r1Idx = array_search('Round 1 (Y/N)', $headers);
                $r2Idx = array_search('Round 2 (Y/N)', $headers);
                $r3Idx = array_search('Round 3 (Y/N)', $headers);
                $r4Idx = array_search('Round 4 (Y/N)', $headers);
                $r5Idx = array_search('Round 5 (Y/N)', $headers);
                $remarksIdx = array_search('Remarks', $headers);
                
                // Fallback for older export formats if they use 'Round Details'
                if ($remarksIdx === false) $remarksIdx = array_search('Round Details', $headers);
                
                if ($appIdIdx !== false && $statusIdx !== false) {
                    try {
                        $pdo->beginTransaction();
                        $stmt = $pdo->prepare("UPDATE tbl_applications SET status = ?, attendance = ?, round_1 = ?, round_2 = ?, round_3 = ?, round_4 = ?, round_5 = ?, round_details = ? WHERE application_id = ?");
                        
                        $updateCount = 0;
                        $skippedCount = 0;
                        
                        while (($row = fgetcsv($file)) !== false) {
                            $appId = (int)($row[$appIdIdx] ?? 0);
                            $status = trim($row[$statusIdx] ?? '');
                            
                            $att = $attIdx !== false ? strtoupper(trim($row[$attIdx] ?? '')) : '';
                            $r1 = $r1Idx !== false ? strtoupper(trim($row[$r1Idx] ?? '')) : '';
                            $r2 = $r2Idx !== false ? strtoupper(trim($row[$r2Idx] ?? '')) : '';
                            $r3 = $r3Idx !== false ? strtoupper(trim($row[$r3Idx] ?? '')) : '';
                            $r4 = $r4Idx !== false ? strtoupper(trim($row[$r4Idx] ?? '')) : '';
                            $r5 = $r5Idx !== false ? strtoupper(trim($row[$r5Idx] ?? '')) : '';
                            $remarks = $remarksIdx !== false ? trim($row[$remarksIdx] ?? '') : '';
                            
                            // Validate enums
                            $att = in_array($att, ['P', 'A']) ? $att : null;
                            $r1 = in_array($r1, ['Y', 'N']) ? $r1 : null;
                            $r2 = in_array($r2, ['Y', 'N']) ? $r2 : null;
                            $r3 = in_array($r3, ['Y', 'N']) ? $r3 : null;
                            $r4 = in_array($r4, ['Y', 'N']) ? $r4 : null;
                            $r5 = in_array($r5, ['Y', 'N']) ? $r5 : null;
                            
                            if ($appId > 0 && $status !== '') {
                                $stmt->execute([$status, $att, $r1, $r2, $r3, $r4, $r5, $remarks, $appId]);
                                $updateCount++;
                            } else {
                                $skippedCount++;
                            }
                        }
                        
                        $pdo->commit();
                        $_SESSION['page_success'] = "Successfully updated $updateCount application(s).";
                        if ($skippedCount > 0) {
                            $_SESSION['page_error'] = "Note: $skippedCount row(s) were skipped due to invalid Status or missing Application ID.";
                        }
                        
                        // Auto-block logic for students with 3 or more absences
                        $stmtCheck = $pdo->query("
                            SELECT s.student_id, s.email, s.full_name
                            FROM tbl_students s
                            WHERE s.is_blocked = 0 
                              AND (SELECT COUNT(*) FROM tbl_applications a WHERE a.student_id = s.student_id AND a.attendance = 'A') >= 3
                        ");
                        $studentsToBlock = $stmtCheck->fetchAll();
                        
                        if (count($studentsToBlock) > 0) {
                            $blockStmt = $pdo->prepare("UPDATE tbl_students SET is_blocked = 1 WHERE student_id = ?");
                            foreach ($studentsToBlock as $stu) {
                                $blockStmt->execute([$stu['student_id']]);
                                if (!empty($stu['email'])) {
                                    sendBlockStatusEmail($stu['email'], $stu['full_name'], 1, "Disciplinary issue - 3 times not attending drive after registering");
                                }
                            }
                            $_SESSION['page_success'] .= " Auto-blocked " . count($studentsToBlock) . " student(s) for 3 or more absences.";
                        }
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $_SESSION['page_error'] = "Error updating statuses.";
                    }
                } else {
                    $_SESSION['page_error'] = "Invalid CSV format. Please export first and keep 'Application ID' and 'Status' columns.";
                }
            } else {
                $_SESSION['page_error'] = "The CSV file is empty.";
            }
            fclose($file);
        } else {
            $_SESSION['page_error'] = "Please upload a valid CSV file.";
        }
    } else {
        $_SESSION['page_error'] = "Error uploading file.";
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
                   a.attendance, a.round_1, a.round_2, a.round_3, a.round_4, a.round_5,
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
                   a.attendance, a.round_1, a.round_2, a.round_3, a.round_4, a.round_5,
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
               a.attendance, a.round_1, a.round_2, a.round_3, a.round_4, a.round_5,
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
    fputcsv($output, ['Application ID', 'Applicant Name', 'Enrollment No', 'Branch', 'Company', 'Batch Year', 'Status', 'Attendance (P/A)', 'Round 1 (Y/N)', 'Round 2 (Y/N)', 'Round 3 (Y/N)', 'Round 4 (Y/N)', 'Round 5 (Y/N)', 'Applied At', 'Remarks']);
    
    foreach ($applications as $app) {
        fputcsv($output, [
            $app['application_id'],
            $app['student_name'],
            $app['enrollment_no'],
            $app['student_branch'],
            $app['company_name'],
            $app['batch_year'],
            $app['status'],
            $app['attendance'] ?: '',
            $app['round_1'] ?: '',
            $app['round_2'] ?: '',
            $app['round_3'] ?: '',
            $app['round_4'] ?: '',
            $app['round_5'] ?: '',
            date('d M Y, h:i A', strtotime($app['applied_at'])),
            $app['round_details'] ?: ''
        ]);
    }
    
    fclose($output);
    exit;
}
// Get distinct branches for superadmin filter dropdown
$distinctBranches = [];
if ($adminRole === 'superadmin') {
    $stmtB = $pdo->query("SELECT branch_name AS branch FROM tbl_branches ORDER BY branch_name ASC");
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
        <div class="sidebar-overlay" id="sidebarOverlay"></div><div class="sidebar" style="width: 250px;">
            <h4 class="text-center mb-4 px-3" style="color: var(--accent-coral);">GEC Admin</h4>
            <a href="dashboard.php"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <?php if ($adminRole === 'superadmin'): ?>
                <a href="manage_admins.php"><i class="fa-solid fa-users-gear me-2"></i> Manage Admins</a>
            <?php endif; ?>
            <a href="all_students.php"><i class="fa-solid fa-user-graduate me-2"></i> All Students</a>
            <a href="manage_companies.php"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
            <a href="view_applicants.php" class="active"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            <a href="reports.php"><i class="fa-solid fa-chart-pie me-2"></i> Reports</a>
            <a href="manage_activities.php"><i class="fa-solid fa-list-check me-2"></i> Manage Activities</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center"><button class="btn btn-sm btn-outline-secondary me-3" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button><h5 class="m-0 text-muted">Applicant Tracking</h5></div>
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
                        <div class="row g-3 align-items-center justify-content-between">
                            <div class="col-md-auto">
                                <form action="" method="GET" class="row g-2">
                                    <?php if ($adminRole === 'superadmin'): ?>
                                    <div class="col-auto">
                                        <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 125px;">
                                            <option value="">All Branches</option>
                                            <?php foreach($distinctBranches as $db): ?>
                                                <option value="<?= htmlspecialchars($db) ?>" <?= $filterBranch === $db ? 'selected' : '' ?>><?= htmlspecialchars($db) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>

                                    <div class="col-auto">
                                        <select name="company" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 135px;">
                                            <option value="">All Companies</option>
                                            <?php foreach($distinctCompanies as $dc): ?>
                                                <option value="<?= htmlspecialchars($dc) ?>" <?= $filterCompany === $dc ? 'selected' : '' ?>><?= htmlspecialchars($dc) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <select name="batch_year" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 120px;">
                                            <option value="">All Batches</option>
                                            <?php foreach($distinctBatches as $dbatch): ?>
                                                <?php if($dbatch): ?>
                                                    <option value="<?= htmlspecialchars($dbatch) ?>" <?= $filterBatch == $dbatch ? 'selected' : '' ?>><?= htmlspecialchars($dbatch) ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 125px;">
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
                            
                            <div class="col-md-auto text-md-end d-flex gap-2 align-items-center flex-wrap justify-content-md-end">
                                <?php
                                    $exportParams = $_GET;
                                    $exportParams['export'] = 'csv';
                                    $exportUrl = '?' . http_build_query($exportParams);
                                ?>
                                <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn btn-sm btn-success shadow-sm">
                                    <i class="fa-solid fa-file-excel me-1"></i> Export
                                </a>
                                <button type="button" class="btn btn-sm btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="fa-solid fa-file-import me-1"></i> Import
                                </button>
                                <span class="badge bg-white text-dark border py-2">Total: <?= count($applications) ?></span>
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
    
    <!-- Import Status Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel"><i class="fa-solid fa-file-import me-2"></i>Import Status (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="view_applicants.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="import_status">
                        
                        <div class="alert alert-info small">
                            <i class="fa-solid fa-circle-info me-1"></i> 
                            Please export the list first, update the <strong>Status</strong> and <strong>Round Details</strong> columns, and then import the same CSV file here. Do not remove the <strong>Application ID</strong> column.
                        </div>
                        
                        <div class="mb-3">
                            <label for="status_csv" class="form-label">Upload CSV File</label>
                            <input class="form-control" type="file" id="status_csv" name="status_csv" accept=".csv" required>
                        </div>
                        
                        <div class="form-text">
                            <strong>Valid Statuses:</strong> Applied, In Progress, Selected, Rejected
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i> Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>



