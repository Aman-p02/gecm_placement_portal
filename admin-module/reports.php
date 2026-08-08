<?php
/**
 * Placement Reports
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';

// Secure the page
require_admin_login();

$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];
$adminRole = $_SESSION['admin_role'];
$adminBranch = $_SESSION['admin_branch'];

// Filters
$filterCompany = filter_input(INPUT_GET, 'company', FILTER_SANITIZE_STRING) ?: '';
$filterBatch = filter_input(INPUT_GET, 'batch_year', FILTER_VALIDATE_INT) ?: '';
$filterBranch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING) ?: '';
$filterDrive = filter_input(INPUT_GET, 'drive_type', FILTER_SANITIZE_STRING) ?: '';

// For subadmin, force the branch filter to their own branch
if ($adminRole !== 'superadmin') {
    $filterBranch = $adminBranch;
}

// Build Query
$query = "
    SELECT s.enrollment_no, s.full_name, s.branch, c.company_name, c.batch_year, a.applied_at, c.drive_type
    FROM tbl_applications a
    JOIN tbl_students s ON a.student_id = s.student_id
    JOIN tbl_companies c ON a.company_id = c.company_id
    WHERE a.status = 'Selected'
";
$params = [];

if ($filterCompany) {
    $query .= " AND c.company_name = ?";
    $params[] = $filterCompany;
}
if ($filterBatch) {
    $query .= " AND c.batch_year = ?";
    $params[] = $filterBatch;
}
if ($filterBranch) {
    $query .= " AND s.branch = ?";
    $params[] = $filterBranch;
}
if ($filterDrive) {
    $query .= " AND c.drive_type = ?";
    $params[] = $filterDrive;
}

$query .= " ORDER BY c.batch_year DESC, c.company_name ASC, s.full_name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=placement_report_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');

    // Output headers
    fputcsv($output, ['Enrollment No', 'Student Name', 'Branch', 'Company', 'Drive Type']);

    foreach ($reports as $row) {
        fputcsv($output, [
            '="' . $row['enrollment_no'] . '"', // Formula forces Excel to treat as text
            $row['full_name'],
            $row['branch'],
            $row['company_name'],
            $row['drive_type'] ?? 'On Campus'
        ]);
    }

    fclose($output);
    exit;
}

// Get distinct companies and batches for filters
$compStmt = $pdo->query("SELECT DISTINCT company_name FROM tbl_companies ORDER BY company_name");
$allCompanies = $compStmt->fetchAll(PDO::FETCH_COLUMN);

$batchStmt = $pdo->query("SELECT DISTINCT batch_year FROM tbl_companies WHERE batch_year IS NOT NULL AND batch_year != 0 ORDER BY batch_year DESC");
$allBatches = $batchStmt->fetchAll(PDO::FETCH_COLUMN);

if ($adminRole === 'superadmin') {
    $branchStmt = $pdo->query("SELECT branch_name AS branch FROM tbl_branches ORDER BY branch_name ASC");
    $allBranches = $branchStmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Reports - GEC Admin</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>

<body class="<?= ($adminRole === 'superadmin') ? 'theme-superadmin' : '' ?>">
    <div class="d-flex">
        <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <a href="?export=csv&company=<?= urlencode($filterCompany) ?>&batch_year=<?= urlencode($filterBatch) ?>&branch=<?= urlencode($filterBranch) ?>&drive_type=<?= urlencode($filterDrive) ?>"
                        class="btn btn-success">
                        <i class="fa-solid fa-file-excel me-2"></i> Export to Excel
                    </a>
                </div>

                <!-- Filters -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body">
                        <form method="GET" action="reports.php" class="row g-3">
                            <div class="col-md-2">
                                <select name="batch_year" class="form-select">
                                    <option value="">All Batches</option>
                                    <?php foreach ($allBatches as $b): ?>
                                        <option value="<?= htmlspecialchars($b) ?>" <?= $filterBatch == $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="drive_type" class="form-select">
                                    <option value="">All Drives</option>
                                    <option value="On Campus" <?= $filterDrive === 'On Campus' ? 'selected' : '' ?>>On
                                        Campus</option>
                                    <option value="Off Campus" <?= $filterDrive === 'Off Campus' ? 'selected' : '' ?>>Off
                                        Campus</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="company" class="form-select">
                                    <option value="">All Companies</option>
                                    <?php foreach ($allCompanies as $comp): ?>
                                        <option value="<?= htmlspecialchars($comp) ?>" <?= $filterCompany === $comp ? 'selected' : '' ?>><?= htmlspecialchars($comp) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($adminRole === 'superadmin'): ?>
                                <div class="col-md-3">
                                    <select name="branch" class="form-select">
                                        <option value="">All Branches</option>
                                        <?php foreach ($allBranches as $br): ?>
                                            <option value="<?= htmlspecialchars($br) ?>" <?= $filterBranch === $br ? 'selected' : '' ?>><?= htmlspecialchars($br) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                <a href="reports.php" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white p-4 rounded shadow-sm">
                    <?php if (count($reports) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Enrollment No</th>
                                        <th>Student Name</th>
                                        <th>Branch</th>
                                        <th>Company</th>
                                        <th>Drive Type</th>
                                        <th>Batch Year</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['enrollment_no']) ?></td>
                                            <td class="fw-medium"><?= htmlspecialchars($row['full_name']) ?></td>
                                            <td><?= htmlspecialchars($row['branch']) ?></td>
                                            <td><?= htmlspecialchars($row['company_name']) ?></td>
                                            <td>
                                                <span
                                                    class="badge <?= ($row['drive_type'] ?? 'On Campus') === 'Off Campus' ? 'bg-warning text-dark' : 'bg-info text-dark' ?>">
                                                    <?= htmlspecialchars($row['drive_type'] ?? 'On Campus') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['batch_year'] ?: 'N/A') ?></td>
                                            <td><span class="badge bg-success">Placed</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-box-open fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No placement records found</h5>
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