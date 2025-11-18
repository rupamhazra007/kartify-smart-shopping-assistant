<?php
session_start();
require 'db.php';

$error = "";
$name_value  = "";
$email_value = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $name_value  = $name;
    $email_value = $email;

    if ($name === '' || $email === '' || $pass === '') {
        $error = "All fields are required.";
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hash);
        if ($stmt->execute()) {
            // ✅ Real-site style: show success on login page
            $_SESSION['flash_success'] = "Your account has been created successfully. Please sign in.";
            header("Location: login.php");
            exit;
        } else {
            $error = "Registration failed. This email may already be in use.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kartify - Create your account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #2563eb;
      --primary-deep: #1d4ed8;
      --primary-soft: #eff6ff;
      --accent: #f97316;
      --bg: #e5edff;
      --card-bg: rgba(255, 255, 255, 0.86);
      --border-soft: #d1d5db;
      --text-main: #020617;
      --text-soft: #6b7280;
      --danger: #dc2626;
      --warning: #ea580c;
      --success: #16a34a;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      font-size: 15px;
      line-height: 1.6;
      /* Same style as login: clean + background image visible */
      background:
        linear-gradient(135deg, #e0f2fe 0%, #eef2ff 40%, #fefce8 100%),
        url('images/B.jpg') center center / cover no-repeat fixed;
      background-blend-mode: soft-light;
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      opacity: 0;
      animation: fadeInBody 0.7s ease-out forwards;
    }

    a {
      color: var(--primary);
      text-decoration: none;
      transition: color 0.18s ease;
    }

    a:hover {
      text-decoration: underline;
      color: var(--primary-deep);
    }

    /* ---------- Top Bar (match login light mode) ---------- */

    .top-bar {
      position: sticky;
      top: 0;
      z-index: 10;
      backdrop-filter: blur(18px);
      background: linear-gradient(
        to right,
        rgba(255, 255, 255, 0.92),
        rgba(248, 250, 252, 0.94)
      );
      border-bottom: 1px solid rgba(209, 213, 219, 0.9);
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .top-bar-inner {
      max-width: 1040px;
      margin: 0 auto;
      padding: 10px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      color: #111827;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-icon {
      width: 34px;
      height: 34px;
      border-radius: 12px;
      background: conic-gradient(from 160deg, #22c55e, #22d3ee, #3b82f6, #eab308, #f97316, #22c55e);
      display: flex;
      align-items: center;
      justify-content: center;
      animation: spin-slow 20s linear infinite;
      box-shadow:
        0 0 18px rgba(59, 130, 246, 0.35),
        0 0 0 1px rgba(226, 232, 240, 0.95);
    }

    .logo-icon-inner {
      width: 22px;
      height: 22px;
      border-radius: 9px;
      background: radial-gradient(circle at 0 0, #bfdbfe, #1d4ed8 55%, #020617 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      color: #f9fafb;
    }

    .logo-text {
      font-weight: 800;
      letter-spacing: 0.12em;
      font-size: 20px;
      color: #0f172a;
      text-transform: uppercase;
    }

    .logo-dot {
      color: var(--accent);
    }

    .top-right {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 12px;
      color: #4b5563;
    }

    .top-chip {
      padding: 4px 10px;
      border-radius: 999px;
      border: 1px solid rgba(209, 213, 219, 0.95);
      background: rgba(248, 250, 252, 0.9);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.9);
    }

    .top-chip span:first-child {
      font-size: 14px;
    }

    .top-link {
      font-size: 13px;
      color: #111827;
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 7px 11px;
      border-radius: 999px;
      background: rgba(239, 246, 255, 0.9);
      border: 1px solid rgba(191, 219, 254, 0.9);
      box-shadow: 0 10px 24px rgba(148, 163, 184, 0.25);
      transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .top-link span.icon {
      font-size: 15px;
    }

    .top-link:hover {
      text-decoration: none;
      background: rgba(37, 99, 235, 0.98);
      border-color: transparent;
      color: #f9fafb;
      transform: translateY(-0.5px);
      box-shadow: 0 14px 32px rgba(15, 23, 42, 0.32);
    }

    /* ---------- Layout ---------- */

    .page-shell {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px 40px;
      animation: subtleRise 0.7s ease-out 0.08s both;
    }

    .card-shell {
      max-width: 1040px;
      width: 100%;
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
      gap: 26px;
      align-items: stretch;
      border-radius: 26px;
      padding: 18px;
      background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.96));
      border: 1px solid rgba(209, 213, 219, 0.9);
      box-shadow:
        0 22px 70px rgba(15, 23, 42, 0.18),
        0 0 0 1px rgba(148, 163, 184, 0.25);
      backdrop-filter: blur(22px);
    }

    /* ---------- Left info panel (professional copy) ---------- */

    .info-panel {
      border-radius: 20px;
      padding: 24px 24px 22px;
      background:
        radial-gradient(circle at top left, rgba(219, 234, 254, 0.9), rgba(239, 246, 255, 0.9)),
        rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(191, 219, 254, 0.9);
      box-shadow:
        0 18px 42px rgba(148, 163, 184, 0.3),
        0 0 0 1px rgba(209, 213, 219, 0.8);
      position: relative;
      overflow: hidden;
      opacity: 0;
      transform: translateY(18px) translateZ(0);
      animation: slideUp 0.65s ease-out 0.05s forwards;
      backdrop-filter: blur(14px);
      color: #0f172a;
      transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
    }

    .info-panel:hover {
      transform: translateY(-2px);
      box-shadow:
        0 24px 54px rgba(148, 163, 184, 0.45),
        0 0 0 1px rgba(59, 130, 246, 0.5);
      border-color: rgba(129, 140, 248, 0.95);
    }

    .info-gradient-orb {
      position: absolute;
      inset: -40%;
      background:
        radial-gradient(circle at 0 0, rgba(129, 140, 248, 0.3), transparent 55%),
        radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.32), transparent 55%);
      opacity: 0.35;
      pointer-events: none;
    }

    .info-content {
      position: relative;
      z-index: 1;
    }

    .info-badge-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 12px;
    }

    .info-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 11px;
      border-radius: 999px;
      background: #ecfdf3;
      border: 1px solid #22c55e;
      color: #166534;
      font-size: 12px;
      box-shadow: 0 0 0 1px rgba(134, 239, 172, 0.7);
    }

    .info-badge span.icon {
      font-size: 14px;
    }

    .info-badge-soft {
      font-size: 12px;
      color: #4b5563;
      opacity: 0.9;
      text-align: right;
      white-space: nowrap;
    }

    .info-title {
      font-size: 26px;
      line-height: 1.3;
      font-weight: 700;
      margin-bottom: 10px;
      color: #0f172a;
    }

    .info-title span {
      background: linear-gradient(120deg, #2563eb, #22c55e);
      -webkit-background-clip: text;
      color: transparent;
    }

    .info-text {
      font-size: 14px;
      color: #4b5563;
      margin-bottom: 18px;
      max-width: 400px;
    }

    .info-list {
      list-style: none;
      font-size: 13px;
      color: #374151;
      display: grid;
      gap: 7px;
      margin-bottom: 14px;
    }

    .info-list li {
      display: flex;
      align-items: center;
      gap: 7px;
    }

    .info-dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: #22c55e;
      box-shadow: 0 0 10px rgba(34, 197, 94, 0.9);
      flex-shrink: 0;
    }

    .info-footer-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 12px;
      color: #6b7280;
      gap: 10px;
      margin-top: 4px;
    }

    .info-footer-cta {
      font-size: 12px;
      padding: 4px 9px;
      border-radius: 999px;
      background: rgba(248, 250, 252, 0.98);
      border: 1px dashed #cbd5f5;
      color: #1f2937;
    }

    /* ---------- Register form glass card ---------- */

    .form-card {
      position: relative;
      border-radius: 20px;
      padding: 26px 24px 24px;
      background: var(--card-bg); /* glassy white */
      backdrop-filter: blur(20px);
      border: 1px solid rgba(226, 232, 240, 0.95);
      box-shadow:
        0 22px 55px rgba(148, 163, 184, 0.45),
        0 0 0 1px rgba(148, 163, 184, 0.2);
      opacity: 0;
      transform: translateY(18px) translateZ(0);
      animation: slideUp 0.65s ease-out 0.12s forwards;
      overflow: hidden;
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .form-card:hover {
      transform: translateY(-2px);
      box-shadow:
        0 26px 65px rgba(148, 163, 184, 0.5),
        0 0 0 1px rgba(59, 130, 246, 0.55);
      border-color: rgba(59, 130, 246, 0.85);
    }

    .form-card::before {
      content: "";
      position: absolute;
      left: 18px;
      right: 18px;
      top: 0;
      height: 4px;
      border-radius: 999px;
      background: linear-gradient(90deg, #60a5fa, #22c55e, #f97316);
      opacity: 0.95;
    }

    .form-header {
      margin-bottom: 18px;
    }

    .form-title-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 8px;
    }

    .form-title {
      font-size: 22px;
      font-weight: 650;
      color: #0f172a;
    }

    .form-chip {
      font-size: 11px;
      padding: 4px 9px;
      border-radius: 999px;
      background: var(--primary-soft);
      color: #1d4ed8;
      border: 1px solid rgba(191, 219, 254, 0.9);
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .form-sub {
      font-size: 13px;
      color: var(--text-soft);
    }

    .error-banner {
      margin-bottom: 10px;
      padding: 9px 11px;
      border-radius: 10px;
      font-size: 13px;
      display: flex;
      align-items: flex-start;
      gap: 7px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
    }

    .error-icon {
      font-size: 15px;
      margin-top: 1px;
    }

    form {
      display: grid;
      gap: 12px;
      margin-top: 4px;
    }

    .input-wrapper {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    label {
      font-size: 13px;
      color: #374151;
      font-weight: 500;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px 11px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      font-size: 14px;
      color: #111827;
      background: rgba(249, 250, 251, 0.95);
      transition:
        border-color 0.18s ease,
        box-shadow 0.18s ease,
        background 0.18s ease,
        transform 0.12s ease;
    }

    input::placeholder {
      color: #9ca3af;
    }

    input:focus {
      outline: none;
      border-color: rgba(37, 99, 235, 0.92);
      background: #ffffff;
      box-shadow:
        0 0 0 1px rgba(37, 99, 235, 0.18),
        0 0 0 4px rgba(191, 219, 254, 0.85);
      transform: translateY(-0.5px);
    }

    .input-with-icon {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-with-icon input[type="password"],
    .input-with-icon input[type="text"] {
      padding-right: 38px;
    }

    .toggle-password {
      position: absolute;
      right: 9px;
      top: 50%;
      transform: translateY(-50%);
      border: none;
      background: none;
      cursor: pointer;
      font-size: 15px;
      color: #6b7280;
      padding: 2px;
      transition: transform 0.12s ease, color 0.12s ease;
    }

    .toggle-password:hover {
      color: #111827;
      transform: translateY(-50%) scale(1.06);
    }

    .hint-text {
      font-size: 12px;
      color: var(--text-soft);
    }

    /* ---------- Password strength ---------- */

    .strength-meter {
      margin-top: 2px;
      width: 100%;
      height: 6px;
      border-radius: 999px;
      background: #e5e7eb;
      overflow: hidden;
    }

    .strength-bar {
      height: 100%;
      width: 0;
      border-radius: inherit;
      transition: width 0.25s ease-out, background-color 0.25s ease-out;
    }

    .strength-text {
      margin-top: 4px;
      font-size: 12px;
      color: var(--text-soft);
      min-height: 16px;
    }

    .strength-weak {
      background-color: var(--danger);
    }

    .strength-medium {
      background-color: var(--warning);
    }

    .strength-strong {
      background-color: var(--success);
    }

    .btn-submit {
      margin-top: 10px;
      width: 100%;
      border-radius: 999px;
      padding: 11px 12px;
      border: none;
      cursor: pointer;
      font-size: 15px;
      font-weight: 600;
      background: radial-gradient(circle at 0 0, #bfdbfe, #60a5fa 35%, #2563eb 100%);
      color: #f9fafb;
      box-shadow:
        0 14px 32px rgba(59, 130, 246, 0.7),
        0 0 0 1px rgba(37, 99, 235, 0.75);
      transition:
        transform 0.16s ease,
        box-shadow 0.16s ease,
        filter 0.16s ease,
        letter-spacing 0.16s ease;
    }

    .btn-submit:hover {
      transform: translateY(-1px);
      box-shadow:
        0 18px 40px rgba(59, 130, 246, 0.9),
        0 0 0 1px rgba(37, 99, 235, 0.9);
      filter: brightness(1.04);
      letter-spacing: 0.01em;
    }

    .btn-submit:active {
      transform: translateY(0);
      box-shadow:
        0 9px 20px rgba(59, 130, 246, 0.7),
        0 0 0 1px rgba(37, 99, 235, 0.9);
      filter: brightness(0.98);
      letter-spacing: normal;
    }

    .form-footer {
      margin-top: 12px;
      font-size: 13px;
      color: var(--text-soft);
      text-align: center;
    }

    .form-footer a {
      font-weight: 500;
    }

    /* ---------- Animations ---------- */

    @keyframes fadeInBody {
      from {
        opacity: 0;
        transform: translateY(6px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes subtleRise {
      from {
        opacity: 0;
        transform: translateY(10px) scale(0.99);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(18px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes spin-slow {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }

    /* ---------- Responsive ---------- */

    @media (max-width: 960px) {
      .card-shell {
        padding: 16px;
      }
    }

    @media (max-width: 840px) {
      .card-shell {
        grid-template-columns: minmax(0, 1fr);
      }

      .info-panel {
        order: 2;
      }

      .form-card {
        order: 1;
      }
    }

    @media (max-width: 520px) {
      .top-bar-inner {
        padding-inline: 12px;
      }

      .page-shell {
        padding-inline: 10px;
      }

      .card-shell {
        padding: 12px;
        border-radius: 20px;
      }

      .info-panel,
      .form-card {
        padding-inline: 16px;
      }

      .info-title {
        font-size: 22px;
      }

      .form-title {
        font-size: 20px;
      }

      .info-badge-soft {
        display: none;
      }
    }
  </style>
</head>
<body>

<header class="top-bar">
  <div class="top-bar-inner">
    <div class="logo">
      <div class="logo-icon">
        <div class="logo-icon-inner">K</div>
      </div>
      <div class="logo-text">KARTIFY<span class="logo-dot">.</span></div>
    </div>
    <div class="top-right">
      <div class="top-chip">
        <span>🔒</span>
        <span>Secure signup • Encrypted</span>
      </div>
      <a href="index.php" class="top-link">
        <span class="icon">🏬</span>
        <span>Back to store</span>
      </a>
    </div>
  </div>
</header>

<main class="page-shell">
  <div class="card-shell">

    <!-- Left info / promo panel -->
    <aside class="info-panel">
      <div class="info-gradient-orb"></div>
      <div class="info-content">
        <div class="info-badge-row">
          <div class="info-badge">
            <span class="icon">✨</span>
            <span>Join Kartify</span>
          </div>
          <div class="info-badge-soft">
            Create your free customer account
          </div>
        </div>

        <h1 class="info-title">
          One <span>secure account</span><br>for all your shopping.
        </h1>
        <p class="info-text">
          Sign up in a few seconds, save your details once, and enjoy a faster,
          more personalised shopping experience every time you use Kartify.
        </p>

        <ul class="info-list">
          <li>
            <span class="info-dot"></span>
            <span>Save delivery addresses and payment preferences for quick checkout.</span>
          </li>
          <li>
            <span class="info-dot"></span>
            <span>Track every order, from confirmation to delivery, in one dashboard.</span>
          </li>
          <li>
            <span class="info-dot"></span>
            <span>Get personalised recommendations based on what you love.</span>
          </li>
        </ul>

        <div class="info-footer-row">
          <div>Your information is encrypted and handled securely.</div>
          <div class="info-footer-cta">Kartify • Designed for modern shoppers 🛍️</div>
        </div>
      </div>
    </aside>

    <!-- Register form card -->
    <section class="form-card">
      <div class="form-header">
        <div class="form-title-row">
          <div class="form-title">Create your Kartify account</div>
          <div class="form-chip">
            <span>🆓</span>
            <span>Free sign up</span>
          </div>
        </div>
        <div class="form-sub">
          Enter your details below to get started. You’ll use this email and password to sign in.
        </div>
      </div>

      <?php if ($error): ?>
        <div class="error-banner">
          <span class="error-icon">⚠️</span>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="on">
        <!-- Name -->
        <div class="input-wrapper">
          <label for="name">Full name</label>
          <input
            type="text"
            id="name"
            name="name"
            placeholder="e.g. Full Name"
            required
            value="<?php echo htmlspecialchars($name_value); ?>"
          >
        </div>

        <!-- Email -->
        <div class="input-wrapper">
          <label for="email">Email address</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="you@example.com"
            required
            value="<?php echo htmlspecialchars($email_value); ?>"
          >
          <div class="hint-text">This email will be your Kartify login ID.</div>
        </div>

        <!-- Password -->
        <div class="input-wrapper">
          <label for="password">Password</label>
          <div class="input-with-icon">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Create a strong password"
              required
            >
            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
              👁️
            </button>
          </div>
          <div class="strength-meter">
            <div id="strength-bar" class="strength-bar"></div>
          </div>
          <div id="strength-text" class="strength-text"></div>
          <div class="hint-text">
            Use at least 8 characters with a mix of letters, numbers &amp; symbols.
          </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit">
          Create account
        </button>

        <div class="form-footer">
          Already have an account?
          <a href="login.php">Sign in</a>
        </div>
      </form>
    </section>
  </div>
</main>

<script>
  // Toggle password visibility
  const passwordInput = document.getElementById('password');
  const toggleBtn = document.querySelector('.toggle-password');

  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', () => {
      const isPassword = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
      toggleBtn.textContent = isPassword ? '🙈' : '👁️';
    });
  }

  // Password strength check
  const strengthBar = document.getElementById('strength-bar');
  const strengthText = document.getElementById('strength-text');

  function evaluateStrength(password) {
    let score = 0;

    if (password.length >= 8) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    return score;
  }

  function updateStrengthMeter() {
    const val = passwordInput.value || '';
    const score = evaluateStrength(val);

    strengthBar.className = 'strength-bar';
    let width = 0;
    let text = '';
    let cls = '';

    if (!val) {
      width = 0;
      text = '';
    } else if (score <= 1) {
      width = 25;
      text = 'Weak – use more characters and mix letters, numbers & symbols.';
      cls = 'strength-weak';
    } else if (score === 2 || score === 3) {
      width = 60;
      text = 'Medium – looks okay, but you can still make it stronger.';
      cls = 'strength-medium';
    } else if (score >= 4) {
      width = 100;
      text = 'Strong – great! This password looks secure.';
      cls = 'strength-strong';
    }

    strengthBar.style.width = width + '%';
    if (cls) strengthBar.classList.add(cls);
    strengthText.textContent = text;
  }

  if (passwordInput) {
    passwordInput.addEventListener('input', updateStrengthMeter);
  }
</script>
</body>
</html>
