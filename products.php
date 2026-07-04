<?php 
require_once 'cms-init.php'; 

$products = [];
$contact = null;

try {
    if (function_exists('cockpit')) {
        $products = cockpit('content')->items('Products');
        $contact = cockpit('content')->item('ContactDetails');
    }
} catch (Exception $e) {
    $products = [];
    $contact = null;
}

$phone   = $contact['phone'] ?? '+256 704 416250';
$email   = $contact['email'] ?? 'smartstudypro36@gmail.com';
$address = strip_tags($contact['address'] ?? 'Kampala, Uganda');

$seoTitle       = 'Educational Resources & Kits | SmartStudyPro Store';
$seoDescription = 'Shop science kits, ICT project tools, and online learning resources. Get the best educational materials delivered in Uganda or access instantly online.';
$siteUrl        = "https://smartstudypro.com";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta name="keywords" content="science kits Uganda, educational tools, lab equipment, online study resources">

  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $siteUrl ?>/products.php">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="og:image" content="<?= $siteUrl ?>/Smart_Study_Logo_Fin-removebg-preview.png">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "SmartStudyPro Educational Store",
    "description": "High-quality educational tools and online resources for students.",
    "itemListElement": [
      <?php 
      $pCount = 1;
      foreach(array_slice($products, 0, 10) as $p): 
      ?>
      {
        "@type": "ListItem",
        "position": <?= $pCount ?>,
        "url": "<?= $siteUrl ?>/product-details.php?id=<?= $p['_id'] ?>",
        "name": "<?= htmlspecialchars($p['Title'] ?? 'Product') ?>"
      }<?= ($pCount < count(array_slice($products, 0, 10))) ? ',' : '' ?>
      <?php $pCount++; endforeach; ?>
    ]
  }
  </script>

  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    .product-item { display: flex; flex-direction: column; height: 100%; background: #fff; border: 1px solid #eef0ef; position: relative; transition: 0.3s; }
    .product-item:hover { box-shadow: 0px 5px 20px rgba(0,0,0,0.1); }
    .type-badge { position: absolute; top: 15px; right: 15px; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 700; color: white; z-index: 5; }
    .badge-online { background-color: #5fcf80; }
    .badge-physical { background-color: #f39c12; }
    .product-content { display: flex; flex-direction: column; flex-grow: 1; padding: 20px; }
    .product-description { flex-grow: 1; color: #777; font-size: 14px; margin-bottom: 15px; }
    .product-footer { border-top: 1px solid #f1f1f1; padding-top: 15px; }
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
          <li><a href="courses.php">Courses</a></li>
          <li><a href="products.php" class="active">Products</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li><a href="cart.php" title="Shopping Cart"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main class="main">

    <div class="page-title" data-aos="fade">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1>Store & Resources</h1>
              <p class="mb-0">Find the right tools and kits to boost your learning journey.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section id="products" class="courses section">
      <div class="container">
        <div class="row gy-4">
          <?php if (!empty($products)): ?>
            <?php foreach($products as $product): ?>
              <?php 
                $type = $product['ProductType'] ?? 'Online';
                $isPhysical = ($type === 'Physical');
                $imgData = $product['Image'] ?? null;
                $img = !empty($imgData['path']) 
                       ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads'.$imgData['path'] 
                       : 'assets/img/placeholder.jpg';
              ?>
              <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in">
                <div class="product-item">
                  
                  <span class="type-badge <?= $isPhysical ? 'badge-physical' : 'badge-online' ?>">
                    <?= strtoupper($type) ?>
                  </span>

                  <img src="<?= $img ?>" class="img-fluid" alt="<?= htmlspecialchars($product['Title'] ?? 'Product') ?>">
                  
                  <div class="product-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <p class="category" style="background: #f1f1f1; padding: 4px 10px; border-radius: 5px; font-size: 12px; margin: 0;">
                        <?= htmlspecialchars($product['Category'] ?? 'General') ?>
                      </p>
                      <p class="price" style="font-weight: 700; color: #37423b; margin: 0;">UGX <?= number_format(floatval($product['Price'] ?? 0), 2) ?></p>
                    </div>

                    <h3><a href="product-details.php?id=<?= $product['_id'] ?>"><?= htmlspecialchars($product['Title'] ?? 'Untitled Item') ?></a></h3>
                    
                    <div class="product-description">
                        <?= strip_tags($product['Description'] ?? '') ?>
                    </div>
                    
                    <div class="product-footer">
                        <?php if($isPhysical): ?>
                            <small class="text-warning d-block mb-2"><i class="bi bi-truck"></i> Home Delivery</small>
                        <?php else: ?>
                            <small class="text-success d-block mb-2"><i class="bi bi-cloud-download"></i> Instant Access</small>
                        <?php endif; ?>
                        
                        <a href="product-details.php?id=<?= $product['_id'] ?>" class="w-100">
                            <button type="button" class="btn btn-success w-100" style="background-color: <?= $isPhysical ? '#f39c12' : '#5fcf80' ?>; border: none; border-radius: 50px; font-weight: 600;">
                                <?= $isPhysical ? 'Order Now' : 'Enroll Now' ?>
                            </button>
                        </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 text-center">
              <p>No products available at the moment.</p>
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

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>