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

// Get distinct branches
$stmtBranches = $pdo->query("
    SELECT DISTINCT s.branch 
    FROM tbl_applications a
    JOIN tbl_students s ON a.student_id = s.student_id
    WHERE a.status = 'Selected'
    ORDER BY s.branch ASC
");
$distinctBranches = $stmtBranches->fetchAll(PDO::FETCH_COLUMN);

// Handle Filtering
$filterBatch = filter_input(INPUT_GET, 'batch_year', FILTER_VALIDATE_INT) ?: '';
$filterBranch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING) ?: '';

$query = "
    SELECT s.full_name, s.enrollment_no, s.branch, c.company_name, c.batch_year, a.applied_at
    FROM tbl_applications a
    JOIN tbl_students s ON a.student_id = s.student_id
    JOIN tbl_companies c ON a.company_id = c.company_id
    WHERE a.status = 'Selected'
";

$params = [];
if ($filterBatch) {
    $query .= " AND c.batch_year = ?";
    $params[] = $filterBatch;
}
if ($filterBranch) {
    $query .= " AND s.branch = ?";
    $params[] = $filterBranch;
}

$query .= " ORDER BY c.batch_year DESC, s.branch ASC, s.full_name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$placedStudents = $stmt->fetchAll();

// Get total count
$totalPlaced = count($placedStudents);

// Determine dynamic titles based on filters
$displayYear = $filterBatch ? $filterBatch . '-' . substr($filterBatch + 1, -2) : 'All Years';
$displayBranch = $filterBranch ? $filterBranch : 'Computer Engineering';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Record - GEC Modasa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: white;
            color: black;
            padding: 40px;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .header-title h2, .header-title h3, .header-title h4 {
            font-weight: bold;
            margin: 8px 0;
            color: black;
        }
        .filter-section {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        .filter-section select {
            padding: 5px;
            font-family: 'Times New Roman', Times, serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 2px solid black;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            color: black;
        }
        th {
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid black;
        }
        .text-center {
            text-align: center !important;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="header-title">
        <h2>Government Engineering College, Modasa</h2>
        <h3>Department of <?= htmlspecialchars($displayBranch) ?></h3>
        <br>
        <h4>Placement Record</h4>
        <h4>Academic Year: <?= htmlspecialchars($displayYear) ?></h4>
    </div>

    <div class="filter-section no-print">
        <form action="" method="GET" class="d-flex align-items-center gap-3">
            <div>
                <label for="batch_year"><strong>Year:</strong></label>
                <select name="batch_year" id="batch_year" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    <?php foreach($distinctBatches as $dbatch): ?>
                        <?php if($dbatch): ?>
                            <option value="<?= htmlspecialchars($dbatch) ?>" <?= $filterBatch == $dbatch ? 'selected' : '' ?>><?= htmlspecialchars($dbatch) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="branch"><strong>Branch:</strong></label>
                <select name="branch" id="branch" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    <?php foreach($distinctBranches as $dbranch): ?>
                        <?php if($dbranch): ?>
                            <option value="<?= htmlspecialchars($dbranch) ?>" <?= $filterBranch == $dbranch ? 'selected' : '' ?>><?= htmlspecialchars($dbranch) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" class="btn btn-sm btn-outline-dark" onclick="window.print()">Print</button>
        </form>
    </div>

    <?php if(empty($placedStudents)): ?>
        <p class="text-center mt-5">No placement records found for the selected criteria.</p>
    <?php else: ?>
        <table>
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

</body>
</html>
