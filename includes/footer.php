<style>
    .premium-footer {
        background: linear-gradient(135deg, #1B365D 0%, #11233D 100%);
        color: rgba(255, 255, 255, 0.85);
        position: relative;
        overflow: hidden;
        margin-top: 80px;
    }
    .premium-footer::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, #E65A4B, #f59e0b);
    }
    .footer-heading {
        color: #fff;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 25px;
        position: relative;
        display: inline-block;
    }
    .footer-heading::after {
        content: '';
        position: absolute;
        left: 0; bottom: -8px;
        width: 40px; height: 3px;
        background: #E65A4B;
        border-radius: 2px;
    }
    .footer-link {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        margin-bottom: 12px;
        font-size: 0.95rem;
    }
    .footer-link:hover {
        color: #E65A4B;
        transform: translateX(5px);
    }
    .footer-contact-icon {
        color: #E65A4B;
        width: 20px;
        margin-right: 12px;
        text-align: center;
    }
    .footer-social-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50%;
        margin-right: 10px;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .footer-social-btn:hover {
        background: #E65A4B;
        color: #fff;
        transform: translateY(-3px);
    }
    .footer-bottom {
        background: rgba(0, 0, 0, 0.2);
        padding: 20px 0;
        border-top: 1px solid rgba(255,255,255,0.05);
    }
</style>

<footer class="premium-footer pt-5">
    <div class="container pb-4">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6 pe-lg-5">
                <h4 class="text-white fw-bold mb-3">
                    <span style="color: #E65A4B;">GEC Modasa</span> Placement
                </h4>
                <p style="font-size: 0.95rem; line-height: 1.8;">
                    Welcome to the Training and Placement Cell of Government Engineering College, Modasa. We are dedicated to building a bright future for our students by connecting talent with opportunity.
                </p>
                <div class="mt-4">
                    <a href="#" class="footer-social-btn"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="footer-social-btn"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="footer-social-btn"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" class="footer-social-btn"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-heading">Quick Links</h5>
                <div class="d-flex flex-column">
                    <a href="placement_statistics.php" class="footer-link">Placement Stats</a>
                    <a href="major_recruiters.php" class="footer-link">Top Recruiters</a>
                    <a href="placement_activities.php" class="footer-link">Latest Drives</a>
                    <a href="rules_and_guidelines.php" class="footer-link">Guidelines</a>
                    <a href="placement_team.php" class="footer-link">Our Team</a>
                </div>
            </div>

            <!-- Useful Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-heading">Portals</h5>
                <div class="d-flex flex-column">
                    <a href="student-module/login.php" class="footer-link">Student Login</a>
                    <a href="student-module/signup.php" class="footer-link">Student Reg.</a>
                    <a href="admin-module/login.php" class="footer-link">Admin Portal</a>
                </div>
            </div>

            <!-- Contact -->
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-heading">Contact Us</h5>
                <div class="d-flex flex-column" style="font-size: 0.95rem;">
                    <div class="mb-3 d-flex align-items-start">
                        <i class="fa-solid fa-location-dot footer-contact-icon mt-1"></i>
                        <span>Shamlaji Road, Modasa,<br>Aravalli, Gujarat 383315</span>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <i class="fa-solid fa-envelope footer-contact-icon"></i>
                        <span>placement@gecmodasa.ac.in</span>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <i class="fa-solid fa-phone footer-contact-icon"></i>
                        <span>+91 99999 88888</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="container text-center">
            <p class="mb-0" style="font-size: 0.9rem;">
                &copy; <?= date('Y') ?> <strong>GEC Modasa Placement Cell</strong>. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>
