<?php 
require_once 'cms-init.php'; 

$courses = [];
$contact = null;

try {
    if (function_exists('cockpit')) {
        $courses = cockpit('content')->items('Courses');
        $contact = cockpit('content')->item('ContactDetails');
    }
} catch (Exception $e) {
    $courses = [];
    $contact = null;
}
$phone   = $contact['phone'] ?? '+256 704 416250';
$email   = $contact['email'] ?? 'smartstudypro36@gmail.com';
$address = strip_tags($contact['address'] ?? 'Kampala, Uganda');

$seoTitle       = 'Professional Courses in Uganda | SmartStudyPro';
$seoDescription = 'Browse our catalog of expert-led courses. From ICT and Science to professional skill development, find the right path for your future at SmartStudyPro.';
$siteUrl        = "https://smartstudypro.com"; // Replace with your actual domain
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta name="keywords" content="coding courses, science projects, ICT training Uganda, professional certificates">

  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $siteUrl ?>/courses.php">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="og:image" content="<?= $siteUrl ?>/Smart_Study_Logo_Fin-removebg-preview.png">

  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "itemListElement": [
      <?php 
      $i = 1;
      foreach(array_slice($courses, 0, 5) as $course): // Lists top 5 for SEO snippet
      ?>
      {
        "@type": "ListItem",
        "position": <?= $i ?>,
        "name": "<?= htmlspecialchars($course['Title'] ?? 'Course') ?>",
        "url": "<?= $siteUrl ?>/course-details.php?id=<?= $course['_id'] ?>"
      }<?= ($i < count(array_slice($courses, 0, 5))) ? ',' : '' ?>
      <?php $i++; endforeach; ?>
    ]
  }
  </script>

  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    /* Your existing CSS fixes for card heights */
    .course-item { display: flex; flex-direction: column; height: 100%; background: #fff; border: 1px solid #eef0ef; }
    .course-content { display: flex; flex-direction: column; flex-grow: 1; padding: 15px; }
    .course-content h3 { margin: 10px 0; }
    .description { flex-grow: 1; margin-bottom: 20px; color: #777; font-size: 14px; }
    .trainer { margin-top: auto; border-top: 1px solid #eef0ef; padding-top: 15px; }
  </style>
</head>

<body class="courses-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="courses.php" class="active">Courses</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="cart.php" title="Shopping Cart"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <a class="btn-getstarted" href="courses.php">Get Started</a>
    </div>
  </header>

  <main class="main">

    <div class="page-title" data-aos="fade">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1>Courses</h1>
              <p class="mb-0">Quality educational resources tailored for your success.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section id="courses" class="courses section">
      <div class="container">
        <div class="row gy-4"> <?php if (!empty($courses)): ?>
            <?php foreach($courses as $course): ?>
              <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in">
                <div class="course-item">
                  <?php 
                    $cImgData = $course['Image'] ?? $course['image'] ?? null;
                    $cImg = !empty($cImgData['path']) 
                            ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads'.$cImgData['path'] 
                            : 'assets/img/course-1.jpg'; 
                  ?>
                  <img src="<?= $cImg ?>" class="img-fluid" alt="<?= htmlspecialchars($course['Title'] ?? 'Course') ?>">
                  
                  <div class="course-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <p class="category" style="background: #5fcf80; color: #fff; padding: 4px 12px; border-radius: 50px; font-size: 12px;"><?= htmlspecialchars($course['Category'] ?? 'General') ?></p>
                      <p class="price" style="font-weight: 700; font-size: 18px; color: #37423b;">UGX <?= number_format(floatval($course['Price'] ?? 0), 2) ?></p>
                    </div>

                    <h3><a href="course-details.php?id=<?= $course['_id'] ?>"><?= htmlspecialchars($course['Title'] ?? 'Untitled Course') ?></a></h3>
                    
                    <div class="description">
                        <?= strip_tags($course['Description'] ?? $course['description'] ?? '') ?>
                    </div>
                    
                    <div class="trainer">
                        <a href="course-details.php?id=<?= $course['_id'] ?>" class="w-100">
                            <button type="button" class="btn btn-success w-100" style="background-color: #5fcf80; border: none; border-radius: 50px; padding: 8px; font-weight: 600;">Explore More</button>
                        </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 text-center">
              <p>No courses found. Please check back later.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

  </main>

    <footer id="footer" class="footer position-relative light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">SmartStudyPro</span>
          </a>
            <div class="footer-contact pt-3">
            <p><?= htmlspecialchars($address) ?></p>
            <p class="mt-3"><strong>Phone:</strong> <span><?= htmlspecialchars($phone) ?></span></p>
            <p><strong>Email:</strong> <span><?= htmlspecialchars($email) ?></span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About us</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="products.php">Product</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Our Courses</h4>
          <ul>
            <li>Private Tutoring</li>
            <li>Holiday Package Guidance</li>
            <li>Homework Assistance</li>
            <li>Science Project Work Innovation</li>
            <li>Computer Lessons (ICT)</li>
          </ul>
        </div>

         <div class="container text-center">
        <p>© 2026 SmartStudyPro. Empowering Education in Uganda.</p>
     </div>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>