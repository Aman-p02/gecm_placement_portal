<?php
/**
 * Student Dashboard
 * Handles profile viewing and updating, including secure file uploads and skills tagging.
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';

// Secure the page
require_login();

$studentId = $_SESSION['student_id'];
$enrollmentNo = $_SESSION['enrollment_no'];

$error = '';
$success = '';

if (isset($_SESSION['dashboard_success'])) {
    $success = $_SESSION['dashboard_success'];
    unset($_SESSION['dashboard_success']);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');

    // Sanitize basic inputs
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone_number'] ?? '');
    $sem5Cgpa = filter_input(INPUT_POST, 'sem5_cgpa', FILTER_VALIDATE_FLOAT);
    $sem5Cpi = filter_input(INPUT_POST, 'sem5_cpi', FILTER_VALIDATE_FLOAT);
    $sem6Cgpa = filter_input(INPUT_POST, 'sem6_cgpa', FILTER_VALIDATE_FLOAT);
    $sem6Cpi = filter_input(INPUT_POST, 'sem6_cpi', FILTER_VALIDATE_FLOAT);
    $activeBacklogs = filter_input(INPUT_POST, 'active_backlogs', FILTER_VALIDATE_INT) ?? 0;

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } else {
        try {
            $pdo->beginTransaction();

            // Fetch student branch
            $stmtBranch = $pdo->prepare("SELECT branch FROM tbl_students WHERE student_id = ?");
            $stmtBranch->execute([$studentId]);
            $studentBranch = $stmtBranch->fetchColumn();
            
            // Format branch name to be safe for directory naming
            $safeBranchName = trim(preg_replace('/[^a-zA-Z0-9\-_ ]/', '', $studentBranch));

            // 1. Handle File Uploads securely
            $uploadDirPics = __DIR__ . '/uploads/profile_pics/' . $safeBranchName . '/';
            $uploadDirResumes = __DIR__ . '/uploads/resumes/' . $safeBranchName . '/';
            
            if (!is_dir($uploadDirPics)) {
                mkdir($uploadDirPics, 0777, true);
            }
            if (!is_dir($uploadDirResumes)) {
                mkdir($uploadDirResumes, 0777, true);
            }

            $profilePicPath = null;
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['profile_pic']['tmp_name'];
                $fileName = $_FILES['profile_pic']['name'];
                $fileSize = $_FILES['profile_pic']['size'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmp);
                finfo_close($finfo);

                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    throw new Exception("Profile picture must be a JPG, PNG, or WEBP image.");
                }
                if ($fileSize > 2 * 1024 * 1024) {
                    throw new Exception("Profile picture must be less than 2MB.");
                }

                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newPicName = $enrollmentNo . '.' . $ext;
                if (move_uploaded_file($fileTmp, $uploadDirPics . $newPicName)) {
                    $profilePicPath = 'uploads/profile_pics/' . $safeBranchName . '/' . $newPicName;
                }
            }

            $resumePath = null;
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['resume']['tmp_name'];
                $fileName = $_FILES['resume']['name'];
                $fileSize = $_FILES['resume']['size'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmp);
                finfo_close($finfo);

                if ($mimeType !== 'application/pdf') {
                    throw new Exception("Resume must be a PDF document.");
                }
                if ($fileSize > 5 * 1024 * 1024) {
                    throw new Exception("Resume must be less than 5MB.");
                }

                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newResumeName = $enrollmentNo . '.' . $ext;
                if (move_uploaded_file($fileTmp, $uploadDirResumes . $newResumeName)) {
                    $resumePath = 'uploads/resumes/' . $safeBranchName . '/' . $newResumeName;
                }
            }

            // 2. Insert or Update Profile
            $stmt = $pdo->prepare("SELECT profile_id, profile_pic, resume_path FROM tbl_student_profile WHERE student_id = ?");
            $stmt->execute([$studentId]);
            $existingProfile = $stmt->fetch();

            if ($existingProfile) {
                // Preserve old paths if new files weren't uploaded
                $profilePicPath = $profilePicPath ?? $existingProfile['profile_pic'];
                $resumePath = $resumePath ?? $existingProfile['resume_path'];

                $stmtUpdate = $pdo->prepare("UPDATE tbl_student_profile SET profile_pic=?, sem5_cgpa=?, sem5_cpi=?, sem6_cgpa=?, sem6_cpi=?, active_backlogs=?, phone_number=?, email=?, resume_path=? WHERE student_id=?");
                $stmtUpdate->execute([$profilePicPath, $sem5Cgpa, $sem5Cpi, $sem6Cgpa, $sem6Cpi, $activeBacklogs, $phone, $email, $resumePath, $studentId]);
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO tbl_student_profile (student_id, profile_pic, sem5_cgpa, sem5_cpi, sem6_cgpa, sem6_cpi, active_backlogs, phone_number, email, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([$studentId, $profilePicPath, $sem5Cgpa, $sem5Cpi, $sem6Cgpa, $sem6Cpi, $activeBacklogs, $phone, $email, $resumePath]);
            }

            // 3. Update Skills
            $hiddenSkills = sanitize_input($_POST['hidden_skills'] ?? '');
            $skillsArray = array_filter(array_map('trim', explode(',', $hiddenSkills)));

            // Wipe old skills and insert fresh list
            $stmtDeleteSkills = $pdo->prepare("DELETE FROM tbl_student_skills WHERE student_id = ?");
            $stmtDeleteSkills->execute([$studentId]);

            if (!empty($skillsArray)) {
                $stmtInsertSkill = $pdo->prepare("INSERT INTO tbl_student_skills (student_id, skill_name) VALUES (?, ?)");
                foreach ($skillsArray as $skill) {
                    $stmtInsertSkill->execute([$studentId, $skill]);
                }
            }

            $pdo->commit();
            $_SESSION['dashboard_success'] = "Profile updated successfully!";
            header("Location: dashboard.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Fetch Latest Data
$stmt = $pdo->prepare("SELECT * FROM tbl_students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM tbl_student_profile WHERE student_id = ?");
$stmt->execute([$studentId]);
$profile = $stmt->fetch();

$stmt = $pdo->prepare("SELECT skill_name FROM tbl_student_skills WHERE student_id = ?");
$stmt->execute([$studentId]);
$skills = $stmt->fetchAll(PDO::FETCH_COLUMN);
$skillsString = implode(',', $skills);

$csrfToken = generate_csrf_token();
$isProfileComplete = !empty($profile);

// Setup view mode (if profile is complete, show summary first, else edit mode)
$mode = (isset($_GET['edit']) || !$isProfileComplete) ? 'edit' : 'view';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GEC Placement Portal</title>
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
                        <a class="nav-link active fw-medium" href="dashboard.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="placement_drives.php">Placement Drives</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="track_applications.php">My Applications</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3 ms-auto mt-3 mt-lg-0">
                    <span class="fw-medium text-dark">Hi, <?= htmlspecialchars($student['full_name']) ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm"><i
                            class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!$isProfileComplete): ?>
            <div class="alert alert-warning shadow-sm border-0 border-start border-warning border-5 mb-4">
                <h5 class="alert-heading"><i class="fa-solid fa-circle-exclamation me-2"></i>Complete Your Profile</h5>
                <p class="mb-0">Please fill out all your details to make your profile visible to recruiters.</p>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar Summary -->
            <div class="col-md-4 mb-4">
                <div class="custom-card text-center h-100">
                    <?php
                    $picUrl = (!empty($profile['profile_pic']) && file_exists(__DIR__ . '/' . $profile['profile_pic']))
                        ? htmlspecialchars($profile['profile_pic'])
                        : 'https://ui-avatars.com/api/?background=random&color=fff&name=' . urlencode($student['full_name']);
                    ?>
                    <img src="<?= $picUrl ?>" alt="Profile Picture" class="profile-pic-preview shadow-sm mb-3">

                    <h4 class="mb-1"><?= htmlspecialchars($student['full_name']) ?></h4>
                    <p class="text-muted mb-3"><i
                            class="fa-solid fa-id-card me-2"></i><?= htmlspecialchars($student['enrollment_no']) ?></p>

                    <div class="badge bg-secondary mb-3 p-2 fs-6"><i
                            class="fa-solid fa-building-columns me-2"></i><?= htmlspecialchars($student['branch']) ?>
                    </div>

                    <?php if ($isProfileComplete): ?>
                        <div class="text-start mt-3 pt-3 border-top border-secondary">
                            <p class="small text-muted mb-1"><i
                                    class="fa-solid fa-envelope me-2"></i><?= htmlspecialchars($profile['email']) ?></p>
                            <p class="small text-muted mb-1"><i
                                    class="fa-solid fa-phone me-2"></i><?= htmlspecialchars($profile['phone_number']) ?></p>
                        </div>
                        <?php if (!empty($profile['resume_path'])): ?>
                            <a href="<?= htmlspecialchars($profile['resume_path']) ?>" target="_blank"
                                class="btn btn-outline-accent btn-sm w-100 mt-3">
                                <i class="fa-solid fa-file-pdf me-2"></i>View Resume
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-8 mb-4">
                <div class="custom-card h-100">

                    <?php if ($mode === 'view'): ?>
                        <!-- VIEW MODE -->
                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
                            <h4 class="m-0">Profile Summary</h4>
                            <a href="?edit=1" class="btn btn-accent btn-sm"><i
                                    class="fa-solid fa-pen-to-square me-1"></i>Edit Profile</a>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border border-secondary">
                                    <small class="text-muted d-block mb-1">Semester 5 CGPA</small>
                                    <strong class="fs-5"><?= htmlspecialchars($profile['sem5_cgpa'] ?? 'N/A') ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border border-secondary">
                                    <small class="text-muted d-block mb-1">Semester 5 CPI</small>
                                    <strong class="fs-5"><?= htmlspecialchars($profile['sem5_cpi'] ?? 'N/A') ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border border-secondary">
                                    <small class="text-muted d-block mb-1">Current CGPA (Upto 6th Sem)</small>
                                    <strong class="fs-5"><?= htmlspecialchars($profile['sem6_cgpa'] ?? 'N/A') ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded bg-light border border-secondary">
                                    <small class="text-muted d-block mb-1">Current CPI (Upto 6th Sem)</small>
                                    <strong class="fs-5"><?= htmlspecialchars($profile['sem6_cpi'] ?? 'N/A') ?></strong>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="p-3 rounded bg-light border border-secondary text-center">
                                    <small class="text-muted d-block mb-1">Active Backlogs (ATKT)</small>
                                    <strong class="fs-5 text-danger"><?= htmlspecialchars($profile['active_backlogs'] ?? '0') ?></strong>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">Technical Skills</h5>
                        <?php if (empty($skills)): ?>
                            <p class="text-muted">No skills added yet.</p>
                        <?php else: ?>
                            <div class="skills-container">
                                <?php foreach ($skills as $s): ?>
                                    <span class="badge bg-secondary p-2 px-3 fw-normal"><?= htmlspecialchars($s) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- EDIT MODE -->
                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
                            <h4 class="m-0">Edit Profile</h4>
                            <?php if ($isProfileComplete): ?>
                                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i
                                        class="fa-solid fa-xmark me-1"></i>Cancel</a>
                            <?php endif; ?>
                        </div>

                        <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                            <h5 class="mb-3">Contact Info</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email"
                                        value="<?= htmlspecialchars($profile['email'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone_number"
                                        value="<?= htmlspecialchars($profile['phone_number'] ?? '') ?>" pattern="[0-9]{10}"
                                        title="10 digit mobile number" required>
                                </div>
                            </div>

                            <h5 class="mb-3">Academic Details</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Sem 5 CGPA</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem5_cgpa"
                                        value="<?= htmlspecialchars($profile['sem5_cgpa'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sem 5 CPI</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem5_cpi"
                                        value="<?= htmlspecialchars($profile['sem5_cpi'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Current CGPA</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem6_cgpa"
                                        value="<?= htmlspecialchars($profile['sem6_cgpa'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Current CPI</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem6_cpi"
                                        value="<?= htmlspecialchars($profile['sem6_cpi'] ?? '') ?>">
                                </div>
                                <div class="col-md-12 mt-3">
                                    <label class="form-label text-danger fw-medium"><i class="fa-solid fa-triangle-exclamation me-1"></i>Active Backlogs (ATKT)</label>
                                    <input type="number" min="0" max="20" class="form-control border-danger" name="active_backlogs"
                                        value="<?= htmlspecialchars($profile['active_backlogs'] ?? '0') ?>">
                                    <small class="text-muted">Enter 0 if you have no active backlogs.</small>
                                </div>
                            </div>

                            <h5 class="mb-3">Files & Documents</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Profile Picture (Image)</label>
                                    <input type="file" class="form-control" name="profile_pic" id="profile_pic"
                                        accept="image/jpeg, image/png, image/webp">
                                    <?php if (!empty($profile['profile_pic'])): ?>
                                        <small class="text-success mt-1 d-block"><i class="fa-solid fa-check me-1"></i>Current
                                            photo uploaded</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Resume (PDF)</label>
                                    <input type="file" class="form-control" name="resume" accept="application/pdf">
                                    <?php if (!empty($profile['resume_path'])): ?>
                                        <small class="text-success mt-1 d-block"><i class="fa-solid fa-check me-1"></i>Current
                                            resume uploaded</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h5 class="mb-3">Technical Skills</h5>
                            <div class="mb-4">
                                <div class="input-group mb-2">
                                    <input type="text" id="skill_input" class="form-control"
                                        placeholder="e.g., Python, React, AutoCAD">
                                    <button class="btn btn-outline-accent" type="button" id="add_skill_btn">Add
                                        Skill</button>
                                </div>
                                <input type="hidden" name="hidden_skills" id="hidden_skills"
                                    value="<?= htmlspecialchars($skillsString) ?>">
                                <div id="skills_container" class="skills-container">
                                    <!-- Skill tags rendered by JS -->
                                </div>
                            </div>

                            <button type="submit" class="btn btn-accent w-100 py-2"><i
                                    class="fa-solid fa-floppy-disk me-2"></i>Save Profile</button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>