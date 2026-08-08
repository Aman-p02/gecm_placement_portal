<style>
    .admin-footer {
        background: linear-gradient(135deg, #1B365D 0%, #11233D 100%);
        background: 
            linear-gradient(135deg, #1B365D 0%, #11233D 100%) padding-box,
            linear-gradient(90deg, #E65A4B, #f59e0b) border-box;
        border-top: 5px solid transparent;
        color: rgba(255, 255, 255, 0.8);
        border-radius: 16px 16px 0 0;
        margin-top: auto;
        width: 100%;
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
        <div class="d-flex justify-content-center align-items-center text-center" style="font-size: 0.85rem;">
            <div class="mb-2 mb-md-0">&copy; <?= date('Y') ?> <strong class="text-white">GEC Modasa Placement Cell</strong></div>
        </div>
    </div>
</footer>
