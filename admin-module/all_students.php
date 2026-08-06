<?php
/**
 * All Students (Admin View)
 * Allows admins to view and edit student profiles for their branch.
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

// Handle Profile Edit by Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_student') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    
    $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    
    if ($studentId) {
        $fullName = sanitize_input($_POST['full_name'] ?? '');
        $enrollmentNo = sanitize_input($_POST['enrollment_no'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone_number'] ?? '');
        $firstName = sanitize_input($_POST['first_name'] ?? '');
        $middleName = sanitize_input($_POST['middle_name'] ?? '');
        $surname = sanitize_input($_POST['surname'] ?? '');
        $fatherName = sanitize_input($_POST['father_name'] ?? '');
        $motherName = sanitize_input($_POST['mother_name'] ?? '');

        $district = sanitize_input($_POST['district'] ?? '');
        $course = sanitize_input($_POST['course'] ?? '');
        $category = sanitize_input($_POST['category'] ?? '');
        
        $sem5Cpi = filter_input(INPUT_POST, 'sem5_cpi', FILTER_VALIDATE_FLOAT);
        $sem6Cpi = filter_input(INPUT_POST, 'sem6_cpi', FILTER_VALIDATE_FLOAT);
        $activeBacklogs = filter_input(INPUT_POST, 'active_backlogs', FILTER_VALIDATE_INT) ?? 0;
        
        $cpiPercentage = null;
        if ($sem6Cpi !== false && $sem6Cpi !== null) {
            $cpiPercentage = max(0, ($sem6Cpi - 0.5) * 10);
        }

        try {
            $pdo->beginTransaction();
            
            // Update tbl_students
            $stmt1 = $pdo->prepare("UPDATE tbl_students SET full_name = ?, enrollment_no = ?, email = ?, phone_number = ? WHERE student_id = ?");
            $stmt1->execute([$fullName, $enrollmentNo, $email, $phone, $studentId]);
            
            // Update tbl_student_profile
            $stmt2 = $pdo->prepare("UPDATE tbl_student_profile SET first_name = ?, middle_name = ?, surname = ?, father_name = ?, mother_name = ?, district = ?, course = ?, category = ?, sem5_cpi = ?, sem6_cpi = ?, cpi_percentage = ?, active_backlogs = ? WHERE student_id = ?");
            $stmt2->execute([$firstName, $middleName, $surname, $fatherName, $motherName, $district, $course, $category, $sem5Cpi, $sem6Cpi, $cpiPercentage, $activeBacklogs, $studentId]);
            
            $pdo->commit();
            $_SESSION['page_success'] = "Student profile updated successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['page_error'] = "Failed to update student profile.";
        }
        header("Location: all_students.php");
        exit;
    }
}

// Handle Block/Unblock Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_block') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $currentStatus = filter_input(INPUT_POST, 'current_status', FILTER_VALIDATE_INT);
    
    if ($studentId) {
        $newStatus = $currentStatus ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE tbl_students SET is_blocked = ? WHERE student_id = ?");
        if ($stmt->execute([$newStatus, $studentId])) {
            
            // Fetch student info for email
            $infoStmt = $pdo->prepare("SELECT email, full_name FROM tbl_students WHERE student_id = ?");
            $infoStmt->execute([$studentId]);
            $info = $infoStmt->fetch();
            
            if ($info) {
                sendBlockStatusEmail($info['email'], $info['full_name'], $newStatus);
            }
            
            $_SESSION['page_success'] = $newStatus ? "Student has been blocked." : "Student has been unblocked.";
        } else {
            $_SESSION['page_error'] = "Failed to update student status.";
        }
        header("Location: all_students.php");
        exit;
    }
}

// Handle Delete Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_student') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    
    if ($studentId) {
        // Due to ON DELETE CASCADE, deleting from tbl_students will remove profile, skills, and applications
        $stmt = $pdo->prepare("DELETE FROM tbl_students WHERE student_id = ?");
        if ($stmt->execute([$studentId])) {
            $_SESSION['page_success'] = "Student deleted successfully.";
        } else {
            $_SESSION['page_error'] = "Failed to delete student.";
        }
        header("Location: all_students.php");
        exit;
    }
}

// Fetch Students
$filterBranch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING) ?: '';

if ($adminRole === 'superadmin') {
    // Superadmin sees all or filtered
    if ($filterBranch) {
        $stmt = $pdo->prepare("
            SELECT s.student_id, s.full_name, s.enrollment_no, s.branch, s.email, s.phone_number, s.is_blocked,
                   p.first_name, p.middle_name, p.surname, p.father_name, p.mother_name, p.district, p.course, p.category, p.sem5_cpi, p.sem6_cpi, p.cpi_percentage, p.active_backlogs, p.profile_pic
            FROM tbl_students s
            LEFT JOIN tbl_student_profile p ON s.student_id = p.student_id
            WHERE s.branch = ?
            ORDER BY s.enrollment_no ASC
        ");
        $stmt->execute([$filterBranch]);
        $students = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("
            SELECT s.student_id, s.full_name, s.enrollment_no, s.branch, s.email, s.phone_number, s.is_blocked,
                   p.first_name, p.middle_name, p.surname, p.father_name, p.mother_name, p.district, p.course, p.category, p.sem5_cpi, p.sem6_cpi, p.cpi_percentage, p.active_backlogs, p.profile_pic
            FROM tbl_students s
            LEFT JOIN tbl_student_profile p ON s.student_id = p.student_id
            ORDER BY s.branch, s.enrollment_no ASC
        ");
        $students = $stmt->fetchAll();
    }
} else {
    // Subadmin sees only their branch
    $stmt = $pdo->prepare("
        SELECT s.student_id, s.full_name, s.enrollment_no, s.branch, s.email, s.phone_number, s.is_blocked,
               p.first_name, p.middle_name, p.surname, p.father_name, p.mother_name, p.district, p.course, p.category, p.sem5_cpi, p.sem6_cpi, p.cpi_percentage, p.active_backlogs, p.profile_pic
        FROM tbl_students s
        LEFT JOIN tbl_student_profile p ON s.student_id = p.student_id
        WHERE s.branch = ?
        ORDER BY s.enrollment_no ASC
    ");
    $stmt->execute([$adminBranch]);
    $students = $stmt->fetchAll();
}

// Additional PHP-based Filtering
$filterStatus = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) ?: '';
$filterCpi = filter_input(INPUT_GET, 'min_cpi', FILTER_VALIDATE_FLOAT) ?: '';
$filterSearchName = filter_input(INPUT_GET, 'search_name', FILTER_SANITIZE_STRING) ?: '';

if ($filterStatus || $filterCpi !== '' || $filterSearchName) {
    $students = array_filter($students, function($stu) use ($filterStatus, $filterCpi, $filterSearchName) {
        $isComplete = !empty($stu['first_name']) && !empty($stu['district']) && !empty($stu['course']) && !empty($stu['sem5_cpi']);
        
        // Status Filter
        if ($filterStatus === 'Blocked' && !$stu['is_blocked']) return false;
        if ($filterStatus === 'Complete' && ($stu['is_blocked'] || !$isComplete)) return false;
        if ($filterStatus === 'Incomplete' && ($stu['is_blocked'] || $isComplete)) return false;

        // CPI Filter
        if ($filterCpi !== '') {
            $cpi = max((float)($stu['sem5_cpi'] ?? 0), (float)($stu['sem6_cpi'] ?? 0));
            if ($cpi < $filterCpi) return false;
        }

        // Search Name Filter
        if ($filterSearchName) {
            $search = strtolower($filterSearchName);
            $names = [
                $stu['full_name'] ?? '',
                $stu['first_name'] ?? '',
                $stu['middle_name'] ?? '',
                $stu['surname'] ?? '',
                $stu['father_name'] ?? '',
                $stu['mother_name'] ?? ''
            ];
            $match = false;
            foreach ($names as $name) {
                if (strpos(strtolower($name), $search) !== false) {
                    $match = true;
                    break;
                }
            }
            if (!$match) return false;
        }

        return true;
    });
}

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=students_export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    // Output headers
    fputcsv($output, ['Enrollment No', 'Full Name', 'First Name', 'Middle Name', 'Surname', 'Father Name', 'Mother Name', 'Branch', 'Email', 'Phone', 'Sem 5 CPI', 'Sem 6 CPI', 'Active Backlogs', 'Profile Status']);
    
    foreach ($students as $stu) {
        $isComplete = !empty($stu['first_name']) && !empty($stu['district']) && !empty($stu['course']) && !empty($stu['sem5_cpi']);
        $statusStr = $stu['is_blocked'] ? 'Blocked' : ($isComplete ? 'Complete' : 'Incomplete');
        
        fputcsv($output, [
            $stu['enrollment_no'],
            $stu['full_name'],
            $stu['first_name'],
            $stu['middle_name'],
            $stu['surname'],
            $stu['father_name'],
            $stu['mother_name'],
            $stu['branch'],
            $stu['email'],
            $stu['phone_number'],
            $stu['sem5_cpi'],
            $stu['sem6_cpi'],
            $stu['active_backlogs'],
            $statusStr
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
    <title>All Students - GEC Placement</title>
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
            <a href="all_students.php" class="active"><i class="fa-solid fa-user-graduate me-2"></i> All Students</a>
            <a href="manage_companies.php"><i class="fa-solid fa-building me-2"></i> Manage Companies</a>
            <a href="view_applicants.php"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            <a href="reports.php"><i class="fa-solid fa-chart-pie me-2"></i> Reports</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-muted">Students Directory</h5>
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
                                <h5 class="m-0" style="color: var(--primary-navy);"><i class="fa-solid fa-users me-2"></i><?= $adminRole === 'superadmin' ? 'All Students' : 'Students in ' . htmlspecialchars($adminBranch) ?></h5>
                            </div>
                            <div class="col-md-7">
                                <form action="" method="GET" class="row g-2 align-items-center">
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
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Statuses</option>
                                            <option value="Complete" <?= $filterStatus === 'Complete' ? 'selected' : '' ?>>Complete</option>
                                            <option value="Incomplete" <?= $filterStatus === 'Incomplete' ? 'selected' : '' ?>>Incomplete</option>
                                            <option value="Blocked" <?= $filterStatus === 'Blocked' ? 'selected' : '' ?>>Blocked</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                                            <input type="text" name="search_name" class="form-control" value="<?= htmlspecialchars($filterSearchName) ?>" placeholder="Search Name..." style="width: 140px;">
                                        </div>
                                    </div>

                                    <div class="col-auto">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Min CPI</span>
                                            <input type="number" step="0.1" min="0" max="10" name="min_cpi" class="form-control" value="<?= htmlspecialchars($filterCpi) ?>" placeholder="7.5" style="width: 70px;">
                                            <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-filter"></i></button>
                                        </div>
                                    </div>
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
                                <span class="badge bg-white text-dark border w-100 py-1">Total: <?= count($students) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($students)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-xmark fs-1 mb-3"></i>
                            <h5>No students found in your department.</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Enrollment No.</th>
                                        <th>Student Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Sem 6 CPI</th>
                                        <th>Backlogs</th>
                                        <th>Profile Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $stu): 
                                        $isComplete = (!empty($stu['district']) && !empty($stu['course']) && !empty($stu['sem5_cpi']));
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($stu['enrollment_no']) ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <?php if ($stu['profile_pic']): ?>
                                                        <img src="../student-module/<?= htmlspecialchars($stu['profile_pic']) ?>" alt="Pic" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                                    <?php else: ?>
                                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                                            <?= strtoupper(substr($stu['full_name'], 0, 1)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong class="d-block"><?= htmlspecialchars($stu['full_name']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= htmlspecialchars($stu['email'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= htmlspecialchars($stu['phone_number'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($stu['sem6_cpi'] ?? 'N/A') ?></strong>
                                            </td>
                                            <td>
                                                <strong class="<?= empty($stu['active_backlogs']) ? 'text-success' : 'text-danger' ?>"><?= htmlspecialchars($stu['active_backlogs'] ?? '0') ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($stu['is_blocked']): ?>
                                                    <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> Blocked</span>
                                                <?php elseif ($isComplete): ?>
                                                    <span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i> Complete</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-circle-exclamation me-1"></i> Incomplete</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $stu['student_id'] ?>">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    
                                                    <form action="all_students.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to <?= $stu['is_blocked'] ? 'unblock' : 'block' ?> this student?');">
                                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                        <input type="hidden" name="action" value="toggle_block">
                                                        <input type="hidden" name="student_id" value="<?= $stu['student_id'] ?>">
                                                        <input type="hidden" name="current_status" value="<?= $stu['is_blocked'] ?>">
                                                        <button type="submit" class="btn btn-sm <?= $stu['is_blocked'] ? 'btn-outline-success' : 'btn-outline-warning' ?>" title="<?= $stu['is_blocked'] ? 'Unblock Student' : 'Block Student' ?>">
                                                            <i class="fa-solid <?= $stu['is_blocked'] ? 'fa-unlock' : 'fa-ban' ?>"></i>
                                                        </button>
                                                    </form>

                                                    <form action="all_students.php" method="POST" class="d-inline" onsubmit="return confirm('CRITICAL WARNING: Are you sure you want to PERMANENTLY DELETE this student? All their profile data and applications will be erased. This cannot be undone.');">
                                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                        <input type="hidden" name="action" value="delete_student">
                                                        <input type="hidden" name="student_id" value="<?= $stu['student_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Student">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="editModal<?= $stu['student_id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Student Profile: <?= htmlspecialchars($stu['full_name']) ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="all_students.php" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                                    <input type="hidden" name="action" value="edit_student">
                                                                    <input type="hidden" name="student_id" value="<?= $stu['student_id'] ?>">
                                                                    
                                                                    <div class="row g-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small">Full Name (Registered)</label>
                                                                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($stu['full_name']) ?>" required>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small">Enrollment No.</label>
                                                                            <input type="text" class="form-control" name="enrollment_no" value="<?= htmlspecialchars($stu['enrollment_no']) ?>" required>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">First Name</label>
                                                                            <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($stu['first_name'] ?? '') ?>" required>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">Middle Name</label>
                                                                            <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($stu['middle_name'] ?? '') ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">Surname</label>
                                                                            <input type="text" class="form-control" name="surname" value="<?= htmlspecialchars($stu['surname'] ?? '') ?>" required>
                                                                        </div>
                                                                        
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small">Father's Name</label>
                                                                            <input type="text" class="form-control" name="father_name" value="<?= htmlspecialchars($stu['father_name'] ?? '') ?>" required>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small">Mother's Name</label>
                                                                            <input type="text" class="form-control" name="mother_name" value="<?= htmlspecialchars($stu['mother_name'] ?? '') ?>" required>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <label class="form-label small">Email</label>
                                                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($stu['email'] ?? '') ?>">
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small">Phone</label>
                                                                            <input type="text" class="form-control" name="phone_number" value="<?= htmlspecialchars($stu['phone_number'] ?? '') ?>">
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">District</label>
                                                                            <input type="text" class="form-control" name="district" value="<?= htmlspecialchars($stu['district'] ?? '') ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">Category</label>
                                                                            <select class="form-select" name="category">
                                                                                <option value="">Select</option>
                                                                                <option value="General" <?= (isset($stu['category']) && $stu['category'] === 'General') ? 'selected' : '' ?>>General</option>
                                                                                <option value="OBC" <?= (isset($stu['category']) && $stu['category'] === 'OBC') ? 'selected' : '' ?>>OBC</option>
                                                                                <option value="SC" <?= (isset($stu['category']) && $stu['category'] === 'SC') ? 'selected' : '' ?>>SC</option>
                                                                                <option value="ST" <?= (isset($stu['category']) && $stu['category'] === 'ST') ? 'selected' : '' ?>>ST</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">Course (e.g. B.E.)</label>
                                                                            <input type="text" class="form-control" name="course" value="<?= htmlspecialchars($stu['course'] ?? '') ?>">
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">Sem 5 CPI</label>
                                                                            <input type="number" step="0.01" class="form-control" name="sem5_cpi" value="<?= htmlspecialchars($stu['sem5_cpi'] ?? '') ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label small">Sem 6 CPI</label>
                                                                            <input type="number" step="0.01" class="form-control" name="sem6_cpi" value="<?= htmlspecialchars($stu['sem6_cpi'] ?? '') ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label small text-danger fw-bold">Active Backlogs</label>
                                                                            <input type="number" class="form-control" name="active_backlogs" value="<?= htmlspecialchars($stu['active_backlogs'] ?? '0') ?>">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-accent">Save Changes</button>
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


