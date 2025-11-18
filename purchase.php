<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

// product_id from POST or GET
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

// address from session
$address = $_SESSION['shipping_address'] ?? null;

// if address not set, go to location.php first
if (!$address) {
    header("Location: location.php?product_id=" . $product_id);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, description, price, image_url FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Product not found. <a href='dashboard.php'>Back to Dashboard</a>";
    exit;
}

$userName = $_SESSION['user_name'] ?? 'User';

// Calculate some extra info (for more real feel)
$price       = (float)$product['price'];
$mrp         = $price * 1.15;
$discountAmt = $mrp - $price;
$discountPct = round(($discountAmt / $mrp) * 100);

// Estimated delivery (3–5 days from today)
$estFrom = date('D, d M', strtotime('+3 days'));
$estTo   = date('D, d M', strtotime('+5 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Review your order - Kartify</title>
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

    html{
      scroll-behavior:smooth;
    }

    body{
      font-family:system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background:
        linear-gradient(135deg, #e0f2fe 0%, #eef2ff 40%, #fefce8 100%),
        url('images/D.jpg') center center / cover no-repeat fixed;
      background-blend-mode:soft-light;
      min-height:100vh;
      display:flex;
      flex-direction:column;
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

    /* ---------- Top bar (match login/register style) ---------- */

    .top{
      position:sticky;
      top:0;
      z-index:10;
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
      max-width:960px;
      margin:0 auto;
      padding:10px 16px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      color:#111827;
    }

    .logo-wrap{
      display:flex;
      align-items:center;
      gap:8px;
    }

    .logo{
      font-weight:800;
      font-size:18px;
      letter-spacing:.12em;
      text-transform:uppercase;
      color:#0f172a;
    }

    .logo-dot{color:var(--accent);}

    .step-pill{
      font-size:11px;
      padding:4px 10px;
      border-radius:999px;
      background:rgba(239,246,255,0.95);
      border:1px solid rgba(191,219,254,0.9);
      display:inline-flex;
      align-items:center;
      gap:6px;
    }

    .user-pill{
      font-size:12px;
      color:var(--text-soft);
      padding:4px 10px;
      border-radius:999px;
      background:rgba(249,250,251,0.9);
      border:1px solid rgba(229,231,235,0.9);
      backdrop-filter:blur(8px);
      -webkit-backdrop-filter:blur(8px);
    }

    .top-right{
      display:flex;
      align-items:center;
      gap:8px;
      flex-wrap:wrap;
      justify-content:flex-end;
    }

    /* ---------- Layout ---------- */

    main{
      flex:1;
    }

    .shell{
      max-width:960px;
      margin:18px auto 26px;
      padding:0 16px;
      display:grid;
      grid-template-columns:minmax(0,1.3fr) minmax(0,1fr);
      gap:16px;
    }

    .card{
      background:rgba(255,255,255,0.86);
      border:1px solid rgba(229,231,235,0.95);
      border-radius:20px;
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

    .title{
      font-size:16px;
      font-weight:600;
      margin-bottom:4px;
      color:#0f172a;
    }

    .sub{
      font-size:12px;
      color:var(--text-soft);
      margin-bottom:10px;
    }

    /* ---------- Product block ---------- */

    .product{
      display:flex;
      gap:12px;
      align-items:flex-start;
    }

    .product img,
    .img-fallback{
      width:120px;
      height:120px;
      border-radius:14px;
      object-fit:cover;
      background:#e5e7eb;
      box-shadow:0 14px 30px rgba(15,23,42,0.35);
      animation:floatImage 4s ease-in-out infinite;
    }

    @keyframes floatImage{
      0%,100%{transform:translateY(0) scale(1);}
      50%{transform:translateY(-6px) scale(1.03);}
    }

    .img-fallback{
      display:flex;
      align-items:flex-end;
      justify-content:flex-start;
      padding:6px 8px;
      font-size:11px;
      color:#e5e7eb;
      font-weight:500;
      background-image:url('images/D.jpg');
      background-size:cover;
      background-position:center;
    }

    .p-name{
      font-size:14px;
      font-weight:600;
      margin-bottom:4px;
      color:#0f172a;
    }

    .p-desc{
      font-size:12px;
      color:var(--text-soft);
      margin-bottom:6px;
      max-width:100%;
    }

    .p-price{
      font-size:15px;
      font-weight:700;
      color:var(--primary);
      margin-bottom:4px;
      display:flex;
      align-items:baseline;
      gap:4px;
      flex-wrap:wrap;
    }

    .p-price small{
      font-size:11px;
      color:#9ca3af;
      text-decoration:line-through;
    }

    .p-tag{
      font-size:11px;
      color:var(--success);
      font-weight:600;
    }

    .info-row{
      font-size:12px;
      color:var(--text-soft);
      margin-top:8px;
    }
    .info-row span{
      font-weight:500;
      color:var(--text);
    }

    .delivery-pill{
      margin-top:6px;
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:4px 8px;
      border-radius:999px;
      background:#ecfdf5;
      border:1px solid #bbf7d0;
      font-size:11px;
      color:#166534;
    }

    /* ---------- Product details under the card ---------- */

    .product-details{
      margin-top:12px;
      padding-top:10px;
      border-top:1px dashed #e5e7eb;
    }

    .details-title{
      font-size:13px;
      font-weight:600;
      margin-bottom:6px;
      color:#0f172a;
    }

    .details-list{
      list-style:none;
      padding-left:0;
      display:grid;
      gap:4px;
      font-size:12px;
      color:var(--text-soft);
    }

    .details-list li span.label{
      font-weight:500;
      color:#111827;
      margin-right:4px;
    }

    /* ---------- Address & price ---------- */

    .addr-label{
      font-size:13px;
      font-weight:600;
      margin-bottom:4px;
    }

    .addr-block{
      font-size:12px;
      color:var(--text-soft);
      border-radius:12px;
      border:1px dashed #cbd5f5;
      padding:8px 10px;
      background:rgba(239,246,255,0.95);
      backdrop-filter:blur(8px);
      -webkit-backdrop-filter:blur(8px);
      margin-bottom:10px;
    }

    .addr-name{
      font-weight:600;
      color:#111827;
    }

    .addr-tag-row{
      margin-top:4px;
      display:flex;
      flex-wrap:wrap;
      gap:6px;
      font-size:11px;
    }

    .addr-tag{
      padding:2px 7px;
      border-radius:999px;
      background:#eef2ff;
      border:1px solid #c7d2fe;
      color:#4f46e5;
    }

    .pay-summary{
      font-size:12px;
      color:var(--text-soft);
      margin-bottom:8px;
      margin-top:6px;
    }

    .pay-box{
      border-radius:12px;
      border:1px solid #e5e7eb;
      background:rgba(249,250,251,0.95);
      padding:8px 10px;
      margin-bottom:10px;
    }

    .pay-row{
      display:flex;
      justify-content:space-between;
      font-size:13px;
      margin-bottom:4px;
    }

    .pay-row span:last-child{
      font-weight:500;
    }

    .pay-row-muted{
      color:var(--text-soft);
      font-size:12px;
    }

    .pay-total{
      font-weight:700;
      color:var(--primary);
      font-size:14px;
      border-top:1px dashed #d1d5db;
      padding-top:6px;
      margin-top:4px;
    }

    .pay-total span:last-child{
      font-size:15px;
    }

    .secure-row{
      display:flex;
      align-items:center;
      gap:8px;
      margin-top:6px;
      font-size:11px;
      color:var(--text-soft);
    }

    .secure-row span.icon{
      font-size:14px;
    }

    .methods-row{
      margin-top:6px;
      font-size:11px;
      color:var(--text-soft);
    }

    .methods-row strong{
      color:#111827;
    }

    /* ---------- Buttons ---------- */

    .btn-primary{
      margin-top:10px;
      width:100%;
      border:none;
      border-radius:999px;
      padding:10px 12px;
      background:radial-gradient(circle at 0 0, #bfdbfe, #60a5fa 35%, #2563eb 100%);
      color:#f9fafb;
      font-size:14px;
      font-weight:600;
      cursor:pointer;
      box-shadow:
        0 14px 32px rgba(59,130,246,.70),
        0 0 0 1px rgba(37,99,235,.80);
      transition:transform .16s, box-shadow .16s, letter-spacing .16s, filter .16s;
    }

    .btn-primary:hover{
      transform:translateY(-1px);
      box-shadow:
        0 18px 40px rgba(59,130,246,.90),
        0 0 0 1px rgba(37,99,235,.90);
      filter:brightness(1.03);
      letter-spacing:0.01em;
    }

    .btn-primary:active{
      transform:translateY(0);
      box-shadow:
        0 9px 20px rgba(59,130,246,.70),
        0 0 0 1px rgba(37,99,235,.95);
      filter:brightness(0.98);
      letter-spacing:normal;
    }

    .btn-secondary{
      margin-top:6px;
      width:100%;
      border-radius:999px;
      padding:9px 12px;
      border:1px solid var(--border);
      background:rgba(255,255,255,0.98);
      font-size:13px;
      cursor:pointer;
      transition:background .15s, box-shadow .15s, transform .15s;
    }

    .btn-secondary a{
      text-decoration:none;
      color:var(--text-soft);
      display:block;
      width:100%;
      height:100%;
    }

    .btn-secondary:hover{
      background:#f9fafb;
      box-shadow:0 8px 18px rgba(148,163,184,.35);
      transform:translateY(-1px);
    }

    /* ---------- Responsive ---------- */

    @media(max-width:780px){
      .shell{grid-template-columns:minmax(0,1fr);}
    }

    @media(max-width:520px){
      .top-inner{
        flex-direction:column;
        align-items:flex-start;
      }
    }
  </style>
</head>
<body>

<header class="top">
  <div class="top-inner">
    <div class="logo-wrap">
      <div class="logo">KARTIFY<span class="logo-dot">.</span></div>
      <div class="step-pill">
        <span>🧾</span>
        <span>Secure checkout · Step 2 of 3</span>
      </div>
    </div>
    <div class="top-right">
      <div class="user-pill">
        Logged in as <strong> <?php echo htmlspecialchars($userName); ?> </strong>
      </div>
    </div>
  </div>
</header>

<main>
  <div class="shell">
    <!-- Left: product summary -->
    <section class="card">
      <div class="title">Review your item</div>
      <div class="sub">Confirm the product details and delivery window before you pay.</div>

      <div class="product">
        <?php if (!empty($product['image_url'])): ?>
          <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="">
        <?php else: ?>
          <div class="img-fallback">Kartify Product</div>
        <?php endif; ?>
        <div>
          <div class="p-name"><?php echo htmlspecialchars($product['name']); ?></div>
          <div class="p-desc"><?php echo htmlspecialchars($product['description']); ?></div>
          <div class="p-price">
            ₹<?php echo number_format($price, 2); ?>
            <small>₹<?php echo number_format($mrp, 2); ?></small>
            <span class="p-tag"><?php echo $discountPct; ?>% off</span>
          </div>
          <div class="info-row">
            Sold by <span>Kartify Retail Partner</span> · Free delivery
          </div>
          <div class="delivery-pill">
            <span>🚚</span>
            <span>Estimated delivery: <?php echo $estFrom; ?> – <?php echo $estTo; ?></span>
          </div>
        </div>
      </div>

      <!-- Product details block under Review your item -->
      <div class="product-details">
        <div class="details-title">Product details</div>
        <ul class="details-list">
          <li>
            <span class="label">Item:</span>
            <?php echo htmlspecialchars($product['name']); ?>
          </li>
          <li>
            <span class="label">Price:</span>
            ₹<?php echo number_format($price, 2); ?>
            (M.R.P. ₹<?php echo number_format($mrp, 2); ?>, <?php echo $discountPct; ?>% off)
          </li>
          <li>
            <span class="label">Delivery:</span>
            Free · Expected <?php echo $estFrom; ?> – <?php echo $estTo; ?>
          </li>
          <li>
            <span class="label">Sold by:</span>
            Kartify Retail Partner
          </li>
        </ul>
      </div>
    </section>

    <!-- Right: address + price + button -->
    <section class="card">
      <div class="title">Delivery &amp; payment summary</div>
      <div class="sub">Your order is protected with secure payment and order tracking.</div>

      <div class="addr-label">Deliver to</div>
      <div class="addr-block">
        <span class="addr-name"><?php echo htmlspecialchars($address['full_name']); ?></span><br>
        <?php echo nl2br(htmlspecialchars($address['address'])); ?><br>
        <?php echo htmlspecialchars($address['city']); ?> - <?php echo htmlspecialchars($address['pincode']); ?><br>
        <?php echo htmlspecialchars($address['state']); ?>, India<br>
        📞 <?php echo htmlspecialchars($address['phone']); ?>

        <div class="addr-tag-row">
          <div class="addr-tag">Home</div>
          <div class="addr-tag">Primary address</div>
        </div>
      </div>

      <div class="pay-summary">Price details</div>
      <div class="pay-box">
        <div class="pay-row">
          <span>Price (1 item)</span>
          <span>₹<?php echo number_format($mrp, 2); ?></span>
        </div>
        <div class="pay-row pay-row-muted">
          <span>Discount</span>
          <span style="color:var(--success);">-₹<?php echo number_format($discountAmt, 2); ?></span>
        </div>
        <div class="pay-row">
          <span>Delivery charges</span>
          <span style="color:var(--success);">FREE</span>
        </div>
        <div class="pay-row pay-total">
          <span>Total amount</span>
          <span>₹<?php echo number_format($price, 2); ?></span>
        </div>
      </div>

      <div class="secure-row">
        <span class="icon">🔐</span>
        <span>Payments are processed over a secure, encrypted connection.</span>
      </div>

      <div class="methods-row">
        <strong>Pay using:</strong> UPI · Credit/Debit Card · Net Banking · Wallets
      </div>

      <form method="post" action="payment.php">
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
        <button type="submit" class="btn-primary">
          Continue to payment
        </button>
      </form>

      <button class="btn-secondary">
        <a href="location.php?product_id=<?php echo (int)$product['id']; ?>">Change delivery address</a>
      </button>
      <button class="btn-secondary" style="margin-top:4px;">
        <a href="dashboard.php">Back to products</a>
      </button>
    </section>
  </div>
</main>

</body>
</html>
