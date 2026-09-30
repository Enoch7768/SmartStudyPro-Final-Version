<?php

require_once __DIR__ . '/content-access.php';
require_login();

$user = current_user();
$bookingId = (int) ($_GET['id'] ?? 0);
$booking = paid_booking_for_resource(auth_db(), (int) $user['id'], $bookingId);

if (!$booking || empty($booking['file_path'])) {
    http_response_code(403);
    exit('This learning resource is not available for your account.');
}

$file = private_resource_path($booking['file_path']);
if (!$file || !resource_extension_allowed($file)) {
    http_response_code(404);
    exit('This learning resource is unavailable.');
}

$mime = resource_mime($file);
$title = $booking['service'] ?? 'Learning Resource';
$isPdf = $mime === 'application/pdf';
$isImage = str_starts_with($mime, 'image/');
$isVideo = str_starts_with($mime, 'video/');
$isAudio = str_starts_with($mime, 'audio/');
$isText = str_starts_with($mime, 'text/');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> | SmartStudyPro</title>
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
<style>
:root{--navy:#0C086B;--orange:#FF7A00;--bg:#F8FAFC;--card:#fff;--text:#1E293B;--muted:#64748B;--border:#E2E8F0}
[data-theme=dark]{--navy:#C7D2FE;--bg:#0B0F17;--card:#151C2C;--text:#F8FAFC;--muted:#94A3B8;--border:#2E3A52}
body{background:var(--bg);color:var(--text);font-family:Arial,sans-serif}
.reader-shell{min-height:100vh}.reader-header{background:var(--card);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:20}.reader-stage{min-height:calc(100vh - 76px)}.resource-frame{width:100%;height:calc(100vh - 140px);border:0;background:#fff;border-radius:16px}.resource-image{max-width:100%;max-height:calc(100vh - 180px);object-fit:contain}.resource-video{width:100%;max-height:calc(100vh - 180px)}.resource-audio{width:100%}.ai-panel{background:var(--card);border:1px solid var(--border);border-radius:18px}.ai-answer{white-space:pre-wrap;line-height:1.7}.btn-main{background:var(--orange);border:0;color:#fff;font-weight:700}
</style>
<script>
(function(){const t=localStorage.getItem('ssp-theme');if(t==='dark'||(!t&&matchMedia('(prefers-color-scheme:dark)').matches))document.documentElement.setAttribute('data-theme','dark')})();
</script>
</head>
<body>
<div class="reader-shell">
<header class="reader-header py-3">
<div class="container-fluid container-xl d-flex align-items-center justify-content-between gap-3">
<a href="profile.php" class="text-decoration-none"><img src="Smart_Study_Logo_Fin-removebg-preview.png" height="42" alt="SmartStudyPro"></a>
<div class="flex-grow-1"><div class="fw-bold" style="color:var(--navy)"><?= htmlspecialchars($title) ?></div><small style="color:var(--muted)">Protected learning resource</small></div>
<a href="profile.php" class="btn btn-sm btn-outline-secondary rounded-pill">My Learning</a>
</div>
</header>
<main class="container-fluid container-xl py-4 reader-stage">
<div class="row g-4">
<div class="col-lg-8">
<div class="p-3 p-md-4 rounded-4" style="background:var(--card);border:1px solid var(--border)">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-book-half me-2" style="color:var(--orange)"></i>SmartStudyPro Reader</h5>
<span class="badge text-bg-light">Paid access</span>
</div>
<?php if ($isPdf): ?>
<iframe class="resource-frame" src="resource-stream.php?id=<?= $bookingId ?>" title="<?= htmlspecialchars($title) ?>"></iframe>
<?php elseif ($isImage): ?>
<div class="text-center"><img class="resource-image" src="resource-stream.php?id=<?= $bookingId ?>" alt="<?= htmlspecialchars($title) ?>"></div>
<?php elseif ($isVideo): ?>
<video class="resource-video" controls controlsList="nodownload noremoteplayback" disablePictureInPicture oncontextmenu="return false;"><source src="resource-stream.php?id=<?= $bookingId ?>" type="<?= htmlspecialchars($mime) ?>"></video>
<?php elseif ($isAudio): ?>
<audio class="resource-audio" controls controlsList="nodownload noremoteplayback" oncontextmenu="return false;"><source src="resource-stream.php?id=<?= $bookingId ?>" type="<?= htmlspecialchars($mime) ?>"></audio>
<?php elseif ($isText): ?>
<iframe class="resource-frame" src="resource-stream.php?id=<?= $bookingId ?>" title="<?= htmlspecialchars($title) ?>"></iframe>
<?php else: ?>
<div class="alert alert-warning">This resource format is not supported by the SmartStudyPro reader.</div>
<?php endif; ?>
</div>
</div>
<div class="col-lg-4">
<div class="ai-panel p-4 sticky-lg-top" style="top:96px">
<div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-stars" style="color:var(--orange);font-size:1.4rem"></i><h5 class="fw-bold mb-0" style="color:var(--navy)">SmartStudy AI Tutor</h5></div>
<p class="small mb-3" style="color:var(--muted)">Ask anything about this resource. The tutor uses your purchased material as its primary context.</p>
<textarea id="aiQuestion" class="form-control mb-2" rows="4" placeholder="Ask a question about what you're learning..."></textarea>
<button id="askAi" class="btn btn-main w-100 rounded-3 py-2">Ask SmartStudy AI</button>
<div id="aiStatus" class="small mt-3" style="color:var(--muted)"></div>
<div id="aiAnswer" class="ai-answer mt-3"></div>
</div>
</div>
</div>
</main>
</div>
<script>
const ask=document.getElementById('askAi'),q=document.getElementById('aiQuestion'),status=document.getElementById('aiStatus'),answer=document.getElementById('aiAnswer');
ask.addEventListener('click',async()=>{const question=q.value.trim();if(!question)return;ask.disabled=true;status.textContent='Thinking…';answer.textContent='';try{const r=await fetch('ai-tutor.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({booking_id:<?= $bookingId ?>,question})});const d=await r.json();if(!r.ok)throw new Error(d.error||'The tutor could not answer.');answer.textContent=d.answer;status.textContent='Answered from your learning resource.'}catch(e){status.textContent=e.message}finally{ask.disabled=false}});
q.addEventListener('keydown',e=>{if(e.key==='Enter'&&(e.ctrlKey||e.metaKey)){e.preventDefault();ask.click()}});
document.addEventListener('contextmenu',e=>e.preventDefault());
</script>
</body>
</html>