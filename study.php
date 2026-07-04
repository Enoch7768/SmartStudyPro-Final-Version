<?php
<<<<<<< HEAD

=======
>>>>>>> dabe44dcea665a2a138291a462ccd22d40c88014

require_once 'cms-init.php';
require_once 'auth.php';
require_login(); 

$user = current_user();

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
    ensure_bookings_columns($db);

    $stmt = $db->prepare("SELECT service FROM bookings WHERE id = ? AND paid = 1 AND (account_id = ? OR email = ?)");
    $stmt->execute([$course_id, $user['id'], $user['email']]);
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
} catch (Exception $e) {
    error_log("study.php error: " . $e->getMessage());
    $error_message = "Access Denied: Payment not verified.";
}

$active_video_path = isset($_GET['v']) ? $_GET['v'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Study Portal | <?= htmlspecialchars($course_name) ?></title>
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
    <div class="alert alert-danger m-5 text-center"><?= htmlspecialchars($error_message) ?></div>
  <?php else: ?>
    <div class="study-layout">
      <nav class="sidebar">
        <?php foreach ($chapters as $chapter): ?>
          <div class="chapter-label"><i class="bi bi-collection-play me-2"></i> <?= htmlspecialchars($chapter['Title'] ?? $chapter['title'] ?? 'Section') ?></div>
          <?php 
            $lessons = cockpit('content')->items('Lessons', ['filter' => ['Chapter' => $chapter['_id']], 'sort' => ['Order' => 1]]);
            foreach ($lessons as $lesson): 
              $l_title = 'Untitled Lesson';
              if (!empty($lesson['Title'])) $l_title = $lesson['Title'];
              elseif (!empty($lesson['title'])) $l_title = $lesson['title'];
              else {
                  foreach($lesson as $k => $v) {
                      if (is_string($v) && strlen($v) > 2 && !in_array($k, ['_id', 'Chapter', 'VIDEO FILE'])) {
                          $l_title = $v; break;
                      }
                  }
              }

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

      <main class="main-video-area">
        <?php if ($active_video_path && $active_lesson_data): ?>
          <div class="video-container" style="background: #000; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <?php 
                $token = sign_video_token($active_video_path, $user['id'], 6 * 3600); 
                $final_url = "stream.php?path=" . rawurlencode($active_video_path)
                           . "&exp=" . $token['exp']
                           . "&sig=" . $token['sig'];
            ?>
            <video id="mainPlayer" controls autoplay controlsList="nodownload noremoteplayback" disablePictureInPicture oncontextmenu="return false;" style="width: 100%; display: block; max-height: 75vh;">
                <source src="<?= htmlspecialchars($final_url) ?>" type="video/mp4">
            </video>
          </div>

          <div class="mt-4 p-4 bg-white rounded shadow-sm border">
              <h4 class="fw-bold mb-3"><?= htmlspecialchars($active_lesson_data['Title '] ?? $active_lesson_data['Title '] ?? 'Lesson Details') ?></h4>
              <div class="text-secondary" style="line-height: 1.6;">
                  <?= $active_lesson_data['Content'] ?? $active_lesson_data['content'] ?? 'No notes availables.' ?>
                  
              </div>

          <?php 
            $quizzes = cockpit('content')->items('Quizzes', ['filter' => ['Lesson' => $active_lesson_data['_id']]]);
            if (!empty($quizzes)): 
          ?>
          <div class="quiz-section mt-5">
            <h4 class="fw-bold mb-4 text-success"><i class="bi bi-patch-check-fill me-2"></i>Knowledge Check</h4>
            <form id="quizForm">
                <?php foreach ($quizzes as $index => $q): ?>
                <div class="quiz-card p-4 shadow-sm border" data-quiz-id="<?= htmlspecialchars($q['_id']) ?>">
                    <p class="fw-bold mb-3"><?= ($index + 1) ?>. <?= htmlspecialchars($q['Question']) ?></p>
                    <?php 
                      $options = explode(',', $q['Options']); 
                      foreach ($options as $opt): $opt = trim($opt);
                    ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="q_<?= $q['_id'] ?>" value="<?= htmlspecialchars($opt) ?>" id="opt_<?= md5($opt.$q['_id']) ?>">
                        <label class="form-check-label" for="opt_<?= md5($opt.$q['_id']) ?>"><?= htmlspecialchars($opt) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                <button type="button" onclick="gradeQuiz()" class="btn btn-success px-5 py-2 fw-bold shadow-sm">Grade My Answers</button>
            </form>
            <div id="quizFeedback" class="mt-4 d-none alert"></div>
          </div>
          <?php endif; ?>

        <?php else: ?>
          <div class="text-center py-5 mt-5">
            <i class="bi bi-play-btn text-success opacity-25" style="font-size: 6rem;"></i>
            <h3 class="mt-4 fw-bold">Ready to Learn?</h3>
            <p class="text-muted">Pick a lesson from the sidebar to start watching.</p>
          </div>
        <?php endif; ?>
      </main>
    </div>
  <?php endif; ?>

  <script>
    function gradeQuiz() {
        const answers = {};
        document.querySelectorAll('.quiz-card').forEach(card => {
            const quizId = card.dataset.quizId;
            const selected = card.querySelector('input[type="radio"]:checked');
            if (selected) answers[quizId] = selected.value;
        });

        if (Object.keys(answers).length === 0) {
            alert("Please answer at least one question.");
            return;
        }

        fetch('grade-quiz.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ answers })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            const fb = document.getElementById('quizFeedback');
            fb.className = "mt-4 alert " + (data.score === data.total ? "alert-success" : "alert-danger");
            fb.innerHTML = `<strong>Result: ${data.score}/${data.total}</strong> — ` +
                           (data.score === data.total ? "Perfect score!" : "Check the video and try again.");
            fb.classList.remove('d-none');
            fb.scrollIntoView({ behavior: 'smooth' });
        })
        .catch(() => alert('Could not grade your answers right now. Please try again.'));
    }
  </script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>