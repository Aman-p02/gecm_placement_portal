<?php
// developers.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meet the Developers | GEC Modasa</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Background Decoration -->
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <!-- Navbar -->
    <div class="navbar-wrapper">
        <nav class="navbar navbar-expand-lg top-navbar navbar-pill py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <a class="navbar-brand brand-text" href="placement_statistics.php">
                    GEC Modasa <span>Placement</span>
                </a>
                <a href="placement_statistics.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="d-none d-sm-inline">Back to Home</span>
                    <span class="d-inline d-sm-none">Back</span>
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
                        <img src="student-module/uploads/developers_pic/Aman.jpg" alt="Prajapati Aman Jayeshbhai"
                            class="dev-img" style="cursor: pointer;" onclick="openImageModal(this.src)">
                    </div>
                    <h3 class="dev-name">PRAJAPATI AMAN JAYESHBHAI</h3>
                    <div class="dev-role">Full Stack Developer</div>
                    <div class="dev-quote">"Transforming ideas into digital reality through modern web technologies."
                    </div>

                    <div class="dev-info">
                        <div class="dev-info-item">
                            <i class="fa-solid fa-id-badge"></i>
                            <span><strong>Enrollment:</strong> 250163107021</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-laptop-code"></i>
                            <span><strong>Branch:</strong> Computer Engineering</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span><strong>Batch:</strong> 2025 to 2028</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span><strong>Email:</strong> <a
                                    href="mailto:amanjp5711@gmail.com">amanjp5711@gmail.com</a></span>
                        </div>

                        <hr class="my-2 border-secondary" style="opacity: 0.15;">

                        <div class="text-start mt-2">
                            <div
                                style="color: var(--primary-navy); font-size: 0.85rem; font-weight: 700; margin-bottom: 2px;">
                                <i class="fa-solid fa-bullseye me-1 text-danger"></i> Key Contributions:
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.3; color: #444;" class="mb-2 fw-medium">
                                Developed student & admin panel, database architecture, backend logic, and UI design.
                            </div>
                            <br>
                            <div
                                style="color: var(--primary-navy); font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">
                                <i class="fa-solid fa-code me-1 text-primary"></i> Tech Stack:
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-white text-dark border fw-medium"
                                    style="font-size: 0.7rem; padding: 3px 6px;">PHP</span>
                                <span class="badge bg-white text-dark border fw-medium"
                                    style="font-size: 0.7rem; padding: 3px 6px;">HTML/CSS</span>
                                <span class="badge bg-white text-dark border fw-medium"
                                    style="font-size: 0.7rem; padding: 3px 6px;">Bootstrap 5</span>
                                <span class="badge bg-white text-dark border fw-medium"
                                    style="font-size: 0.7rem; padding: 3px 6px;">MySQL</span>
                                <span class="badge bg-white text-dark border fw-medium"
                                    style="font-size: 0.7rem; padding: 3px 6px;">JS/AJAX</span>
                                <span class="badge bg-white text-dark border fw-medium"
                                    style="font-size: 0.7rem; padding: 3px 6px;">CI/CD</span>
                                <span class="badge bg-white text-dark border fw-medium"
                                    style="font-size: 0.7rem; padding: 3px 6px;">Git</span>
                            </div>
                        </div>
                    </div>

                    <div class="connect-heading">Let's Connect</div>
                    <div class="social-links">
                        <a href="https://www.linkedin.com/in/aman-prajapati-855a8a37b/" target="_blank"
                            class="social-btn linkedin" title="LinkedIn">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                        <a href="https://github.com/Aman-p02" target="_blank" class="social-btn github" title="GitHub">
                            <i class="fa-brands fa-github"></i>
                        </a>
                        <a href="https://amanprajapati2.vercel.app/" target="_blank" class="social-btn portfolio"
                            title="Portfolio">
                            <i class="fa-solid fa-globe"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Developer 2: Vanit -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="dev-card">
                    <div class="dev-img-wrapper">
                        <!-- REPLACE THIS SRC WITH ACTUAL IMAGE -->
                        <img src="student-module/uploads/developers_pic/vanit.jpg"
                            alt="Vanit Dantani Nitinbhai" class="dev-img" style="cursor: pointer;"
                            onclick="openImageModal(this.src)">
                    </div>
                    <h3 class="dev-name">DANTANI VANIT NITINBHAI</h3>
                    <div class="dev-role">Full Stack Developer</div>
                    <div class="dev-quote">"Bridging the gap between complex logic and beautiful user experiences."
                    </div>

                    <div class="dev-info">
                        <div class="dev-info-item">
                            <i class="fa-solid fa-id-badge"></i>
                            <span><strong>Enrollment:</strong> 250163107004</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-laptop-code"></i>
                            <span><strong>Branch:</strong> Computer Engineering</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span><strong>Batch:</strong> 2025 to 2028</span>
                        </div>
                        <div class="dev-info-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span><strong>Email:</strong> <a
                                    href="mailto:vanitdantani05@gmail.com">vanitdantani05@gmail.com</a></span>
                        </div>

                        <hr class="my-2 border-secondary" style="opacity: 0.15;">

                        <div class="text-start mt-2">
                            <div
                                style="color: var(--primary-navy); font-size: 0.85rem; font-weight: 700; margin-bottom: 2px;">
                                <i class="fa-solid fa-bullseye me-1 text-danger"></i> Key Contributions:
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.3; color: #444;" class="mb-2 fw-medium">
                                Developed statistics page and super admin panel, backend logic & database structure .
                            </div>
                            <br>
                            <div style="color: var(--primary-navy); font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">
                                <i class="fa-solid fa-code me-1 text-primary"></i> Tech Stack:
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-white text-dark border fw-medium" style="font-size: 0.7rem; padding: 3px 6px;">PHP</span>
                                <span class="badge bg-white text-dark border fw-medium" style="font-size: 0.7rem; padding: 3px 6px;">HTML/CSS</span>
                                <span class="badge bg-white text-dark border fw-medium" style="font-size: 0.7rem; padding: 3px 6px;">Bootstrap 5</span>
                                <span class="badge bg-white text-dark border fw-medium" style="font-size: 0.7rem; padding: 3px 6px;">JavaScript</span>
                                <span class="badge bg-white text-dark border fw-medium" style="font-size: 0.7rem; padding: 3px 6px;">MySQL</span>
                                <span class="badge bg-white text-dark border fw-medium" style="font-size: 0.7rem; padding: 3px 6px;">Git</span>
                            </div>
                        </div>
                    </div>

                    <div class="connect-heading">Let's Connect</div>
                    <div class="social-links">
                        <a href="https://www.linkedin.com/in/vanitdantani" target="_blank" class="social-btn linkedin"
                            title="LinkedIn">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                        <a href="https://github.com/VanitDantani" target="_blank" class="social-btn github"
                            title="GitHub">
                            <i class="fa-brands fa-github"></i>
                        </a>
                        <a href="https://vanitdantaniportfolio.netlify.app/" target="_blank"
                            class="social-btn portfolio" title="Portfolio">
                            <i class="fa-solid fa-globe"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-5">
            <p class="text-muted small fw-medium">Developed with <i class="fa-solid fa-heart text-danger mx-1"></i> for
                GEC Modasa (August 2026)</p>
        </div>
    </div>

    <!-- Image Modal (Instagram Style) -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center position-relative p-0">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="z-index: 1055; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></button>
                    <img id="modalImage" src="" alt="Developer" class="img-fluid rounded-4 shadow-lg"
                        style="max-height: 85vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function openImageModal(src) {
            document.getElementById('modalImage').src = src;
            var imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }
    </script>
    <?php include 'includes/footer.php'; ?>
</body>

</html>
