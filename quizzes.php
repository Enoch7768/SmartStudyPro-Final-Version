<?php
/**
 * SmartStudyPro - Quiz Center
 * Sidebar-based navigation for all quizzes
 */

require_once 'cms-init.php'; 

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$active_quiz_id = $_GET['quiz_id'] ?? null;
$course_name = '';
$all_quizzes = [];
$active_quiz_questions = [];

try {
    $db_file = __DIR__ . "/database/bookings.db";
    $db = new PDO("sqlite:$db_file");
    $db = new PDO("sqlite:$db_file");
    
    // 1. Get Course Name
    $stmt = $db->prepare("SELECT service FROM bookings WHERE id = ? AND paid = 1");
    $stmt->execute([$course_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    $course_name = $booking ? trim($booking['service']) : "Course";

    if (function_exists('cockpit')) {
        // 2. Fetch ALL available quizzes for the sidebar
        $all_quizzes = cockpit('content')->items('Quizzes');

        // 3. Fetch specific questions if one is selected
        if ($active_quiz_id) {
            $active_quiz_questions = cockpit('content')->items('Quizzes', [
                'filter' => ['_id' => $active_quiz_id]
            ]);
        }
    }
} catch (Exception $e) { $error_message = $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quiz Center | <?= htmlspecialchars($course_name) ?></title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
    <style>
        :root { --study-green: #5fcf80; }
        body { background: #fcfdfd; overflow: hidden; }
        .quiz-layout { display: flex; height: calc(100vh - 70px); }
        .quiz-sidebar { width: 350px; background: #fff; border-right: 1px solid #eee; overflow-y: auto; padding: 20px; }
        .quiz-content { flex: 1; overflow-y: auto; padding: 40px; background: #f8fafb; }
        .quiz-nav-item { 
            display: block; padding: 15px; border-radius: 10px; border: 1px solid #f0f0f0; 
            margin-bottom: 10px; color: #444; text-decoration: none; transition: 0.2s;
        }
        .quiz-nav-item:hover { background: #f0fff4; border-color: var(--study-green); }
        .quiz-nav-item.active { background: var(--study-green); color: #fff; border-color: var(--study-green); }
        .card-quiz { background: #fff; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-success { background: var(--study-green); border: none; padding: 10px 30px; border-radius: 30px; font-weight: bold; }
    </style>
</head>
<body>

<header class="d-flex align-items-center px-4 border-bottom bg-white" style="height:70px;">
    <a href="study.php?course_id=<?= $course_id ?>" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Study Room</a>
    <h5 class="mb-0 fw-bold text-success">Quiz Center: <?= htmlspecialchars($course_name) ?></h5>
</header>

<div class="quiz-layout">
    <aside class="quiz-sidebar">
        <h6 class="text-uppercase text-muted fw-bold mb-3 small">Available Quizzes</h6>
        <?php if (!empty($all_quizzes)): ?>
            <?php foreach ($all_quizzes as $q_nav): ?>
                <a href="?course_id=<?= $course_id ?>&quiz_id=<?= $q_nav['_id'] ?>" 
                   class="quiz-nav-item <?= ($active_quiz_id == $q_nav['_id']) ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <?= htmlspecialchars($q_nav['Question'] ?? 'Quiz Question') ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted italic">No quizzes found.</p>
        <?php endif; ?>
    </aside>

    <main class="quiz-content">
        <?php if ($active_quiz_id && !empty($active_quiz_questions)): ?>
            <?php foreach ($active_quiz_questions as $q): ?>
                <div class="card card-quiz p-5 mx-auto" style="max-width: 700px;">
                    <span class="badge bg-light text-success mb-3 px-3 py-2 border w-25">Question</span>
                    <h3 class="fw-bold mb-4"><?= htmlspecialchars($q['Question']) ?></h3>
                    
                    <form id="activeQuizForm">
                        <?php 
                            $options = explode(',', $q['Options']); 
                            foreach ($options as $opt): $opt = trim($opt);
                        ?>
                            <div class="form-check p-3 border rounded mb-3 shadow-sm-hover">
                                <input class="form-check-input ms-0 me-3" type="radio" name="q_choice" value="<?= htmlspecialchars($opt) ?>" id="opt_<?= md5($opt) ?>">
                                <label class="form-check-label w-100" for="opt_<?= md5($opt) ?>">
                                    <?= htmlspecialchars($opt) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <input type="hidden" id="correct_ans" value="<?= htmlspecialchars($q['Correct Answer']) ?>">
                        
                        <div class="text-center mt-4">
                            <button type="button" onclick="checkAnswer()" class="btn btn-success px-5">Submit Answer</button>
                        </div>
                    </form>
                    
                    <div id="quizFeedback" class="mt-4 alert d-none text-center fw-bold"></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center mt-5 py-5">
                <i class="bi bi-ui-checks text-success opacity-25" style="font-size: 5rem;"></i>
                <h2 class="mt-4 fw-bold">Select a Quiz to Start</h2>
                <p class="text-muted">Choose from the questions on the left to test your knowledge.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
function checkAnswer() {
    const selected = document.querySelector('input[name="q_choice"]:checked');
    const correct = document.getElementById('correct_ans').value.trim();
    const feedback = document.getElementById('quizFeedback');

    if (!selected) {
        alert("Please select an answer!");
        return;
    }

    feedback.classList.remove('d-none', 'alert-success', 'alert-danger');
    
    if (selected.value.trim() === correct) {
        feedback.classList.add('alert-success');
        feedback.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Correct! Excellent work.';
    } else {
        feedback.classList.add('alert-danger');
        feedback.innerHTML = `<i class="bi bi-x-circle-fill me-2"></i> Incorrect. The correct answer was: ${correct}`;
    }
}
</script>

</body>
</html>