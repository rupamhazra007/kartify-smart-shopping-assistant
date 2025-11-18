<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$userName = $_SESSION['user_name'] ?? 'User';

// product_id POST theke / GET theke nichi
$product_id = 0;
if (isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
} elseif (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
}

if ($product_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// ❌ আর কোন auto-fill session theke nei না
// $existingAddress use korchi na

// form submit hole
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $full_name   = trim($_POST['full_name'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $state       = trim($_POST['state'] ?? '');
    $pincode     = trim($_POST['pincode'] ?? '');

    // simple validation
    if ($full_name !== '' && $phone !== '' && $address !== '' && $city !== '' && $state !== '' && $pincode !== '') {
        $_SESSION['shipping_address'] = [
            'full_name' => $full_name,
            'phone'     => $phone,
            'address'   => $address,
            'city'      => $city,
            'state'     => $state,
            'pincode'   => $pincode,
        ];

        // address set hoye geche, ekhon purchase.php te jao
        header("Location: purchase.php?product_id=" . $product_id);
        exit;
    } else {
        $error = "Please fill all address fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Set Delivery Location - Kartify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root{
      --primary:#2563eb;
      --primary-soft:#dbeafe;
      --accent:#f97316;
      --bg:#eef2ff;
      --card:#ffffff;
      --border:#e4e4e7;
      --text:#0f172a;
      --text-soft:#6b7280;
      --danger:#dc2626;
    }
    *{box-sizing:border-box;margin:0;padding:0;}

    body{
      font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      /* 🔹 FULL CLEAR BACKGROUND IMAGE */
      background-image:url('images/D.jpg');
      background-size:cover;
      background-position:center;
      background-attachment:fixed;
      min-height:100vh;
      display:flex;
      flex-direction:column;
      color:var(--text);
      opacity:0;
      animation:fadeIn .6s forwards;
    }

    @keyframes fadeIn{
      from{opacity:0;transform:translateY(8px) scale(.99);}
      to{opacity:1;transform:translateY(0) scale(1);}
    }

    header{
      position:sticky;top:0;z-index:10;
      background:rgba(255,255,255,0.96);
      backdrop-filter:blur(14px);
      -webkit-backdrop-filter:blur(14px);
      border-bottom:1px solid rgba(226,232,240,0.9);
      box-shadow:0 14px 32px rgba(148,163,184,.4);
    }

    .top-inner{
      max-width:960px;margin:0 auto;
      padding:10px 18px;
      display:flex;align-items:center;justify-content:space-between;gap:10px;
    }

    .logo-wrap{
      display:flex;align-items:center;gap:10px;
    }

    .logo-orb{
      width:32px;height:32px;
      border-radius:999px;
      background:conic-gradient(from 160deg,#22c55e,#22d3ee,#3b82f6,#eab308,#f97316,#22c55e);
      display:flex;align-items:center;justify-content:center;
      box-shadow:0 0 18px rgba(59,130,246,.6);
      animation:spin-slow 22s linear infinite;
    }
    .logo-orb-inner{
      width:22px;height:22px;
      border-radius:999px;
      background:radial-gradient(circle at 0 0,#dbeafe,#1d4ed8 55%,#0f172a 100%);
      display:flex;align-items:center;justify-content:center;
      font-size:12px;font-weight:700;color:#f9fafb;
    }

    .logo{
      font-weight:700;
      font-size:18px;
      letter-spacing:.16em;
      text-transform:uppercase;
      color:#111827;
      display:flex;align-items:center;gap:6px;
    }
    .logo-dot{color:var(--accent);}

    .step-pill{
      font-size:11px;
      padding:4px 11px;
      border-radius:999px;
      background:rgba(248,250,252,0.98);
      border:1px solid rgba(209,213,219,0.9);
      color:#4b5563;
      display:flex;align-items:center;gap:6px;
    }
    .step-pill span{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      width:18px;height:18px;
      border-radius:999px;
      background:rgba(219,234,254,0.9);
      border:1px solid rgba(129,140,248,0.9);
      font-size:10px;
      color:#1d4ed8;
    }

    main{flex:1;display:flex;}

    .shell{
      max-width:980px;
      margin:26px auto 34px;
      padding:0 18px;
      width:100%;
      display:grid;
      grid-template-columns:minmax(0,1.15fr) minmax(0,0.9fr);
      gap:18px;
      align-items:stretch;
    }

    .card{
      position:relative;
      border-radius:22px;
      padding:1px;
      background:
        linear-gradient(135deg,rgba(59,130,246,0.45),rgba(191,219,254,0.3),rgba(249,115,22,0.45));
      box-shadow:0 24px 64px rgba(15,23,42,0.45);
      overflow:hidden;
    }

    .card-inner{
      height:100%;
      border-radius:21px;
      background:rgba(249,250,251,0.92);
      padding:18px 18px 16px;
      color:var(--text);
      display:flex;
      flex-direction:column;
      gap:10px;
      backdrop-filter:blur(4px);
      -webkit-backdrop-filter:blur(4px);
    }

    .card-right{
      position:relative;
      border-radius:22px;
      padding:1px;
      background:
        linear-gradient(145deg,rgba(148,163,184,0.6),rgba(219,234,254,0.5),rgba(255,255,255,0.9));
      box-shadow:0 22px 60px rgba(15,23,42,0.45);
      overflow:hidden;
    }
    .card-right-inner{
      height:100%;
      border-radius:21px;
      background:rgba(255,255,255,0.9);
      padding:18px 18px 16px;
      color:var(--text);
      display:flex;
      flex-direction:column;
      gap:10px;
      backdrop-filter:blur(4px);
      -webkit-backdrop-filter:blur(4px);
    }

    .title{
      font-size:18px;
      font-weight:600;
      letter-spacing:.02em;
      display:flex;
      align-items:center;
      gap:8px;
      color:#111827;
    }
    .title-badge{
      font-size:10px;
      padding:3px 8px;
      border-radius:999px;
      border:1px solid rgba(59,130,246,0.5);
      background:#eff6ff;
      color:#1d4ed8;
      text-transform:uppercase;
    }

    .sub{
      font-size:12px;
      color:var(--text-soft);
      margin-bottom:8px;
    }

    .mini-progress{
      display:flex;
      align-items:center;
      gap:6px;
      font-size:11px;
      color:#4b5563;
      margin-bottom:10px;
    }
    .mini-bar{
      flex:1;
      height:4px;
      border-radius:999px;
      background:#e5e7eb;
      overflow:hidden;
    }
    .mini-bar span{
      display:block;
      width:52%;
      height:100%;
      border-radius:999px;
      background:linear-gradient(90deg,#22c55e,#65a30d);
      box-shadow:0 0 12px rgba(22,163,74,0.6);
    }

    .row{display:flex;gap:10px;}
    .field{margin-bottom:8px;width:100%;}

    .field label{
      display:flex;
      align-items:center;
      justify-content:space-between;
      font-size:11px;
      font-weight:500;
      margin-bottom:3px;
      color:#111827;
    }
    .field label span.hint{
      font-size:10px;
      font-weight:400;
      color:rgba(107,114,128,0.9);
    }

    .field input,
    .field textarea{
      width:100%;
      border-radius:12px;
      border:1px solid var(--border);
      padding:8px 10px;
      font-size:13px;
      outline:none;
      background:#f9fafb;
      color:var(--text);
      transition:border-color .18s, box-shadow .18s, background .18s, transform .12s;
    }
    .field textarea{
      resize:vertical;
      min-height:70px;
      max-height:130px;
    }
    .field input::placeholder,
    .field textarea::placeholder{
      color:#9ca3af;
    }
    .field input:focus,
    .field textarea:focus{
      border-color:rgba(37,99,235,0.9);
      background:#ffffff;
      box-shadow:0 0 0 1px rgba(37,99,235,0.26),0 0 0 5px rgba(191,219,254,0.9);
      transform:translateY(-1px);
    }

    .error{
      font-size:12px;
      color:#991b1b;
      margin-bottom:8px;
      padding:6px 8px;
      border-radius:10px;
      border:1px solid #fecaca;
      background:#fee2e2;
    }

    .btn-primary{
      margin-top:10px;
      width:100%;
      border:none;
      border-radius:999px;
      padding:10px 12px;
      background:linear-gradient(135deg,#4f46e5,#2563eb,#0ea5e9);
      color:#f9fafb;
      font-size:14px;
      font-weight:500;
      cursor:pointer;
      box-shadow:0 18px 38px rgba(37,99,235,.45);
      transition:transform .16s, box-shadow .16s, filter .12s;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:6px;
    }
    .btn-primary span.icon{
      font-size:16px;
    }
    .btn-primary:hover{
      transform:translateY(-1px);
      box-shadow:0 24px 50px rgba(37,99,235,.6);
      filter:brightness(1.04);
    }
    .btn-primary:active{
      transform:translateY(0);
      box-shadow:0 14px 30px rgba(37,99,235,.45);
    }

    .btn-secondary{
      margin-top:8px;
      width:100%;
      border-radius:999px;
      padding:8px 12px;
      border:1px solid rgba(209,213,219,0.9);
      background:#ffffff;
      font-size:13px;
      cursor:pointer;
      transition:background .15s, box-shadow .15s, transform .15s, border-color .15s;
    }
    .btn-secondary a{
      display:block;
      text-decoration:none;
      color:#4b5563;
      text-align:center;
    }
    .btn-secondary:hover{
      background:#f9fafb;
      border-color:#cbd5f5;
      box-shadow:0 12px 30px rgba(148,163,184,.6);
      transform:translateY(-1px);
    }

    .right-heading{
      font-size:14px;
      font-weight:600;
      margin-bottom:6px;
      display:flex;
      align-items:center;
      gap:6px;
      color:#111827;
    }
    .right-heading-pill{
      font-size:10px;
      padding:2px 6px;
      border-radius:999px;
      border:1px solid rgba(148,163,184,0.9);
      color:#4b5563;
      background:#f9fafb;
    }

    .summary-line{
      font-size:12px;
      color:#111827;
      margin-bottom:4px;
      display:flex;
      justify-content:space-between;
    }
    .summary-line span.label{
      color:#6b7280;
    }

    .hint-line{
      font-size:11px;
      color:#6b7280;
      margin-top:8px;
    }

    .blur-pill{
      margin-top:auto;
      padding:7px 9px;
      border-radius:999px;
      font-size:11px;
      background:radial-gradient(circle at 0 0,rgba(219,234,254,0.95),#ffffff);
      border:1px solid rgba(191,219,254,0.9);
      display:inline-flex;
      align-items:center;
      gap:6px;
      color:#1f2937;
    }
    .blur-pill span.dot{
      width:6px;height:6px;
      border-radius:999px;
      background:#22c55e;
      box-shadow:0 0 10px rgba(34,197,94,0.8);
    }

    @keyframes spin-slow{
      from{transform:rotate(0deg);}
      to{transform:rotate(360deg);}
    }

    @media(max-width:900px){
      .shell{
        grid-template-columns:minmax(0,1fr);
      }
      .card-right{
        margin-top:4px;
      }
    }

    @media(max-width:520px){
      .top-inner{
        flex-direction:column;
        align-items:flex-start;
        gap:6px;
      }
      .step-pill{
        align-self:flex-end;
      }
    }
  </style>
</head>
<body>
<header>
  <div class="top-inner">
    <div class="logo-wrap">
      <div class="logo-orb">
        <div class="logo-orb-inner">K</div>
      </div>
      <div class="logo">KARTIFY<span class="logo-dot">.</span></div>
    </div>
    <div class="step-pill">
      <span>1/2</span>
      Location · <?php echo htmlspecialchars($userName); ?>
    </div>
  </div>
</header>

<main>
  <div class="shell">
    <!-- LEFT: Address form -->
    <section class="card">
      <div class="card-inner">
        <div>
          <div class="title">
            Delivery address
            <span class="title-badge">Secure</span>
          </div>
          <div class="sub">
            We’ll use this address for your current order. You can always change it before payment.
          </div>

          <div class="mini-progress">
            <span>Step 1 · Location</span>
            <div class="mini-bar"><span></span></div>
            <span style="opacity:.9;">Next: Review &amp; payment</span>
          </div>
        </div>

        <?php if (!empty($error)): ?>
          <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="location.php" autocomplete="off">
          <input type="hidden" name="product_id" value="<?php echo (int)$product_id; ?>">
          <input type="hidden" name="save_address" value="1">

          <div class="field">
            <label for="full_name">
              Full name
              <span class="hint">As per your account</span>
            </label>
            <input
              type="text"
              id="full_name"
              name="full_name"
              value="<?php echo htmlspecialchars($userName); ?>"
              autocomplete="off"
              required
            >
          </div>

          <div class="field">
            <label for="phone">
              Phone
              <span class="hint">10-digit mobile · digits show as *</span>
            </label>
            <input
              type="password"
              id="phone"
              name="phone"
              placeholder="10-digit mobile number"
              inputmode="numeric"
              pattern="\d{10}"
              maxlength="10"
              autocomplete="new-password"
              required
            >
          </div>

          <div class="field">
            <label for="address">
              Address
              <span class="hint">House no, street, locality</span>
            </label>
            <textarea
              id="address"
              name="address"
              placeholder="Flat / House no, street, landmark"
              autocomplete="off"
              required
            ></textarea>
          </div>

          <div class="row">
            <div class="field">
              <label for="city">
                City
                <span class="hint">Town / City</span>
              </label>
              <input
                type="text"
                id="city"
                name="city"
                placeholder="City / Town"
                value=""
                autocomplete="off"
                required
              >
            </div>
            <div class="field">
              <label for="state">
                State
                <span class="hint">e.g. West Bengal</span>
              </label>
              <input
                type="text"
                id="state"
                name="state"
                placeholder="State"
                value=""
                autocomplete="off"
                required
              >
            </div>
          </div>

          <div class="field">
            <label for="pincode">
              Pincode
              <span class="hint">6-digit PIN · will show as *</span>
            </label>
            <input
              type="password"
              id="pincode"
              name="pincode"
              placeholder="e.g. 700000"
              inputmode="numeric"
              pattern="\d{6}"
              maxlength="6"
              autocomplete="new-password"
              required
            >
          </div>

          <button type="submit" class="btn-primary">
            <span class="icon">📍</span>
            Save location &amp; continue
          </button>
        </form>

        <button class="btn-secondary">
          <a href="dashboard.php">Cancel &amp; go back</a>
        </button>
      </div>
    </section>

    <!-- RIGHT: small info -->
    <section class="card-right">
      <div class="card-right-inner">
        <div>
          <div class="right-heading">
            Delivery details
            <span class="right-heading-pill">For this order</span>
          </div>
          <div class="summary-line">
            <span class="label">Used for</span>
            <span>Delivery &amp; order updates</span>
          </div>
          <div class="summary-line">
            <span class="label">Contact</span>
            <span>Courier &amp; OTP</span>
          </div>
          <div class="summary-line">
            <span class="label">Saved as</span>
            <span>Current checkout address</span>
          </div>

          <div class="hint-line">
            Make sure your pin code and phone number are correct so delivery partners can reach you easily.
          </div>
        </div>

        <div class="blur-pill">
          <span class="dot"></span>
          Phone &amp; PIN are masked on screen (shown as *) but used internally to process your order securely.
        </div>
      </div>
    </section>
  </div>
</main>

</body>
</html>