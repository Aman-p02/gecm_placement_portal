<?php
/**
 * Public Placement Statistics Page
 */
require_once __DIR__ . '/admin-module/includes/db_connect.php';

// Handle URL Parameters
$filterBranch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING) ?: '';
$filterBatch = filter_input(INPUT_GET, 'batch_year', FILTER_VALIDATE_INT) ?: '';

// Determine dynamic titles
$displayYear = $filterBatch ? $filterBatch . '-' . substr($filterBatch + 1, -2) : 'All Years';
$displayBranch = $filterBranch ? $filterBranch : 'All Branches';

// ---------------------------------------------------------
// GLOBAL DATA FETCHING
// ---------------------------------------------------------
// Fetch aggregate data for branches (used in navbar dropdown and default view)
$aggQuery = "
    SELECT s.branch, COUNT(*) as total_placed
    FROM tbl_applications a
    JOIN tbl_students s ON a.student_id = s.student_id
    JOIN tbl_companies c ON a.company_id = c.company_id
    WHERE a.status = 'Selected'
    GROUP BY s.branch ORDER BY s.branch ASC
";
$stmtAgg = $pdo->query($aggQuery);
$branchStats = $stmtAgg->fetchAll();
$totalOverall = array_sum(array_column($branchStats, 'total_placed'));

// ---------------------------------------------------------
// DATA FETCHING BASED ON VIEW STATE
// ---------------------------------------------------------

