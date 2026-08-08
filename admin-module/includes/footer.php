<style>
    .admin-footer {
        background: #1B365D;
        color: rgba(255, 255, 255, 0.8);
        border-radius: 16px 16px 0 0;
        margin-top: 60px;
        padding: 35px 30px 20px;
        box-shadow: 0 -10px 30px rgba(0,0,0,0.05);
    }
    .admin-footer-link {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }
    .admin-footer-link:hover {
        color: #E65A4B;
        padding-left: 6px;
    }
    .admin-footer-title {
        color: #fff;
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }
</style>
<footer class="admin-footer no-print">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-lg-4 col-md-12 mb-4 mb-lg-0 pe-lg-4">
                <h5 class="admin-footer-title"><i class="fa-solid fa-shield-halved me-2" style="color: #E65A4B;"></i>Admin Portal</h5>
                <p style="font-size: 0.85rem; line-height: 1.8;">
                    Manage placement drives, student applications, company records, and generate detailed reports efficiently from this centralized admin dashboard.
                </p>
            </div>
            <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                <h5 class="admin-footer-title">Quick Actions</h5>
                <div class="d-flex flex-column gap-2">
                    <a href="dashboard.php" class="admin-footer-link"><i class="fa-solid fa-angle-right me-2"></i> Dashboard Overview</a>
                    <a href="manage_companies.php" class="admin-footer-link"><i class="fa-solid fa-angle-right me-2"></i> Manage Companies</a>
                    <a href="manage_activities.php" class="admin-footer-link"><i class="fa-solid fa-angle-right me-2"></i> Manage Activities</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5 class="admin-footer-title">Management & Reports</h5>
                <div class="d-flex flex-column gap-2">
                    <a href="all_students.php" class="admin-footer-link"><i class="fa-solid fa-angle-right me-2"></i> Student Database</a>
                    <a href="view_applicants.php" class="admin-footer-link"><i class="fa-solid fa-angle-right me-2"></i> View Applications</a>
                    <a href="reports.php" class="admin-footer-link"><i class="fa-solid fa-angle-right me-2"></i> Placement Reports</a>
                </div>
            </div>
        </div>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 25px 0 20px;">
        <div class="d-flex flex-column justify-content-center align-items-center text-center" style="font-size: 0.85rem;">
            <div class="mb-3">&copy; <?= date('Y') ?> <strong class="text-white">GEC Modasa Placement Cell</strong></div>
            <a href="/gec_placement_portal/developers.php" title="Meet the Developers Team"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background-color: #0f172a; color: white; border-radius: 50px; padding: 6px 14px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s ease;">
                <i class="fa-solid fa-code"></i> Developer Team
            </a>
        </div>
    </div>
</footer>
