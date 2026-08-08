<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';

require_admin_login();

$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];
$adminRole = $_SESSION['admin_role'];

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

$activityId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$activityId) {
    header("Location: manage_activities.php");
    exit;
}

// Fetch Activity and verify ownership
$stmt = $pdo->prepare("SELECT * FROM placement_activities WHERE id = ? AND admin_id = ?");
$stmt->execute([$activityId, $adminId]);
$activity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activity) {
    $_SESSION['page_error'] = "Activity not found or you don't have permission to edit it.";
    header("Location: manage_activities.php");
    exit;
}

// Handle Update Details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_activity') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    $eventType = filter_input(INPUT_POST, 'event_type', FILTER_SANITIZE_STRING);
    
    if (empty($title) || empty($description) || empty($eventDate) || empty($eventType)) {
        $error = "Title, Description, Date, and Type are required.";
    } else {
        try {
            $uploadDir = __DIR__ . '/uploads/activities/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Handle PDF update
            $pdfPath = $activity['report_pdf'];
            if (isset($_FILES['report_pdf']) && $_FILES['report_pdf']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['report_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $pdfName = uniqid('report_') . '.pdf';
                    if(move_uploaded_file($_FILES['report_pdf']['tmp_name'], $uploadDir . $pdfName)) {
                        $pdfPath = 'admin-module/uploads/activities/' . $pdfName;
                        // Delete old PDF
                        if($activity['report_pdf']) {
                            $oldPdfPath = dirname(__DIR__) . '/' . str_replace('admin-module/', '', $activity['report_pdf']);
                            if(file_exists($oldPdfPath)) unlink($oldPdfPath);
                        }
                    }
                }
            }

            $stmt = $pdo->prepare("UPDATE placement_activities SET title = ?, description = ?, event_date = ?, event_type = ?, report_pdf = ? WHERE id = ?");
            $stmt->execute([$title, $description, $eventDate, $eventType, $pdfPath, $activityId]);
            
            // Upload new photos if any
            $uploadDir = __DIR__ . '/uploads/activities/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (isset($_FILES['images']) && is_array($_FILES['images']['name']) && !empty($_FILES['images']['name'][0])) {
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
            $_SESSION['page_success'] = "Activity updated successfully!";
            header("Location: edit_activity.php?id=" . $activityId);
            exit;
        } catch (Exception $e) {
            $error = "Error updating: " . $e->getMessage();
        }
    }
}

// Handle Delete Photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
    $imageId = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
    if ($imageId) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM activity_images WHERE id = ? AND activity_id = ?");
            $stmt->execute([$imageId, $activityId]);
            $imagePath = $stmt->fetchColumn();
            
            if ($imagePath) {
                // Delete from DB
                $delStmt = $pdo->prepare("DELETE FROM activity_images WHERE id = ?");
                $delStmt->execute([$imageId]);
                // Delete file
                $fullPath = dirname(__DIR__) . '/' . str_replace('admin-module/', '', $imagePath);
                if(file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $_SESSION['page_success'] = "Photo deleted successfully!";
            }
        } catch (Exception $e) {
            $_SESSION['page_error'] = "Failed to delete photo.";
        }
    }
    header("Location: edit_activity.php?id=" . $activityId);
    exit;
}

// Fetch current images
$stmt = $pdo->prepare("SELECT * FROM activity_images WHERE activity_id = ?");
$stmt->execute([$activityId]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Re-fetch updated activity info in case of error
$stmt = $pdo->prepare("SELECT * FROM placement_activities WHERE id = ?");
$stmt->execute([$activityId]);
$activity = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <style>
        /* Force hide TinyMCE warning */
        .tox-notifications-container { display: none !important; }
    </style>
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
                <div class="d-flex align-items-center"><button class="btn btn-sm btn-outline-secondary me-3" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button><h5 class="m-0 text-muted">Edit Activity</h5></div>
                <div>
                    <span class="fw-medium me-3 text-dark">Hi, <?= htmlspecialchars($adminName) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($adminRole)) ?></span>
                </div>
            </div>

            
        
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Activity</h4>
                <a href="manage_activities.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to Activities</a>
            </div>

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
                <!-- Edit Details Form -->
                <div class="col-md-7 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                            <h5 class="mb-0">Activity Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="edit_activity.php?id=<?= $activityId ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_activity">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Title</label>
                                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($activity['title']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Event Date</label>
                                    <input type="date" name="event_date" class="form-control" required value="<?= htmlspecialchars($activity['event_date']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Type of Event</label>
                                    <select name="event_type" class="form-select" required>
                                        <option value="Institute Level" <?= $activity['event_type'] == 'Institute Level' ? 'selected' : '' ?>>Institute Level</option>
                                        <option value="Department Level" <?= $activity['event_type'] == 'Department Level' ? 'selected' : '' ?>>Department Level</option>
                                        <option value="District Level" <?= $activity['event_type'] == 'District Level' ? 'selected' : '' ?>>District Level</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium">Upload New Report (PDF - Optional)</label>
                                    <input type="file" name="report_pdf" class="form-control" accept="application/pdf">
                                    <?php if($activity['report_pdf']): ?>
                                        <div class="mt-2 text-muted" style="font-size: 0.85rem;">
                                            Current PDF: <a href="../<?= str_replace('admin-module/', '', $activity['report_pdf']) ?>" target="_blank">View PDF</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Description</label>
                                    <textarea name="description" class="form-control" rows="6" required><?= htmlspecialchars($activity['description']) ?></textarea>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-medium">Add More Photos (Optional)</label>
                                    <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                                    <div id="fileList" class="mt-2 text-muted" style="font-size: 0.85rem;"></div>
                                    <div class="form-text">You can select multiple new photos to add to this activity.</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-save me-2"></i>Update Activity</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Manage Existing Photos -->
                <div class="col-md-5 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                            <h5 class="mb-0">Manage Photos</h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($images)): ?>
                                <p class="text-muted">No photos uploaded for this activity.</p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach($images as $img): ?>
                                        <div class="col-6">
                                            <div class="card border-0 bg-light rounded-3 overflow-hidden position-relative">
                                                <!-- Image stored as 'admin-module/uploads/...' so from here we need to point to it properly. Since we are in admin-module, we can strip 'admin-module/' -->
                                                <?php $imgSrc = str_replace('admin-module/', '', $img['image_path']); ?>
                                                <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top" alt="Activity Photo" style="height: 120px; object-fit: cover;">
                                                <div class="position-absolute top-0 end-0 p-1">
                                                    <form action="edit_activity.php?id=<?= $activityId ?>" method="POST" onsubmit="return confirm('Delete this photo?');">
                                                        <input type="hidden" name="action" value="delete_photo">
                                                        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-circle shadow-sm" style="width: 28px; height: 28px; padding: 0; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
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
