<?php
/**
 * Manage Companies (Admin)
 * Allows adding and viewing companies based on role.
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';

// Secure the page
require_admin_login();

$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];
$adminRole = $_SESSION['admin_role'];
$adminBranch = $_SESSION['admin_branch'];

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

$allBranches = [
    'Computer Engineering',
    'Information Technology',
    'Mechanical Engineering',
    'Civil Engineering',
    'Electrical Engineering'
];

// Handle Add Company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_company') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    
    $companyName = sanitize_input($_POST['company_name'] ?? '');
    $batchYear = filter_input(INPUT_POST, 'batch_year', FILTER_VALIDATE_INT) ?: date('Y');
    $lastDate = sanitize_input($_POST['last_date'] ?? '');
    
    // Determine branches (Now all admins can select multiple branches)
    $selectedBranches = $_POST['branches'] ?? [];
    
    if (empty($companyName) || empty($lastDate) || empty($selectedBranches)) {
        $_SESSION['page_error'] = "Company Name, Last Date, and at least one Branch are required.";
        header("Location: manage_companies.php");
        exit;
    } else {
        try {
            $pdo->beginTransaction();
            
            // Handle Files
            $uploadDir = __DIR__ . '/uploads/companies/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $logoPath = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $newLogoName = uniqid('logo_') . '.' . $ext;
                    move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newLogoName);
                    $logoPath = 'uploads/companies/' . $newLogoName;
                } else {
                    throw new Exception("Logo must be an image (jpg, png, webp).");
                }
            }
            
            $docPath = null;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $newDocName = uniqid('doc_') . '.pdf';
                    move_uploaded_file($_FILES['document']['tmp_name'], $uploadDir . $newDocName);
                    $docPath = 'uploads/companies/' . $newDocName;
                } else {
                    throw new Exception("Document must be a PDF.");
                }
            }
            
            // Insert Company
            $stmt = $pdo->prepare("INSERT INTO tbl_companies (company_name, batch_year, logo_path, document_path, last_date_to_apply, added_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$companyName, $batchYear, $logoPath, $docPath, $lastDate, $adminId]);
            $companyId = $pdo->lastInsertId();
            
            // Insert Branches
            $stmtBranch = $pdo->prepare("INSERT INTO tbl_company_branches (company_id, branch_name) VALUES (?, ?)");
            foreach ($selectedBranches as $b) {
                $stmtBranch->execute([$companyId, $b]);
            }
            
            $pdo->commit();
            $_SESSION['page_success'] = "Company added successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['page_error'] = $e->getMessage();
        }
        header("Location: manage_companies.php");
        exit;
    }
}

// Handle Delete Company (Superadmin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_company') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    
    if ($adminRole !== 'superadmin') {
        $_SESSION['page_error'] = "Unauthorized action.";
        header("Location: manage_companies.php");
        exit;
    }
    
    $companyId = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
    
    if ($companyId) {
        $stmt = $pdo->prepare("DELETE FROM tbl_companies WHERE company_id = ?");
        if ($stmt->execute([$companyId])) {
            $_SESSION['page_success'] = "Company deleted successfully.";
        } else {
            $_SESSION['page_error'] = "Failed to delete company.";
        }
        header("Location: manage_companies.php");
        exit;
    }
}

// Fetch Companies
$companies = [];
if ($adminRole === 'superadmin') {
    $stmt = $pdo->query("SELECT c.*, a.full_name as added_by_name FROM tbl_companies c JOIN tbl_admins a ON c.added_by = a.admin_id ORDER BY c.created_at DESC");
    $companies = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT c.*, a.full_name as added_by_name 
        FROM tbl_companies c 
        JOIN tbl_company_branches cb ON c.company_id = cb.company_id
        JOIN tbl_admins a ON c.added_by = a.admin_id
        WHERE cb.branch_name = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$adminBranch]);
    $companies = $stmt->fetchAll();
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - GEC Placement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            <?php if ($adminRole === 'superadmin'): ?>
            --primary-navy: #000000; /* Pure Black */
            --accent-coral: #F1C40F; /* Gold Accent */
            <?php else: ?>
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
            <?php endif; ?>
        }
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: var(--primary-navy); color: white; padding-top: 20px; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; display: block; padding: 12px 20px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--accent-coral); }
        .topbar { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .custom-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-accent { background-color: var(--accent-coral); color: white; font-weight: 500; }
        .btn-accent:hover { background-color: #d14d3f; color: white; }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <h4 class="text-center mb-4 px-3" style="color: var(--accent-coral);">GEC Admin</h4>
            <a href="dashboard.php"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <?php if ($adminRole === 'superadmin'): ?>
                <a href="manage_admins.php"><i class="fa-solid fa-users-gear me-2"></i> Manage Admins</a>
            <?php endif; ?>
            <a href="all_students.php"><i class="fa-solid fa-user-graduate me-2"></i> All Students</a>
            <a href="manage_companies.php" class="active"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
            <a href="view_applicants.php"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-muted">Manage Companies</h5>
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

                <div class="row">
                    <!-- Add Company Form -->
                    <div class="col-md-4 mb-4">
                        <div class="custom-card border-top border-4 border-warning">
                            <h5 class="mb-4" style="color: var(--primary-navy);"><i class="fa-solid fa-plus-circle me-2"></i>Add New Company</h5>
                            <form action="manage_companies.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="action" value="add_company">
                                
                                <div class="mb-3">
                                    <label class="form-label small">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small">Batch Year (Target Batch)</label>
                                    <input type="number" class="form-control" name="batch_year" value="<?= date('Y') ?>" min="2000" max="2100" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small">Last Date to Apply</label>
                                    <input type="date" class="form-control" name="last_date" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small">Company Logo (Image)</label>
                                    <input type="file" class="form-control" name="logo" accept="image/jpeg, image/png, image/webp">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small">Job Description (PDF)</label>
                                    <input type="file" class="form-control" name="document" accept="application/pdf">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small">Eligible Branches</label>
                                    <div class="p-2 border rounded bg-light">
                                        <?php foreach ($allBranches as $b): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="branches[]" value="<?= htmlspecialchars($b) ?>" id="branch_<?= md5($b) ?>">
                                                <label class="form-check-label small" for="branch_<?= md5($b) ?>"><?= htmlspecialchars($b) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-accent w-100">Add Company</button>
                            </form>
                        </div>
                    </div>

                    <!-- Company List -->
                    <div class="col-md-8">
                        <div class="custom-card">
                            <h5 class="mb-4" style="color: var(--primary-navy);"><i class="fa-solid fa-list me-2"></i>Active Companies</h5>
                            
                            <?php if (empty($companies)): ?>
                                <p class="text-muted">No companies have been added yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Company</th>
                                                <th>Batch Year</th>
                                                <th>Last Date</th>
                                                <th>Added By</th>
                                                <th>Logo</th>
                                                <th>Document</th>
                                                <th>Branches</th>
                                                <?php if ($adminRole === 'superadmin'): ?>
                                                    <th>Action</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($companies as $c): ?>
                                                <?php
                                                    // Fetch branches for this company
                                                    $stmtB = $pdo->prepare("SELECT branch_name FROM tbl_company_branches WHERE company_id = ?");
                                                    $stmtB->execute([$c['company_id']]);
                                                    $bList = $stmtB->fetchAll(PDO::FETCH_COLUMN);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($c['company_name']) ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary text-white"><?= htmlspecialchars($c['batch_year'] ?? 'N/A') ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="<?= (strtotime($c['last_date_to_apply']) < time()) ? 'text-danger' : 'text-success' ?>">
                                                            <?= date('d M, Y', strtotime($c['last_date_to_apply'])) ?>
                                                        </span>
                                                    </td>
                                                    <td><small class="text-muted"><?= htmlspecialchars($c['added_by_name']) ?></small></td>
                                                    <td>
                                                        <?php if ($c['logo_path']): ?>
                                                            <a href="<?= htmlspecialchars($c['logo_path']) ?>" target="_blank" class="badge bg-secondary text-decoration-none">Logo</a>
                                                        <?php else: ?>
                                                            <span class="text-muted small">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($c['document_path']): ?>
                                                            <a href="<?= htmlspecialchars($c['document_path']) ?>" target="_blank" class="badge bg-info text-dark text-decoration-none">PDF</a>
                                                        <?php else: ?>
                                                            <span class="text-muted small">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php foreach($bList as $branchName): ?>
                                                            <span class="badge bg-light text-dark border mb-1"><?= htmlspecialchars($branchName) ?></span><br>
                                                        <?php endforeach; ?>
                                                    </td>
                                                    <?php if ($adminRole === 'superadmin'): ?>
                                                    <td>
                                                        <form action="manage_companies.php" method="POST" class="d-inline" onsubmit="return confirm('CRITICAL WARNING: Are you sure you want to permanently delete this company? All related applications will also be deleted.');">
                                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                            <input type="hidden" name="action" value="delete_company">
                                                            <input type="hidden" name="company_id" value="<?= $c['company_id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Company">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <?php endif; ?>
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
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
