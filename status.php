<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

$user_id  = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

$cancelMsg   = '';
$cancelError = '';

// ---------- HANDLE CANCEL REQUEST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancelId = (int)$_POST['cancel_order_id'];
    $reason   = trim($_POST['cancel_reason'] ?? '');

    if ($cancelId <= 0) {
        $cancelError = "Invalid order selection.";
    } elseif ($reason === '') {
        $cancelError = "Please provide a reason for cancellation.";
    } else {
        // Check that the order belongs to this user
        $checkSql = "SELECT id, user_id, order_status FROM orders WHERE id = ? AND user_id = ? LIMIT 1";
        $stmtChk  = $conn->prepare($checkSql);
        $stmtChk->bind_param('ii', $cancelId, $user_id);
        $stmtChk->execute();
        $resChk   = $stmtChk->get_result();
        $rowChk   = $resChk->fetch_assoc();
        $stmtChk->close();

        if (!$rowChk) {
            $cancelError = "We could not find this order for your account.";
        } elseif (strcasecmp($rowChk['order_status'] ?? '', 'Cancelled') === 0) {
            $cancelError = "This order is already cancelled.";
        } else {
            $updateSql = "
                UPDATE orders
                SET order_status  = 'Cancelled',
                    cancel_reason = ?,
                    cancelled_at  = NOW()
                WHERE id = ? AND user_id = ?
                LIMIT 1
            ";
            $stmtUp = $conn->prepare($updateSql);
            $stmtUp->bind_param('sii', $reason, $cancelId, $user_id);
            if ($stmtUp->execute()) {
                $cancelMsg = "The order has been cancelled successfully.";
            } else {
                $cancelError = "Unable to cancel the order. Please try again.";
            }
            $stmtUp->close();
        }
    }
}

// ---------- WHICH VIEW? ----------
$orderId    = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$isListPage = ($orderId <= 0);

$order    = null;
$orders   = [];
$errorMsg = '';

