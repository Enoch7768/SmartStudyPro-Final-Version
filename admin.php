<?php
// Simple Security: Change 'admin123' to your preferred password
$admin_password = 'admin123'; 

if (!isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_PW'] !== $admin_password || $_SERVER['PHP_AUTH_USER'] !== 'admin') {
    header('WWW-Authenticate: Basic realm="SmartStudyPro Admin"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Password required to access this page.';
    exit;
}

try {
    $db_file = __DIR__ . "/database/bookings.db";
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Bookings (Removed ORDER BY created_at)
    $bookings = $db->query("SELECT * FROM bookings ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Contact Messages (Removed ORDER BY created_at)
    $messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SmartStudyPro</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .nav-tabs .nav-link.active { background-color: #5fcf80; color: white; border: none; }
        .table thead { background-color: #5fcf80; color: white; }
        .navbar-brand img { height: 40px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1">SmartStudyPro Admin</span>
        <a href="index.php" class="btn btn-outline-light btn-sm">View Website</a>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-4 text-center">
                <h3 class="display-6 fw-bold text-success"><?= count($bookings) ?></h3>
                <p class="text-muted mb-0">Total Bookings</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 text-center">
                <h3 class="display-6 fw-bold text-primary"><?= count($messages) ?></h3>
                <p class="text-muted mb-0">Contact Messages</p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs mb-4" id="adminTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button">Bookings</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button">Messages</button>
                </li>
            </ul>

            <div class="tab-content" id="adminTabContent">
                <div class="tab-pane fade show active" id="bookings">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student Name</th>
                                    <th>Course/Service</th>
                                    <th>Requested Date</th>
                                    <th>Price</th>
                                    <th>Contact Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>#<?= $b['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($b['name']) ?></strong></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($b['service']) ?></span></td>
                                    <td><?= htmlspecialchars($b['date']) ?></td>
                                    <td>$<?= htmlspecialchars($b['price']) ?></td>
                                    <td>
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($b['email']) ?><br>
                                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($b['phone']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="messages">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $m): ?>
                                <tr>
                                    <td>#<?= $m['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($m['name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($m['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($m['subject']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>