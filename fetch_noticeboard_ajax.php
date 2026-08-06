<?php
require_once __DIR__ . '/admin-module/includes/db_connect.php';

$noticeboardQuery = "
    SELECT company_name, logo_path, last_date_to_apply, drive_type, batch_year, created_at 
    FROM tbl_companies 
    ORDER BY 
        CASE WHEN last_date_to_apply >= CURDATE() THEN 0 ELSE 1 END ASC,
        CASE WHEN last_date_to_apply >= CURDATE() THEN last_date_to_apply END ASC,
        last_date_to_apply DESC
    LIMIT 15
";
$noticeboardStmt = $pdo->query($noticeboardQuery);
$noticeboardCompanies = $noticeboardStmt->fetchAll();

if (empty($noticeboardCompanies)) {
    echo '<div class="d-flex flex-column justify-content-center align-items-center h-100 text-muted">';
    echo '<i class="fa-solid fa-folder-open display-4 mb-3 opacity-25"></i>';
    echo '<p class="mb-0 fs-5">No active placement drives currently.</p>';
    echo '</div>';
    exit;
}
?>
<marquee direction="up" scrollamount="3" onmouseover="this.stop()" onmouseout="this.start()" height="445">
    <?php foreach($noticeboardCompanies as $company): ?>
        <?php 
            $isExpired = strtotime($company['last_date_to_apply'] . ' 23:59:59') < time();
            $statusClass = $isExpired ? 'bg-secondary' : 'bg-success';
            $statusText = $isExpired ? 'Closed' : 'Active';
        ?>
        <a href="student-module/login.php" class="notice-item">
            <?php if($company['logo_path']): ?>
                <img src="admin-module/<?= htmlspecialchars($company['logo_path']) ?>" alt="Logo" class="notice-logo">
            <?php else: ?>
                <div class="notice-logo d-flex align-items-center justify-content-center text-secondary bg-light">
                    <i class="fa-solid fa-building fs-4"></i>
                </div>
            <?php endif; ?>
            
            <div class="notice-details">
                <h5><?= htmlspecialchars($company['company_name']) ?> <span class="badge <?= $statusClass ?> ms-2" style="font-size: 0.7rem; vertical-align: middle;"><?= $statusText ?></span></h5>
                <div class="notice-meta">
                    <span><i class="fa-solid fa-graduation-cap me-1"></i> Batch <?= htmlspecialchars($company['batch_year'] ?? 'N/A') ?></span>
                    <span><i class="fa-solid fa-map-marker-alt me-1"></i> <?= htmlspecialchars($company['drive_type'] ?? 'On Campus') ?></span>
                    <span class="<?= $isExpired ? 'text-danger' : 'text-success fw-bold' ?>"><i class="fa-regular fa-clock me-1"></i> Deadline: <?= date('d M Y', strtotime($company['last_date_to_apply'])) ?></span>
                </div>
            </div>
            <div class="notice-action">
                <span class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold">Apply Now <i class="fa-solid fa-arrow-right ms-1"></i></span>
            </div>
        </a>
    <?php endforeach; ?>
</marquee>
