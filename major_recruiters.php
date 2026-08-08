<?php // Major Recruiters - Single Exact Grid Image ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Major Recruiters | GEC Modasa Placement Cell</title>
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
            padding: 28px 0 35px 0;
            text-align: center;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 10px 30px rgba(27, 54, 93, 0.2);
        }

        .hero-section h1 {
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 8px;
        }

        .hero-section p {
            font-size: 1.02rem;
            opacity: 0.9;
            max-width: 720px;
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

        .section-intro {
            font-size: 1.05rem;
            color: #444;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .recruiters-grid-img {
            width: 100%;
            max-width: 950px;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 15px 45px rgba(27, 54, 93, 0.08);
            display: block;
            margin: 0 auto;
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        
        .recruiters-grid-img:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 55px rgba(27, 54, 93, 0.12);
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
                        <li class="nav-item"><a class="nav-link active" href="major_recruiters.php">Major Recruiters</a></li>
                        <li class="nav-item"><a class="nav-link" href="placement_activities.php">Placement Activities</a></li>
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
            <div class="hero-badge"><i class="fa-solid fa-building-user me-2"></i>Placement Cell Partners</div>
            <h1>Major Recruiters</h1>
            <p>Over the time, institute has developed good relations with industries. Few of the industries who is connected with the institute in placement process are listed below:</p>
        </div>
    </div>

    <!-- CONTENT CARD WITH EXACT SINGLE RECRUITERS GRID IMAGE -->
    <div class="container">
        <div class="content-card text-center" style="padding: 40px 20px;">
            <h2 class="page-header-title text-center mb-4" style="font-size: 1.6rem; font-weight: 800; color: #1B365D;">Our Industry Partners</h2>
            <img src="assets/images/real_recruiter_grid.png" alt="GEC Modasa Major Recruiters Grid" class="recruiters-grid-img mt-2">
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
