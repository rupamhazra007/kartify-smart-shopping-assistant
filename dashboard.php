<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db.php';

// Fetch products from DB
$result = $conn->query("SELECT * FROM products");

// Helper: guess category from product name
function guessCategory($name) {
    $n = strtolower($name);

    // ⭐ Mobiles:
    if (
        strpos($n, 'phone') !== false ||
        strpos($n, 'mobile') !== false ||
        strpos($n, ' 5g') !== false ||
        strpos($n, 'nova') !== false ||
        strpos($n, 'blaze') !== false ||
        strpos($n, 'note') !== false ||
        strpos($n, 'neo') !== false ||
        strpos($n, 'edge ') !== false ||
        strpos($n, 'pixelview') !== false ||
        strpos($n, 'aura c3') !== false ||
        strpos($n, 'turbo g') !== false
    ) {
        return 'mobiles';
    }

    // ⭐ Laptops:
    if (
        strpos($n, 'laptop') !== false ||
        strpos($n, 'notebook') !== false ||
        strpos($n, 'macbook') !== false ||
        strpos($n, 'kartifybook') !== false ||
        strpos($n, 'gaming r15') !== false ||
        (strpos($n, 'office') !== false && strpos($n, 'mate') !== false) ||
        strpos($n, 'creator studio') !== false ||
        strpos($n, 'student edition') !== false ||
        strpos($n, 'ryzen') !== false
    ) {
        return 'laptops';
    }

    // ⭐ TVs & Monitors:
    if (
        strpos($n, ' tv') !== false ||
        substr($n, -2) === 'tv' ||
        strpos($n, 'television') !== false ||
        strpos($n, 'monitor') !== false ||
        strpos($n, 'vision') !== false
    ) {
        return 'tvs';
    }

    // ⭐ Wallets:
    if (
        strpos($n, 'wallet') !== false ||
        strpos($n, 'card holder') !== false ||
        strpos($n, 'card case') !== false ||
        strpos($n, 'urbanfold') !== false
    ) {
        return 'wallets';
    }

    // ⭐ Watches & Bands:
    if (
        strpos($n, 'watch') !== false ||
        strpos($n, 'band') !== false ||
        strpos($n, 'smartwatch') !== false ||
        strpos($n, 'chronoactive') !== false
    ) {
        return 'wearables';
    }

    return 'others';
}

$userName = $_SESSION['user_name'] ?? 'User';

// ---------- My Orders (JOIN with products to get image, name, price) ----------
$user_id = (int)$_SESSION['user_id'];

$orderSql = "
    SELECT 
        o.id,
        o.product_id,
        o.created_at,
        o.order_status,
        p.name  AS product_name,
        p.price AS product_price,
        p.image_url
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";

