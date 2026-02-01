<?php
/**
 * UNIVERSAL BOOKING HANDLER - SmartStudyPro
 * Handles Courses, Physical Products, and Digital Downloads.
 */

// 1. Identification Logic
if(!isset($_COOKIE['user_id'])) {
    $user_id = bin2hex(random_bytes(16));
    setcookie('user_id', $user_id, time() + (86400*30), "/"); 
} else {
    $user_id = $_COOKIE['user_id'];
}

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 2. Capture Data (Universal Mapping)
    $name     = $_POST['name']    ?? '';
    $email    = $_POST['email']   ?? '';
    $phone    = $_POST['phone']   ?? 'N/A';
    $date     = $_POST['date']    ?? date('Y-m-d');
    $service  = $_POST['service'] ?? $_POST['item_name'] ?? 'Unknown Item';
    $message  = $_POST['message'] ?? $_POST['address'] ?? ''; 
    $price    = $_POST['price']   ?? $_POST['item_price'] ?? '0';
    $filePath = $_POST['product_file'] ?? ''; 

    try {
        $db_dir = __DIR__ . "/database";
        $db_file = $db_dir . "/bookings.db";
        
        if (!file_exists($db_dir)) mkdir($db_dir, 0777, true);

        $db = new PDO("sqlite:$db_file");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 3. Create Table if not exists
        $db->exec("CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id TEXT,
            name TEXT,
            email TEXT,
            phone TEXT,
            date TEXT,
            service TEXT,
            message TEXT,
            price TEXT,
            paid INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // 4. SELF-HEALING: Check for 'file_path' column (Fixes the "No Column" Error)
        $tableInfo = $db->query("PRAGMA table_info(bookings)")->fetchAll(PDO::FETCH_ASSOC);
        $hasFilePath = false;
        foreach ($tableInfo as $column) {
            if ($column['name'] === 'file_path') {
                $hasFilePath = true;
                break;
            }
        }
        if (!$hasFilePath) {
            $db->exec("ALTER TABLE bookings ADD COLUMN file_path TEXT");
        }

        // 5. Insert the Data
        $stmt = $db->prepare("INSERT INTO bookings (user_id, name, email, phone, date, service, message, price, file_path) 
                              VALUES (:user_id, :name, :email, :phone, :date, :service, :message, :price, :file_path)");
        $stmt->execute([
            ':user_id'   => $user_id,
            ':name'      => $name,
            ':email'     => $email,
            ':phone'     => $phone,
            ':date'      => $date,
            ':service'   => $service,
            ':message'   => $message,
            ':price'     => $price,
            ':file_path' => $filePath
        ]);

        $success = true;
    } catch (Exception $e) {
        $error = "System Error: " . $e->getMessage();
    }
} else {
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Processing Order - SmartStudyPro</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
  <link rel="apple-touch-icon" href="Smart_Study_Logo_Fin-removebg-preview.png">
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-light">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo">
      </a>
    </div>
  </header>

  <main class="container py-5 mt-5">
    <div class="row justify-content-center">
      <div class="col-md-7">
        
        <?php if($success): ?>
          <div class="card shadow-lg border-0 text-center p-5 rounded-4">
            <div class="mb-4">
              <i class="bi bi-bag-check-fill text-success" style="font-size: 5rem;"></i>
            </div>
            <h2 class="fw-bold">Item Added to Cart</h2>
            <p class="text-muted px-lg-5">Your selection <strong>"<?= htmlspecialchars($service) ?>"</strong> has been successfully added to your shopping bag.</p>
            <hr class="my-4 mx-5 opacity-25">
            <div class="d-grid gap-2 d-md-block">
              <a href="products.php" class="btn btn-outline-secondary rounded-pill px-4 me-md-2">Continue Shopping</a>
              <a href="cart.php" class="btn btn-success rounded-pill px-4" style="background:#5fcf80; border:none;">View My Cart</a>
            </div>
          </div>

        <?php else: ?>
          <div class="card shadow-lg border-0 p-5 rounded-4 border-start border-danger border-5">
            <h3 class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Database Sync Required</h3>
            <p class="mt-3"><?= $error ?></p>
            <p class="small text-muted">Technical Tip: If this error persists, try deleting <code>database/bookings.db</code> to reset the schema.</p>
            <a href="javascript:history.back()" class="btn btn-dark rounded-pill mt-3">Go Back</a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </main>

  <footer class="text-center mt-5 text-muted small">
    <p>© 2026 SmartStudyPro - Matugga, Uganda</p>
  </footer>

</body>
</html>