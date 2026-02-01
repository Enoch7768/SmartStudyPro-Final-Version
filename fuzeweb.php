<?php
// ============================
// HANDLE QUOTE REQUEST (API)
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quote') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $project = trim($_POST['project_type'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$project || !$budget) {
        http_response_code(400);
        echo "Missing required fields";
        exit;
    }

    $to = "r7194714@gmail.com";
    $subject = "New Quote Request – FuzeWeb";

    $body = "New quote request from FuzeWeb site:\n\n"
          . "Name: $name\n"
          . "Email: $email\n"
          . "Project Type: $project\n"
          . "Budget: $budget\n\n"
          . "Message:\n$message";

    $headers = "From: FuzeWeb <no-reply@fuzeweb.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8";

    if (mail($to, $subject, $body, $headers)) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Email failed";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FuzeWeb — Web Development Company</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root {
  --bg: #0B0F1A;
  --cyan: #00F5FF;
  --text: #B6C2CF;
  --white: #ffffff;
}

* { box-sizing: border-box; }
body {
  margin: 0;
  font-family: Inter, system-ui, sans-serif;
  background: var(--bg);
  color: var(--text);
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 40px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.logo {
  color: var(--cyan);
  font-size: 22px;
  letter-spacing: 2px;
}

nav a {
  color: var(--text);
  margin-left: 24px;
  text-decoration: none;
}

nav a.cta {
  padding: 8px 16px;
  border: 1px solid var(--cyan);
  border-radius: 4px;
}

nav a:hover {
  color: var(--white);
}

main {
  padding: 80px 40px;
  min-height: 80vh;
}

h1, h2 {
  color: var(--white);
}

button {
  background: transparent;
  border: 1px solid var(--cyan);
  color: var(--cyan);
  padding: 12px 24px;
  cursor: pointer;
}

button:hover {
  box-shadow: 0 0 12px rgba(0,245,255,0.6);
}

input, select, textarea {
  width: 100%;
  background: transparent;
  border: 1px solid rgba(255,255,255,0.2);
  color: var(--white);
  padding: 12px;
  margin-bottom: 16px;
}

section {
  max-width: 900px;
}

.success {
  color: var(--cyan);
  margin-top: 20px;
}
</style>
</head>

<body>

<header>
  <div class="logo">FuzeWeb</div>
  <nav>
    <a href="/" data-link>Home</a>
    <a href="/services" data-link>Services</a>
    <a href="/quote" class="cta" data-link>Request a Quote</a>
  </nav>
</header>

<main id="app"></main>

<script>
const app = document.getElementById("app");

function render(page) {
  if (page === "services") {
    app.innerHTML = `
      <section>
        <h1>Services</h1>
        <p>We design and build modern, scalable web systems.</p>
        <ul>
          <li>Web Development</li>
          <li>UI / UX Design</li>
          <li>CMS Integration</li>
          <li>Performance Optimization</li>
        </ul>
      </section>`;
  }

  else if (page === "quote") {
    app.innerHTML = `
      <section>
        <h1>Request a Quote</h1>
        <p>Tell us about your project.</p>

        <form id="quoteForm">
          <input name="name" placeholder="Your Name" required>
          <input name="email" type="email" placeholder="Your Email" required>

          <select name="project_type" required>
            <option value="">Project Type</option>
            <option>Website</option>
            <option>Web App</option>
            <option>Redesign</option>
            <option>CMS Integration</option>
          </select>

          <select name="budget" required>
            <option value="">Budget</option>
            <option>$500 – $1,000</option>
            <option>$1,000 – $3,000</option>
            <option>$3,000+</option>
          </select>

          <textarea name="message" placeholder="Project details"></textarea>

          <button type="submit">Send Request</button>
        </form>
        <div id="response" class="success"></div>
      </section>
    `;

    document.getElementById("quoteForm").addEventListener("submit", async e => {
      e.preventDefault();
      const formData = new FormData(e.target);
      formData.append("action", "quote");

      const res = await fetch("", { method: "POST", body: formData });
      const text = await res.text();

      document.getElementById("response").textContent =
        res.ok ? "Request received. We’ll contact you shortly." : text;
    });
  }

  else {
    app.innerHTML = `
      <section>
        <h1>We Build Intelligent Web Systems</h1>
        <p>FuzeWeb is a modern web development company focused on clean design and scalable engineering.</p>
        <button onclick="navigate('/quote')">Request a Quote</button>
      </section>`;
  }
}

function navigate(path) {
  history.pushState({}, "", path);
  render(path.replace("/", ""));
}

document.addEventListener("click", e => {
  if (e.target.matches("[data-link]")) {
    e.preventDefault();
    navigate(e.target.getAttribute("href"));
  }
});

window.addEventListener("popstate", () => {
  render(location.pathname.replace("/", ""));
});

render(location.pathname.replace("/", ""));
</script>

</body>
</html>
