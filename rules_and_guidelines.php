<?php // Rules & Guidelines - Public Static Page ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules & Guidelines | GEC Modasa Placement Cell</title>
    <meta name="description" content="Official rules and guidelines for students participating in campus placement drives at Government Engineering College, Modasa.">
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

        .footer-note {
            text-align: center;
            color: #64748b;
            margin-top: 50px;
            font-size: 0.95rem;
        }

        /* Animation Classes */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
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

        /* Section Headings */
        .sec-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 36px 0 16px;
            scroll-margin-top: 90px;
        }
        .sec-title .ico {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .sec-title h2 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0;
        }

        /* Card Layouts */
        .card-white {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0,0,0,0.03);
            overflow: hidden;
        }

        /* Dress Code */
        .dress-grid { display: grid; grid-template-columns: 1fr 1fr; }
        .dress-col { padding: 30px; }
        .dress-col:first-child { border-right: 1px solid #f0f3f8; }
        .dress-col h6 { font-size: 1.05rem; font-weight: 800; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .dress-col ul { margin: 0; padding-left: 20px; font-size: 1rem; color: #444; line-height: 2.1; }
        .dress-col ul li span { color: var(--accent-coral); font-weight: 600; }

        /* Document Chips */
        .doc-wrap { display: flex; flex-direction: column; gap: 12px; padding: 26px 30px; }
        .doc-chip { background: #eef2ff; color: var(--primary-navy); border: 1px solid #c7d2fe; border-radius: 50px; padding: 10px 20px; font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }

        /* Rules List */
        .rule-item { display: flex; gap: 0; padding: 20px 28px; border-bottom: 1px solid #f3f6fb; align-items: flex-start; }
        .rule-item:last-child { border-bottom: none; }
        .rule-num { width: 32px; height: 32px; min-width: 32px; background: #eef2ff; color: var(--primary-navy); border-radius: 50%; font-size: 0.85rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin-right: 18px; margin-top: 2px; }
        .rule-body { font-size: 1rem; line-height: 1.78; color: #333; }

        /* Grade Table */
        .grade-wrap { padding: 26px 30px; }
        .grade-tbl { width: 100%; border-collapse: collapse; margin-bottom: 20px; scroll-margin-top: 90px; }
        .grade-tbl thead th { text-align: left; font-size: 0.82rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #888; padding: 0 16px 12px; border-bottom: 2px solid #e8edf5; }
        .grade-tbl tbody td { padding: 15px 16px; border-bottom: 1px solid #f3f6fb; font-size: 1rem; vertical-align: middle; }
        .grade-tbl tbody tr:last-child td { border-bottom: none; }
        .g-badge { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px; font-weight: 900; font-size: 1.05rem; }
        .grade-rule { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 10px; padding: 16px 20px; font-size: 0.97rem; color: #78350f; line-height: 1.75; }

        /* Penalty */
        .penalty-wrap { padding: 26px 30px; display: flex; gap: 20px; align-items: flex-start; background: #fff; border-radius: 16px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05); border: 1px solid rgba(0,0,0,0.03); }
        .penalty-ico { width: 50px; min-width: 50px; height: 50px; background: #fee2e2; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #dc2626; }
        .penalty-body { font-size: 1rem; line-height: 1.8; color: #333; }
        .penalty-body strong { color: #991b1b; }
        .penalty-body .debarred { color: #dc2626; font-weight: 800; }

        /* Footer */
        .footer-note { text-align: center; color: #6c757d; font-size: 0.88rem; margin-top: 40px; padding-top: 22px; }

        @media (max-width: 768px) {
            .hero-section h1 { font-size: 1.8rem; }
            .dress-grid { grid-template-columns: 1fr; }
            .dress-col:first-child { border-right: none; border-bottom: 1px solid #f0f3f8; }
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
                        <li class="nav-item"><a class="nav-link" href="placement_statistics.php?view=placement">Training &amp; Placement</a></li>
                        <li class="nav-item"><a class="nav-link active" href="rules_and_guidelines.php">Rules &amp; Guidelines</a></li>
                        <li class="nav-item"><a class="nav-link" href="major_recruiters.php">Major Recruiters</a></li>
                        <li class="nav-item"><a class="nav-link" href="placement_activities.php">Placement Activities</a></li>
                        <li class="nav-item"><a class="nav-link" href="placement_team.php">Placement Team</a></li>
                        <li class="nav-item d-lg-none mt-2">
                            <a class="btn-nav-filled w-100 text-center justify-content-center" href="student-module/login.php">
                                <i class="fa-solid fa-user-graduate"></i> Student Login
                            </a>
                        </li>
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

    <!-- ═══════════ HERO SECTION ═══════════ -->
    <div class="hero-section no-print">
        <div class="container">
            <div class="hero-badge"><i class="fa-solid fa-shield-halved me-2"></i>Official Guidelines</div>
            <h1>Rules &amp; Guidelines</h1>
            <p>Guidelines for students participating in campus placement at GEC Modasa.</p>
        </div>
    </div>

    <!-- ═══════════ MAIN CONTENT (Matching Bootstrap container width) ═══════════ -->
    <div class="container py-4">

        <div class="row gx-4 align-items-start flex-lg-row-reverse">
            <!-- LEFT COLUMN -->
            <div class="col-lg-6">
<!-- 3. Rules (MOBILE ONLY) -->
                <div class="d-block d-lg-none">
                    <div class="sec-title" style="margin-top:36px;">
                        <div class="ico" style="background:#eef2ff;color:var(--primary-navy);"><i class="fa-solid fa-list-check"></i></div>
                        <h2 style="color:var(--primary-navy);">Rules &amp; Regulations</h2>
                    </div>
                    <div class="card-white">
                        <div class="rule-item">
                            <div class="rule-num">1</div>
                            <div class="rule-body">It is mandatory for students to provide their correct details to the departmental placement representative. <strong>False entry in data will lead to disqualification</strong> in the placement drive, even if the student has paid the fees.</div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-num">2</div>
                            <div class="rule-body">The departmental coordinator will <strong>verify entries within one week</strong> of the declaration of results and approve them. Only approved students will qualify for placement.</div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-num">3</div>
                            <div class="rule-body">Students should regularly check the <strong>notice board</strong> in their respective department and the <strong>placement website</strong> for any T&amp;P information.</div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-num">4</div>
                            <div class="rule-body">Students must <strong>report 30 minutes before</strong> the start of any placement activity.</div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-num">5</div>
                            <div class="rule-body">Students should regularly check their <strong>email accounts, SMS, and WhatsApp</strong> for any T&amp;P updates.</div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-num">6</div>
                            <div class="rule-body">Students should be in <strong>regular contact</strong> with their T&amp;P representative.</div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-num">7</div>
                            <div class="rule-body">Students must <strong>maintain discipline</strong> during campus placement. Misconduct may result in <strong style="color:var(--accent-coral);">debarment from further opportunities</strong>.</div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-num">8</div>
                            <div class="rule-body">Students should enter their latest correct data on the placement website at the <strong>beginning of each semester</strong> and keep it updated within <strong>two days</strong> of any change or result declaration. If data is not updated in time, the TPO is not responsible for sending the student's name for placement.</div>
                        </div>
                    </div>
                </div>
                    
                    <!-- 1. Dress Code -->
                <div class="sec-title mt-0">
                    <div class="ico" style="background:#eef2ff;color:var(--primary-navy);"><i class="fa-solid fa-shirt"></i></div>
                    <h2 style="color:var(--primary-navy);">Dress Code</h2>
                </div>
                <div class="card-white">
                    <div class="dress-grid">
                        <div class="dress-col">
                            <h6 style="color:var(--primary-navy);"><i class="fa-solid fa-person"></i> Boys</h6>
                            <ul>
                                <li>Shirt</li>
                                <li>Trouser <span>(Jeans not allowed)</span></li>
                                <li>Leather shoes</li>
                            </ul>
                        </div>
                        <div class="dress-col">
                            <h6 style="color:var(--accent-coral);"><i class="fa-solid fa-person-dress"></i> Girls</h6>
                            <ul>
                                <li>Punjabi dress</li>
                                <li><em>or</em> Shirt and trouser <span>(Jeans not allowed)</span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 2. Documents -->
                <div class="sec-title" style="margin-top:36px;">
                    <div class="ico" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-file-lines"></i></div>
                    <h2 style="color:#15803d;">Documents to Carry</h2>
                </div>
                <div class="card-white">
                    <div class="doc-wrap">
                        <span class="doc-chip"><i class="fa-solid fa-file-alt"></i> Latest Resume</span>
                        <span class="doc-chip"><i class="fa-solid fa-certificate"></i> All Original Certificates</span>
                        <span class="doc-chip"><i class="fa-solid fa-copy"></i> One Attested Copy of Each Certificate</span>
                        <span class="doc-chip"><i class="fa-regular fa-image"></i> Two Passport-Size Photographs</span>
                        <span class="doc-chip"><i class="fa-solid fa-envelope"></i> Envelopes &amp; Stationery</span>
                    </div>
                </div>



                <!-- 4. Company Grades -->
                <div class="sec-title" style="margin-top:36px;">
                    <div class="ico" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-layer-group"></i></div>
                    <h2 style="color:#92400e;">Company Grade Classification</h2>
                </div>
                <div class="card-white">
                    <div class="grade-wrap">
                        <table class="grade-tbl">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Salary Package</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="g-badge" style="background:#fef9c3;color:#854d0e;">A</span></td>
                                    <td>Salary up to <strong>1.5 LPA</strong></td>
                                </tr>
                                <tr>
                                    <td><span class="g-badge" style="background:#dbeafe;color:#1e40af;">B</span></td>
                                    <td>Salary in range from <strong>1.5 LPA</strong> to <strong>3.0 LPA</strong></td>
                                </tr>
                                <tr>
                                    <td><span class="g-badge" style="background:#dcfce7;color:#166534;">C</span></td>
                                    <td>Salary greater than <strong>3.0 LPA</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="grade-rule">
                            <strong><i class="fa-solid fa-circle-info me-2"></i>Eligibility Rule:</strong>
                            A student already selected in a particular grade will <strong>not be allowed</strong> to attend the campus placement for a company of the same or lower grade. However, a student selected in a lower grade company <strong>will be allowed</strong> to attend campus placement for a higher grade company.
                        </div>
                    </div>
                </div>
                
                <div class="d-block d-lg-none">
                    <div class="sec-title" style="margin-top:36px;">
                        <div class="ico" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <h2 style="color:#b91c1c;">Penalty Rules</h2>
                    </div>
                    <div class="penalty-wrap mb-4">
                        <div class="penalty-body">
                            A student who has expressed willingness to appear for campus placement through a visit created, if <strong>quits the procedure in between</strong> or <strong>does not accept the offer after selection</strong>, will be <span class="debarred">permanently debarred</span> from further placements and will not be given any opportunity under any circumstances.
                        </div>
                    </div>
                </div>

                

            </div>

            <!-- RIGHT COLUMN (DESKTOP ONLY) -->
            <div class="col-lg-6 d-none d-lg-flex flex-column">
                <!-- 3. Rules -->
                <div class="sec-title mt-0">
                    <div class="ico" style="background:#eef2ff;color:var(--primary-navy);"><i class="fa-solid fa-list-check"></i></div>
                    <h2 style="color:var(--primary-navy);">Rules &amp; Regulations</h2>
                </div>
                <div class="card-white">
                    <div class="rule-item">
                        <div class="rule-num">1</div>
                        <div class="rule-body">It is mandatory for students to provide their correct details to the departmental placement representative. <strong>False entry in data will lead to disqualification</strong> in the placement drive, even if the student has paid the fees.</div>
                    </div>
                    <div class="rule-item">
                        <div class="rule-num">2</div>
                        <div class="rule-body">The departmental coordinator will <strong>verify entries within one week</strong> of the declaration of results and approve them. Only approved students will qualify for placement.</div>
                    </div>
                    <div class="rule-item">
                        <div class="rule-num">3</div>
                        <div class="rule-body">Students should regularly check the <strong>notice board</strong> in their respective department and the <strong>placement website</strong> for any T&amp;P information.</div>
                    </div>
                    <div class="rule-item">
                        <div class="rule-num">4</div>
                        <div class="rule-body">Students must <strong>report 30 minutes before</strong> the start of any placement activity.</div>
                    </div>
                    <div class="rule-item">
                        <div class="rule-num">5</div>
                        <div class="rule-body">Students should regularly check their <strong>email accounts, SMS, and WhatsApp</strong> for any T&amp;P updates.</div>
                    </div>
                    <div class="rule-item">
                        <div class="rule-num">6</div>
                        <div class="rule-body">Students should be in <strong>regular contact</strong> with their T&amp;P representative.</div>
                    </div>
                    <div class="rule-item">
                        <div class="rule-num">7</div>
                        <div class="rule-body">Students must <strong>maintain discipline</strong> during campus placement. Misconduct may result in <strong style="color:var(--accent-coral);">debarment from further opportunities</strong>.</div>
                    </div>
                    <div class="rule-item">
                        <div class="rule-num">8</div>
                        <div class="rule-body">Students should enter their latest correct data on the placement website at the <strong>beginning of each semester</strong> and keep it updated within <strong>two days</strong> of any change or result declaration. If data is not updated in time, the TPO is not responsible for sending the student's name for placement.</div>
                    </div>
                </div>
                
                <!-- Penalty (DESKTOP ONLY) -->
                <div class="sec-title" style="margin-top:36px;">
                    <div class="ico" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h2 style="color:#b91c1c;">Penalty Rules</h2>
                </div>
                <div class="penalty-wrap mb-4">
                    <div class="penalty-body">
                        A student who has expressed willingness to appear for campus placement through a visit created, if <strong>quits the procedure in between</strong> or <strong>does not accept the offer after selection</strong>, will be <span class="debarred">permanently debarred</span> from further placements and will not be given any opportunity under any circumstances.
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-note mt-4">
            <i class="fa-solid fa-circle-info me-1"></i>
            For queries, contact your departmental T&amp;P coordinator.
        </div>

    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                let delay = 0;
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('visible');
                        }, delay);
                        delay += 150; // Stagger effect
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: "0px 0px -20px 0px"
            });

            document.querySelectorAll('.animate-on-scroll').forEach((el) => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
