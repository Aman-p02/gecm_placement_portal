<?php 
// Placement Team - Dynamic MySQL Database integration with fallback
if (file_exists(__DIR__ . '/admin-module/includes/db_connect.php')) {
    @include_once __DIR__ . '/admin-module/includes/db_connect.php';
}

$team_members = [];

// Try fetching from MySQL table if connection exists
if (isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `tbl_placement_team` ORDER BY `sort_order` ASC, `id` ASC");
        if ($stmt) {
            $team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (\PDOException $e) {
        // Fallback to older table name if tbl_placement_team doesn't exist
        try {
            $stmt = $pdo->query("SELECT * FROM `placement_team` ORDER BY `sort_order` ASC, `id` ASC");
            if ($stmt) {
                $team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $e2) {
            // Do nothing, fallback array will be used
        }
    }
}

// Aap apni pasand ka image folder path yahan change kar sakte hain:
$image_base_dir = 'assets/images/team/';

// Default Fallback Array with User's Exact File Names
if (empty($team_members)) {
    $team_members = [
        ['name' => 'Dr. M M Goyani', 'designation' => 'Associate Professor', 'department' => 'CE Department', 'role' => 'Placement Coordinator - Institute', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'MMG.jpg'],
        ['name' => 'Prof. P M Mistri', 'designation' => 'Assistant Professor', 'department' => 'ME Department', 'role' => 'Placement Co-Coordinator - Institute', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'mech_PMM.jpg'],
        ['name' => 'Prof. A. J Patel', 'designation' => 'Assistant Professor', 'department' => 'Civil Department', 'role' => 'Departmental Placement Coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'app_AJP.jpg'],
        ['name' => 'Prof. M. G. Patel', 'designation' => 'Asst. Prof.', 'department' => 'ME Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'mech_MGP.jpg'],
        ['name' => 'Prof. J. C. Gamit', 'designation' => 'Asst. Prof.', 'department' => 'ME Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'mech_JCG.jpg'],
        ['name' => 'Prof. S. L. Ghanchi', 'designation' => 'Asst. Prof.', 'department' => 'ME Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'mech_SLG.jpg'],
        ['name' => 'Prof. H. K. Sharma', 'designation' => 'Asst. Prof.', 'department' => 'Civil Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'civil_HKS.jpg'],
        ['name' => 'Prof. A. D. Chaudhari', 'designation' => 'Asst. Prof.', 'department' => 'IT Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'it_ac.jpg'],
        ['name' => 'Prof. S. R. Patel', 'designation' => 'Asst. Prof.', 'department' => 'CE Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'ce_srp.jpg'],
        ['name' => 'Prof. N. V. Nagekar', 'designation' => 'Asst. Prof.', 'department' => 'CE Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'ce_nvn.jpg'],
        ['name' => 'Prof. M. V. Chauhan', 'designation' => 'Asst. Prof.', 'department' => 'IT Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'CE_MC.jpg'],
        ['name' => 'Prof. B. A. Brahmbhatt', 'designation' => 'Asst. Prof.', 'department' => 'EC Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'ec_BAB.jpg'],
        ['name' => 'Prof. P. V. Patel', 'designation' => 'Asst. Prof.', 'department' => 'EC Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'ec_PVP.jpg'],
        ['name' => 'Prof. D. U. Thakkar', 'designation' => 'Asst. Prof.', 'department' => 'EE Department', 'role' => 'Departmental Placement coordinator', 'email' => 'faculty.name@gecmodasa.ac.in', 'photo' => $image_base_dir . 'ee_darshan.png']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Team | GEC Modasa Placement Cell</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #003B71;
            --accent-coral: #E65A4B;
            --light-bg: #e9eef6;
        }

        html {
            overflow-y: scroll;
            scrollbar-gutter: stable;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
            color: #333;
            overflow-x: hidden;
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
            color: #555;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 7px 15px;
            border-radius: 6px;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .nav-pill-links a.nav-link:hover {
            background: #eef2ff;
            color: var(--primary-navy);
        }

        .nav-pill-links a.nav-link.active {
            background: var(--primary-navy);
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 8px rgba(0, 59, 113, 0.18);
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
            color: white !important;
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

        /* Mobile Navbar Responsive Alignment */
        @media (max-width: 991.98px) {
            .navbar-pill .container-fluid {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
            }
            .brand-text {
                font-size: 1.1rem;
            }
            .navbar-toggler {
                padding: 4px 8px;
            }
            .navbar-collapse {
                width: 100%;
                flex-basis: 100%;
            }
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #082848 100%);
            color: white;
            padding: 28px 0 35px 0;
            text-align: center;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 59, 113, 0.2);
        }

        .hero-section h1 {
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 8px;
        }

        .hero-section p {
            font-size: 1.02rem;
            opacity: 0.9;
            max-width: 760px;
            margin: 0 auto 16px;
            line-height: 1.5;
        }

        .hero-badge {
            display: inline-block;
            padding: 5px 16px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 100px;
            font-weight: 600;
            font-size: 0.82rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Main Content Box */
        .content-card {
            background: white;
            border-radius: 16px;
            padding: 35px 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            margin-bottom: 40px;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .page-header-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #2C3E50;
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .page-header-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 75px;
            height: 3px;
            background: #3498DB;
            border-radius: 2px;
        }

        .intro-text p {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.65;
            margin-bottom: 14px;
        }

        /* Placement Team Table */
        .team-table-card {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            margin-top: 30px;
        }

        .team-table-header {
            background: #003B71;
            color: white;
            padding: 14px 20px;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .team-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .team-table th {
            background: #F8FAFC;
            color: #333;
            font-weight: 800;
            font-size: 0.92rem;
            padding: 14px 20px;
            text-align: center;
            border-bottom: 2px solid #E2E8F0;
            border-right: 1px solid #E2E8F0;
        }

        .team-table th:last-child {
            border-right: none;
        }

        .team-table td {
            padding: 18px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #EDF2F7;
            border-right: 1px solid #EDF2F7;
            font-size: 0.92rem;
        }

        .team-table td:last-child {
            border-right: none;
        }

        .team-table tr:hover {
            background-color: #F8FAFC;
        }

        /* Photo Column */
        .photo-cell {
            width: 160px;
            text-align: center;
        }

        .faculty-photo {
            width: 105px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #CBD5E1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            background: #FFF;
            padding: 3px;
        }

        /* Details Column */
        .details-cell {
            text-align: center;
            color: #4A5568;
            line-height: 1.6;
        }

        .member-name {
            font-weight: 700;
            color: #2D3748;
            font-size: 0.98rem;
        }

        .member-dept {
            color: #718096;
            font-size: 0.9rem;
        }

        /* Role Column */
        .role-cell {
            text-align: center;
            color: #4A5568;
            font-size: 0.93rem;
            font-weight: 600;
        }

        /* Animation classes */
        .animate-row td {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }

        .animate-row.visible td {
            opacity: 1;
            transform: translateY(0);
        }

        /* Prevent vertical scrollbar on table container during animation */
        .table-responsive {
            overflow-y: hidden;
        }

        /* Mobile Responsive Table (Stacking) */
        @media (max-width: 768px) {
            .team-table thead {
                display: none;
            }
            .team-table, .team-table tbody, .team-table tr, .team-table td {
                display: block;
                width: 100%;
            }
            .team-table tr {
                margin-bottom: 20px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 15px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            }
            .team-table td {
                border: none !important;
                padding: 8px 0 !important;
                text-align: center;
            }
            .faculty-photo {
                margin: 0 auto;
            }
            .role-cell {
                margin-top: 10px;
                padding-top: 12px !important;
                border-top: 1px dashed #e2e8f0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <div class="navbar-wrapper no-print">
        <nav class="navbar navbar-expand-lg navbar-pill py-2">
            <div class="container-fluid px-4">
                <a href="placement_statistics.php" class="navbar-brand brand-text text-decoration-none">GEC Modasa <span>Placement</span></a>

                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav flex-column flex-lg-row mx-auto mt-3 mt-lg-0 text-center text-lg-start nav-pill-links w-100 justify-content-center">
                        <li class="nav-item"><a class="nav-link" href="placement_statistics.php?view=placement">Training &amp; Placement</a></li>
                        <li class="nav-item"><a class="nav-link" href="rules_and_guidelines.php">Rules &amp; Guidelines</a></li>
                        <li class="nav-item"><a class="nav-link" href="major_recruiters.php">Major Recruiters</a></li>
                        <li class="nav-item"><a class="nav-link" href="placement_activities.php">Placement Activities</a></li>
                        <li class="nav-item"><a class="nav-link active" href="placement_team.php">Placement Team</a></li>
                        <li class="nav-item d-lg-none mt-2">
                            <a class="btn-nav-filled w-100 text-center justify-content-center" href="student-module/login.php">
                                <i class="fa-solid fa-user-graduate"></i> Student Login
                            </a>
                        </li>
                    </ul>

                    <div class="nav-pill-actions mt-3 mt-lg-0 mb-2 mb-lg-0 text-center d-none d-lg-block">
                        <a href="student-module/login.php" class="btn-nav-filled">
                            <i class="fa-solid fa-user-graduate"></i> Student Login
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- HERO -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-badge"><i class="fa-solid fa-users-gear me-2"></i>T&amp;P Faculty Coordinators</div>
            <h1>Placement Team</h1>
            <p>Dedicated faculty coordinators overseeing training sessions, corporate liaison, and campus placement drives across all engineering departments.</p>
        </div>
    </div>

    <!-- MAIN CONTENT CARD -->
    <div class="container">
        <div class="content-card">
            
            <h2 class="page-header-title">Placement Team</h2>

            <div class="intro-text">
                <p><strong>Placement Team:</strong> Dr. M M Goyani (Computer Department) is heading the placement cell with his deep vision and experience. Prof. P M Mistri (Mechanical Department) acting as placement co-coordinator for smooth conduct of placement activities.</p>
                <p>Each department has nominated faculty coordinators and few students to conduct placement drive in their own department. The team of department coordinator and student coordinators organizes pre-placement talks, entrance tests, documentation, report generation etc.</p>
            </div>

            <!-- TEAM TABLE -->
            <div class="team-table-card">
                <div class="team-table-header">
                    Placement Team
                </div>

                <div class="table-responsive">
                    <table class="team-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Photo</th>
                                <th style="width: 55%;">Details</th>
                                <th style="width: 25%;">Role</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php foreach ($team_members as $m): ?>
                                <tr class="animate-row">
                                    <td class="photo-cell">
                                        <img src="<?= htmlspecialchars($m['photo']) ?>" alt="<?= htmlspecialchars($m['name']) ?>" class="faculty-photo" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($m['name']) ?>&background=003B71&color=fff';">
                                    </td>
                                    <td class="details-cell">
                                        <div class="member-name"><?= htmlspecialchars($m['name']) ?></div>
                                        <div class="member-dept"><?= htmlspecialchars($m['designation']) ?></div>
                                        <div class="member-dept"><?= htmlspecialchars($m['department']) ?></div>
                                        <div class="member-email mt-1" style="font-size: 0.85rem;">
                                            <i class="fa-solid fa-envelope me-1" style="color: var(--accent-coral);"></i> 
                                            <a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="color: #475569; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                                <?= htmlspecialchars($m['email']) ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="role-cell">
                                        <?= htmlspecialchars($m['role']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                let delay = 0;
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('visible');
                        }, delay);
                        delay += 200; // 200ms delay for staggered "one-by-one" effect
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: "0px 0px -20px 0px"
            });

            document.querySelectorAll('.animate-row').forEach((row) => {
                observer.observe(row);
            });
        });
    </script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
