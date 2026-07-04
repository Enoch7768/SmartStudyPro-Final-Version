<?php
require_once 'auth.php';

if (is_logged_in()) {
    header("Location: profile.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid name and email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        try {
            $stmt = auth_db()->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)");
            $stmt->execute([
                ':name'  => $name,
                ':email' => $email,
                ':hash'  => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = auth_db()->lastInsertId();
            login_user(['id' => $userId]);
            header("Location: profile.php");
            exit;
        } catch (Exception $e) {
            // Most likely a UNIQUE constraint failure on email
            $error = "That email is already registered, or something went wrong. Please try signing in instead.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Create Account - SmartStudyPro</title>
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
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

  <main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 420px; border-radius: 15px;">
      <div class="text-center mb-4">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo" style="height: 60px;">
        <h4 class="fw-bold mt-3">Create Your Account</h4>
      </div>

      <?php if ($error): ?>
        <p class="text-danger small text-center"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control rounded-pill" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control rounded-pill" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control rounded-pill" minlength="8" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control rounded-pill" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-success w-100 rounded-pill" style="background:#5fcf80; border:none;">Create Account</button>
      </form>

      <p class="text-center small text-muted mt-3 mb-0">
        Already have an account? <a href="login.php">Sign in</a>
      </p>
    </div>
  </main>

  <footer class="text-center py-4 text-muted small">
    <p>© 2026 SmartStudyPro - Matugga, Uganda</p>
  </footer>

</body>
</html>