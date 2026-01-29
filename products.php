<?php 
require_once 'cms-init.php'; 

$products = [];
$contact = null;

try {
    if (function_exists('cockpit')) {
        // Fetch products from the new collection
        $products = cockpit('content')->items('Products');
        // Fetch contact details for the footer
        $contact = cockpit('content')->item('ContactDetails');
    }
} catch (Exception $e) {
    $products = [];
    $contact = null;
}

// Fallbacks for Footer
$phone   = $contact['phone'] ?? '+256 704 416250';
$email   = $contact['email'] ?? 'smartstudypro36@gmail.com';
$address = strip_tags($contact['address'] ?? 'Kampala, Uganda');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Products - SmartStudyPro</title>

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
    /* UI Fixes for card alignment */
    .product-item {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
        border: 1px solid #eef0ef;
        position: relative;
        transition: 0.3s;
    }
    .product-item:hover {
        box-shadow: 0px 5px 20px rgba(0,0,0,0.1);
    }
    .type-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        color: white;
        z-index: 5;
    }
    .badge-online { background-color: #5fcf80; }
    .badge-physical { background-color: #f39c12; }

    .product-content {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        padding: 20px;
    }
    .product-description {
        flex-grow: 1;
        color: #777;
        font-size: 14px;
        margin-bottom: 15px;
    }
    .product-footer {
        border-top: 1px solid #f1f1f1;
        padding-top: 15px;
    }
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
                      <p class="price" style="font-weight: 700; color: #37423b; margin: 0;">$<?= htmlspecialchars($product['Price'] ?? '0') ?></p>
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
          <a href="index.php" class="logo d-flex align-items-center">
            <span class="sitename">SmartStudyPro</span>
          </a>
          <div class="footer-contact pt-3">
            <p><?= htmlspecialchars($address) ?></p>
            <p class="mt-3"><strong>Phone:</strong> <span><?= htmlspecialchars($phone) ?></span></p>
            <p><strong>Email:</strong> <span><?= htmlspecialchars($email) ?></span></p>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>