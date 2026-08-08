<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';

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

// Handle Add Activity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_activity') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING) ?: date('Y-m-d');
    $eventType = filter_input(INPUT_POST, 'event_type', FILTER_SANITIZE_STRING) ?: 'Institute Level';
    
    if (empty($title) || empty($description)) {
        $_SESSION['page_error'] = "Title and Description are required.";
        header("Location: manage_activities.php");
        exit;
    } else {
        try {
            $pdo->beginTransaction();
            
            $uploadDir = __DIR__ . '/uploads/activities/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Handle PDF upload
            $pdfPath = null;
            if (isset($_FILES['report_pdf']) && $_FILES['report_pdf']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['report_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $pdfName = uniqid('report_') . '.pdf';
                    if(move_uploaded_file($_FILES['report_pdf']['tmp_name'], $uploadDir . $pdfName)) {
                        $pdfPath = 'admin-module/uploads/activities/' . $pdfName;
                    }
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO placement_activities (title, description, event_date, event_type, report_pdf, admin_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $eventDate, $eventType, $pdfPath, $adminId]);
            $activityId = $pdo->lastInsertId();
            
            $uploadDir = __DIR__ . '/uploads/activities/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Handle multiple images
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $total_files = count($_FILES['images']['name']);
                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $newImageName = uniqid('act_') . '_' . $i . '.' . $ext;
                            if(move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $newImageName)) {
                                $imagePath = 'admin-module/uploads/activities/' . $newImageName;
                                $stmtImage = $pdo->prepare("INSERT INTO activity_images (activity_id, image_path) VALUES (?, ?)");
                                $stmtImage->execute([$activityId, $imagePath]);
                            }
                        }
                    }
                }
            }
            
            $pdo->commit();
            $_SESSION['page_success'] = "Activity added successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['page_error'] = "Error: " . $e->getMessage();
        }
        header("Location: manage_activities.php");
        exit;
    }
}

// Handle Delete Activity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_activity') {
    $activityId = filter_input(INPUT_POST, 'activity_id', FILTER_VALIDATE_INT);
    if ($activityId) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM activity_images WHERE activity_id = ?");
            $stmt->execute([$activityId]);
            $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach($images as $img) {
                $filePath = dirname(__DIR__) . '/' . str_replace('admin-module/', '', $img);
                if(file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM placement_activities WHERE id = ?");
            $stmt->execute([$activityId]);
            $_SESSION['page_success'] = "Activity deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['page_error'] = "Failed to delete: " . $e->getMessage();
        }
    }
    header("Location: manage_activities.php");
    exit;
}

// Fetch all activities
$stmt = $pdo->prepare("SELECT * FROM placement_activities WHERE admin_id = ? ORDER BY event_date DESC");
$stmt->execute([$adminId]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - GEC Placement</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css?v=2">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '.tinymce-editor',
        plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks code fullscreen insertdatetime table help wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
        menubar: false,
        height: 300,
        branding: false,
        promotion: false
      });
      
      // Fix for Bootstrap Modal focus issue (for the Edit modal)
      document.addEventListener('focusin', function (e) {
        if (e.target.closest('.tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root') !== null) {
          e.stopImmediatePropagation();
        }
      });
    </script>
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
            <a href="view_applicants.php"><i class="fa-solid fa-users me-2"></i> View Applicants</a>
            <a href="reports.php"><i class="fa-solid fa-chart-pie me-2"></i> Reports</a>
            <a href="manage_activities.php" class="active"><i class="fa-solid fa-list-check me-2"></i> Manage Activities</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center"><button class="btn btn-sm btn-outline-secondary me-3" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button><h5 class="m-0 text-muted">Manage Activities</h5></div>
                <div>
                    <span class="fw-medium me-3 text-dark">Hi, <?= htmlspecialchars($adminName) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($adminRole)) ?></span>
                </div>
            </div>

            
        <div class="p-4">
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

            <div class="row">
                <!-- Add Form -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                            <h5 class="mb-0 text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Add New Activity</h5>
                        </div>
                        <div class="card-body">
                            <form action="manage_activities.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="add_activity">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Title</label>
                                    <input type="text" name="title" class="form-control" required placeholder="e.g. IBM Skill Build">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Event Date</label>
                                    <input type="date" name="event_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Type of Event</label>
                                    <select name="event_type" class="form-select" required>
                                        <option value="Institute Level">Institute Level</option>
                                        <option value="Department Level">Department Level</option>
                                        <option value="District Level">District Level</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Upload Report (PDF)</label>
                                    <input type="file" name="report_pdf" class="form-control" accept="application/pdf">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Description</label>
                                    <textarea name="description" class="form-control" rows="4" required placeholder="Detailed description..."></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-medium">Photos (Multiple)</label>
                                    <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*" required>
                                    <div id="fileList" class="mt-2 text-muted" style="font-size: 0.85rem;"></div>
                                    <div class="form-text">You can select multiple photos. JPG, PNG, WEBP allowed.</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-save me-2"></i>Save Activity</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- List Table -->
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Title</th>
                                            <th>Event Details</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($activities)): ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No activities found.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($activities as $act): ?>
                                            <tr>
                                                <td class="ps-4 fw-medium"><?= htmlspecialchars($act['title']) ?></td>
                                                <td>
                                                    <span class="badge bg-secondary mb-1"><?= date('d M Y', strtotime($act['event_date'])) ?></span><br>
                                                    <small class="text-muted"><?= htmlspecialchars($act['event_type']) ?></small>
                                                </td>
                                                <td>
                                                    <a href="edit_activity.php?id=<?= $act['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen-to-square"></i></a>
                                                    <form action="manage_activities.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this activity?');">
                                                        <input type="hidden" name="action" value="delete_activity">
                                                        <input type="hidden" name="activity_id" value="<?= $act['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
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
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const imageInput = document.getElementById('imageInput');
            const fileList = document.getElementById('fileList');
            
            // We use DataTransfer to accumulate files across multiple clicks
            const dataTransfer = new DataTransfer();
            
            if(imageInput && fileList) {
                imageInput.addEventListener('change', function() {
                    // Add newly selected files to our DataTransfer object
                    for (let i = 0; i < this.files.length; i++) {
                        dataTransfer.items.add(this.files[i]);
                    }
                    
                    // Update the input's files with our accumulated list
                    this.files = dataTransfer.files;
                    
                    fileList.innerHTML = ''; // clear old list
                    const files = this.files;
                    if(files.length > 0) {
                        let html = '<strong>Selected Files:</strong><ul class="mb-0 ps-3">';
                        for(let i=0; i<files.length; i++) {
                            html += '<li>' + files[i].name + '</li>';
                        }
                        html += '</ul>';
                        fileList.innerHTML = html;
                    }
                });
            }
        });
    </script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