// ---------- LIST VIEW: MY ORDERS ----------
if ($isListPage) {
    $sqlList = "
        SELECT 
            o.*,
            p.name      AS product_name,
            p.price     AS product_price,
            p.image_url AS product_image
        FROM orders o
        LEFT JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ";
    $stmtList = $conn->prepare($sqlList);
    if ($stmtList) {
        $stmtList->bind_param('i', $user_id);
        $stmtList->execute();
        $resList = $stmtList->get_result();
        while ($row = $resList->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmtList->close();
    }

// ---------- SINGLE ORDER VIEW ----------
} else {
    $sql = "
        SELECT 
            o.*,
            p.name      AS product_name,
            p.price     AS product_price,
            p.image_url AS product_image
        FROM orders o
        LEFT JOIN products p ON o.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $orderId, $user_id);
    $stmt->execute();
    $res   = $stmt->get_result();
    $order = $res->fetch_assoc();
    $stmt->close();

    if (!$order) {
        $errorMsg = "Order not found or you don't have permission to view this order.";
    } else {
        $placedAtTs = !empty($order['created_at']) ? strtotime($order['created_at']) : time();
        $now        = time();
        $diffHours  = ($now - $placedAtTs) / 3600;

        // order_status from DB (default: Order placed)
        $orderStatusDb = trim($order['order_status'] ?? '');
        if ($orderStatusDb === '') {
            $orderStatusDb = 'Order placed';
        }

        // basic timeline steps
        $steps = [
            "Order placed",
            "Packed",
            "Shipped",
            "In transit",
            "Out for delivery",
            "Delivered",
            "Cancelled"
        ];

        // map for comparison
        function statusIndex($label) {
            static $map = [
                "Order placed"      => 1,
                "Packed"            => 2,
                "Shipped"           => 3,
                "In transit"        => 4,
                "Out for delivery"  => 5,
                "Delivered"         => 6,
                "Cancelled"         => 7,
            ];
            return $map[$label] ?? 0;
        }

        // derive "currentStatus" + note
        if (strcasecmp($orderStatusDb, 'Cancelled') === 0) {
            $currentStatus = "Cancelled";
            $statusNote    = "This order was cancelled by you. It will not be delivered.";
        } else {
            if ($diffHours < 1) {
                $currentStatus = "Order placed";
                $statusNote    = "We have received your order and the seller is processing it.";
            } elseif ($diffHours < 6) {
                $currentStatus = "Packed";
                $statusNote    = "Your item has been packed and is waiting for pickup.";
            } elseif ($diffHours < 24) {
                $currentStatus = "Shipped";
                $statusNote    = "Your package is in transit with the courier partner.";
            } elseif ($diffHours < 48) {
                $currentStatus = "In transit";
                $statusNote    = "Your package is moving between courier hubs.";
            } elseif ($diffHours < 72) {
                $currentStatus = "Out for delivery";
                $statusNote    = "The delivery agent will attempt delivery today.";
            } else {
                $currentStatus = "Delivered";
                $statusNote    = "The order is marked as delivered.";
            }
        }

        // helper for timeline
        function isStepDoneLocal($stepLabel, $currentStatus, $orderStatusDb) {
            $currentIndex = statusIndex($currentStatus);
            $dbIndex      = statusIndex($orderStatusDb);

            // if cancelled, everything up to "Cancelled" is treated as done
            if (strcasecmp($orderStatusDb, 'Cancelled') === 0) {
                return statusIndex($stepLabel) <= statusIndex('Cancelled');
            }
            return statusIndex($stepLabel) <= $currentIndex;
        }

        // expected delivery
        if (strcasecmp($orderStatusDb, 'Cancelled') === 0) {
            $expectedDate = "Order cancelled";
        } else {
            $expectedTs   = $placedAtTs + (3 * 24 * 3600);
            $expectedDate = date('d M Y', $expectedTs);
        }

        // safe defaults
        $quantity        = (int)($order['quantity'] ?? 1);
        if ($quantity <= 0) $quantity = 1;

        $paymentMethod   = $order['payment_method']   ?? 'Online payment';
        $paymentStatus   = $order['payment_status']   ?? 'Paid';
        $shippingName    = $order['shipping_name']    ?? $userName;
        $shippingPhone   = $order['shipping_phone']   ?? 'Not available';
        $shippingAddress = $order['shipping_address'] ?? '';
        $shippingCity    = $order['shipping_city']    ?? '';
        $shippingState   = $order['shipping_state']   ?? '';
        $shippingZip     = $order['shipping_zip']     ?? '';
        $shippingLine    = trim($shippingAddress . ', ' . $shippingCity . ', ' . $shippingState . ' ' . $shippingZip, " ,");

        $assistantOrderCode = 'KARTIFY-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>
    <?php if ($isListPage): ?>
      My Orders - Kartify
    <?php else: ?>
      Order Status #<?php echo htmlspecialchars($orderId); ?> - Kartify
    <?php endif; ?>
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #2563eb;
      --accent: #f97316;
      --card-bg: #ffffff;
      --border-soft: #e5e7eb;
      --text-main: #0f172a;
      --text-soft: #6b7280;
      --success: #16a34a;
      --danger: #dc2626;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      background:
        linear-gradient(rgba(254, 249, 195, 0.55), rgba(254, 243, 199, 0.4)),
        url('images/C.jpg') center center / cover no-repeat fixed;
      color:var(--text-main);
      min-height:100vh;
      display:flex;
      flex-direction:column;
      opacity:0;
      transform:translateY(8px);
      animation:softFade .55s ease-out forwards;
    }
    a{text-decoration:none;color:inherit;}

    .top-bar{
      position:sticky;top:0;z-index:20;
      backdrop-filter:blur(16px);
      -webkit-backdrop-filter:blur(16px);
      background:rgba(255,255,255,0.96);
      border-bottom:1px solid rgba(226,232,240,0.95);
      box-shadow:0 10px 26px rgba(148,163,184,0.35);
    }
    .top-bar-inner{
      max-width:900px;margin:0 auto;
      padding:10px 16px;
      display:flex;align-items:center;justify-content:space-between;gap:8px;
    }
    .logo{
      display:flex;align-items:center;gap:8px;
    }
    .logo-icon{
      width:30px;height:30px;border-radius:11px;
      background:conic-gradient(from 160deg,#22c55e,#22d3ee,#3b82f6,#eab308,#f97316,#22c55e);
      display:flex;align-items:center;justify-content:center;
      animation:spin-slow 16s linear infinite;
      box-shadow:0 0 12px rgba(37,99,235,0.5);
      flex-shrink:0;
    }
    .logo-icon-inner{
      width:18px;height:18px;border-radius:7px;
      background:radial-gradient(circle at 0 0,#bfdbfe,#1d4ed8 55%,#020617 100%);
      display:flex;align-items:center;justify-content:center;
      font-size:12px;font-weight:700;color:#f9fafb;
    }
    .logo-text{
      font-weight:700;letter-spacing:.08em;font-size:16px;color:#111827;text-transform:uppercase;
    }
    .logo-dot{color:var(--accent);}

    .top-actions{
      display:flex;align-items:center;gap:8px;flex-wrap:wrap;
    }
    .pill-btn{
      display:inline-flex;align-items:center;gap:4px;
      padding:6px 11px;font-size:11px;
      border-radius:999px;
      border:1px solid rgba(209,213,219,0.9);
      background:rgba(249,250,251,0.98);
      color:#374151;cursor:pointer;
      transition:background .18s,box-shadow .18s,transform .18s,border-color .18s;
    }
    .pill-btn:hover{
      background:#f9fafb;
      box-shadow:0 6px 16px rgba(148,163,184,0.6);
      transform:translateY(-1px);
      border-color:rgba(191,219,254,0.9);
    }
    .user-pill{
      display:inline-flex;align-items:center;gap:6px;
      padding:5px 9px;border-radius:999px;
      background:rgba(249,250,251,0.98);
      border:1px solid rgba(209,213,219,0.9);
      font-size:11px;color:#4b5563;
      max-width:170px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
      box-shadow:0 4px 12px rgba(148,163,184,0.4);
    }
    .user-avatar{
      width:22px;height:22px;border-radius:999px;
      background:radial-gradient(circle at 30% 0,#22c55e,#2563eb);
      display:flex;align-items:center;justify-content:center;
      font-size:12px;font-weight:600;color:#f9fafb;
      flex-shrink:0;box-shadow:0 0 8px rgba(34,197,94,0.6);
    }

    .shell{
      max-width:900px;margin:22px auto 24px;
      padding:0 16px 20px;
    }
    .card-main{
      border-radius:22px;
      background:radial-gradient(circle at top left,rgba(248,250,252,0.96),rgba(229,231,235,0.98));
      border:1px solid rgba(209,213,219,0.95);
      box-shadow:0 22px 50px rgba(15,23,42,0.2);
      padding:18px 16px 14px;
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
      position:relative;overflow:hidden;
    }
    .card-main::before{
      content:"";position:absolute;inset:0;
      background:
        radial-gradient(circle at 0% 0%,rgba(59,130,246,0.12),transparent 55%),
        radial-gradient(circle at 100% 0%,rgba(244,114,182,0.12),transparent 55%);
      opacity:.8;pointer-events:none;
    }
    .card-main-inner{position:relative;z-index:1;}

    .order-header{
      display:flex;justify-content:space-between;flex-wrap:wrap;
      gap:10px;margin-bottom:14px;align-items:center;
    }
    .order-title{
      font-size:16px;font-weight:700;color:var(--text-main);
      display:flex;flex-direction:column;gap:3px;
    }
    .order-title small{
      font-size:11px;font-weight:500;color:var(--text-soft);
    }
    .order-sub{font-size:12px;color:var(--text-soft);margin-top:4px;}

    .status-pill{
      display:inline-flex;align-items:center;gap:6px;
      padding:7px 13px;border-radius:999px;font-size:12px;
      background:linear-gradient(to right,#ecfdf3,#dcfce7);
      border:1px solid #22c55e;color:#15803d;
      box-shadow:0 6px 18px rgba(34,197,94,0.35);
      animation:floatPulse 2.4s ease-in-out infinite;
    }
    .status-pill.cancelled{
      background:linear-gradient(to right,#fee2e2,#fee2e2);
      border-color:#fecaca;
      color:#b91c1c;
      box-shadow:0 6px 18px rgba(248,113,113,0.5);
    }
    .status-dot{
      width:8px;height:8px;border-radius:999px;
      background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,0.35);
    }
    .status-pill.cancelled .status-dot{
      background:#dc2626;
      box-shadow:0 0 0 3px rgba(248,113,113,0.6);
    }

    .order-meta{
      display:flex;flex-wrap:wrap;gap:10px;
      font-size:12px;color:var(--text-soft);margin-bottom:14px;
    }
    .meta-pill{
      padding:5px 10px;border-radius:999px;
      background:rgba(249,250,251,0.98);
      border:1px solid #e5e7eb;
      display:inline-flex;align-items:center;gap:5px;
      box-shadow:0 3px 8px rgba(148,163,184,0.35);
    }
    .meta-pill strong{color:#111827;}

    .order-info-grid{
      display:grid;
      grid-template-columns:minmax(0,1.4fr) minmax(0,1.2fr);
      gap:10px;margin-bottom:16px;font-size:12px;
    }
    .info-card{
      border-radius:12px;border:1px solid rgba(226,232,240,0.9);
      background:rgba(249,250,251,0.98);
      padding:10px 11px;
      box-shadow:0 10px 16px rgba(148,163,184,0.35);
    }
    .info-card-title{
      font-size:12px;font-weight:600;margin-bottom:6px;
      display:flex;justify-content:space-between;align-items:center;color:#111827;
    }
    .info-tag{
      font-size:10px;padding:2px 6px;border-radius:999px;
      background:rgba(219,234,254,0.9);
      border:1px solid rgba(191,219,254,0.9);
      text-transform:uppercase;letter-spacing:.05em;
      color:#1d4ed8;
    }
    .info-row{display:flex;justify-content:space-between;gap:6px;margin-bottom:4px;}
    .info-label{color:var(--text-soft);}
    .info-value{font-weight:500;text-align:right;}
    .info-note{font-size:11px;color:var(--text-soft);margin-top:4px;}

    .product-row{
      display:flex;gap:14px;align-items:flex-start;margin-bottom:18px;flex-wrap:wrap;
    }
    .product-img{
      width:120px;height:120px;border-radius:14px;
      background-color:#ffffff;
      border:1px solid rgba(226,232,240,0.9);
      object-fit:contain;
      box-shadow:0 10px 18px rgba(148,163,184,0.55);
      animation:popIn .35s ease-out;
    }
    .product-info{flex:1;min-width:210px;}
    .product-name{font-size:15px;font-weight:600;margin-bottom:4px;}
    .product-price{font-size:14px;font-weight:700;color:#2563eb;margin-bottom:4px;}
    .product-badges{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px;}
    .badge{
      font-size:10px;padding:3px 7px;border-radius:999px;
      border:1px solid #e5e7eb;background:#f9fafb;
    }
    .product-meta-note{font-size:12px;color:var(--text-soft);}

    .timeline-card{
      margin-top:4px;border-radius:16px;
      border:1px dashed #e5e7eb;
      background:linear-gradient(to right,#f9fafb,#eff6ff);
      padding:12px 12px 10px;
      box-shadow:0 10px 24px rgba(148,163,184,0.5);
    }
    .timeline-title{
      font-size:13px;font-weight:600;margin-bottom:8px;
      display:flex;justify-content:space-between;align-items:center;
    }
    .timeline-sub{font-size:11px;color:var(--text-soft);}
    .timeline{
      position:relative;padding-left:14px;margin-top:6px;
    }
    .timeline::before{
      content:"";position:absolute;left:6px;top:0;bottom:0;
      width:2px;background:linear-gradient(to bottom,#2563eb,#93c5fd,#e5e7eb);
      opacity:.85;
    }
    .step{
      position:relative;padding-left:14px;margin-bottom:7px;font-size:12px;
    }
    .step:last-child{margin-bottom:0;}
    .step-marker{
      position:absolute;left:-2px;top:3px;
      width:10px;height:10px;border-radius:999px;
      background:#e5e7eb;border:2px solid #f9fafb;
    }
    .step.completed .step-marker{
      background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,0.25);
    }
    .step.current .step-marker{
      background:#2563eb;box-shadow:0 0 0 2px rgba(59,130,246,0.45);
    }
    .step-label{font-weight:500;}
    .step-status-text{font-size:11px;color:var(--text-soft);}
    .status-note{font-size:12px;color:var(--text-soft);margin-top:8px;}

    .cancel-card{
      margin-top:14px;
      border-radius:14px;
      border:1px solid #fecaca;
      background:rgba(254,242,242,0.95);
      padding:10px 11px;
      font-size:12px;
      color:#7f1d1d;
      box-shadow:0 10px 24px rgba(248,113,113,0.4);
    }
    .cancel-card h3{
      font-size:13px;
      margin-bottom:6px;
      display:flex;align-items:center;gap:6px;
      color:#991b1b;
    }
    .cancel-card textarea{
      width:100%;margin-top:4px;
      font-size:12px;font-family:inherit;
      border-radius:10px;border:1px solid #fecaca;
      padding:7px 8px;resize:vertical;min-height:48px;
      background:#fff7f7;
    }
    .cancel-card textarea:focus{
      outline:none;
      border-color:#f87171;
      box-shadow:0 0 0 1px rgba(248,113,113,0.4);
      background:#ffffff;
    }
    .cancel-actions{
      margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;
    }
    .btn-cancel{
      border:none;border-radius:999px;
      padding:7px 13px;
      background:linear-gradient(135deg,#ef4444,#b91c1c);
      color:#fef2f2;font-size:12px;font-weight:500;
      cursor:pointer;
      box-shadow:0 10px 20px rgba(239,68,68,0.55);
      transition:transform .15s,box-shadow .15s;
    }
    .btn-cancel:hover{
      transform:translateY(-1px);
      box-shadow:0 14px 26px rgba(239,68,68,0.7);
    }
    .cancel-note{
      font-size:11px;color:#7f1d1d;
    }

    .alert{
      margin-bottom:10px;padding:8px 10px;border-radius:10px;
      font-size:13px;
    }
    .alert-success{
      background:#ecfdf3;border:1px solid #bbf7d0;color:#166534;
    }
    .alert-error{
      background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;
    }

    /* LIST VIEW */
    .list-header-sub{font-size:12px;color:var(--text-soft);margin-top:4px;}
    .order-grid{
      margin-top:16px;
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:14px;
    }
    .order-card{
      border-radius:20px;
      background:#ffffff;
      border:1px solid rgba(226,232,240,0.95);
      padding:12px 12px 10px;
      box-shadow:0 16px 32px rgba(148,163,184,0.45);
      cursor:pointer;
      transition:transform .18s,box-shadow .18s,border-color .18s;
    }
    .order-card:hover{
      transform:translateY(-3px);
      box-shadow:0 22px 40px rgba(148,163,184,0.7);
      border-color:rgba(191,219,254,0.95);
    }
    .order-card-imgwrap{
      border-radius:16px;
      border:1px solid rgba(226,232,240,0.9);
      background:#f9fafb;
      padding:8px;
      display:flex;align-items:center;justify-content:center;
      margin-bottom:8px;
    }
    .order-card-imgwrap img{
      width:140px;height:140px;object-fit:contain;
    }
    .order-card-title{
      font-size:14px;font-weight:600;margin-bottom:3px;
    }
    .order-card-sub{
      font-size:11px;color:var(--text-soft);margin-bottom:8px;
    }
    .order-card-bottom{
      display:flex;justify-content:space-between;align-items:center;margin-top:4px;
    }
    .price-main{font-size:15px;font-weight:700;color:#2563eb;}
    .status-pill-mini{
      font-size:11px;padding:4px 10px;border-radius:999px;
      border:1px solid #22c55e;
      background:#ecfdf3;color:#16a34a;
    }
    .status-pill-mini.cancelled{
      border-color:#fecaca;
      background:#fef2f2;
      color:#b91c1c;
    }
    .placed-line{
      margin-top:8px;font-size:11px;color:var(--text-soft);
      display:flex;align-items:center;gap:5px;
    }

    .empty-state{margin-top:16px;font-size:13px;color:var(--text-soft);}
    .empty-state strong{color:var(--text-main);}

    .footer{
      border-top:1px solid rgba(209,213,219,0.95);
      background:rgba(255,255,255,0.96);
      backdrop-filter:blur(10px);
      -webkit-backdrop-filter:blur(10px);
      padding:8px 16px 10px;
      font-size:11px;color:#6b7280;
      box-shadow:0 -8px 22px rgba(148,163,184,0.4);
      margin-top:auto;
    }
    .footer-inner{
      max-width:900px;margin:0 auto;
      display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;
    }

    @keyframes softFade{
      from{opacity:0;transform:translateY(8px) scale(.99);}
      to{opacity:1;transform:translateY(0) scale(1);}
    }
    @keyframes spin-slow{
      from{transform:rotate(0deg);}
      to{transform:rotate(360deg);}
    }
    @keyframes floatPulse{
      0%,100%{transform:translateY(0);box-shadow:0 6px 18px rgba(34,197,94,0.35);}
      50%{transform:translateY(-1px);box-shadow:0 10px 24px rgba(34,197,94,0.55);}
    }
    @keyframes popIn{
      from{opacity:0;transform:translateY(6px) scale(.98);}
      to{opacity:1;transform:translateY(0) scale(1);}
    }

    @media(max-width:640px){
      .order-info-grid{grid-template-columns:minmax(0,1fr);}
      .product-row{flex-direction:column;align-items:flex-start;}
    }
  </style>
</head>
<body>

<header class="top-bar">
  <div class="top-bar-inner">
    <a href="index.php" class="logo">
      <div class="logo-icon">
        <div class="logo-icon-inner">K</div>
      </div>
      <div class="logo-text">KARTIFY<span class="logo-dot">.</span></div>
    </a>

    <div class="top-actions">
      <div class="user-pill">
        <div class="user-avatar">
          <?php
            $initial = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
            echo htmlspecialchars($initial);
          ?>
        </div>
        <span><?php echo htmlspecialchars($userName); ?></span>
      </div>
      <a href="dashboard.php" class="pill-btn">⬅ Back to Dashboard</a>
      <a href="logout.php" class="pill-btn">Logout</a>
    </div>
  </div>
</header>

<main class="shell">
  <div class="card-main">
    <div class="card-main-inner">

      <?php if ($cancelMsg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($cancelMsg); ?></div>
      <?php endif; ?>
      <?php if ($cancelError): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($cancelError); ?></div>
      <?php endif; ?>

      <?php if ($isListPage): ?>
        <!-- MY ORDERS GRID -->
        <div class="order-header">
          <div>
            <div class="order-title">
              <span>My Orders</span>
              <small>All orders you placed in Kartify. Click a card to view full status.</small>
            </div>
          </div>
        </div>

        <?php if (empty($orders)): ?>
          <div class="empty-state">
            <strong>No orders yet.</strong> Go back to the dashboard and place your first order – it will appear here.
          </div>
        <?php else: ?>
          <div class="order-grid">
            <?php foreach ($orders as $row):
              $oid        = (int)$row['id'];
              $qty        = (int)($row['quantity'] ?? 1);
              if ($qty <= 0) $qty = 1;
              $price      = (float)($row['product_price'] ?? 0);
              $amount     = $price * $qty;
              $pname      = $row['product_name'] ?? 'Product';
              $pimg       = $row['product_image'] ?? '';
              $createdRaw = !empty($row['created_at']) ? strtotime($row['created_at']) : 0;
              $created    = $createdRaw ? date('d M Y, h:i A', $createdRaw) : 'N/A';
              $statusDb   = trim($row['order_status'] ?? '');
              if ($statusDb === '') $statusDb = 'Order placed';
              $isCancelled = (strcasecmp($statusDb, 'Cancelled') === 0);
            ?>
            <div class="order-card" onclick="window.location.href='status.php?order_id=<?php echo $oid; ?>'">
              <div class="order-card-imgwrap">
                <?php if (!empty($pimg)): ?>
                  <img src="<?php echo htmlspecialchars($pimg); ?>" alt="">
                <?php else: ?>
                  <span style="font-size:11px;color:#9ca3af;">No image</span>
                <?php endif; ?>
              </div>
              <div class="order-card-title"><?php echo htmlspecialchars($pname); ?></div>
              <div class="order-card-sub">
                Order ID #<?php echo htmlspecialchars($oid); ?> · Tap to view full status
              </div>
              <div class="order-card-bottom">
                <div class="price-main">₹<?php echo number_format($amount, 2); ?></div>
                <div class="status-pill-mini <?php echo $isCancelled ? 'cancelled' : ''; ?>">
                  <?php echo $isCancelled ? 'Order cancelled' : 'Order placed'; ?>
                </div>
              </div>
              <div class="placed-line">
                📦 Placed on <?php echo htmlspecialchars($created); ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <!-- SINGLE ORDER VIEW -->
        <?php if (!empty($errorMsg)): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php else: ?>
          <div class="order-header">
            <div>
              <div class="order-title">
                <span>Order Status</span>
                <small>
                  Order ID: <strong>#<?php echo htmlspecialchars($orderId); ?></strong> ·
                  Code: <strong><?php echo htmlspecialchars($assistantOrderCode); ?></strong>
                </small>
              </div>
              <div class="order-sub">
                Placed on
                <strong><?php echo htmlspecialchars(date('d M Y, h:i A', $placedAtTs)); ?></strong>
              </div>
            </div>
            <div>
              <?php
                $isCancelled = (strcasecmp($orderStatusDb, 'Cancelled') === 0);
              ?>
              <div class="status-pill <?php echo $isCancelled ? 'cancelled' : ''; ?>">
                <span class="status-dot"></span>
                <span><?php echo htmlspecialchars($orderStatusDb); ?></span>
              </div>
            </div>
          </div>

          <div class="order-meta">
            <div class="meta-pill">
              💰 Paid amount:
              <strong>₹<?php echo number_format((float)$order['product_price'] * $quantity, 2); ?></strong>
            </div>
            <div class="meta-pill">
              🧾 Items: <strong><?php echo $quantity; ?></strong>
            </div>
            <div class="meta-pill">
              🚚 Expected delivery:
              <strong><?php echo htmlspecialchars($expectedDate); ?></strong>
            </div>
          </div>

          <div class="order-info-grid">
            <div class="info-card">
              <div class="info-card-title">
                <span>Order summary</span>
                <span class="info-tag">Overview</span>
              </div>
              <div class="info-row">
                <span class="info-label">Order ID</span>
                <span class="info-value">#<?php echo htmlspecialchars($orderId); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Tracking code</span>
                <span class="info-value"><?php echo htmlspecialchars($assistantOrderCode); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Payment</span>
                <span class="info-value">
                  <?php echo htmlspecialchars($paymentMethod); ?> · <?php echo htmlspecialchars($paymentStatus); ?>
                </span>
              </div>
              <div class="info-row">
                <span class="info-label">Placed on</span>
                <span class="info-value"><?php echo htmlspecialchars(date('d M Y, h:i A', $placedAtTs)); ?></span>
              </div>
              <?php if (!empty($order['cancelled_at']) && strcasecmp($orderStatusDb,'Cancelled')===0): ?>
                <div class="info-row">
                  <span class="info-label">Cancelled on</span>
                  <span class="info-value"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($order['cancelled_at']))); ?></span>
                </div>
              <?php endif; ?>
            </div>

            <div class="info-card">
              <div class="info-card-title">
                <span>Delivery details</span>
                <span class="info-tag">Shipping</span>
              </div>
              <div class="info-row">
                <span class="info-label">Receiver</span>
                <span class="info-value"><?php echo htmlspecialchars($shippingName); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Contact</span>
                <span class="info-value"><?php echo htmlspecialchars($shippingPhone); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Address</span>
                <span class="info-value"><?php echo htmlspecialchars($shippingLine ?: 'Not available'); ?></span>
              </div>
            </div>
          </div>

          <div class="product-row">
            <?php if (!empty($order['product_image'])): ?>
              <img src="<?php echo htmlspecialchars($order['product_image']); ?>" alt="" class="product-img">
            <?php else: ?>
              <div class="product-img" style="display:flex;align-items:center;justify-content:center;font-size:12px;color:#9ca3af;">
                Product
              </div>
            <?php endif; ?>
            <div class="product-info">
              <div class="product-name">
                <?php echo htmlspecialchars($order['product_name'] ?? 'Product not found'); ?>
              </div>
              <div class="product-price">
                ₹<?php echo number_format((float)$order['product_price'], 2); ?>
                <span style="font-size:11px;color:#6b7280;">× <?php echo $quantity; ?></span>
              </div>
              <div class="product-badges">
                <span class="badge">Order ID: #<?php echo htmlspecialchars($orderId); ?></span>
                <span class="badge">Code: <?php echo htmlspecialchars($assistantOrderCode); ?></span>
                <span class="badge"><?php echo htmlspecialchars($paymentMethod); ?></span>
              </div>
              <div class="product-meta-note">
                Use this page to share order details or track progress through your Kartify assistant.
              </div>
            </div>
          </div>

          <div class="timeline-card">
            <div class="timeline-title">
              <span>Tracking timeline</span>
              <span class="timeline-sub">
                Last update: <strong><?php echo htmlspecialchars(date('d M Y, h:i A', $now)); ?></strong>
              </span>
            </div>
            <div class="timeline">
              <?php foreach ($steps as $step):
                $done    = isStepDoneLocal($step, $currentStatus, $orderStatusDb);
                $current = ($step === $currentStatus);
              ?>
              <div class="step <?php echo $done ? 'completed' : ''; ?> <?php echo $current ? 'current' : ''; ?>">
                <div class="step-marker"></div>
                <div class="step-label"><?php echo htmlspecialchars($step); ?></div>
                <?php if ($current): ?>
                  <div class="step-status-text">Current update · <?php echo htmlspecialchars($statusNote); ?></div>
                <?php elseif ($done): ?>
                  <div class="step-status-text">Completed</div>
                <?php else: ?>
                  <div class="step-status-text">Pending</div>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="status-note">
              Status is calculated from when the order was placed and its current state.
              In a production build, you can replace this with live courier tracking events.
            </div>
          </div>

          <?php if (strcasecmp($orderStatusDb, 'Cancelled') === 0 && !empty($order['cancel_reason'])): ?>
            <div class="cancel-card" style="margin-top:12px;">
              <h3>Cancellation details</h3>
              <div><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($order['cancel_reason'])); ?></div>
            </div>
          <?php elseif (strcasecmp($orderStatusDb, 'Cancelled') !== 0): ?>
            <form method="post" class="cancel-card">
              <h3>Cancel this order?</h3>
              <p>
                You can cancel this order before it is shipped. Please share the reason so we can improve our service.
              </p>
              <textarea name="cancel_reason" placeholder="Write your reason (for example: ordered by mistake, found a better price, address issue, etc.)" required></textarea>
              <div class="cancel-actions">
                <button type="submit" class="btn-cancel">Cancel order</button>
                <span class="cancel-note">
                  Once cancelled, the order will not be delivered and the status will appear as <strong>Order cancelled</strong> in My Orders.
                </span>
              </div>
              <input type="hidden" name="cancel_order_id" value="<?php echo (int)$orderId; ?>">
            </form>
          <?php endif; ?>

        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</main>

<footer class="footer">
  <div class="footer-inner">
    <div>© <?php echo date('Y'); ?> Kartify. All rights reserved.</div>
    <div>View orders, track status and manage cancellations from this page.</div>
  </div>
</footer>

</body>
</html>
