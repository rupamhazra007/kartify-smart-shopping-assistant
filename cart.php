<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$user_id  = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Fetch all orders for this user, including order_status
$sql = "
    SELECT 
        o.id AS order_id,
        o.created_at,
        o.order_status,
        p.name,
        p.description,
        p.price,
        p.image_url
    FROM orders o
    INNER JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kartify - My Orders</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root{
      --primary:#2563eb;
      --accent:#f97316;
      --bg:#f3f4f6;
      --card:#ffffff;
      --border:#e5e7eb;
      --text:#0f172a;
      --text-soft:#6b7280;
      --success:#16a34a;
      --danger:#dc2626;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      background:radial-gradient(circle at top,#fff,#f3f4f6 45%,#e5e7eb 100%);
      min-height:100vh;
      display:flex;flex-direction:column;
      color:var(--text);
      opacity:0;animation:fadeIn .5s forwards;
    }
    @keyframes fadeIn{to{opacity:1;}}

    a{text-decoration:none;color:inherit;}

    /* Top bar */
    .top{
      position:sticky;top:0;z-index:20;
      background:#ffffffee;
      backdrop-filter:blur(12px);
      border-bottom:1px solid #e5e7eb;
    }
    .top-inner{
      max-width:960px;margin:0 auto;
      padding:10px 16px;
      display:flex;align-items:center;justify-content:space-between;gap:10px;
    }
    .logo{
      display:flex;align-items:center;gap:8px;
      font-size:18px;font-weight:700;letter-spacing:.06em;
    }
    .logo-dot{color:var(--accent);}
    .user-pill{
      font-size:12px;color:var(--text-soft);
      display:flex;align-items:center;gap:6px;
    }
    .user-avatar{
      width:22px;height:22px;border-radius:999px;
      background:radial-gradient(circle at 30% 0,#22c55e,#2563eb);
      display:flex;align-items:center;justify-content:center;
      color:#f9fafb;font-size:12px;font-weight:600;
    }

    .nav-actions{
      display:flex;gap:8px;align-items:center;
    }
    .pill-btn{
      padding:6px 10px;border-radius:999px;
      border:1px solid #e5e7eb;background:#ffffff;
      font-size:11px;color:#374151;cursor:pointer;
      transition:background .15s, box-shadow .15s, transform .15s;
    }
    .pill-btn:hover{
      background:#f9fafb;
      box-shadow:0 4px 10px rgba(148,163,184,.35);
      transform:translateY(-1px);
    }

    main{flex:1;}
    .shell{
      max-width:960px;margin:16px auto 24px;
      padding:0 16px;
    }
    .page-title{
      font-size:18px;font-weight:600;margin-bottom:4px;
    }
    .page-sub{
      font-size:12px;color:var(--text-soft);margin-bottom:10px;
    }

    .orders-list{
      display:grid;
      gap:10px;
      margin-top:8px;
    }

    .order-card{
      background:var(--card);
      border-radius:16px;
      border:1px solid var(--border);
      padding:10px 10px 9px;
      box-shadow:0 14px 30px rgba(148,163,184,.2);
      display:flex;
      flex-direction:column;
      gap:6px;
      font-size:12px;
    }

    .order-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:8px;
      margin-bottom:3px;
    }
    .order-id{
      font-weight:600;
      font-size:12px;
    }
    .order-date{
      font-size:11px;
      color:var(--text-soft);
    }

    .badge-status{
      font-size:10px;
      padding:2px 7px;
      border-radius:999px;
      background:#dcfce7;
      border:1px solid #bbf7d0;
      color:#15803d;
    }
    .badge-cancel{
      font-size:10px;
      padding:2px 7px;
      border-radius:999px;
      background:#fef2f2;
      border:1px solid #fecaca;
      color:var(--danger);
    }

    .order-body{
      display:flex;
      gap:10px;
    }
    .order-img{
      width:70px;height:70px;border-radius:10px;
      object-fit:cover;background:#e5e7eb;
      flex-shrink:0;
    }
    .p-name{
      font-size:13px;font-weight:500;
    }
    .p-desc{
      font-size:11px;color:var(--text-soft);
      margin-top:2px;
    }
    .p-price{
      margin-top:4px;
      font-size:13px;
      font-weight:700;
      color:#2563eb;
    }

    .order-footer{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:8px;
      margin-top:4px;
      font-size:11px;
      color:var(--text-soft);
    }
    .delivery-text strong{
      color:var(--success);
    }
    .delivery-text.cancelled strong{
      color:var(--danger);
    }
    .btn-sm{
      padding:5px 9px;border-radius:999px;
      border:1px solid #d1d5db;
      background:#ffffff;
      font-size:11px;cursor:pointer;
    }

    .empty{
      margin-top:14px;
      font-size:13px;
      color:var(--text-soft);
      background:#f9fafb;
      border-radius:12px;
      border:1px dashed #e5e7eb;
      padding:10px 10px;
    }
    .empty a{color:#2563eb;font-weight:500;}

    @media(max-width:640px){
      .order-body{
        flex-direction:row;
      }
    }
  </style>
</head>
<body>

<header class="top">
  <div class="top-inner">
    <div class="logo">
      KARTIFY<span class="logo-dot">.</span>
    </div>
    <div class="nav-actions">
      <div class="user-pill">
        <div class="user-avatar">
          <?php
            $initial = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
            echo htmlspecialchars($initial);
          ?>
        </div>
        <span>Hi, <?php echo htmlspecialchars($userName); ?></span>
      </div>
      <a href="dashboard.php" class="pill-btn">Dashboard</a>
      <a href="logout.php" class="pill-btn">Logout</a>
    </div>
  </div>
</header>

<main>
  <div class="shell">
    <div class="page-title">My Orders</div>
    <div class="page-sub">
      All your orders on Kartify are listed here with current status and delivery information.
    </div>

    <?php if ($orders->num_rows === 0): ?>
      <div class="empty">
        You haven't placed any orders yet.  
        Go to the <a href="dashboard.php">Dashboard</a> and click <strong>Buy Now</strong> on any product to place your first order.
      </div>
    <?php else: ?>
      <div class="orders-list">
        <?php while ($row = $orders->fetch_assoc()):
          $created   = strtotime($row['created_at']);
          $orderId   = (int)$row['order_id'];
          $orderNum  = 'KART' . date('Ymd', $created) . str_pad($orderId, 4, '0', STR_PAD_LEFT);
          $purchase  = date('d M Y, h:i A', $created);
          $deliveryTs = strtotime('+5 days', $created);
          $delivery  = date('d M Y', $deliveryTs);

          $statusRaw = strtoupper(trim($row['order_status'] ?? 'PLACED'));
          if ($statusRaw === 'CANCELLED') {
              $badgeClass   = 'badge-cancel';
              $badgeText    = 'Cancelled';
              $isCancelled  = true;
          } else {
              $badgeClass   = 'badge-status';
              $badgeText    = 'Confirmed · Paid';
              $isCancelled  = false;
          }
        ?>
        <article class="order-card">
          <div class="order-header">
            <div>
              <div class="order-id"><?php echo htmlspecialchars($orderNum); ?></div>
              <div class="order-date">Placed on <?php echo htmlspecialchars($purchase); ?></div>
            </div>
            <span class="<?php echo $badgeClass; ?>">
              <?php echo htmlspecialchars($badgeText); ?>
            </span>
          </div>

          <div class="order-body">
            <?php if (!empty($row['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="" class="order-img">
            <?php else: ?>
              <img src="https://via.placeholder.com/200x200?text=Product" alt="" class="order-img">
            <?php endif; ?>
            <div>
              <div class="p-name"><?php echo htmlspecialchars($row['name']); ?></div>
              <div class="p-desc">
                <?php echo htmlspecialchars(mb_strimwidth($row['description'] ?? '', 0, 80, '...', 'UTF-8')); ?>
              </div>
              <div class="p-price">₹<?php echo number_format($row['price'],2); ?></div>
            </div>
          </div>

          <div class="order-footer">
            <?php if ($isCancelled): ?>
              <div class="delivery-text cancelled">
                <strong>Order cancelled.</strong> There will be no delivery for this shipment.
              </div>
            <?php else: ?>
              <div class="delivery-text">
                Estimated delivery: <strong><?php echo htmlspecialchars($delivery); ?></strong>
              </div>
            <?php endif; ?>
            <button class="btn-sm" type="button" onclick="window.location.href='status.php?order_id=<?php echo $orderId; ?>'">
              View details
            </button>
          </div>
        </article>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
