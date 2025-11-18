<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$userName = $_SESSION['user_name'] ?? 'User';
$userId   = (int)$_SESSION['user_id'];
$address  = $_SESSION['shipping_address'] ?? null;

$orderId  = null;
$product  = null;

// From payment.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];

    $stmt = $conn->prepare("SELECT id, name, description, price, image_url FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($product) {
        $amount = (float)$product['price'];

        $stmtOrder = $conn->prepare(
            "INSERT INTO orders (user_id, product_id, amount, payment_status)
             VALUES (?, ?, ?, 'SUCCESS')"
        );
        $stmtOrder->bind_param("iid", $userId, $product['id'], $amount);
        $stmtOrder->execute();
        $orderId = $conn->insert_id;
        $stmtOrder->close();
    }
}

// Estimated delivery window
$estFrom = date('D, d M', strtotime('+4 days'));
$estTo   = date('D, d M', strtotime('+6 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmed - Kartify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root{
      --primary:#22c55e;
      --accent:#2563eb;
      --card:#ffffff;
      --border:#e5e7eb;
      --text:#0f172a;
      --text-soft:#6b7280;
    }
    *{box-sizing:border-box;margin:0;padding:0;}

    body{
      font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      background:
        linear-gradient(135deg,#e0f2fe 0%,#eef2ff 40%,#fefce8 100%),
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
      from{opacity:0;transform:translateY(8px);}
      to{opacity:1;transform:translateY(0);}
    }

    /* Top bar */
    .top{
      position:sticky;top:0;z-index:10;
      background:linear-gradient(
        to right,
        rgba(255,255,255,0.94),
        rgba(248,250,252,0.96)
      );
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
      border-bottom:1px solid rgba(226,232,240,0.9);
      box-shadow:0 10px 25px rgba(15,23,42,0.18);
    }
    .top-inner{
      max-width:960px;margin:0 auto;
      padding:10px 16px;
      display:flex;align-items:center;justify-content:space-between;gap:10px;
    }
    .logo{
      display:flex;align-items:center;gap:8px;
      font-weight:800;font-size:18px;letter-spacing:.12em;
      text-transform:uppercase;
    }
    .logo-dot{color:#f97316;}
    .user-pill{
      font-size:12px;color:var(--text-soft);
      padding:4px 10px;
      border-radius:999px;
      background:rgba(249,250,251,0.9);
      border:1px solid rgba(229,231,235,0.9);
      backdrop-filter:blur(8px);
    }

    main{flex:1;}
    .shell{
      max-width:960px;margin:22px auto 30px;
      padding:0 16px;
      display:grid;
      grid-template-columns:minmax(0,1.1fr) minmax(0,0.9fr);
      gap:18px;
    }
    .card{
      background:rgba(255,255,255,0.9);
      border-radius:20px;
      border:1px solid rgba(229,231,235,0.95);
      padding:18px 18px 16px;
      box-shadow:
        0 22px 46px rgba(15,23,42,0.35),
        0 0 0 1px rgba(148,163,184,0.25);
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
      animation:cardFloat .5s ease-out;
    }
    @keyframes cardFloat{
      from{opacity:0;transform:translateY(12px);}
      to{opacity:1;transform:translateY(0);}
    }

    /* Left: success info */
    .success-top{
      display:flex;
      align-items:center;
      gap:12px;
      margin-bottom:8px;
    }
    .success-icon{
      width:46px;height:46px;
      border-radius:999px;
      background:#ecfdf5;
      border:1px solid #22c55e;
      display:flex;align-items:center;justify-content:center;
      font-size:26px;
      box-shadow:0 12px 26px rgba(34,197,94,0.4);
    }
    .success-title{
      font-size:18px;
      font-weight:700;
    }
    .success-sub{
      font-size:13px;
      color:var(--text-soft);
      margin-top:2px;
    }

    .info-grid{
      margin-top:12px;
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:8px;
      font-size:12px;
      color:var(--text-soft);
    }
    .info-label{font-weight:500;color:var(--text);}
    .highlight{
      font-weight:600;color:var(--accent);
    }

    .addr-block{
      margin-top:12px;
      font-size:12px;
      color:var(--text-soft);
      border-radius:14px;
      border:1px dashed #cbd5f5;
      padding:8px 10px;
      background:rgba(239,246,255,0.96);
      backdrop-filter:blur(10px);
      -webkit-backdrop-filter:blur(10px);
    }
    .addr-title{
      font-size:13px;
      font-weight:600;
      margin-bottom:4px;
      color:var(--text);
    }
    .addr-name{
      font-weight:600;
      color:#111827;
    }

    .btn-row{
      margin-top:14px;
      display:flex;flex-wrap:wrap;
      gap:8px;
    }
    .btn{
      border:none;
      border-radius:999px;
      padding:8px 14px;
      font-size:13px;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      gap:6px;
      text-decoration:none;
    }
    .btn-primary{
      background:linear-gradient(135deg,#22c55e,#16a34a);
      color:#f9fafb;
      box-shadow:0 12px 26px rgba(34,197,94,0.55);
      transition:transform .15s, box-shadow .15s;
    }
    .btn-primary:hover{
      transform:translateY(-1px);
      box-shadow:0 16px 32px rgba(34,197,94,0.7);
    }
    .btn-secondary{
      background:rgba(255,255,255,0.96);
      border:1px solid rgba(226,232,240,0.95);
      color:var(--text-soft);
    }

    /* Right: summary */
    .summary-title{
      font-size:15px;
      font-weight:600;
      margin-bottom:6px;
    }
    .sum-top{
      display:flex;gap:10px;margin-bottom:8px;
      align-items:center;
    }
    .sum-img{
      width:80px;height:80px;border-radius:10px;
      object-fit:cover;background:#e5e7eb;
    }
    .sum-name{
      font-size:14px;font-weight:600;
    }
    .sum-row{
      font-size:12px;color:var(--text-soft);
      margin-bottom:4px;
      display:flex;justify-content:space-between;
      gap:8px;
    }
    .sum-row span:first-child{
      white-space:nowrap;
    }
    .sum-price{
      font-size:15px;font-weight:700;color:var(--accent);
    }
    .small-note{
      margin-top:8px;
      font-size:11px;
      color:var(--text-soft);
    }

    @media(max-width:780px){
      .shell{grid-template-columns:minmax(0,1fr);}
    }
  </style>
</head>
<body>

<header class="top">
  <div class="top-inner">
    <div class="logo">KARTIFY<span class="logo-dot">.</span></div>
    <div class="user-pill">
      Thank you for your order, <?php echo htmlspecialchars($userName); ?> 💚
    </div>
  </div>
</header>

<main>
  <div class="shell">
    <!-- Left: success message + details -->
    <section class="card">
      <div class="success-top">
        <div class="success-icon">✅</div>
        <div>
          <div class="success-title">Payment successful</div>
          <div class="success-sub">
            Your order has been placed successfully. You’ll receive an update once it’s shipped.
          </div>
        </div>
      </div>

      <div class="info-grid">
        <div>
          <div class="info-label">Order status</div>
          <div class="highlight">Confirmed</div>
        </div>
        <div>
          <div class="info-label">Payment type</div>
          <div>Card · OTP verified</div>
        </div>
        <div>
          <div class="info-label">Estimated delivery</div>
          <div><?php echo $estFrom; ?> – <?php echo $estTo; ?></div>
        </div>
        <div>
          <div class="info-label">Order for</div>
          <div><?php echo htmlspecialchars($userName); ?></div>
        </div>
        <div>
          <div class="info-label">Order ID</div>
          <div>
            <?php
              if ($orderId) {
                  echo 'KARTIFY-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
              } else {
                  echo 'Generating...';
              }
            ?>
          </div>
        </div>
        <div>
          <div class="info-label">Placed on</div>
          <div><?php echo date('d M Y, h:i A'); ?></div>
        </div>
      </div>

      <?php if ($address): ?>
        <div class="addr-block">
          <div class="addr-title">Delivery address</div>
          <div class="addr-name"><?php echo htmlspecialchars($address['full_name']); ?></div>
          <?php echo nl2br(htmlspecialchars($address['address'])); ?><br>
          <?php echo htmlspecialchars($address['city']); ?> - <?php echo htmlspecialchars($address['pincode']); ?><br>
          <?php echo htmlspecialchars($address['state']); ?>, India<br>
          📞 <?php echo htmlspecialchars($address['phone']); ?>
        </div>
      <?php endif; ?>

      <div class="btn-row">
        <a href="dashboard.php" class="btn btn-primary">
          🧾 View my orders
        </a>
        <a href="index.php" class="btn btn-secondary">
          ⬅ Continue shopping
        </a>
      </div>
    </section>

    <!-- Right: order summary -->
    <section class="card">
      <div class="summary-title">Order summary</div>

      <?php if ($product): ?>
        <div class="sum-top">
          <?php if (!empty($product['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="sum-img" alt="">
          <?php else: ?>
            <div class="sum-img" style="
              background-image:url('images/D.jpg');
              background-size:cover;background-position:center;">
            </div>
          <?php endif; ?>
          <div>
            <div class="sum-name"><?php echo htmlspecialchars($product['name']); ?></div>
            <div style="font-size:12px;color:var(--text-soft);margin-top:3px;">
              <?php echo htmlspecialchars(mb_strimwidth($product['description'] ?? '',0,70,'...','UTF-8')); ?>
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
          <span>Estimated delivery</span>
          <span><?php echo $estFrom; ?> – <?php echo $estTo; ?></span>
        </div>
        <div class="sum-row">
          <span>Item price</span>
          <span>₹<?php echo number_format($product['price'],2); ?></span>
        </div>
        <div class="sum-row">
          <span>Delivery</span>
          <span style="color:#16a34a;">FREE</span>
        </div>
        <div class="sum-row">
          <span>Total paid</span>
          <span class="sum-price">₹<?php echo number_format($product['price'],2); ?></span>
        </div>
      <?php else: ?>
        <div class="small-note">
          The product details could not be loaded, but your payment has been captured and the order is confirmed.
        </div>
      <?php endif; ?>

      <div class="small-note">
        You can view full order details and live status anytime from the <strong>My Orders</strong> section.
      </div>
    </section>
  </div>
</main>

</body>
</html>
