<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['product_id'])) {
    header("Location: dashboard.php");
    exit;
}

$product_id = (int)$_POST['product_id'];

$stmt = $conn->prepare("SELECT id, name, description, price, image_url FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Product not found. <a href='dashboard.php'>Back to Dashboard</a>";
    exit;
}

$userName = $_SESSION['user_name'] ?? 'User';
$address  = $_SESSION['shipping_address'] ?? null;

// money + delivery window
$price   = (float)$product['price'];
$estFrom = date('D, d M', strtotime('+4 days'));
$estTo   = date('D, d M', strtotime('+6 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment - Kartify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary:#2563eb;
      --primary-deep:#1d4ed8;
      --accent:#f97316;
      --bg:#e5edff;
      --card:#ffffff;
      --border:#e5e7eb;
      --text:#0f172a;
      --text-soft:#6b7280;
      --success:#16a34a;
      --danger:#dc2626;
    }
    *{box-sizing:border-box;margin:0;padding:0;}

    html{scroll-behavior:smooth;}

    body{
      font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      background:
        linear-gradient(135deg, #e0f2fe 0%, #eef2ff 40%, #fefce8 100%),
        url('images/D.jpg') center center / cover no-repeat fixed;
      background-blend-mode:soft-light;
      min-height:100vh;
      display:flex;flex-direction:column;
      color:var(--text);
      opacity:0;
      animation:fadeIn .6s forwards;
      font-size:14px;
      line-height:1.5;
    }
    @keyframes fadeIn{
      from{opacity:0; transform:translateY(8px);}
      to{opacity:1; transform:translateY(0);}
    }

    /* ---------- Top bar ---------- */
    .top{
      position:sticky;top:0;z-index:10;
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
      background:linear-gradient(
        to right,
        rgba(255,255,255,0.92),
        rgba(248,250,252,0.94)
      );
      border-bottom:1px solid rgba(209,213,219,0.9);
      box-shadow:0 6px 18px rgba(15,23,42,0.10);
    }
    .top-inner{
      max-width:960px;margin:0 auto;
      padding:10px 16px;
      display:flex;align-items:center;justify-content:space-between;gap:10px;
      color:#111827;
    }
    .logo-wrap{
      display:flex;align-items:center;gap:8px;
    }
    .logo{
      font-weight:800;font-size:18px;letter-spacing:.12em;
      text-transform:uppercase;color:#0f172a;
    }
    .logo-dot{color:var(--accent);}
    .step-pill{
      font-size:11px;padding:4px 10px;border-radius:999px;
      background:rgba(239,246,255,0.95);
      border:1px solid rgba(191,219,254,0.9);
      display:inline-flex;align-items:center;gap:6px;
    }
    .secure{
      font-size:11px;color:var(--text-soft);
      display:flex;align-items:center;gap:4px;
      padding:4px 10px;
      border-radius:999px;
      background:rgba(249,250,251,0.96);
      border:1px solid rgba(229,231,235,0.9);
      backdrop-filter:blur(8px);
    }

    main{flex:1;}
    .shell{
      max-width:960px;margin:18px auto 26px;
      padding:0 16px;
      display:grid;
      grid-template-columns:minmax(0,1.1fr) minmax(0,0.9fr);
      gap:16px;
    }
    .card{
      background:rgba(255,255,255,0.86);
      border-radius:20px;
      border:1px solid rgba(229,231,235,0.95);
      padding:16px 16px 14px;
      box-shadow:
        0 22px 55px rgba(148,163,184,0.45),
        0 0 0 1px rgba(148,163,184,0.20);
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
      animation:cardFloat .5s ease-out;
      transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .card:hover{
      transform:translateY(-2px);
      box-shadow:
        0 26px 65px rgba(148,163,184,0.55),
        0 0 0 1px rgba(37,99,235,0.55);
      border-color:rgba(59,130,246,0.9);
    }
    @keyframes cardFloat{
      from{opacity:0; transform:translateY(10px);}
      to{opacity:1; transform:translateY(0);}
    }

    .title{font-size:16px;font-weight:600;margin-bottom:6px;}
    .sub{font-size:12px;color:var(--text-soft);margin-bottom:8px;}

    .razor-head{
      display:flex;justify-content:space-between;align-items:center;
      margin-bottom:6px;
    }
    .razor-logo{
      font-size:13px;font-weight:600;color:#111827;
    }
    .pill{
      font-size:11px;padding:3px 8px;border-radius:999px;
      background:#ecfdf5;border:1px solid #22c55e;color:#15803d;
    }

    form{display:grid;gap:8px;margin-top:8px;}
    label{font-size:12px;color:#374151;display:block;margin-bottom:2px;}

    input{
      width:100%;padding:7px 9px;
      border-radius:10px;border:1px solid #d1d5db;
      background:#f9fafb;font-size:13px;
      transition:border-color .15s, box-shadow .15s, background .15s;
    }
    input:focus{
      outline:none;border-color:rgba(37,99,235,.85);
      background:#fff;box-shadow:0 0 0 1px rgba(37,99,235,.14),0 0 0 4px rgba(191,219,254,.75);
    }

    .row{
      display:flex;gap:8px;
    }

    .card-type-row{
      display:flex;
      flex-direction:column;
      gap:4px;
      margin-bottom:4px;
    }
    .card-type-options{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
    }
    .card-type-pill{
      display:inline-flex;
      align-items:center;
      gap:4px;
      padding:4px 9px;
      border-radius:999px;
      border:1px solid rgba(209,213,219,0.95);
      background:#f9fafb;
      font-size:11px;
      cursor:pointer;
      user-select:none;
    }
    .card-type-pill input{
      width:auto;
      margin:0;
    }

    .card-number-inner{
      display:flex;
      align-items:center;
      gap:8px;
    }
    .brand-pill{
      font-size:11px;
      padding:4px 9px;
      border-radius:999px;
      border:1px solid rgba(229,231,235,0.95);
      background:rgba(249,250,251,0.9);
      color:var(--text-soft);
      white-space:nowrap;
      min-width:70px;
      text-align:center;
    }

    .info-box{
      font-size:11px;color:var(--text-soft);
      margin-top:6px;
      background:#eff6ff;
      border-radius:10px;
      padding:6px 8px;
      border:1px solid #bfdbfe;
    }

    .btn-pay{
      margin-top:8px;
      width:100%;border:none;border-radius:999px;
      padding:10px 12px;
      background:radial-gradient(circle at 0 0, #bfdbfe, #60a5fa 35%, #2563eb 100%);
      color:#f9fafb;font-size:14px;font-weight:600;
      cursor:pointer;
      box-shadow:
        0 14px 32px rgba(59,130,246,.70),
        0 0 0 1px rgba(37,99,235,.80);
      transition:transform .16s, box-shadow .16s, letter-spacing .16s, filter .16s;
    }
    .btn-pay:hover{
      transform:translateY(-1px);
      box-shadow:
        0 18px 40px rgba(59,130,246,.90),
        0 0 0 1px rgba(37,99,235,.90);
      filter:brightness(1.03);
      letter-spacing:0.01em;
    }
    .btn-pay:active{
      transform:translateY(0);
      box-shadow:
        0 9px 20px rgba(59,130,246,.70),
        0 0 0 1px rgba(37,99,235,.95);
      filter:brightness(0.98);
      letter-spacing:normal;
    }

    .btn-back{
      margin-top:6px;
      width:100%;border-radius:999px;
      padding:9px 12px;
      border:1px solid var(--border);
      background:rgba(255,255,255,0.98);font-size:13px;
      cursor:pointer;
      transition:background .15s, box-shadow .15s, transform .15s;
    }
    .btn-back a{text-decoration:none;color:var(--text-soft);display:block;width:100%;height:100%;}
    .btn-back:hover{
      background:#f9fafb;
      box-shadow:0 8px 18px rgba(148,163,184,.35);
      transform:translateY(-1px);
    }

    /* ---------- Order summary ---------- */
    .summary-title{font-size:14px;font-weight:600;margin-bottom:6px;}

    .sum-top{
      display:flex;
      gap:10px;
      margin-bottom:10px;
      align-items:center;
    }
    .sum-img{
      width:60px;height:60px;border-radius:10px;
      object-fit:cover;background:#e5e7eb;
    }
    .sum-main{
      display:flex;
      flex-direction:column;
      gap:3px;
    }
    .sum-name{
      font-size:13px;
      font-weight:600;
      color:#111827;
    }
    .sum-merchant{
      font-size:12px;
      color:var(--text-soft);
    }

    .sum-row{
      font-size:12px;
      color:var(--text-soft);
      margin-bottom:4px;
      display:flex;
      justify-content:space-between;
      gap:6px;
    }
    .sum-row span:first-child{
      white-space:nowrap;
    }
    .sum-row span:last-child{
      text-align:right;
    }
    .sum-price{
      font-size:15px;
      font-weight:700;
      color:var(--primary);
    }
    .sum-detail-text{
      margin-top:8px;
      font-size:11px;
      color:var(--text-soft);
    }

    /* OTP section */
    .otp-section{
      margin-top:4px;
      padding:7px 9px;
      border-radius:12px;
      background:rgba(239,246,255,0.95);
      border:1px solid #bfdbfe;
      display:none;
      flex-direction:column;
      gap:4px;
    }
    .otp-section.visible{
      display:flex;
      animation:fadeInOtp .3s ease-out;
    }
    @keyframes fadeInOtp{
      from{opacity:0; transform:translateY(4px);}
      to{opacity:1; transform:translateY(0);}
    }
    .otp-info{
      font-size:11px;color:#1d4ed8;
    }
    .otp-error{
      font-size:11px;color:var(--danger);
      display:none;
    }
    .otp-error.visible{display:block;}

    /* OTP overlay popup */
    .otp-overlay{
      position:fixed;
      inset:0;
      display:none;
      align-items:center;
      justify-content:center;
      background:rgba(15,23,42,0.55);
      z-index:35;
    }
    .otp-overlay-card{
      background:rgba(255,255,255,0.96);
      border-radius:18px;
      padding:14px 16px;
      min-width:260px;
      text-align:center;
      box-shadow:0 20px 45px rgba(15,23,42,0.65);
      backdrop-filter:blur(16px);
      animation:otpPopup .35s ease-out;
    }
    @keyframes otpPopup{
      from{opacity:0; transform:translateY(12px) scale(.96);}
      to{opacity:1; transform:translateY(0) scale(1);}
    }
    .otp-overlay-icon{
      width:38px;height:38px;
      border-radius:999px;
      margin:0 auto 6px;
      display:flex;align-items:center;justify-content:center;
      background:#eff6ff;
      border:1px solid #bfdbfe;
      font-size:20px;
    }
    .otp-overlay-title{
      font-size:14px;font-weight:600;margin-bottom:4px;
    }
    .otp-overlay-text{
      font-size:12px;color:var(--text-soft);
    }

    /* Verifying overlay */
    .verifying-overlay{
      position:fixed;
      inset:0;
      background:rgba(15,23,42,0.6);
      display:none;
      align-items:center;
      justify-content:center;
      z-index:38;
    }
    .verifying-card{
      background:rgba(255,255,255,0.96);
      border-radius:20px;
      padding:18px 20px;
      max-width:320px;
      text-align:center;
      box-shadow:0 24px 50px rgba(15,23,42,0.6);
      backdrop-filter:blur(16px);
      animation:otpPopup .35s ease-out;
    }
    .verifying-icon{
      width:44px;height:44px;
      border-radius:999px;
      margin:0 auto 8px;
      display:flex;align-items:center;justify-content:center;
      background:#eff6ff;
      border:1px solid #93c5fd;
      font-size:20px;
    }
    .spinner{
      width:20px;height:20px;
      border-radius:50%;
      border:3px solid #bfdbfe;
      border-top-color:#2563eb;
      animation:spin 0.8s linear infinite;
    }
    @keyframes spin{
      to{transform:rotate(360deg);}
    }
    .verifying-title{
      font-size:14px;font-weight:600;margin-bottom:4px;
    }
    .verifying-text{
      font-size:12px;color:var(--text-soft);
    }

    /* Success overlay */
    .success-overlay{
      position:fixed;
      inset:0;
      background:rgba(15,23,42,0.6);
      display:none;
      align-items:center;
      justify-content:center;
      z-index:40;
    }
    .success-card{
      background:rgba(255,255,255,0.96);
      border-radius:20px;
      padding:18px 20px;
      max-width:320px;
      text-align:center;
      box-shadow:0 24px 50px rgba(15,23,42,0.6);
      backdrop-filter:blur(16px);
    }
    .success-icon{
      width:44px;height:44px;
      border-radius:999px;
      margin:0 auto 8px;
      display:flex;align-items:center;justify-content:center;
      background:#ecfdf5;
      border:1px solid #22c55e;
      font-size:24px;
    }
    .success-title{
      font-size:16px;font-weight:600;margin-bottom:4px;
    }
    .success-text{
      font-size:12px;color:var(--text-soft);
    }

    @media(max-width:780px){
      .shell{grid-template-columns:minmax(0,1fr);}
    }
    @media(max-width:520px){
      .top-inner{flex-direction:column;align-items:flex-start;}
    }
  </style>
</head>
<body>

<header class="top">
  <div class="top-inner">
    <div class="logo-wrap">
      <div class="logo">KARTIFY<span class="logo-dot">.</span></div>
      <div class="step-pill">
        <span>💳</span>
        <span>Secure checkout · Step 3 of 3</span>
      </div>
    </div>
    <div class="secure">
      🔒 256-bit SSL secured · We never store your card details
    </div>
  </div>
</header>

<main>
  <div class="shell">
    <!-- Left: Payment form -->
    <section class="card">
      <div class="razor-head">
        <div class="razor-logo">Card payment</div>
        <div class="pill">Secure gateway</div>
      </div>
      <div class="sub">
        Enter your card details and verify with OTP to complete your payment.
      </div>

      <form id="paymentForm" method="post" action="success.php" autocomplete="off">
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

        <!-- Card type select (debit / credit) -->
        <div class="card-type-row">
          <label>Card type</label>
          <div class="card-type-options">
            <label class="card-type-pill">
              <input type="radio" name="card_type" value="debit" checked>
              Debit card
            </label>
            <label class="card-type-pill">
              <input type="radio" name="card_type" value="credit">
              Credit card
            </label>
          </div>
        </div>

        <!-- Card number (masked, brand detect on right) -->
        <div>
          <label>Card number</label>
          <div class="card-number-inner">
            <input
              type="password"
              id="cardNumber"
              name="cardNumber"
              autocomplete="off"
              placeholder="Enter card number"
              maxlength="19"
              inputmode="numeric"
            >
            <span id="cardBrand" class="brand-pill">••••</span>
          </div>
        </div>

        <div>
          <label>Name on card</label>
          <input
            type="text"
            id="cardName"
            name="cardName"
            autocomplete="off"
            placeholder="Name as on card"
            value="<?php echo htmlspecialchars($userName); ?>"
          >
        </div>

        <div class="row">
          <div style="flex:1;">
            <label>Expiry (MM/YY)</label>
            <input
              type="password"
              id="cardExpiry"
              name="cardExpiry"
              autocomplete="off"
              maxlength="5"
              placeholder="MM/YY"
              inputmode="numeric"
            >
          </div>
          <div style="flex:1;">
            <label>CVV</label>
            <input
              type="password"
              id="cardCVV"
              name="cardCVV"
              autocomplete="off"
              maxlength="3"
              placeholder="3-digit CVV"
              inputmode="numeric"
            >
          </div>
        </div>

        <!-- OTP section (hidden initially, show after Pay click) -->
        <div id="otpSection" class="otp-section">
          <div class="otp-info">
            An OTP has been sent to your registered mobile number ending with ••XX.
          </div>
          <div>
            <label>OTP (One Time Password)</label>
            <input
              type="password"
              id="cardOTP"
              name="cardOTP"
              autocomplete="one-time-code"
              maxlength="6"
              placeholder="Enter 6-digit OTP"
              inputmode="numeric"
            >
          </div>
          <div id="otpError" class="otp-error"></div>
        </div>

        <div class="info-box">
          • Your card details are encrypted and processed securely.<br>
          • For your safety, we may mask sensitive information on this screen.
        </div>

        <button type="submit" class="btn-pay" id="payButton">
          Pay securely ₹<?php echo number_format($price,2); ?>
        </button>

        <button type="button" class="btn-back">
          <a href="dashboard.php">Cancel &amp; go back</a>
        </button>
      </form>
    </section>

    <!-- Right: Order summary -->
    <section class="card">
      <div class="summary-title">Order summary</div>

      <div class="sum-top">
        <?php if (!empty($product['image_url'])): ?>
          <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="sum-img" alt="">
        <?php else: ?>
          <div class="sum-img" style="
               background-image:url('images/D.jpg');
               background-size:cover;background-position:center;">
          </div>
        <?php endif; ?>

        <div class="sum-main">
          <div class="sum-name">
            <?php echo htmlspecialchars($product['name']); ?>
          </div>
          <div class="sum-merchant">
            Paying to <strong>Kartify Retail Partner</strong>
          </div>
        </div>
      </div>

      <div class="sum-row">
        <span>Items</span>
        <span>1 item</span>
      </div>
      <div class="sum-row">
        <span>Paying to</span>
        <span>Kartify Retail Partner</span>
      </div>
      <div class="sum-row">
        <span>Delivery window</span>
        <span><?php echo $estFrom; ?> – <?php echo $estTo; ?></span>
      </div>
      <div class="sum-row">
        <span>Delivery to</span>
        <span>
          <?php
            if ($address) {
                echo htmlspecialchars($address['full_name']) . ', '
                   . htmlspecialchars($address['city']) . ' - '
                   . htmlspecialchars($address['pincode']);
            } else {
                echo htmlspecialchars($userName);
            }
          ?>
        </span>
      </div>
      <div class="sum-row">
        <span>Payment method</span>
        <span>Card</span>
      </div>

      <hr style="border:none;border-top:1px dashed #e5e7eb;margin:6px 0 6px;">

      <div class="sum-row">
        <span>Item price</span>
        <span>₹<?php echo number_format($price,2); ?></span>
      </div>
      <div class="sum-row">
        <span>Delivery</span>
        <span style="color:var(--success);">FREE</span>
      </div>
      <div class="sum-row">
        <span>Payable amount</span>
        <span class="sum-price">₹<?php echo number_format($price,2); ?></span>
      </div>

      <div class="sum-detail-text">
        You are paying <strong>Kartify Retail Partner</strong> for 1 item
        (<?php echo htmlspecialchars($product['name']); ?>).  
        Your order will be shipped to
        <?php
          if ($address) {
              echo htmlspecialchars($address['city']) . ' - ' . htmlspecialchars($address['pincode']);
          } else {
              echo 'your saved address';
          }
        ?>
        and is expected to be delivered between <strong><?php echo $estFrom; ?></strong> and
        <strong><?php echo $estTo; ?></strong>.
      </div>
    </section>
  </div>
</main>

<!-- OTP sent popup -->
<div id="otpOverlay" class="otp-overlay">
  <div class="otp-overlay-card">
    <div class="otp-overlay-icon">📲</div>
    <div class="otp-overlay-title">OTP sent</div>
    <div class="otp-overlay-text">
      We’ve sent a one-time password to your registered mobile number.  
      Please do not share this code with anyone.
    </div>
  </div>
</div>

<!-- Verifying OTP overlay -->
<div id="verifyingOverlay" class="verifying-overlay">
  <div class="verifying-card">
    <div class="verifying-icon">
      <div class="spinner"></div>
    </div>
    <div class="verifying-title">Verifying your OTP</div>
    <div class="verifying-text">
      Please don’t close or refresh this window while we confirm your payment.
    </div>
  </div>
</div>

<!-- Success overlay -->
<div id="successOverlay" class="success-overlay">
  <div class="success-card">
    <div class="success-icon">✅</div>
    <div class="success-title">Payment successful</div>
    <div class="success-text">
      Successfully purchased! Redirecting you to the order details page...
    </div>
  </div>
</div>

<script>
  const cardNumber      = document.getElementById('cardNumber');
  const cardName        = document.getElementById('cardName');
  const cardExpiry      = document.getElementById('cardExpiry');
  const cardCVV         = document.getElementById('cardCVV');
  const cardOTP         = document.getElementById('cardOTP');
  const otpSection      = document.getElementById('otpSection');
  const otpError        = document.getElementById('otpError');
  const cardBrand       = document.getElementById('cardBrand');
  const payForm         = document.getElementById('paymentForm');
  const successOverlay  = document.getElementById('successOverlay');
  const otpOverlay      = document.getElementById('otpOverlay');
  const verifyingOverlay= document.getElementById('verifyingOverlay');
  const payButton       = document.getElementById('payButton');

  // Detect brand (UI only)
  cardNumber.addEventListener('input', () => {
    let digits = cardNumber.value.replace(/\D/g,'').slice(0,16);

    let brand = 'Card';
    if (digits.startsWith('4') && digits.length >= 13) {
      brand = 'VISA';
    } else if (digits.startsWith('5') && digits.length >= 16) {
      brand = 'MasterCard';
    } else if (digits.length === 0) {
      brand = '••••';
    } else {
      brand = 'Card';
    }
    cardBrand.textContent = brand;
  });

  // Expiry auto-format (MM/YY) without weird 3rd-digit jump
  cardExpiry.addEventListener("input", function () {
    let raw = this.value.replace(/\D/g, "");

    if (raw.length > 4) raw = raw.slice(0, 4);

    if (raw.length === 3) {
      this.value = raw;
      return;
    }

    if (raw.length === 4) {
      this.value = raw.slice(0,2) + "/" + raw.slice(2);
      return;
    }

    this.value = raw;
  });

  // Luhn check
  function luhnCheck(num) {
    let arr = (num + '')
      .split('')
      .reverse()
      .map(x => parseInt(x, 10));
    let sum = 0;
    for (let i = 0; i < arr.length; i++) {
      let val = arr[i];
      if (i % 2 === 1) {
        val *= 2;
        if (val > 9) val -= 9;
      }
      sum += val;
    }
    return sum % 10 === 0;
  }

  payForm.addEventListener('submit', (e) => {
    e.preventDefault();
    otpError.classList.remove('visible');
    otpError.textContent = '';

    const num = cardNumber.value.replace(/\D/g,'');
    const name = cardName.value.trim();
    const exp  = cardExpiry.value.trim();
    const cvv  = cardCVV.value.trim();
    const otp  = cardOTP.value.trim();

    // Basic validations
    if (!num || num.length < 13) {
      alert('Please enter a valid card number.');
      return;
    }
    if (!luhnCheck(num)) {
      alert('Card number does not look valid. Please check and try again.');
      return;
    }
    if (!name) {
      alert('Please enter name on card.');
      return;
    }
    if (!/^\d{2}\/\d{2}$/.test(exp)) {
      alert('Please enter expiry in MM/YY format.');
      return;
    }
    if (!/^\d{3}$/.test(cvv)) {
      alert('Please enter a valid 3-digit CVV.');
      return;
    }

    // First click → show OTP section + overlay
    if (!otpSection.classList.contains('visible')) {
      otpSection.classList.add('visible');
      payButton.textContent = 'Verify OTP & Pay';

      otpOverlay.style.display = 'flex';
      setTimeout(() => {
        otpOverlay.style.display = 'none';
        cardOTP.focus();
      }, 1400);

      return;
    }

    // Validate OTP (any 6-digit)
    if (!/^\d{6}$/.test(otp)) {
      otpError.textContent = 'Please enter a 6-digit OTP.';
      otpError.classList.add('visible');
      cardOTP.focus();
      return;
    }

    // OTP OK → show "Verifying your OTP" first
    verifyingOverlay.style.display = 'flex';

    // After ~2s, hide verifying + show success
    setTimeout(() => {
      verifyingOverlay.style.display = 'none';
      successOverlay.style.display = 'flex';

      // After another ~4s, submit the form (total ~6s)
      setTimeout(() => {
        payForm.submit();
      }, 4000);
    }, 2000);
  });
</script>

</body>
</html>
