<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';

try {
    $response = $butterCms->fetchPage('course', $slug);
    $fields = $response->getFields();
    
    // Logic to grab the right data from your ButterCMS fields
    $title = $fields['title'] ?? ($fields['service_name'] ?? $response->getName());
    $price = $fields['price'] ?? '150';
    $description = $fields['description'] ?? 'Personalized support for academic success.';
} catch (Exception $e) {
    die("Course not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($title); ?> - SmartStudyPro</title>

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="starter-page-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="contact.html">Contact</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="main">

    <div class="page-title">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1><?php echo htmlspecialchars($title); ?></h1>
              <p class="mb-0"><?php echo strip_tags(substr($description, 0, 150)); ?></p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current"><?php echo htmlspecialchars($title); ?></li>
          </ol>
        </div>
      </nav>
    </div>

    <section id="starter-section" class="starter-section section">
      <div class="container section-title">
        <h2><?php echo htmlspecialchars($title); ?></h2>
        <p>Book Your Session Now</p>
      </div>

      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <form action="booking.php" method="post" class="p-4 shadow rounded bg-white" style="border-top: 3px solid #5fcf80;">
              <input type="hidden" name="service" value="<?php echo htmlspecialchars($title); ?>">
              <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">

              <div class="row gy-4">
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Phone Number</label>
                  <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Preferred Date</label>
                  <input type="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Additional Notes</label>
                  <textarea name="message" class="form-control" rows="5" placeholder="Any specific requirements?"></textarea>
                </div>
                <div class="col-md-12">
                  <div class="p-3 bg-light rounded d-flex justify-content-between">
                    <strong>Total Course Fee:</strong>
                    <span class="h4 text-success mb-0">$<?php echo htmlspecialchars($price); ?></span>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary btn-lg px-5" style="background: #5fcf80; border: none;">Book Now</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer light-background text-center py-4 border-top">
    <div class="container">
      <p>© <strong>SmartStudyPro</strong> - All Rights Reserved</p>
    </div>
  </footer>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>