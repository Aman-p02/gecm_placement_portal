<?php
require_once __DIR__ . '/admin-module/includes/db_connect.php';

// Fetch distinct years for the dropdown
$stmt = $pdo->query("SELECT DISTINCT activity_year FROM placement_activities ORDER BY activity_year DESC");
$availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Determine selected year
$selectedYear = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
if (!$selectedYear && !empty($availableYears)) {
    $selectedYear = $availableYears[0];
}

// Fetch activities for the selected year
if ($selectedYear) {
    $stmt = $pdo->prepare("SELECT * FROM placement_activities WHERE activity_year = ? ORDER BY created_at DESC");
    $stmt->execute([$selectedYear]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM placement_activities ORDER BY created_at DESC");
    $stmt->execute();
}
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php // Placement Activities - Dummy Public Page ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Activities | GEC Modasa Placement Cell</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #1B365D;
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
            box-shadow: 0 2px 8px rgba(27, 54, 93, 0.18);
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

        .hero-badge {
            display: inline-block;
            padding: 6px 18px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dummy-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
        }
    
        /* Timeline Styles */
        .timeline {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 2px;
            background-color: #e5e7eb;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -1px;
        }

        .timeline-item {
            padding: 0 40px;
            position: relative;
            background-color: inherit;
            width: 50%;
            margin-bottom: 40px;
        }

        .timeline-item.left {
            left: 0;
        }

        .timeline-item.right {
            left: 50%;
        }

        /* Make cards interlock/stagger on desktop */
        @media screen and (min-width: 769px) {
            .timeline-item:not(:first-child) {
                margin-top: -220px;
            }
        }

        .timeline-node {
            position: absolute;
            width: 16px;
            height: 16px;
            right: -8px;
            background-color: white;
            border: 3px solid #d1d5db;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
        }

        .timeline-item.right .timeline-node {
            left: -8px;
        }

        /* Triangle pointers */
        .timeline-item.left::after {
            content: " ";
            height: 0;
            position: absolute;
            top: 50%;
            width: 0;
            z-index: 1;
            right: 25px;
            border: medium solid white;
            border-width: 10px 0 10px 15px;
            border-color: transparent transparent transparent #e5e7eb;
            transform: translateY(-50%);
        }

        .timeline-item.right::after {
            content: " ";
            height: 0;
            position: absolute;
            top: 50%;
            width: 0;
            z-index: 1;
            left: 25px;
            border: medium solid white;
            border-width: 10px 15px 10px 0;
            border-color: transparent #e5e7eb transparent transparent;
            transform: translateY(-50%);
        }

        .activity-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0;
        }

        .activity-card .card-body {
            padding: 20px;
        }
        
        .activity-card h5 {
            color: #333;
            font-size: 1.1rem;
            line-height: 1.4;
            margin-bottom: 12px;
            font-weight: 400;
        }

        .activity-card img {
            height: 280px;
            object-fit: cover;
        }

        .carousel-control-prev, .carousel-control-next {
            width: 10%;
        }

        .carousel-control-prev-icon, .carousel-control-next-icon {
            width: 30px;
            height: 30px;
            background-size: 50%;
            opacity: 0.8;
        }

        /* Responsive Timeline */
        @media screen and (max-width: 768px) {
            .timeline::after {
                left: 20px;
            }
            .timeline-item {
                width: 100%;
                padding-left: 50px;
                padding-right: 0;
            }
            .timeline-item.right {
                left: 0%;
            }
            .timeline-item.left .timeline-node,
            .timeline-item.right .timeline-node {
                left: 12px;
                top: 50%;
            }
            .timeline-item.left::after,
            .timeline-item.right::after {
                left: 35px;
                border-width: 10px 15px 10px 0;
                border-color: transparent #e5e7eb transparent transparent;
            }
        }

    
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 35px 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
            margin-bottom: 40px;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        @media screen and (max-width: 768px) {
            .content-card {
                padding: 20px 15px;
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
                        <li class="nav-item"><a class="nav-link active" href="placement_activities.php">Placement Activities</a></li>
                        <li class="nav-item"><a class="nav-link" href="placement_team.php">Placement Team</a></li>
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
            <div class="hero-badge"><i class="fa-solid fa-calendar-check me-2"></i>Events &amp; Workshops</div>
            <h1>Placement Activities</h1>
            <p>Training sessions, mock interviews, and placement drives at GEC Modasa.</p>
        </div>
    </div>

    
    <!-- CONTENT -->
    <div class="container py-4">
        <div class="content-card">
        <p class="text-muted mb-4 text-center" style="font-size: 0.95rem;">Placement Activity carried out by Training and Placement Cell, GEC Modasa.</p>
        
        <div class="row mb-5 justify-content-center">
            <div class="col-md-6 d-flex justify-content-center">
                <select class="form-select me-2" style="border-radius: 0; box-shadow: none; border-color: #ced4da; max-width: 450px;">
                    <option>Select Year</option>
                    <option selected>2024</option>
                    <option>2022</option>
                    <option>2021</option>
                    <option>2020</option>
                    <option>2019</option>
                    <option>2017</option>
                </select>
                <button class="btn text-white" style="background-color: #3498db; border-radius: 0; padding: 6px 20px;">Submit</button>
            </div>
        </div>

        <div class="timeline">
            <?php if(empty($activities)): ?>
                <p class="text-center text-muted my-5">No placement activities found for the selected year.</p>
            <?php else: ?>
                <?php 
                $count = 0; 
                foreach($activities as $act): 
                    $count++;
                    $alignment = ($count % 2 == 1) ? 'left' : 'right';
                    
                    // Fetch images for this activity
                    $stmtImg = $pdo->prepare("SELECT image_path FROM activity_images WHERE activity_id = ?");
                    $stmtImg->execute([$act['id']]);
                    $images = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
                    
                    $carouselId = "carousel_act_" . $act['id'];
                ?>
                <div class="timeline-item <?= $alignment ?>">
                    <div class="timeline-node"></div>
                    <div class="activity-card">
                        <?php if(!empty($images)): ?>
                        <div id="<?= $carouselId ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach($images as $index => $img): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100" alt="Activity Image">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if(count($images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon bg-dark p-3" aria-hidden="true" style="border-radius: 50%;"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon bg-dark p-3" aria-hidden="true" style="border-radius: 50%;"></span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5><?= htmlspecialchars($act['title']) ?></h5>
                            <p class="text-muted" style="font-size: 0.9rem;"><?= nl2br(htmlspecialchars($act['description'])) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
