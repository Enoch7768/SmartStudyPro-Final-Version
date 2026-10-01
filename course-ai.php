<?php

require_once __DIR__ . '/content-access.php';
require_login();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$courseId = (int) ($input['course_id'] ?? 0);
$lessonId = (string) ($input['lesson_id'] ?? '');
$question = trim((string) ($input['question'] ?? ''));

if ($courseId <= 0 || $question === '' || mb_strlen($question) > 4000) {
    http_response_code(422);
    echo json_encode(['error' => 'Please provide a valid question.']);
    exit;
}

$key = app_config('GEMINI_API_KEY');
if (!$key) {
    http_response_code(503);
    echo json_encode(['error' => 'The SmartStudyPro AI tutor is not configured yet.']);
    exit;
}

$user = current_user();
$db = auth_db();
$stmt = $db->prepare("SELECT * FROM bookings WHERE id = :id AND paid = 1 AND account_id = :account_id LIMIT 1");
$stmt->execute([':id' => $courseId, ':account_id' => $user['id']]);
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$purchase) {
    http_response_code(403);
    echo json_encode(['error' => 'Payment is required before using the course AI tutor.']);
    exit;
}

$lesson = null;
if (function_exists('cms_items') && $lessonId !== '') {
    $lesson = cms_item('Lessons', ['_id' => $lessonId]);
}
if (!$lesson && function_exists('cms_items')) {
    $lessons = cms_items('Lessons', ['limit' => 1]);
    $lesson = $lessons[0] ?? null;
}

$notes = strip_tags((string) ($lesson['Content'] ?? $lesson['content'] ?? ''));
$title = (string) ($lesson['Title'] ?? $lesson['title'] ?? 'Current lesson');
$prompt = "You are the SmartStudyPro course tutor. Teach the learner clearly and step by step. Use the current lesson notes below as the primary source. If the answer is not supported by the lesson, say so and then provide a clearly labelled general explanation. Do not invent course facts. Course: " . ($purchase['service'] ?? 'Course') . ". Lesson: {$title}. Notes: {$notes}. Learner question: {$question}";

$payload = ['contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]], 'generationConfig' => ['temperature' => 0.35, 'maxOutputTokens' => 2048]];
$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=' . urlencode($key));
curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_TIMEOUT => 60]);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $status < 200 || $status >= 300) {
    http_response_code(502);
    echo json_encode(['error' => 'The course tutor could not answer right now.']);
    exit;
}

$data = json_decode($response, true);
$answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
if (!$answer) {
    http_response_code(502);
    echo json_encode(['error' => 'The course tutor returned an empty answer.']);
    exit;
}
echo json_encode(['answer' => $answer], JSON_UNESCAPED_UNICODE);
