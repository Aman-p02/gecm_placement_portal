<?php
/**
 * Manage Companies (Admin)
 * Allows adding, editing, and viewing companies based on role.
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
    
    $selectedBranches = $_POST['branches'] ?? [];
    
    if (empty($companyName) || empty($lastDate) || empty($selectedBranches)) {
        $_SESSION['page_error'] = "Company Name, Last Date, and at least one Branch are required.";
        header("Location: manage_companies.php");
        exit;
    } else {
        try {
            $pdo->beginTransaction();
            
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
            
            $jobDescriptionText = sanitize_input($_POST['job_description_text'] ?? '');

            $stmt = $pdo->prepare("INSERT INTO tbl_companies (company_name, batch_year, logo_path, document_path, job_description_text, last_date_to_apply, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$companyName, $batchYear, $logoPath, $docPath, $jobDescriptionText, $lastDate, $adminId]);
            $companyId = $pdo->lastInsertId();
            
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

// Handle Edit Company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_company') {
    validate_csrf_token($_POST['csrf_token'] ?? '');

    $companyId   = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
    $companyName = sanitize_input($_POST['company_name'] ?? '');
    $batchYear   = filter_input(INPUT_POST, 'batch_year', FILTER_VALIDATE_INT) ?: date('Y');
    $lastDate    = sanitize_input($_POST['last_date'] ?? '');
    $selectedBranches = $_POST['branches'] ?? [];

    if (!$companyId || empty($companyName) || empty($lastDate) || empty($selectedBranches)) {
        $_SESSION['page_error'] = "All fields and at least one branch are required.";
        header("Location: manage_companies.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $uploadDir = __DIR__ . '/uploads/companies/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Fetch existing file paths
        $stmtOld = $pdo->prepare("SELECT logo_path, document_path FROM tbl_companies WHERE company_id = ?");
        $stmtOld->execute([$companyId]);
        $oldData = $stmtOld->fetch();

        $logoPath = $oldData['logo_path'];  // keep existing by default
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

        $docPath = $oldData['document_path'];  // keep existing by default
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

        // Update company record
        $jobDescriptionText = sanitize_input($_POST['job_description_text'] ?? '');
        $stmtUpdate = $pdo->prepare("UPDATE tbl_companies SET company_name=?, batch_year=?, last_date_to_apply=?, logo_path=?, document_path=?, job_description_text=? WHERE company_id=?");
        $stmtUpdate->execute([$companyName, $batchYear, $lastDate, $logoPath, $docPath, $jobDescriptionText, $companyId]);

        // Refresh branches: delete old, insert new
        $pdo->prepare("DELETE FROM tbl_company_branches WHERE company_id = ?")->execute([$companyId]);
        $stmtBranch = $pdo->prepare("INSERT INTO tbl_company_branches (company_id, branch_name) VALUES (?, ?)");
        foreach ($selectedBranches as $b) {
            $stmtBranch->execute([$companyId, $b]);
        }

        $pdo->commit();
        $_SESSION['page_success'] = "Company updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['page_error'] = $e->getMessage();
    }
    header("Location: manage_companies.php");
    exit;
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

// Pre-fetch branches for all companies (for edit modal)
$companyBranchMap = [];
foreach ($companies as $c) {
    $stmtB = $pdo->prepare("SELECT branch_name FROM tbl_company_branches WHERE company_id = ?");
    $stmtB->execute([$c['company_id']]);
    $companyBranchMap[$c['company_id']] = $stmtB->fetchAll(PDO::FETCH_COLUMN);
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - GEC Placement</title>
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
            <a href="manage_companies.php" class="active"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
            <a href="view_applicants.php"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            <a href="reports.php"><i class="fa-solid fa-chart-pie me-2"></i> Reports</a>
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

                                <div class="mb-3">
                                    <label class="form-label small">Job Description (Text)</label>
                                    <textarea class="form-control" name="job_description_text" rows="4" placeholder="Enter job description here..."></textarea>
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
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($companies as $c): ?>
                                                <?php $bList = $companyBranchMap[$c['company_id']] ?? []; ?>
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
                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            <!-- Edit Button -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                title="Edit Company"
                                                                onclick="openEditModal(
                                                                    <?= $c['company_id'] ?>,
                                                                    <?= htmlspecialchars(json_encode($c['company_name'])) ?>,
                                                                    <?= (int)$c['batch_year'] ?>,
                                                                    '<?= htmlspecialchars($c['last_date_to_apply']) ?>',
                                                                    <?= htmlspecialchars(json_encode($bList)) ?>,
                                                                    <?= htmlspecialchars(json_encode($c['job_description_text'] ?? '')) ?>
                                                                )">
                                                                <i class="fa-solid fa-pen-to-square"></i>
                                                            </button>

                                                            <!-- Delete Button (superadmin only) -->
                                                            <?php if ($adminRole === 'superadmin'): ?>
                                                            <form action="manage_companies.php" method="POST" class="d-inline" onsubmit="return confirm('CRITICAL WARNING: Are you sure you want to permanently delete this company? All related applications will also be deleted.');">
                                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                                <input type="hidden" name="action" value="delete_company">
                                                                <input type="hidden" name="company_id" value="<?= $c['company_id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Company">
                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                </button>
                                                            </form>
                                                            <?php endif; ?>
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
        </div>
    </div>

    <!-- ═══════════ EDIT COMPANY MODAL ═══════════ -->
    <div class="modal fade" id="editCompanyModal" tabindex="-1" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary-navy, #1B365D);">
                    <h5 class="modal-title text-white" id="editCompanyModalLabel">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Edit Company
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="manage_companies.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="edit_company">
                        <input type="hidden" name="company_id" id="edit_company_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="company_name" id="edit_company_name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Batch Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="batch_year" id="edit_batch_year" min="2000" max="2100" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Last Date to Apply <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="last_date" id="edit_last_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Replace Logo (optional)</label>
                                <input type="file" class="form-control" name="logo" accept="image/jpeg, image/png, image/webp">
                                <div class="form-text">Leave blank to keep existing logo.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Replace Job Description PDF (optional)</label>
                                <input type="file" class="form-control" name="document" accept="application/pdf">
                                <div class="form-text">Leave blank to keep existing document.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Job Description (Text)</label>
                                <textarea class="form-control" name="job_description_text" id="edit_job_description_text" rows="4" placeholder="Enter job description here..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Eligible Branches <span class="text-danger">*</span></label>
                                <div class="p-3 border rounded bg-light d-flex flex-wrap gap-3" id="edit_branches_container">
                                    <?php foreach ($allBranches as $b): ?>
                                        <div class="form-check">
                                            <input class="form-check-input edit-branch-check" type="checkbox"
                                                name="branches[]"
                                                value="<?= htmlspecialchars($b) ?>"
                                                id="edit_branch_<?= md5($b) ?>">
                                            <label class="form-check-label small" for="edit_branch_<?= md5($b) ?>"><?= htmlspecialchars($b) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(id, name, batchYear, lastDate, branches, jobDescText) {
            // Populate fields
            document.getElementById('edit_company_id').value   = id;
            document.getElementById('edit_company_name').value = name;
            document.getElementById('edit_batch_year').value   = batchYear;
            document.getElementById('edit_last_date').value    = lastDate;
            document.getElementById('edit_job_description_text').value = jobDescText;

            // Reset all branch checkboxes then check the ones that match
            document.querySelectorAll('.edit-branch-check').forEach(cb => {
                cb.checked = branches.includes(cb.value);
            });

            // Show the modal
            new bootstrap.Modal(document.getElementById('editCompanyModal')).show();
        }
    </script>
</body>
</html>
