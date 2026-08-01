<?php
require_once 'auth.php';

if (is_logged_in()) {
    header("Location: profile.php");
    exit;
}

$redirect = $_GET['redirect'] ?? 'profile.php';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter your email and password.";
    } else {
        $stmt = auth_db()->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            login_user($user);
            header("Location: " . ($_POST['redirect'] ?: 'profile.php'));
            exit;
        }
        $error = "Invalid email or password.";
    }
}

$phone   = '+256 704 416250';
$emailContact = 'smartstudypro36@gmail.com';
$address = 'Kampala, Uganda';

$seoTitle       = 'Sign In | SmartStudyPro';
$seoDescription = 'Sign in to access your SmartStudyPro account, view your courses, and manage your learning.';
$siteUrl        = "https://smartstudypro.com";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta name="author" content="SmartStudyPro">

  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <script src="https://accounts.google.com/gsi/client" async defer></script>

  <style>
    :root {
      --ssp-navy: #0C086B;
      --ssp-navy-dark: #070443;
      --ssp-orange: #FF7A00;
      --ssp-orange-hover: #E06B00;
      --ssp-bg-soft: #F8FAFC;
      --ssp-text-main: #1E293B;
      --ssp-text-muted: #64748B;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: var(--ssp-text-main);
      background-color: var(--ssp-bg-soft);
    }

    h1, h2, h3, h4, h5, .brand-font {
      font-family: 'Outfit', sans-serif;
    }

    .ssp-header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(12px);
      border-bottom: 2px solid rgba(12, 8, 107, 0.08);
      transition: all 0.3s ease;
    }

    .navmenu ul {
      list-style: none !important;
      margin: 0 !important;
      padding: 0 !important;
      display: flex !important;
      align-items: center !important;
      gap: 24px;
    }

    .navmenu ul li {
      list-style: none !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    .navmenu ul li a {
      color: var(--ssp-navy);
      font-weight: 600;
      font-size: 0.95rem;
      text-decoration: none !important;
      display: inline-block;
      white-space: nowrap;
      transition: color 0.2s ease;
    }

    .navmenu ul li a:hover,
    .navmenu ul li a.active {
      color: var(--ssp-orange) !important;
      font-weight: 700;
    }

    .dropdown-toggle-no-caret::after {
      display: none !important;
    }

    .dropdown-menu .dropdown-item:hover {
      background-color: var(--ssp-bg-soft);
      color: var(--ssp-orange);
    }

    .btn-ssp-primary {
      background-color: var(--ssp-orange);
      color: #FFFFFF;
      font-weight: 700;
      border-radius: 10px;
      padding: 10px 24px;
      border: none;
      box-shadow: 0 4px 14px rgba(255, 122, 0, 0.35);
      transition: all 0.25s ease;
    }

    .btn-ssp-primary:hover {
      background-color: var(--ssp-orange-hover);
      color: #FFFFFF;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 122, 0, 0.45);
    }

    .ssp-auth-card {
      background: #FFFFFF;
      border-radius: 20px;
      border: 1px solid #E2E8F0;
      box-shadow: 0 10px 25px -5px rgba(12, 8, 107, 0.08);
      width: 100%;
      max-width: 440px;
    }

    .form-control:focus {
      border-color: var(--ssp-navy);
      box-shadow: 0 0 0 0.25rem rgba(12, 8, 107, 0.15);
    }

    .ssp-footer {
      background-color: var(--ssp-navy-dark);
      color: #94A3B8;
    }

    .ssp-footer-brand {
      color: #FFFFFF;
      font-size: 1.5rem;
      font-weight: 800;
    }

    .ssp-footer-brand span {
      color: var(--ssp-orange);
    }

    .social-icon-btn {
      width: 38px;
      height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      color: #FFFFFF;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .social-icon-btn:hover {
      background: var(--ssp-orange);
      color: #FFFFFF;
    }
  </style>
</head>

<body class="login-page">

  <header id="header" class="header ssp-header d-flex align-items-center sticky-top py-2">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      
      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0 text-decoration-none">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo" height="48">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a></li>
          <li><a href="about.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : '' ?>">About Us</a></li>
          <li><a href="courses.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'courses.php') ? 'active' : '' ?>">Courses</a></li>
          <li><a href="products.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : '' ?>">Products</a></li>
          <li><a href="contact.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : '' ?>">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list fs-2 ms-3"></i>
      </nav>

        <div class="dropdown">
          <a href="#" class="text-dark fs-5 text-decoration-none dropdown-toggle-no-caret" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
            <i class="bi bi-person-circle"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" aria-labelledby="userMenuDropdown">
            <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2" style="color: var(--ssp-navy);"></i>My Profile</a></li>
            <li><a class="dropdown-item py-2" href="cart.php"><i class="bi bi-bag me-2" style="color: var(--ssp-navy);"></i>My Cart</a></li>
            <li><hr class="dropdown-divider"></li>
          </ul>
        </div>

        <a class="btn-ssp-primary d-none d-sm-inline-block text-decoration-none ms-2" href="courses.php">Explore Courses</a>
      </div>

    </div>
  </header>

  <main class="main py-5 d-flex align-items-center min-vh-100">
    <div class="container d-flex justify-content-center" data-aos="fade-up">
      
      <div class="ssp-auth-card p-4 p-md-5">
        <div class="text-center mb-4">
          <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo" height="64" class="mb-3">
          <h3 class="fw-bold mb-1" style="color: var(--ssp-navy);">Welcome Back</h3>
          <p class="text-muted small mb-0">Sign in to view your profile and enrolled courses</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2 px-3 small text-center rounded-3 mb-3">
            <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="d-flex justify-content-center mb-3">
          <div id="g_id_onload"
               data-client_id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
               data-callback="handleGoogleCredential"
               data-auto_prompt="false">
          </div>
          <div class="g_id_signin" data-type="standard" data-shape="pill" data-theme="outline" data-text="signin_with" data-size="large" data-width="320"></div>
        </div>

        <div class="d-flex align-items-center my-3">
          <hr class="flex-grow-1 text-muted opacity-25">
          <span class="mx-3 text-muted small fw-semibold">or</span>
          <hr class="flex-grow-1 text-muted opacity-25">
        </div>

        <form method="POST">
          <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
          
          <div class="mb-3">
            <label class="form-label small fw-semibold" style="color: var(--ssp-navy);">Email Address</label>
            <input type="email" name="email" class="form-control rounded-3 py-2" placeholder="name@example.com" required>
          </div>
          
          <div class="mb-4">
            <label class="form-label small fw-semibold" style="color: var(--ssp-navy);">Password</label>
            <input type="password" name="password" class="form-control rounded-3 py-2" placeholder="••••••••" required>
          </div>
          
          <button type="submit" name="login" class="btn-ssp-primary w-100 py-2 fs-6">Sign In</button>
        </form>

        <p class="text-center small text-muted mt-4 mb-0">
          Don't have an account? <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--ssp-orange);">Create one</a>
        </p>
      </div>

    </div>
  </main>

  <footer id="footer" class="ssp-footer pt-5 pb-3">
    <div class="container footer-top mb-4">
      <div class="row gy-4">
        
        <div class="col-lg-4 col-md-6">
          <a href="index.php" class="ssp-footer-brand text-decoration-none mb-3 d-inline-block">
            SmartStudy<span>Pro</span>
          </a>
          <p class="small text-light opacity-75 mb-3">Study Made Simple, Success Made Sure.</p>
          
          <div class="small mb-3">
            <p class="mb-1"><i class="bi bi-geo-alt-fill me-2" style="color: var(--ssp-orange);"></i><?= htmlspecialchars($address) ?></p>
            <p class="mb-1"><i class="bi bi-telephone-fill me-2" style="color: var(--ssp-orange);"></i><?= htmlspecialchars($phone) ?></p>
            <p class="mb-1"><i class="bi bi-envelope-fill me-2" style="color: var(--ssp-orange);"></i><?= htmlspecialchars($emailContact) ?></p>
          </div>

          <div class="d-flex gap-2">
            <a href="#" class="social-icon-btn"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-md-3">
          <h5 class="text-white fw-bold mb-3">Quick Navigation</h5>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="index.php" class="text-decoration-none text-light opacity-75">Home</a></li>
            <li class="mb-2"><a href="about.php" class="text-decoration-none text-light opacity-75">About Us</a></li>
            <li class="mb-2"><a href="courses.php" class="text-decoration-none text-light opacity-75">Courses</a></li>
            <li class="mb-2"><a href="products.php" class="text-decoration-none text-light opacity-75">Products & Materials</a></li>
            <li class="mb-2"><a href="contact.php" class="text-decoration-none text-light opacity-75">Contact Us</a></li>
          </ul>
        </div>

        <div class="col-lg-5 col-md-3">
          <h5 class="text-white fw-bold mb-3">Core Educational Offerings</h5>
          <ul class="list-unstyled small opacity-75">
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Private & Customized Tutoring</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Holiday Package & Guided Learning</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Homework & Assignment Support</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Science Project & STEM Innovation</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Applied Computer & ICT Lessons</li>
          </ul>
        </div>

      </div>
    </div>

    <div class="container text-center border-top border-secondary pt-3 mt-3 opacity-75 small">
      <p class="mb-0">&copy; <?= date('Y') ?> <strong>SmartStudyPro</strong>. All Rights Reserved. Empowering Education in Uganda.</p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center text-decoration-none"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    function handleGoogleCredential(response) {
      fetch('google-auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'credential=' + encodeURIComponent(response.credential) +
              '&redirect=' + encodeURIComponent(<?= json_encode($redirect) ?>)
      }).then(r => r.json()).then(data => {
        if (data.success) {
          window.location.href = data.redirect || 'profile.php';
        } else {
          alert(data.message || 'Google sign-in failed. Please try again.');
        }
      }).catch(() => alert('Google sign-in failed. Please try again.'));
    }
  </script>

</body>
</html>