$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param('i', $user_id);
$orderStmt->execute();
$ordersResult = $orderStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kartify - Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #2563eb;
      --primary-soft: #eff6ff;
      --accent: #f97316;
      --bg: #f3f4f6;
      --card-bg: #ffffff;
      --border-soft: #e5e7eb;
      --text-main: #0f172a;
      --text-soft: #6b7280;
      --danger: #dc2626;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      /* Background full clear C.jpg */
      background:
        linear-gradient(rgba(255,255,255,0.06), rgba(248,250,252,0.10)),
        url('images/C.jpg') center center / cover no-repeat;
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;

      /* ✨ Smooth, satisfying intro */
      opacity: 0;
      transform: translateY(12px) scale(0.99);
      filter: blur(6px);
      animation: softIntro 0.75s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* ---------- Top Navbar ---------- */

    .top-bar {
      position: sticky;
      top: 0;
      z-index: 30;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      background: rgba(255, 255, 255, 0.93);
      border-bottom: 1px solid rgba(226, 232, 240, 0.9);
      box-shadow: 0 10px 25px rgba(15,23,42,0.15);
    }

    .top-bar-inner {
      max-width: 1120px;
      margin: 0 auto;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-right: 8px;
    }

    .logo-icon {
      width: 30px;
      height: 30px;
      border-radius: 11px;
      background: conic-gradient(from 160deg, #22c55e, #22d3ee, #3b82f6, #eab308, #f97316, #22c55e);
      display: flex;
      align-items: center;
      justify-content: center;
      animation: spin-slow 16s linear infinite;
      box-shadow: 0 0 16px rgba(37, 99, 235, 0.4);
      flex-shrink: 0;
    }

    .logo-icon-inner {
      width: 20px;
      height: 20px;
      border-radius: 8px;
      background: radial-gradient(circle at 0 0, #bfdbfe, #1d4ed8 55%, #111827 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      color: #f9fafb;
    }

    .logo-text {
      font-weight: 700;
      letter-spacing: 0.06em;
      font-size: 18px;
      color: #111827;
    }

    .logo-dot {
      color: var(--accent);
    }

    .search-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .search-box {
      flex: 1;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 8px 10px 8px 32px;
      border-radius: 999px;
      border: 1px solid #d1d5db;
      background: rgba(249,250,251,0.95);
      font-size: 13px;
      color: #111827;
      transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, transform 0.18s ease;
    }

    .search-box input::placeholder{
      color:#9ca3af;
    }

    .search-box input:focus {
      outline: none;
      border-color: rgba(37, 99, 235, 0.85);
      background: #ffffff;
      transform: translateY(-1px);
      box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.16), 0 0 0 4px rgba(191, 219, 254, 0.8);
    }

    .search-icon {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 14px;
      color: #9ca3af;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-left: 6px;
    }

    .pill-btn {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 6px 10px;
      font-size: 11px;
      border-radius: 999px;
      border: 1px solid #e5e7eb;
      background: rgba(255,255,255,0.95);
      color: #374151;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
      backdrop-filter: blur(6px);
    }

    .pill-btn:hover {
      background: #f9fafb;
      box-shadow: 0 4px 10px rgba(148, 163, 184, 0.3);
      transform: translateY(-1px);
    }

    .user-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 9px;
      border-radius: 999px;
      background: rgba(249,250,251,0.95);
      border: 1px solid #e5e7eb;
      font-size: 11px;
      color: #4b5563;
      max-width: 170px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      backdrop-filter: blur(6px);
    }

    .user-avatar {
      width: 22px;
      height: 22px;
      border-radius: 999px;
      background: radial-gradient(circle at 30% 0, #22c55e, #2563eb);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 600;
      color: #f9fafb;
      flex-shrink: 0;
    }

    /* ---------- Category Bar ---------- */

    .category-bar {
      border-bottom: 1px solid rgba(229, 231, 235, 0.8);
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 8px 20px rgba(15,23,42,0.1);
    }

    .category-inner {
      max-width: 1120px;
      margin: 0 auto;
      padding: 8px 16px;
      display: flex;
      gap: 8px;
      overflow-x: auto;
      scrollbar-width: thin;
    }

    .cat-chip {
      padding: 6px 11px;
      border-radius: 999px;
      border: 1px solid rgba(229,231,235,0.9);
      font-size: 12px;
      color: #4b5563;
      background: rgba(249,250,251,0.94);
      cursor: pointer;
      white-space: nowrap;
      transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
      backdrop-filter: blur(6px);
    }

    .cat-chip.active {
      background: var(--primary-soft);
      border-color: #2563eb;
      color: #1d4ed8;
      transform: translateY(-1px);
      box-shadow: 0 6px 14px rgba(37,99,235,0.3);
    }

    .cat-chip:hover {
      background: #ffffff;
      border-color: #cbd5f5;
    }

    /* ---------- Main Content ---------- */

    .main {
      flex: 1;
    }

    .content-shell {
      max-width: 1120px;
      margin: 16px auto 22px;
      padding: 0 16px 20px;
      display: grid;
      grid-template-columns: 240px minmax(0, 1fr);
      gap: 16px;
    }

    /* Sidebar filters */

    .sidebar {
      border-radius: 18px;
      background: rgba(255,255,255,0.9);
      border: 1px solid rgba(229,231,235,0.8);
      padding: 12px 12px 10px;
      height: fit-content;
      box-shadow: 0 14px 30px rgba(148, 163, 184, 0.35);
    }

    .side-title {
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 4px;
      color: #111827;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .side-sub {
      font-size: 11px;
      color: var(--text-soft);
      margin-bottom: 10px;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .filter-block {
      margin-bottom: 10px;
    }

    .filter-header {
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 4px;
      color: #111827;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .filter-option {
      font-size: 11px;
      color: #4b5563;
      display: flex;
      align-items: center;
      gap: 4px;
      margin-bottom: 3px;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .filter-option input {
      accent-color: #2563eb;
    }

    .ai-hint {
      font-size: 11px;
      margin-top: 6px;
      padding: 6px 7px;
      border-radius: 10px;
      background: rgba(239,246,255,0.95);
      border: 1px dashed #bfdbfe;
      color: #1d4ed8;
    }

    /* ---------- Products area ---------- */

    .products-wrap {
      border-radius: 20px;
      background: rgba(255,255,255,0.9);
      border: 1px solid rgba(229,231,235,0.9);
      padding: 14px 14px 12px;
      box-shadow: 0 18px 40px rgba(148, 163, 184, 0.45);
      min-height: 200px;
      animation: blockFadeUp 0.6s ease-out 0.24s both;
    }

    .products-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .products-title {
      font-size: 14px;
      font-weight: 600;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .products-sub {
      font-size: 11px;
      color: var(--text-soft);
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .result-count {
      font-size: 11px;
      color: var(--text-soft);
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 16px;          /* 🔹 slightly bigger gap between cards */
      margin-top: 8px;
    }

    .card {
      position: relative;
      border-radius: 16px;
      background: rgba(255,255,255,0.95);
      border: 1px solid rgba(229,231,235,0.9);
      padding: 9px 9px 10px;
      margin: 2px;        /* 🔹 small margin all around (up/down/left/right) */
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-size: 12px;
      cursor: pointer;
      transition:
        transform 0.24s ease,
        box-shadow 0.24s ease,
        border-color 0.18s ease,
        background 0.18s ease,
        opacity 0.4s ease;
      overflow: hidden;

      /* scroll reveal base state */
      opacity: 0;
      transform: translateY(18px) scale(0.985);
    }

    .card.card-visible {
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    .card::before {
      content: "";
      position: absolute;
      top: 0;
      left: -60%;
      width: 40%;
      height: 100%;
      background: linear-gradient(120deg, rgba(255,255,255,0.0), rgba(255,255,255,0.55), rgba(255,255,255,0));
      transform: skewX(-18deg);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease;
    }

    .card:hover::before {
      opacity: 1;
      animation: sweep 0.8s ease-out;
    }

    .card:hover {
      transform: translateY(-4px) scale(1.02);
      border-color: #bfdbfe;
      background: rgba(255,255,255,0.98);
      box-shadow: 0 18px 36px rgba(37, 99, 235, 0.4);
    }

    .card-img {
      width: 100%;
      height: 180px;
      border-radius: 12px;
      object-fit: contain;
      background-color: #ffffff;
      border: 1px solid rgba(226,232,240,0.9);
      image-rendering: -webkit-optimize-contrast;
      image-rendering: crisp-edges;
    }

    .name {
      font-weight: 600;
      color: #111827;
      min-height: 34px;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .desc {
      font-size: 11px;
      color: var(--text-soft);
      min-height: 30px;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .price-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 2px;
    }

    .price {
      font-weight: 700;
      color: #2563eb;
      font-size: 13px;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .price small {
      font-size: 11px;
      color: #9ca3af;
      text-decoration: line-through;
      margin-left: 4px;
    }

    .badge {
      font-size: 10px;
      padding: 2px 6px;
      border-radius: 999px;
      background: #ecfdf5;
      border: 1px solid #22c55e;
      color: #15803d;
    }

    /* 🔴 Cancelled badge style */
    .badge.badge-cancelled {
      background: #fef2f2;
      border-color: #fecaca;
      color: #b91c1c;
    }

    .card-footer {
      margin-top: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 6px;
    }

    .rating {
      font-size: 11px;
      color: #111827;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    .rating span {
      font-size: 10px;
      color: #6b7280;
    }

    .buy-btn {
      padding: 6px 9px;
      border-radius: 999px;
      border: none;
      font-size: 11px;
      font-weight: 500;
      cursor: pointer;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #f9fafb;
      box-shadow: 0 8px 18px rgba(37, 99, 235, 0.35);
      transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .buy-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 11px 24px rgba(37, 99, 235, 0.45);
    }

    .buy-btn:active {
      transform: translateY(0);
      box-shadow: 0 6px 14px rgba(37, 99, 235, 0.3);
    }

    .no-results {
      font-size: 13px;
      color: var(--text-soft);
      padding: 20px 6px 4px;
      text-shadow: 0 1px 1px rgba(255,255,255,0.9);
    }

    /* ---------- Footer ---------- */

    .footer {
      border-top: 1px solid rgba(229,231,235,0.9);
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      padding: 8px 16px 10px;
      font-size: 11px;
      color: var(--text-soft);
      box-shadow: 0 -8px 20px rgba(15,23,42,0.18);
    }

    .footer-inner {
      max-width: 1120px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      gap: 8px;
      flex-wrap: wrap;
    }

    /* ---------- Animations ---------- */

    @keyframes softIntro {
      0% {
        opacity: 0;
        transform: translateY(18px) scale(0.985);
        filter: blur(10px);
      }
      55% {
        opacity: 1;
        transform: translateY(0) scale(1.002);
        filter: blur(0);
      }
      100% {
        opacity: 1;
        transform: translateY(0) scale(1);
        filter: blur(0);
      }
    }

    @keyframes blockFadeUp {
      0% {
        opacity: 0;
        transform: translateY(14px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes sweep {
      0% {
        transform: translateX(-80%) skewX(-18deg);
        opacity: 0;
      }
      40% {
        opacity: 1;
      }
      100% {
        transform: translateX(220%) skewX(-18deg);
        opacity: 0;
      }
    }

    @keyframes spin-slow {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }

    /* ---------- Responsive ---------- */

    @media (max-width: 960px) {
      .content-shell {
        grid-template-columns: minmax(0, 1fr);
      }
    }

    @media (max-width: 780px) {
      .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .top-bar-inner {
        flex-wrap: wrap;
      }
    }

    @media (max-width: 520px) {
      .grid {
        grid-template-columns: minmax(0, 1fr);
      }
    }
  </style>
</head>
<body>

<!-- TOP NAV -->
<header class="top-bar">
  <div class="top-bar-inner">
    <a href="index.php" class="logo">
      <div class="logo-icon">
        <div class="logo-icon-inner">K</div>
      </div>
      <div class="logo-text">KARTIFY<span class="logo-dot">.</span></div>
    </a>

    <div class="search-wrap">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Search for mobiles, TVs, wallets, monitors...">
      </div>
    </div>

    <div class="nav-actions">
      <div class="user-pill">
        <div class="user-avatar">
          <?php
            $initial = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
            echo htmlspecialchars($initial);
          ?>
        </div>
        <span>Hello, <?php echo htmlspecialchars($userName); ?> 👋</span>
      </div>

      <!-- Back to home button -->
      <a href="index.php" class="pill-btn">
        <span>🏠</span>
        <span>Back to home</span>
      </a>

      <a href="logout.php" class="pill-btn">Logout</a>
    </div>
  </div>
</header>

<!-- CATEGORY BAR -->
<div class="category-bar">
  <div class="category-inner">
    <button class="cat-chip active" data-category="all">All Products</button>
    <button class="cat-chip" data-category="mobiles">Mobiles</button>
    <button class="cat-chip" data-category="laptops">Laptops</button>
    <button class="cat-chip" data-category="tvs">TVs &amp; Monitors</button>
    <button class="cat-chip" data-category="wallets">Wallets</button>
    <button class="cat-chip" data-category="wearables">Watches &amp; Bands</button>
    <button class="cat-chip" data-category="others">More</button>
  </div>
</div>

<main class="main">
  <div class="content-shell">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="side-title">Filters</div>
      <div class="side-sub">Quick options</div>

      <div class="filter-block">
        <div class="filter-header">Price</div>
        <label class="filter-option">
          <input type="checkbox" class="price-filter" value="under5">
          Under ₹5,000
        </label>
        <label class="filter-option">
          <input type="checkbox" class="price-filter" value="5to20">
          ₹5,000 - ₹20,000
        </label>
        <label class="filter-option">
          <input type="checkbox" class="price-filter" value="above20">
          Above ₹20,000
        </label>
      </div>

      <div class="filter-block">
        <div class="filter-header">Category</div>
        <label class="filter-option">
          <input type="checkbox" class="side-cat-filter" value="mobiles">
          Mobiles
        </label>
        <label class="filter-option">
          <input type="checkbox" class="side-cat-filter" value="tvs">
          TVs &amp; Monitors
        </label>
        <label class="filter-option">
          <input type="checkbox" class="side-cat-filter" value="wallets">
          Wallets &amp; Accessories
        </label>
      </div>

      <div class="ai-hint">
        🤖 Tip: Your AI Support Bot can explain these filters or suggest products to the user.
      </div>
    </aside>

    <!-- Products -->
    <section class="products-wrap">
      <div class="products-header">
        <div>
          <div class="products-title">Available Products</div>
          <div class="products-sub">Browse and use “Buy Now” to place your order.</div>
        </div>
        <div class="result-count">
          <span id="resultCount"></span>
        </div>
      </div>

      <div class="grid" id="productGrid">
        <?php
        $count = 0;
        while ($row = $result->fetch_assoc()):
            $count++;
            $name = $row['name'] ?? 'Product';
            $desc = $row['description'] ?? '';
            $price = isset($row['price']) ? (float)$row['price'] : 0;
            $img = $row['image_url'] ?? '';
            $category = guessCategory($name);

            // Approx MRP for visual +15%
            $mrp = $price > 0 ? $price * 1.15 : 0;
        ?>
          <div class="card"
               data-category="<?php echo htmlspecialchars($category); ?>"
               data-name="<?php echo htmlspecialchars(strtolower($name . ' ' . $desc)); ?>"
               data-price="<?php echo htmlspecialchars($price); ?>">
            <?php if ($img): ?>
              <img src="<?php echo htmlspecialchars($img); ?>" alt="" class="card-img">
            <?php else: ?>
              <!-- fallback using C.jpg as generic product visual -->
              <div class="card-img" style="
                   background-image:
                     linear-gradient(to bottom, rgba(15,23,42,0.2), rgba(15,23,42,0.55)),
                     url('images/C.jpg');
                   background-size: cover;
                   background-position: center;
                   display:flex;align-items:flex-end;justify-content:flex-start;
                   padding:6px 8px;
                   font-size:11px;color:#e5e7eb;font-weight:500;
                   border-radius:12px;">
                Kartify Product
              </div>
            <?php endif; ?>

            <div class="name"><?php echo htmlspecialchars($name); ?></div>
            <div class="desc"><?php echo htmlspecialchars($desc); ?></div>

            <div class="price-row">
              <div class="price">
                ₹<?php echo number_format($price, 2); ?>
                <?php if ($mrp > 0): ?>
                  <small>₹<?php echo number_format($mrp, 2); ?></small>
                <?php endif; ?>
              </div>
              <div class="badge">Exclusive offer</div>
            </div>

            <div class="card-footer">
              <div class="rating">
                ⭐ 4.3 <span>· Customer rating</span>
              </div>
              <form action="location.php" method="post" style="margin:0;">
                <input type="hidden" name="product_id" value="<?php echo (int)$row['id']; ?>">
                <button type="submit" class="buy-btn">Buy Now</button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <?php if ($count === 0): ?>
        <div class="no-results">
          No products found in your database yet. Add rows to the <strong>products</strong> table (name, price, image_url, description)
          and they will appear here automatically.
        </div>
      <?php endif; ?>
    </section>

    <!-- MY ORDERS SECTION (full width) -->
    <section class="products-wrap" style="grid-column: 1 / -1; margin-top: 10px;">
      <div class="products-header">
        <div>
          <div class="products-title">My Orders</div>
          <div class="products-sub">
            All orders you placed in Kartify. Click a card to view full status.
          </div>
        </div>
      </div>

      <?php if ($ordersResult->num_rows === 0): ?>
        <div class="no-results">
          You haven’t placed any orders yet. Buy a product from above to see it here.
        </div>
      <?php else: ?>
        <div class="grid">
          <?php while ($o = $ordersResult->fetch_assoc()):
              $statusRaw   = trim($o['order_status'] ?? '');
              if ($statusRaw === '') $statusRaw = 'Order placed';
              $isCancelled = (strcasecmp($statusRaw, 'Cancelled') === 0);
              $badgeText   = $isCancelled ? 'Order cancelled' : 'Order placed';
          ?>
            <!-- 🔗 Entire order card clickable to status.php -->
            <a href="status.php?order_id=<?php echo (int)$o['id']; ?>" class="card" style="text-decoration:none;color:inherit;">
              <?php if (!empty($o['image_url'])): ?>
                <!-- Real product image from products.image_url -->
                <img src="<?php echo htmlspecialchars($o['image_url']); ?>" alt="" class="card-img">
              <?php else: ?>
                <!-- Fallback: use C.jpg as background -->
                <div class="card-img" style="
                     background-image:
                       linear-gradient(to bottom, rgba(15,23,42,0.2), rgba(15,23,42,0.6)),
                       url('images/C.jpg');
                     background-size: cover;
                     background-position: center;
                     display:flex;align-items:flex-end;justify-content:flex-start;
                     padding:6px 8px;
                     font-size:11px;color:#e5e7eb;font-weight:500;
                     border-radius:12px;">
                  <?php echo htmlspecialchars($o['product_name']); ?>
                </div>
              <?php endif; ?>

              <div class="name">
                <?php echo htmlspecialchars($o['product_name']); ?>
              </div>
              <div class="desc">
                Order ID #<?php echo (int)$o['id']; ?> ·
                <?php echo $isCancelled ? 'Order cancelled' : 'Tap to view full status'; ?>
              </div>

              <div class="price-row">
                <div class="price">
                  ₹<?php echo number_format((float)$o['product_price'], 2); ?>
                </div>
                <div class="badge <?php echo $isCancelled ? 'badge-cancelled' : ''; ?>">
                  <?php echo htmlspecialchars($badgeText); ?>
                </div>
              </div>

              <div class="card-footer">
                <div class="rating">
                  📦 Placed on
                  <span>
                    <?php
                      if (!empty($o['created_at'])) {
                        echo htmlspecialchars(date('d M Y, h:i A', strtotime($o['created_at'])));
                      } else {
                        echo 'N/A';
                      }
                    ?>
                  </span>
                </div>
              </div>
            </a>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </section>

  </div>
</main>

<footer class="footer">
  <div class="footer-inner">
    <div>© <?php echo date('Y'); ?> Kartify Dashboard</div>
    <div>Crafted with ❤️ for a smooth shopping experience.</div>
  </div>
</footer>

<script>
  const searchInput     = document.getElementById('searchInput');
  const productGrid     = document.getElementById('productGrid');
  const cards           = productGrid ? Array.from(productGrid.getElementsByClassName('card')) : [];
  const catChips        = Array.from(document.getElementsByClassName('cat-chip'));
  const resultCount     = document.getElementById('resultCount');
  const priceFilters    = Array.from(document.getElementsByClassName('price-filter'));
  const sideCatFilters  = Array.from(document.getElementsByClassName('side-cat-filter'));

  let activeCategory = 'all';

  function matchPrice(price, selectedRanges) {
    if (selectedRanges.length === 0) return true;
    if (isNaN(price) || price <= 0) return false;

    let ok = false;
    selectedRanges.forEach(range => {
      if (range === 'under5' && price < 5000) ok = true;
      if (range === '5to20' && price >= 5000 && price <= 20000) ok = true;
      if (range === 'above20' && price > 20000) ok = true;
    });
    return ok;
  }

  function applyFilters() {
    const query = (searchInput?.value || '').toLowerCase().trim();
    const selectedPrice = priceFilters
      .filter(cb => cb.checked)
      .map(cb => cb.value);

    const selectedSideCats = sideCatFilters
      .filter(cb => cb.checked)
      .map(cb => cb.value);

    let visible = 0;

    cards.forEach(card => {
      const cardCat   = card.getAttribute('data-category') || 'others';
      const cardName  = card.getAttribute('data-name') || '';
      const priceRaw  = parseFloat(card.getAttribute('data-price') || '0');

      let matchCategory = true;
      if (selectedSideCats.length > 0) {
        matchCategory = selectedSideCats.includes(cardCat);
      } else {
        matchCategory = (activeCategory === 'all') || (cardCat === activeCategory);
      }

      const matchSearch = !query || cardName.includes(query);
      const matchPriceFlag = matchPrice(priceRaw, selectedPrice);

      if (matchCategory && matchSearch && matchPriceFlag) {
        card.style.display = '';
        visible++;
      } else {
        card.style.display = 'none';
      }
    });

    if (resultCount) {
      resultCount.textContent = visible + " product" + (visible === 1 ? "" : "s") + " found";
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }

  catChips.forEach(chip => {
    chip.addEventListener('click', () => {
      catChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activeCategory = chip.getAttribute('data-category') || 'all';
      applyFilters();
    });
  });

  priceFilters.forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  sideCatFilters.forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  // Initial count
  applyFilters();

  // ---------- Scroll reveal for all cards (products + orders) ----------
  document.addEventListener('DOMContentLoaded', () => {
    const allCards = document.querySelectorAll('.card');

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('card-visible');
            obs.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });

      allCards.forEach(card => observer.observe(card));
    } else {
      allCards.forEach(card => card.classList.add('card-visible'));
    }
  });
</script>
</body>
</html>
