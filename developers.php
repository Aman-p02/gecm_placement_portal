<?php
// developers.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meet the Developers | GEC Modasa</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #334155;
            --accent: #3b82f6;
            --text-light: #94a3b8;
        }
        
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 0;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .brand-text {
            color: var(--primary);
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            white-space: nowrap;
        }

        .brand-text span {
            color: var(--accent);
        }

        /* Header Section */
        .dev-header {
            text-align: center;
            padding: clamp(1.5rem, 5vw, 2.5rem) 1rem 1.5rem;
            position: relative;
        }

        .dev-header h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: clamp(2rem, 8vw, 3.5rem);
            color: var(--primary);
            margin-bottom: 1rem;
            letter-spacing: -1px;
        }

        .dev-header p {
            color: var(--secondary);
            font-size: 1.15rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Glassmorphism Cards */
        .dev-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 24px;
            padding: clamp(1.5rem, 5vw, 2.5rem);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .dev-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .dev-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .dev-card:hover::before {
            transform: scaleX(1);
        }

        .dev-img-wrapper {
            width: clamp(120px, 35vw, 160px);
            height: clamp(120px, 35vw, 160px);
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            padding: 6px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            position: relative;
        }

        .dev-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            transition: transform 0.3s ease;
        }

        .dev-card:hover .dev-img {
            transform: scale(1.05);
        }

        .dev-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: clamp(0.9rem, 4.2vw, 1.35rem);
            color: var(--primary);
            margin-bottom: 0.25rem;
            white-space: nowrap;
        }

        .dev-role {
            color: var(--accent);
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
        }

        .dev-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            text-align: left;
            background: rgba(255,255,255,0.5);
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
            color: var(--secondary);
        }

        .dev-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dev-info-item i {
            color: var(--text-light);
            width: 16px;
            text-align: center;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08);
            font-size: 1.4rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        
        .social-btn.linkedin {
            color: #0077b5;
            border-color: rgba(0, 119, 181, 0.3);
            background: rgba(0, 119, 181, 0.05);
        }
        
        .social-btn.github {
            color: #333;
            border-color: rgba(51, 51, 51, 0.3);
            background: rgba(51, 51, 51, 0.05);
        }

        .social-btn:hover {
            color: #fff;
            transform: translateY(-3px);
        }

        .social-btn.linkedin:hover {
            background: #0077b5;
            border-color: #0077b5;
            box-shadow: 0 4px 12px rgba(0, 119, 181, 0.3);
        }

        .social-btn.github:hover {
            background: #333;
            border-color: #333;
            box-shadow: 0 4px 12px rgba(51, 51, 51, 0.3);
        }

        /* Decorative Elements */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
        }

        .blob-1 {
            top: -10%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: #e0e7ff;
            border-radius: 50%;
        }

        .blob-2 {
            bottom: -10%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: #ede9fe;
            border-radius: 50%;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #fff;
            color: var(--primary);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            color: var(--primary);
        }
    </style>
</head>
<body>

    <!-- Background Decoration -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- Navbar -->
    <div class="navbar-wrapper">
        <nav class="navbar navbar-expand-lg top-navbar navbar-pill py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <a class="navbar-brand brand-text" href="placement_statistics.php">
                    GEC Modasa <span>Placement</span>
                </a>
                <a href="placement_statistics.php" class="btn-back d-none d-md-flex">
                    <i class="fa-solid fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="container pb-5 flex-grow-1">
        <div class="dev-header">
            <h1 class="mb-3">Meet the Developers</h1>
            <p>The engineering minds behind the architecture and development of the GEC Modasa Placement Portal.</p>
        </div>

        <div class="row g-4 justify-content-center">
            
            <!-- Developer 1: Aman -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="dev-card">
                    <div class="dev-img-wrapper">
                        <!-- REPLACE THIS SRC WITH ACTUAL IMAGE -->
                        <img src="https://ui-avatars.com/api/?name=Aman+Prajapati&background=3b82f6&color=fff&size=200&font-size=0.33" alt="Prajapati Aman Jayeshbhai" class="dev-img">
                    </div>
                    <h3 class="dev-name">Prajapati Aman Jayeshbhai</h3>
                    <div class="dev-role">Full Stack Developer</div>
                    
                    <div class="dev-info">
                        <div class="dev-info-item">
                            <i class="fa-solid fa-laptop-code"></i>
                            <span>Computer Engineering</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>Batch: 2025 to 2028</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-id-badge"></i>
                            <span>Enrollment: 250163107021</span>
                        </div>
                    </div>

                    <div class="social-links">
                        <a href="https://www.linkedin.com/in/aman-prajapati-855a8a37b/" target="_blank" class="social-btn linkedin" title="LinkedIn">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                        <a href="https://github.com/Aman-p02" target="_blank" class="social-btn github" title="GitHub">
                            <i class="fa-brands fa-github"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Developer 2: Vanit -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="dev-card">
                    <div class="dev-img-wrapper">
                        <!-- REPLACE THIS SRC WITH ACTUAL IMAGE -->
                        <img src="https://ui-avatars.com/api/?name=Vanit+Dantani&background=8b5cf6&color=fff&size=200&font-size=0.33" alt="Vanit Dantani Nitinbhai" class="dev-img">
                    </div>
                    <h3 class="dev-name">Vanit Dantani Nitinbhai</h3>
                    <div class="dev-role">Full Stack Developer</div>
                    
                    <div class="dev-info">
                        <div class="dev-info-item">
                            <i class="fa-solid fa-laptop-code"></i>
                            <span>Computer Engineering</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>Batch: 2025 to 2028</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-id-badge"></i>
                            <span>Enrollment: 250163107004</span>
                        </div>
                    </div>

                    <div class="social-links">
                        <a href="https://www.linkedin.com/in/vanitdantani" target="_blank" class="social-btn linkedin" title="LinkedIn">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                        <a href="https://github.com/VanitDantani" target="_blank" class="social-btn github" title="GitHub">
                            <i class="fa-brands fa-github"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-5">
            <p class="text-muted small fw-medium">Developed with <i class="fa-solid fa-heart text-danger mx-1"></i> for GEC Modasa (August 2026)</p>
            <a href="placement_statistics.php" class="btn btn-link text-decoration-none d-md-none mt-3">Back to Home</a>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
