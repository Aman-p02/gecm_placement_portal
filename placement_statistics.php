<?php
/**
 * Public Placement Statistics Page
 */
require_once __DIR__ . '/admin-module/includes/db_connect.php';

// Get distinct batch years
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
$filterBranch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING) ?: '';

// Determine dynamic titles
$displayYear = $filterBatch ? $filterBatch . '-' . substr($filterBatch + 1, -2) : 'All Years';
$displayBranch = $filterBranch ? $filterBranch : 'All Branches';

// If no branch is selected, fetch aggregate data for branch cards
if (empty($filterBranch)) {
    $aggQuery = "
        SELECT s.branch, COUNT(*) as total_placed
        FROM tbl_applications a
        JOIN tbl_students s ON a.student_id = s.student_id
        JOIN tbl_companies c ON a.company_id = c.company_id
        WHERE a.status = 'Selected'
    ";
    $aggParams = [];
    if ($filterBatch) {
        $aggQuery .= " AND c.batch_year = ?";
        $aggParams[] = $filterBatch;
    }
    $aggQuery .= " GROUP BY s.branch ORDER BY s.branch ASC";
    
    $stmtAgg = $pdo->prepare($aggQuery);
    $stmtAgg->execute($aggParams);
    $branchStats = $stmtAgg->fetchAll();
    
    // Calculate total across all branches
    $totalOverall = array_sum(array_column($branchStats, 'total_placed'));
} else {
    // If a branch is selected, fetch the actual student details for the table
    $query = "
        SELECT s.full_name, s.enrollment_no, s.branch, c.company_name, c.batch_year, a.applied_at
        FROM tbl_applications a
        JOIN tbl_students s ON a.student_id = s.student_id
        JOIN tbl_companies c ON a.company_id = c.company_id
        WHERE a.status = 'Selected' AND s.branch = ?
    ";
    $params = [$filterBranch];
    
    if ($filterBatch) {
        $query .= " AND c.batch_year = ?";
        $params[] = $filterBatch;
    }
    
    $query .= " ORDER BY c.batch_year DESC, s.full_name ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $placedStudents = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO Optimization -->
    <title>Placement Statistics <?= $filterBatch ? ' - ' . $filterBatch : '' ?> | GEC Modasa</title>
    <meta name="description" content="View the placement statistics, records, and successfully placed students of Government Engineering College, Modasa.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
            --light-bg: #f8faff;
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
            color: #333;
        }
        .top-navbar {
            background-color: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .brand-text {
            color: var(--primary-navy);
            font-weight: 800;
            font-size: 1.5rem;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .brand-text span {
            color: var(--accent-coral);
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

        /* Filter Controls */
        .filter-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 100px;
            padding: 10px 25px;
            display: inline-flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.4);
            margin-top: 30px;
        }
        .filter-glass select {
            border: none;
            background: transparent;
            font-weight: 600;
            color: var(--primary-navy);
            font-size: 1.1rem;
            cursor: pointer;
            outline: none;
            box-shadow: none !important;
            padding-right: 30px;
        }
        .filter-glass select:focus {
            box-shadow: none;
        }

        /* Branch Cards (Khatarnak UI) */
        .branch-grid {
            margin-top: 40px;
            padding-bottom: 60px;
        }
        .branch-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            height: 100%;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
        }
        .branch-card::before {
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
        .branch-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        }
        .branch-card:hover::before {
            opacity: 1;
        }
        .branch-icon {
            width: 70px;
            height: 70px;
            background: rgba(27, 54, 93, 0.05);
            color: var(--primary-navy);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .branch-card:hover .branch-icon {
            transform: scale(1.1) rotate(5deg);
            background: var(--primary-navy);
            color: white;
        }
        .branch-card h3 {
            color: var(--primary-navy);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        .branch-card p {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }
        .placed-count {
            background: var(--light-bg);
            padding: 10px 20px;
            border-radius: 100px;
            font-weight: 800;
            color: var(--accent-coral);
            font-size: 1.1rem;
            margin-top: auto;
            border: 1px solid rgba(230, 90, 75, 0.1);
        }
        
        .total-badge {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 100px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Table Styles (Detail View) */
        .record-container {
            background-color: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            margin-top: 60px;
            font-family: 'Times New Roman', Times, serif;
            color: black;
            position: relative;
        }
        .header-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .header-title h1, .header-title h2, .header-title h3, .header-title h4 {
            font-weight: bold;
            margin: 8px 0;
            color: black;
        }
        .header-title h1 { font-size: 1.8rem; }
        .header-title h2 { font-size: 1.5rem; }
        .header-title h3 { font-size: 1.3rem; }
        .header-title h4 { font-size: 1.1rem; }
        
        table.record-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 2px solid black;
        }
        table.record-table th, table.record-table td {
            border: 1px solid black;
            padding: 10px 8px;
            text-align: left;
            color: black;
        }
        table.record-table th {
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid black;
            background-color: rgba(0,0,0,0.02);
        }
        .text-center {
            text-align: center !important;
        }
        
        .back-btn {
            position: absolute;
            top: 40px;
            left: 40px;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            color: #6c757d;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-btn:hover {
            color: var(--primary-navy);
        }

        @media print {
            .no-print { display: none !important; }
            body { background-color: white; padding: 0; font-family: 'Times New Roman', Times, serif; }
            .record-container { box-shadow: none; padding: 0; margin: 0; border: none; }
            .back-btn { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar top-navbar no-print">
        <div class="container d-flex justify-content-center align-items-center">
            <span class="brand-text">GEC Modasa <span>Placement Statistics</span></span>
        </div>
    </nav>

    <?php if (empty($filterBranch)): ?>
        <!-- ==========================================
             VIEW 1: Premium Branch Cards (Khatarnak UI)
             ========================================== -->
        
        <div class="hero-section no-print">
            <div class="container">
                <h1>Campus Placement Records</h1>
                <p>Discover the success stories of our brilliant minds securing their future in top-tier organizations.</p>
                
                <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-4">
                    <form action="" method="GET" class="filter-glass m-0">
                        <i class="fa-solid fa-calendar-check text-muted ps-2"></i>
                        <select name="batch_year" onchange="this.form.submit()">
                            <option value="">All Batches</option>
                            <?php foreach($distinctBatches as $dbatch): ?>
                                <?php if($dbatch): ?>
                                    <option value="<?= htmlspecialchars($dbatch) ?>" <?= $filterBatch == $dbatch ? 'selected' : '' ?>>Class of <?= htmlspecialchars($dbatch) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    
                    <div class="total-badge m-0">
                        <i class="fa-solid fa-trophy text-warning me-2"></i> 
                        Total Placed Students: <strong><?= isset($totalOverall) ? $totalOverall : 0 ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="container branch-grid no-print">
            <?php if(empty($branchStats)): ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-folder-open display-1 text-muted mb-3 opacity-25"></i>
                    <h3 class="text-muted fw-bold">No placement records found.</h3>
                    <p class="text-muted">No students were found for the selected criteria.</p>
                </div>
            <?php else: ?>
                <div class="row g-4 justify-content-center">
                    <?php foreach($branchStats as $stat): 
                        // Assign a generic icon based on branch name keywords (just for visuals)
                        $iconClass = 'fa-laptop-code';
                        $branchNameLower = strtolower($stat['branch']);
                        if (strpos($branchNameLower, 'civil') !== false) $iconClass = 'fa-hard-hat';
                        if (strpos($branchNameLower, 'mechanical') !== false || strpos($branchNameLower, 'auto') !== false) $iconClass = 'fa-cogs';
                        if (strpos($branchNameLower, 'electrical') !== false) $iconClass = 'fa-bolt';
                        if (strpos($branchNameLower, 'electronics') !== false || strpos($branchNameLower, 'ec') !== false) $iconClass = 'fa-microchip';
                        if (strpos($branchNameLower, 'information') !== false || strpos($branchNameLower, 'it') !== false) $iconClass = 'fa-network-wired';
                    ?>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <!-- Make the card clickable to go to the detail view -->
                            <a href="?branch=<?= urlencode($stat['branch']) ?><?= $filterBatch ? '&batch_year=' . urlencode($filterBatch) : '' ?>" class="branch-card">
                                <div class="branch-icon">
                                    <i class="fa-solid <?= $iconClass ?>"></i>
                                </div>
                                <h3><?= htmlspecialchars($stat['branch']) ?></h3>
                                <p>Click to view detailed report</p>
                                
                                <div class="placed-count">
                                    <i class="fa-solid fa-user-graduate me-1"></i>
                                    <?= htmlspecialchars($stat['total_placed']) ?> Placed
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ==========================================
             VIEW 2: Simple Printable Table (Detail View)
             ========================================== -->
             
        <div class="container pb-5">
            <div class="record-container">
                
                <a href="placement_statistics.php<?= $filterBatch ? '?batch_year=' . urlencode($filterBatch) : '' ?>" class="back-btn no-print">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to Branches
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

                <?php if(empty($placedStudents)): ?>
                    <p class="text-center mt-5">No placement records found for this branch.</p>
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
                            foreach($placedStudents as $s): 
                            ?>
                            <tr>
                                <td class="text-center"><?= $srNo++ ?></td>
                                <td class="text-center"><?= htmlspecialchars($s['branch']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($s['enrollment_no']) ?></td>
                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                <td><?= htmlspecialchars($s['company_name']) ?></td>
                                <td>On Campus</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