if (empty($filterBranch)) {
    // Already fetched globally
} elseif (!empty($filterBranch) && empty($filterBatch)) {
    // VIEW 2: Fetch available batches for the selected branch
    $batchQuery = "
        SELECT c.batch_year, COUNT(*) as total_placed
        FROM tbl_applications a
        JOIN tbl_students s ON a.student_id = s.student_id
        JOIN tbl_companies c ON a.company_id = c.company_id
        WHERE a.status = 'Selected' AND s.branch = ?
        GROUP BY c.batch_year
        ORDER BY c.batch_year DESC
    ";
    $stmtBatch = $pdo->prepare($batchQuery);
    $stmtBatch->execute([$filterBranch]);
    $availableBatches = $stmtBatch->fetchAll();

} else {
    // VIEW 3: Fetch the actual student details for the table
    $query = "
        SELECT s.full_name, s.enrollment_no, s.branch, c.company_name, c.batch_year, a.applied_at
        FROM tbl_applications a
        JOIN tbl_students s ON a.student_id = s.student_id
        JOIN tbl_companies c ON a.company_id = c.company_id
        WHERE a.status = 'Selected' AND s.branch = ? AND c.batch_year = ?
        ORDER BY s.full_name ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$filterBranch, $filterBatch]);
    $placedStudents = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO Optimization -->
    <title>Placement Statistics <?= $filterBranch ? ' - ' . htmlspecialchars($filterBranch) : '' ?> | GEC Modasa</title>
    <meta name="description"
        content="View the placement statistics, records, and successfully placed students of Government Engineering College, Modasa.">

    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

    <style>
        :root {
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
            --light-bg: #e9eef6;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
            color: #333;
        }

        /* ── Floating Pill Navbar ─────────────────── */
        .navbar-wrapper {
            position: sticky;
            top: 0;
            z-index: 1050;
            padding: 0;
            background: transparent;
        }

        .navbar-pill {
            background: #faf9f7;
            border-radius: 0;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06), 0 1px 4px rgba(0, 0, 0, 0.04);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .brand-text {
            color: var(--primary-navy);
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -0.3px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .brand-text span {
            color: var(--accent-coral);
        }

        /* Center Nav Links */
        .nav-pill-links {
            display: flex;
            gap: 4px;
            flex: 1;
        }

        .nav-pill-links::-webkit-scrollbar {
            display: none;
        }

        .nav-pill-links a.nav-link {
            color: #444;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            padding: 8px 14px;
            border-radius: 100px;
            white-space: nowrap;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .nav-pill-links a:hover {
            background: rgba(27, 54, 93, 0.07);
            color: var(--primary-navy);
        }

        /* Right Buttons */
        .nav-pill-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-nav-filled {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 100px;
            padding: 9px 22px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-nav-filled:hover {
            background-color: #0f2340;
            color: white;
            transform: translateY(-1px);
        }

        .btn-nav-outline {
            background: white;
            color: #333;
            border: 1.5px solid #d0d0d0;
            border-radius: 100px;
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: border-color 0.2s ease, background 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-nav-outline:hover {
            border-color: var(--primary-navy);
            color: var(--primary-navy);
            background: #f0f3f8;
        }

        /* Premium Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #0d213b 100%);
            color: white;
            padding: 25px 0 35px 0;
            text-align: center;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 10px 30px rgba(27, 54, 93, 0.2);
        }

        .hero-section h1 {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .total-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Generic Grid Layouts */
        .card-grid {
            margin-top: 40px;
            padding-bottom: 60px;
        }

        /* Branch Cards (Khatarnak UI) */
        .department-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            height: 100%;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .department-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-navy), var(--accent-coral));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .department-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
        }

        .department-card:hover::before {
            opacity: 1;
        }

        .department-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(27, 54, 93, 0.1) 0%, rgba(230, 90, 75, 0.1) 100%);
            color: var(--primary-navy);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 25px;
            transition: all 0.4s ease;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.02);
        }

        .department-card:hover .department-icon {
            transform: scale(1.1) rotate(8deg);
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--accent-coral) 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(230, 90, 75, 0.3);
        }

        .department-card h3 {
            color: var(--primary-navy);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        /* Batch Year Cards */
        .batch-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }

        .batch-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--accent-coral);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.4s ease;
        }

        .batch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .batch-card:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }

        .batch-card h2 {
            color: var(--primary-navy);
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .batch-card span {
            color: #6c757d;
            font-weight: 500;
        }

        .placed-count {
            background: var(--light-bg);
            padding: 10px 20px;
            border-radius: 100px;
            font-weight: 800;
            color: var(--accent-coral);
            font-size: 1.1rem;
            margin-top: 15px;
            display: inline-block;
            border: 1px solid rgba(230, 90, 75, 0.1);
        }

        /* Table Styles (Detail View) */
        .record-container {
            background-color: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            margin-top: 60px;
            font-family: 'Times New Roman', Times, serif;
            color: black;
            position: relative;
        }

        .header-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-title h1,
        .header-title h2,
        .header-title h3,
        .header-title h4 {
            font-weight: bold;
            margin: 8px 0;
            color: black;
        }

        .header-title h1 {
            font-size: 1.8rem;
        }

        .header-title h2 {
            font-size: 1.5rem;
        }

        .header-title h3 {
            font-size: 1.3rem;
        }

        .header-title h4 {
            font-size: 1.1rem;
        }

        table.record-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 2px solid black;
        }

        table.record-table th,
        table.record-table td {
            border: 1px solid black;
            padding: 10px 8px;
            text-align: left;
            color: black;
        }

        table.record-table th {
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid black;
            background-color: rgba(0, 0, 0, 0.02);
        }

        .back-btn {
            position: absolute;
            top: -45px;
            left: 0;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            color: #6c757d;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-btn:hover {
            color: var(--primary-navy);
        }

        @media (max-width: 576px) {
            .brand-text {
                font-size: 1.25rem;
                display: block;
            }

            .top-navbar .container {
                flex-direction: column;
                justify-content: center !important;
                text-align: center;
            }

            .hero-section h1 {
                font-size: 1.8rem;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white;
                padding: 0;
                font-family: 'Times New Roman', Times, serif;
            }

            .record-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                border: none;
            }

            .back-btn {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- ═══════════ FULL WIDTH NAVBAR ═══════════ -->
    <div class="navbar-wrapper no-print">
        <nav class="navbar navbar-expand-lg navbar-pill py-2">
            <div class="container-fluid px-4">

                <!-- Brand -->
                <a href="placement_statistics.php" class="navbar-brand brand-text text-decoration-none">GEC Modasa <span>Placement</span></a>

                <!-- Hamburger Button -->
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Collapsible Content -->
                <div class="collapse navbar-collapse" id="mainNavbar">

                    <!-- Center Nav Links -->
                    <ul
                        class="navbar-nav flex-column flex-lg-row mx-auto mt-3 mt-lg-0 text-center text-lg-start nav-pill-links w-100 justify-content-center">
                        <li class="nav-item"><a class="nav-link" href="training_and_placement.php">Training &amp;
                                Placement</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Rules &amp; Guidelines</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Major Recruiters</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Placement Activities</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Placement Team</a></li>
                    </ul>

                    <!-- Right Action Buttons -->
                    <div class="nav-pill-actions mt-3 mt-lg-0 mb-2 mb-lg-0 text-center d-none d-lg-block">
                        <a href="student-module/login.php" class="btn-nav-filled">
                            <i class="fa-solid fa-user-graduate"></i> Student Login
                        </a>
                    </div>

                </div>
            </div>
        </nav>
    </div>

    <?php if (empty($filterBranch)): ?>
        <!-- ==========================================
             VIEW 1: Premium Branch Cards
             ========================================== -->

        <div class="hero-section no-print">
            <div class="container">
                <h1>Campus Placement Records</h1>
                <p>Select a department below to explore our placement success stories.</p>
                <div class="mt-4">
                    <div class="total-badge m-0">
                        <i class="fa-solid fa-trophy text-warning me-2"></i>
                        Total Placed Students: <strong><?= isset($totalOverall) ? $totalOverall : 0 ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="container card-grid no-print">
            <!-- Branch Cards -->
            <div>
                <?php if (empty($branchStats)): ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-folder-open display-1 text-muted mb-3 opacity-25"></i>
                        <h3 class="text-muted fw-bold">No placement records found.</h3>
                    </div>
                <?php else: ?>
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($branchStats as $stat):
                            $iconClass = 'fa-laptop-code';
                            $branchNameLower = strtolower($stat['branch']);
                            if (strpos($branchNameLower, 'civil') !== false)
                                $iconClass = 'fa-hard-hat';
                            if (strpos($branchNameLower, 'mechanical') !== false || strpos($branchNameLower, 'auto') !== false)
                                $iconClass = 'fa-cogs';
                            if (strpos($branchNameLower, 'electrical') !== false)
                                $iconClass = 'fa-bolt';
                            if (strpos($branchNameLower, 'ec') !== false)
                                $iconClass = 'fa-microchip';
                            if (strpos($branchNameLower, 'it') !== false)
                                $iconClass = 'fa-network-wired';
                            ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <!-- Click goes to View 2 (Select Year) -->
                                <a href="?branch=<?= urlencode($stat['branch']) ?>" class="department-card">
                                    <div class="department-icon">
                                        <i class="fa-solid <?= $iconClass ?>"></i>
                                    </div>
                                    <h3><?= htmlspecialchars($stat['branch']) ?></h3>
                                    <p class="text-muted small mb-0 mt-2">Click to view detailed report</p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- MOBILE VIEW: Student Login Button -->
            <div class="row g-4 justify-content-center">
                <div class="col-12 d-block d-md-none mt-4 text-center">
                    <a href="student-module/login.php" class="btn btn-nav-filled py-3 px-5 fs-5 rounded-pill shadow-sm"
                        style="background: var(--primary-navy); color: white;">
                        <i class="fa-solid fa-user-graduate me-2"></i> Student Login
                    </a>
                </div>
            </div>
        </div>

    <?php elseif (!empty($filterBranch) && empty($filterBatch)): ?>
        <!-- ==========================================
             VIEW 2: Select Batch Year for specific Branch
             ========================================== -->

        <div class="hero-section no-print">
            <div class="container">
                <h1><?= htmlspecialchars($filterBranch) ?></h1>
                <p>Select a specific graduation year to view the detailed placement report.</p>
            </div>
        </div>

        <div class="container card-grid no-print position-relative">
            <a href="training_and_placement.php" class="back-btn mb-3 d-inline-block position-static">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Departments
            </a>

            <?php if (empty($availableBatches)): ?>
                <div class="text-center py-5">
                    <h3 class="text-muted fw-bold">No records found for this department.</h3>
                </div>
            <?php else: ?>
                <div class="row g-4 justify-content-center mt-2">
                    <?php foreach ($availableBatches as $batch): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <!-- Click goes to View 3 (Table) -->
                            <a href="?branch=<?= urlencode($filterBranch) ?>&batch_year=<?= urlencode($batch['batch_year']) ?>"
                                class="batch-card">
                                <span>Placed in</span>
                                <h2><?= htmlspecialchars($batch['batch_year']) ?></h2>
                                <div class="placed-count">
                                    <i class="fa-solid fa-user-graduate me-1"></i>
                                    <?= htmlspecialchars($batch['total_placed']) ?> Students
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ==========================================
             VIEW 3: Simple Printable Table
             ========================================== -->

        <div class="container pb-5">
            <div class="record-container">

                <a href="training_and_placement.php?branch=<?= urlencode($filterBranch) ?>" class="back-btn no-print">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to Years
                </a>

                <div class="text-end mb-3 no-print">
                    <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i> Print Report
                    </button>
                </div>

                <div class="header-title">
                    <h1>Government Engineering College, Modasa</h1>
                    <h2>Department of <?= htmlspecialchars($displayBranch) ?></h2>
                    <br>
                    <h3>Placement Record</h3>
                    <h4>Academic Year: <?= htmlspecialchars($displayYear) ?></h4>
                </div>

                <?php if (empty($placedStudents)): ?>
                    <p class="text-center mt-5">No placement records found.</p>
                <?php else: ?>
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Sr</th>
                                <th style="width: 80px;">Branch</th>
                                <th style="width: 150px;">Enrolment No</th>
                                <th>Full Name</th>
                                <th>Company Name</th>
                                <th style="width: 120px;">Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $srNo = 1;
                            foreach ($placedStudents as $s):
                                ?>
                                <tr>
                                    <td class="text-center"><?= $srNo++ ?></td>
                                    <td class="text-center"><?= htmlspecialchars($s['branch']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($s['enrollment_no']) ?></td>
                                    <td><?= htmlspecialchars($s['full_name']) ?></td>
                                    <td><?= htmlspecialchars($s['company_name']) ?></td>
                                    <td class="text-center">On Campus</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>