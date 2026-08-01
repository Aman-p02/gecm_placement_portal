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

if (isset($_SESSION['dashboard_error'])) {
    $error = $_SESSION['dashboard_error'];
    unset($_SESSION['dashboard_error']);
}

if (isset($_SESSION['dashboard_success'])) {
    $success = $_SESSION['dashboard_success'];
    unset($_SESSION['dashboard_success']);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');

    $firstName = sanitize_input($_POST['first_name'] ?? '');
    $middleName = sanitize_input($_POST['middle_name'] ?? '');
    $surname = sanitize_input($_POST['surname'] ?? '');
    $fatherName = sanitize_input($_POST['father_name'] ?? '');
    $motherName = sanitize_input($_POST['mother_name'] ?? '');

    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone_number'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $dob = sanitize_input($_POST['dob'] ?? '');
    $physicallyHandicap = isset($_POST['physically_handicap']) ? 1 : 0;
    $category = sanitize_input($_POST['category'] ?? '');
    $district = sanitize_input($_POST['district'] ?? '');
    $course = sanitize_input($_POST['course'] ?? '');
    $passingYear = filter_input(INPUT_POST, 'passing_year', FILTER_VALIDATE_INT) ?: null;
    $sem5Cgpa = filter_input(INPUT_POST, 'sem5_cgpa', FILTER_VALIDATE_FLOAT);
    $sem5Cpi = filter_input(INPUT_POST, 'sem5_cpi', FILTER_VALIDATE_FLOAT);
    $sem6Cgpa = filter_input(INPUT_POST, 'sem6_cgpa', FILTER_VALIDATE_FLOAT);
    $sem6Cpi = filter_input(INPUT_POST, 'sem6_cpi', FILTER_VALIDATE_FLOAT);
    $activeBacklogs = filter_input(INPUT_POST, 'active_backlogs', FILTER_VALIDATE_INT) ?? 0;
    
    // Calculate CPI Percentage from Sem 6 CPI
    $cpiPercentage = null;
    if ($sem6Cpi !== false && $sem6Cpi !== null) {
        $cpiPercentage = max(0, ($sem6Cpi - 0.5) * 10);
    }
    
    $finishingSchool = isset($_POST['finishing_school']) ? 1 : 0;
    $skillTraining = isset($_POST['skill_training']) ? 1 : 0;
    $trainingDetails = sanitize_input($_POST['training_details'] ?? '');
    $hscPercentage = filter_input(INPUT_POST, 'hsc_percentage', FILTER_VALIDATE_FLOAT);
    $sscPercentage = filter_input(INPUT_POST, 'ssc_percentage', FILTER_VALIDATE_FLOAT);

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
            
            // Map full branch names to short codes
            $branchCodeMap = [
                'Computer Engineering' => 'CE',
                'Information Technology' => 'IT',
                'Mechanical Engineering' => 'ME',
                'Civil Engineering' => 'Civil',
                'Electrical Engineering' => 'EE',
                'Electronics & Communication' => 'EC',
                'Automobile Engineering' => 'Auto'
            ];
            
            // Format branch name to be safe for directory naming, using short code if available
            $safeBranchName = $branchCodeMap[$studentBranch] ?? trim(preg_replace('/[^a-zA-Z0-9\-_ ]/', '', $studentBranch));
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

                $stmtUpdate = $pdo->prepare("UPDATE tbl_student_profile SET first_name=?, middle_name=?, surname=?, father_name=?, mother_name=?, profile_pic=?, gender=?, dob=?, physically_handicap=?, category=?, district=?, course=?, passing_year=?, sem5_cgpa=?, sem5_cpi=?, sem6_cgpa=?, sem6_cpi=?, cpi_percentage=?, active_backlogs=?, finishing_school=?, skill_training=?, training_details=?, hsc_percentage=?, ssc_percentage=?, phone_number=?, email=?, resume_path=? WHERE student_id=?");
                $stmtUpdate->execute([$firstName, $middleName, $surname, $fatherName, $motherName, $profilePicPath, $gender, $dob, $physicallyHandicap, $category, $district, $course, $passingYear, $sem5Cgpa, $sem5Cpi, $sem6Cgpa, $sem6Cpi, $cpiPercentage, $activeBacklogs, $finishingSchool, $skillTraining, $trainingDetails, $hscPercentage, $sscPercentage, $phone, $email, $resumePath, $studentId]);
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO tbl_student_profile (student_id, first_name, middle_name, surname, father_name, mother_name, profile_pic, gender, dob, physically_handicap, category, district, course, passing_year, sem5_cgpa, sem5_cpi, sem6_cgpa, sem6_cpi, cpi_percentage, active_backlogs, finishing_school, skill_training, training_details, hsc_percentage, ssc_percentage, phone_number, email, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([$studentId, $firstName, $middleName, $surname, $fatherName, $motherName, $profilePicPath, $gender, $dob, $physicallyHandicap, $category, $district, $course, $passingYear, $sem5Cgpa, $sem5Cpi, $sem6Cgpa, $sem6Cpi, $cpiPercentage, $activeBacklogs, $finishingSchool, $skillTraining, $trainingDetails, $hscPercentage, $sscPercentage, $phone, $email, $resumePath]);
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
$isProfileComplete = !empty($profile) && !empty($profile['district']) && !empty($profile['course']) && !empty($profile['sem5_cpi']) && !empty($profile['first_name']) && !empty($profile['surname']) && !empty($profile['father_name']) && !empty($profile['mother_name']);

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
                    <?php if ($isProfileComplete): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="placement_drives.php">Placement Drives</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="track_applications.php">My Applications</a>
                    </li>
                    <?php endif; ?>
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

                        <div class="mb-4">
                            <!-- Personal Details -->
                            <h5 class="border-bottom pb-2 mb-3 text-secondary">Personal Details</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Enrollment No.</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($student['enrollment_no']) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">First Name</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['first_name'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Middle Name</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['middle_name'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Surname</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['surname'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Father's Name</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['father_name'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Mother's Name</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['mother_name'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Email Address</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($student['email']) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Contact Number</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($student['phone_number']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Date of Birth</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['dob'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Gender</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['gender'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Category</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['category'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">District</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['district'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <h5 class="border-bottom pb-2 mb-3 text-secondary mt-5">Academic Information</h5>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Branch</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($student['branch']) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Course</div>
                                        <div class="col-sm-8 fw-medium">
                                            <?= htmlspecialchars($profile['course'] ?? 'N/A') ?>
                                            <?php if(!empty($profile['physically_handicap'])): ?>
                                                <span class="badge bg-info text-dark ms-2">Physically Handicapped</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Passing Year</div>
                                        <div class="col-sm-8 fw-bold"><?= htmlspecialchars($profile['passing_year'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">SSC %</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['ssc_percentage'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">HSC %</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['hsc_percentage'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Sem 5 CPI</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['sem5_cpi'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Current CPI</div>
                                        <div class="col-sm-8 fw-medium"><?= htmlspecialchars($profile['sem6_cpi'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">B.E. Percentage</div>
                                        <div class="col-sm-8 fw-bold text-success"><?= htmlspecialchars($profile['cpi_percentage'] ?? 'N/A') ?>%</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-4 text-muted small">Active Backlogs</div>
                                        <div class="col-sm-8 fw-bold <?= empty($profile['active_backlogs']) ? 'text-success' : 'text-danger' ?>">
                                            <?= htmlspecialchars($profile['active_backlogs'] ?? '0') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <!-- Training Details -->
                            <?php if(!empty($profile['finishing_school']) || !empty($profile['skill_training']) || !empty($profile['training_details'])): ?>
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-3 text-secondary mt-5">Training & Certifications</h5>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <?php if(!empty($profile['finishing_school'])): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Finishing School</span>
                                    <?php endif; ?>
                                    <?php if(!empty($profile['skill_training'])): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Skill Training</span>
                                    <?php endif; ?>
                                </div>
                                <?php if(!empty($profile['training_details'])): ?>
                                    <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($profile['training_details'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Skills -->
                            <?php if(!empty($skills)): ?>
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-3 text-secondary mt-4">Technical Skills</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach($skills as $skill): ?>
                                        <span class="badge bg-secondary text-uppercase py-2 px-3"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
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

                            <h5 class="mb-3">Personal Details</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($profile['middle_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Surname</label>
                                    <input type="text" class="form-control" name="surname" value="<?= htmlspecialchars($profile['surname'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Father's Name</label>
                                    <input type="text" class="form-control" name="father_name" value="<?= htmlspecialchars($profile['father_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" class="form-control" name="mother_name" value="<?= htmlspecialchars($profile['mother_name'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select</option>
                                        <option value="Male" <?= (isset($profile['gender']) && $profile['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= (isset($profile['gender']) && $profile['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= (isset($profile['gender']) && $profile['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="<?= htmlspecialchars($profile['dob'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select</option>
                                        <option value="General" <?= (isset($profile['category']) && $profile['category'] === 'General') ? 'selected' : '' ?>>General</option>
                                        <option value="OBC" <?= (isset($profile['category']) && $profile['category'] === 'OBC') ? 'selected' : '' ?>>OBC</option>
                                        <option value="SC" <?= (isset($profile['category']) && $profile['category'] === 'SC') ? 'selected' : '' ?>>SC</option>
                                        <option value="ST" <?= (isset($profile['category']) && $profile['category'] === 'ST') ? 'selected' : '' ?>>ST</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">District</label>
                                    <input type="text" class="form-control" name="district" value="<?= htmlspecialchars($profile['district'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 d-flex align-items-end pb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="physically_handicap" id="physically_handicap" <?= !empty($profile['physically_handicap']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="physically_handicap">
                                            Physically Handicapped?
                                        </label>
                                    </div>
                                </div>
                            </div>

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
                            <div class="alert alert-warning py-2 small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Be careful while entering your CPI. Once saved, it cannot be changed in the future.
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Course (e.g. B.E.)</label>
                                    <input type="text" class="form-control" name="course" value="<?= htmlspecialchars($profile['course'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Passing Year</label>
                                    <input type="number" class="form-control border-primary" name="passing_year" value="<?= htmlspecialchars($profile['passing_year'] ?? '') ?>" min="2000" max="2100" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">SSC Percentage</label>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control" name="ssc_percentage" value="<?= htmlspecialchars($profile['ssc_percentage'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">HSC Percentage</label>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control" name="hsc_percentage" value="<?= htmlspecialchars($profile['hsc_percentage'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sem 5 CGPA</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem5_cgpa"
                                        value="<?= htmlspecialchars($profile['sem5_cgpa'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sem 5 CPI</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem5_cpi"
                                        value="<?= htmlspecialchars($profile['sem5_cpi'] ?? '') ?>" <?= !empty($profile['sem5_cpi']) ? 'readonly' : '' ?> required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sem 6 CGPA</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem6_cgpa"
                                        value="<?= htmlspecialchars($profile['sem6_cgpa'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sem 6 CPI (Current)</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="sem6_cpi"
                                        value="<?= htmlspecialchars($profile['sem6_cpi'] ?? '') ?>" <?= !empty($profile['sem6_cpi']) ? 'readonly' : '' ?>>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <label class="form-label text-danger fw-medium"><i class="fa-solid fa-triangle-exclamation me-1"></i>Active Backlogs (ATKT)</label>
                                    <input type="number" min="0" max="20" class="form-control border-danger" name="active_backlogs"
                                        value="<?= htmlspecialchars($profile['active_backlogs'] ?? '0') ?>">
                                    <small class="text-muted">Enter 0 if you have no active backlogs.</small>
                                </div>
                            </div>

                            <h5 class="mb-3">Training & Certifications</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="finishing_school" id="finishing_school" <?= !empty($profile['finishing_school']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="finishing_school">
                                            Completed Finishing School Training?
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="skill_training" id="skill_training" <?= !empty($profile['skill_training']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="skill_training">
                                            Completed other Skill Training?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Training Details (If any)</label>
                                    <textarea class="form-control" name="training_details" rows="2" placeholder="Mention your training provider and course name..."><?= htmlspecialchars($profile['training_details'] ?? '') ?></textarea>
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