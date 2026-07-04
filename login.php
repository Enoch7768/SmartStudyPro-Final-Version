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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Sign In - SmartStudyPro</title>
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <script src="https://accounts.google.com/gsi/client" async defer></script>
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
        <h4 class="fw-bold mt-3">Welcome Back</h4>
        <p class="text-muted small mb-0">Sign in to view your profile and courses</p>
      </div>

      <?php if ($error): ?>
        <p class="text-danger small text-center"><?= htmlspecialchars($error) ?></p>
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
        <hr class="flex-grow-1">
        <span class="mx-2 text-muted small">or</span>
        <hr class="flex-grow-1">
      </div>

      <form method="POST">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control rounded-pill" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control rounded-pill" required>
        </div>
        <button type="submit" name="login" class="btn btn-success w-100 rounded-pill" style="background:#5fcf80; border:none;">Sign In</button>
      </form>

      <p class="text-center small text-muted mt-3 mb-0">
        Don't have an account? <a href="register.php">Create one</a>
      </p>
    </div>
  </main>

  <footer class="text-center py-4 text-muted small">
    <p>© 2026 SmartStudyPro - Matugga, Uganda</p>
  </footer>

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