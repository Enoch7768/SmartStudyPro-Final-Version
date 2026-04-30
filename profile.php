<?php
/**
 * SmartStudyPro V2.7 - FINAL PRODUCTION BUILD
 * Fixes: Line 128 Null Error, "Untitled" Lesson Bug, Local MP4 Routing
 */

require_once 'cms-init.php'; 

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$course_name = '';
$chapters = [];
$error_message = '';
$active_lesson_data = null; 

try {
    $db_file = __DIR__ . "/database/bookings.db";
    if (!file_exists($db_file)) throw new Exception("Database missing.");

    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare("SELECT service FROM bookings WHERE id = ? AND paid = 1");
    $stmt->execute([$course_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $error_message = "Access Denied: Payment not verified.";
    } else {
        $course_name = trim($booking['service']);

        if (function_exists('cockpit')) {
            $sample = cockpit('content')->items('Chapters', ['limit' => 1]);
            $c_field = 'COURSE TITLE'; 
            if (!empty($sample)) {
                foreach ($sample[0] as $k => $v) {
                    if (is_string($v) && stripos($course_name, substr($v, 0, 5)) !== false) { $c_field = $k; break; }
                }
            }

            $variations = array_unique([$course_name, strtoupper($course_name), strtolower($course_name), ucwords(strtolower($course_name))]);
            foreach ($variations as $v_name) {
                $chapters = cockpit('content')->items('Chapters', ['filter' => [$c_field => $v_name], 'sort' => ['CHAPTER ID' => 1]]);
                if (!empty($chapters)) break;
            }
        }
    }
} catch (Exception $e) { $error_message = $e->getMessage(); }

$active_video_path = isset($_GET['v']) ? $_GET['v'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Profile </title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
  <style>
    :root { --study-green: #5fcf80; }
    .study-layout { display: flex; min-height: calc(100vh - 70px); }
    .sidebar { width: 320px; background: #fff; border-right: 1px solid #eee; height: calc(100vh - 70px); position: sticky; top: 70px; overflow-y: auto; }
    .main-video-area { flex: 1; padding: 40px; background: #fcfdfd; }
    .chapter-label { background: #f8f9fa; padding: 12px 20px; font-weight: 700; border-bottom: 1px solid #eee; color: #333; }
    .lesson-link { display: block; padding: 12px 25px; color: #555; text-decoration: none; border-bottom: 1px solid #f9f9f9; font-size: 0.95rem; border-left: 4px solid transparent; }
    .lesson-link:hover, .lesson-link.active { background: #f0fff4; color: var(--study-green); border-left: 4px solid var(--study-green); }
    .quiz-card { background: #fff; border-radius: 12px; border: 1px solid #eef0f2; margin-bottom: 20px; }
    @media (max-width: 991px) { .study-layout { flex-direction: column; } .sidebar { width: 100%; height: auto; position: static; } }
  </style>
</head>
<body>

  <header class="d-flex align-items-center px-4 border-bottom bg-white" style="height:70px;">
    <a href="index.php"><img src="Smart_Study_Logo_Fin-removebg-preview.png" height="40"></a>
    <h5 class="ms-4 mb-0 d-none d-md-block text-success fw-bold"><?= htmlspecialchars($course_name) ?></h5>
  </header>

  <?php if ($error_message): ?>
    <div class="alert alert-danger m-5 text-center"><?= $error_message ?></div>
  <?php else: ?>
    <div class="study-layout">
      <nav class="sidebar">
        <?php foreach ($chapters as $chapter): ?>
          <div class="chapter-label"><i class="bi bi-collection-play me-2"></i> <?= htmlspecialchars($chapter['Title'] ?? $chapter['title'] ?? 'Section') ?></div>
          <?php 
            $lessons = cockpit('content')->items('Lessons', ['filter' => ['Chapter' => $chapter['_id']], 'sort' => ['Order' => 1]]);
            foreach ($lessons as $lesson): 
              
              // --- 1. SMART TITLE DETECTION ---
              $l_title = 'Untitled Lesson';
              if (!empty($lesson['Title'])) $l_title = $lesson['Title'];
              elseif (!empty($lesson['title'])) $l_title = $lesson['title'];
              else {
                  // Fallback: Use the first string field that isn't ID or Chapter
                  foreach($lesson as $k => $v) {
                      if (is_string($v) && strlen($v) > 2 && !in_array($k, ['_id', 'Chapter', 'VIDEO FILE'])) {
                          $l_title = $v; break;
                      }
                  }
              }

              // --- 2. NULL-SAFE VIDEO DETECTION ---
              $video_asset = !empty($lesson['VIDEO FILE']) ? $lesson['VIDEO FILE'] : null;
              $l_video_path = '';
              if ($video_asset) {
                  $l_video_path = is_array($video_asset) ? ltrim($video_asset['path'], '/') : ltrim($video_asset, '/');
              }

              $is_active = ($active_video_path && ltrim($active_video_path, '/') === $l_video_path);
              if ($is_active) { $active_lesson_data = $lesson; }
          ?>
            <a href="?course_id=<?= $course_id ?>&v=<?= urlencode($l_video_path) ?>" class="lesson-link <?= $is_active ? 'active' : '' ?>">
              <i class="bi bi-play-circle me-2"></i> <?= htmlspecialchars($l_title) ?>
            </a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </nav>

      
        <?php endif; ?>
      </main>
    </div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>