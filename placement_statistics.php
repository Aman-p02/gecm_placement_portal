<?php
/**
 * Public Placement Statistics Page
 */
require_once __DIR__ . '/admin-module/includes/db_connect.php';

// Get distinct batch years from selected students for the filter
$stmt = $pdo->query("
    SELECT DISTINCT c.batch_year 
    FROM tbl_applications a
    JOIN tbl_companies c ON a.company_id = c.company_id
    WHERE a.status = 'Selected'
    ORDER BY c.batch_year DESC
");
$distinctBatches = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle Filtering
$filterBatch = filter_input(INPUT_GET, 'batch_year', FILTER_VALIDATE_INT) ?: '';

$query = "
    SELECT s.full_name, s.branch, p.passing_year, p.profile_pic, c.company_name, c.batch_year, a.applied_at
    FROM tbl_applications a
    JOIN tbl_students s ON a.student_id = s.student_id
    LEFT JOIN tbl_student_profile p ON s.student_id = p.student_id
    JOIN tbl_companies c ON a.company_id = c.company_id
    WHERE a.status = 'Selected'
";

$params = [];
if ($filterBatch) {
    $query .= " AND c.batch_year = ?";
    $params[] = $filterBatch;
}

$query .= " ORDER BY c.batch_year DESC, a.applied_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$placedStudents = $stmt->fetchAll();

// Get total count
$totalPlaced = count($placedStudents);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Statistics - GEC Modasa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
            --light-bg: #f4f6f9;
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
        }
        .top-navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .brand-text {
            color: var(--primary-navy);
            font-weight: 700;
            font-size: 1.4rem;
            text-decoration: none;
        }
        .brand-text span {
            color: var(--accent-coral);
        }
        .custom-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: none;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .page-header {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #2c5282 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
            text-align: center;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-bottom: 4px solid var(--accent-coral);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-navy);
        }
        .student-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e2e8f0;
        }
        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-navy);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            border: 3px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar top-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="brand-text" href="index.php">GEC Modasa <span>Placement</span></a>
            <div>
                <a href="student-module/login.php" class="btn btn-outline-primary me-2 fw-medium">Student Login</a>
                <a href="admin-module/login.php" class="btn btn-primary fw-medium" style="background-color: var(--primary-navy); border-color: var(--primary-navy);">Admin Login</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3"><i class="fa-solid fa-chart-line me-3 text-warning"></i>Placement Statistics</h1>
            <p class="lead mb-0 text-light opacity-75">Celebrating the success of our students who secured positions in top companies.</p>
        </div>
    </div>

    <div class="container mb-5">
        
        <!-- Controls & Stats Row -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-4">
                <div class="stat-card py-3">
                    <div class="stat-number"><?= $totalPlaced ?></div>
                    <div class="text-muted fw-medium text-uppercase small">Total Students Placed</div>
                </div>
            </div>
            
            <div class="col-md-8 text-md-end">
                <form action="" method="GET" class="d-inline-flex align-items-center gap-2 bg-white p-3 rounded shadow-sm">
                    <label class="form-label mb-0 fw-medium text-muted text-nowrap"><i class="fa-solid fa-filter me-1"></i> Filter Batch:</label>
                    <select name="batch_year" class="form-select form-select-lg w-auto" onchange="this.form.submit()">
                        <option value="">All Batches</option>
                        <?php foreach($distinctBatches as $dbatch): ?>
                            <?php if($dbatch): ?>
                                <option value="<?= htmlspecialchars($dbatch) ?>" <?= $filterBatch == $dbatch ? 'selected' : '' ?>>Batch <?= htmlspecialchars($dbatch) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- Grid of Placed Students -->
        <?php if(empty($placedStudents)): ?>
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <i class="fa-solid fa-users-slash display-1 text-muted mb-3 opacity-25"></i>
                <h4 class="text-muted">No placement records found.</h4>
                <?php if($filterBatch): ?>
                    <p class="text-muted">No students were found for Batch <?= htmlspecialchars($filterBatch) ?>.</p>
                    <a href="placement_statistics.php" class="btn btn-outline-secondary mt-2">Clear Filter</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach($placedStudents as $s): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="custom-card p-4 text-center h-100 d-flex flex-column align-items-center">
                            
                            <div class="mb-3 position-relative">
                                <?php if(!empty($s['profile_pic'])): ?>
                                    <img src="student-module/<?= htmlspecialchars($s['profile_pic']) ?>" alt="Profile Picture" class="student-avatar">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?= strtoupper(substr($s['full_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-n3">
                                    <span class="badge bg-success border border-white border-2 rounded-pill shadow-sm px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Hired</span>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-1 mt-2 text-dark"><?= htmlspecialchars($s['full_name']) ?></h5>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($s['branch']) ?></p>
                            
                            <div class="mt-auto w-100 bg-light rounded p-3 text-start">
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary-subtle">
                                    <span class="text-muted small">Company</span>
                                    <strong class="text-dark"><?= htmlspecialchars($s['company_name']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary-subtle">
                                    <span class="text-muted small">Batch Year</span>
                                    <strong class="text-primary"><?= htmlspecialchars($s['batch_year'] ?? $s['passing_year'] ?? 'N/A') ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Placement</span>
                                    <span class="badge bg-info text-dark">On Campus</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container text-center text-muted small">
            &copy; <?= date('Y') ?> Government Engineering College, Modasa. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
