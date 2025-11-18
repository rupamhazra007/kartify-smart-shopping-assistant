<?php
session_start();

// If not logged in, block API
if (empty($_SESSION['user_id'])) {
    header("Content-Type: application/json");
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

require 'db.php';

// 🔐 Load secret Gemini API key from config.php
// config.php should define:  $GEMINI_API_KEY = "YOUR_REAL_KEY";
require __DIR__ . '/config.php';

if (empty($GEMINI_API_KEY)) {
    header("Content-Type: application/json");
    echo json_encode(["error" => "Server misconfigured: missing API key."]);
    exit;
}

$apiKey   = $GEMINI_API_KEY;
$user_id  = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? "User";

header("Content-Type: application/json");

// read incoming JSON
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);
$usr  = trim($data["message"] ?? "");

if ($usr === "") {
    echo json_encode(["error" => "No message provided"]);
    exit;
}

/**
 * Detect order id / KARTIFY code from message
 * and return a formatted English reply from DB.
 * Returns null if it does not look like an order query.
 *
 * NOTE: Uses orders.order_status:
 *  - CANCELLED  => clearly shows cancelled, no delivery
 *  - PLACED/empty => uses timeline logic based on created_at
 *  - anything else => shown as-is, with timeline-style note
 */
function kartify_order_reply_from_message(string $text, int $user_id, mysqli $conn): ?string {
    $text = trim($text);

    // KARTIFY-000123 pattern
    if (preg_match('/\bKARTIFY-(\d{1,10})\b/i', $text, $m)) {
        $orderId = (int)$m[1];
    }
    // "order 5", "order #5", "track 5", "status 5"
    elseif (preg_match('/\b(order|track|status)\s*#?\s*(\d{1,10})\b/i', $text, $m)) {
        $orderId = (int)$m[2];
    }
    // Just a number – last fallback
    elseif (preg_match('/\b(\d{1,10})\b/', $text, $m)) {
        $orderId = (int)$m[1];
    } else {
        return null;
    }

    if ($orderId <= 0) {
        return "This order ID does not look valid. Please double-check it and send again.";
    }

    // Fetch order + product for this user
    $sql = "
        SELECT 
            o.*,
            p.name      AS product_name,
            p.price     AS product_price
        FROM orders o
        LEFT JOIN products p ON o.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return "I tried to look up your order, but there was an internal server error. Please try again in a moment.";
    }

    $stmt->bind_param('ii', $orderId, $user_id);
    $stmt->execute();
    $res   = $stmt->get_result();
    $order = $res->fetch_assoc();
    $stmt->close();

    if (!$order) {
        return "I could not find any order with ID #{$orderId} linked to your account.\n"
             . "Please confirm the ID from your My Orders page and share it again.";
    }

    // ---------- Base fields ----------
    $placedAtTs = !empty($order['created_at']) ? strtotime($order['created_at']) : time();
    $now        = time();
    $diffHours  = ($now - $placedAtTs) / 3600;

    $assistantCode = 'KARTIFY-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    $product       = $order['product_name'] ?? 'Product';
    $qty           = (int)($order['quantity'] ?? 1);
    if ($qty <= 0) $qty = 1;
    $price   = (float)($order['product_price'] ?? 0);
    $amount  = $price * $qty;

    $paymentMethod = $order['payment_method'] ?? 'Online payment';
    $paymentStatus = $order['payment_status'] ?? 'Paid';

    $placedStr = date('d M Y, h:i A', $placedAtTs);

    // Shipping info (optional)
    $shipName  = $order['shipping_name']    ?? '';
    $shipPhone = $order['shipping_phone']   ?? '';
    $shipAddr  = $order['shipping_address'] ?? '';
    $shipCity  = $order['shipping_city']    ?? '';
    $shipState = $order['shipping_state']   ?? '';
    $shipZip   = $order['shipping_zip']     ?? '';
    $shipLine  = trim($shipAddr . ', ' . $shipCity . ', ' . $shipState . ' ' . $shipZip, " ,");

    // ---------- Timeline-based status (like status.php) ----------
    if ($diffHours < 1) {
        $timelineStatus = "Order placed";
        $timelineNote   = "We have received your order and it is currently being processed.";
    } elseif ($diffHours < 6) {
        $timelineStatus = "Packed";
        $timelineNote   = "Your item has been packed and will be handed over to the courier shortly.";
    } elseif ($diffHours < 24) {
        $timelineStatus = "Shipped";
        $timelineNote   = "Your package is in transit with our delivery partner.";
    } elseif ($diffHours < 48) {
        $timelineStatus = "In transit";
        $timelineNote   = "Your package is travelling between courier hubs.";
    } elseif ($diffHours < 72) {
        $timelineStatus = "Out for delivery";
        $timelineNote   = "The delivery agent is expected to attempt delivery today.";
    } else {
        $timelineStatus = "Delivered";
        $timelineNote   = "According to our system, this order is considered delivered.";
    }

    // Expected delivery – 3 days from placement (approx)
    $expectedTs   = $placedAtTs + (3 * 24 * 3600);
    $expectedDate = date('d M Y', $expectedTs);

    // ---------- Use order_status column if present ----------
    $explicitStatus = strtoupper(trim($order['order_status'] ?? ''));

    if ($explicitStatus === 'CANCELLED') {
        $currentStatus = "Cancelled";
        $statusNote    = "This order has been cancelled. There will be no delivery for this shipment.";
    } elseif ($explicitStatus === 'PLACED' || $explicitStatus === '') {
        // Default to timeline-based logic
        $currentStatus = $timelineStatus;
        $statusNote    = $timelineNote;
    } else {
        // Any other custom status from DB (e.g. RETURNED, FAILED, etc.)
        $currentStatus = ucwords(strtolower($explicitStatus));
        $statusNote    = $timelineNote;
    }

    // ---------- Build reply (English only) ----------
    $lines = [];

    $lines[] = "Here is the latest status of your Kartify order:";
    $lines[] = "";
    $lines[] = "• Order ID: #{$orderId} ({$assistantCode})";
    $lines[] = "• Product: {$product}";
    $lines[] = "• Quantity: {$qty}";
    $lines[] = "• Amount: ₹" . number_format($amount, 2);
    $lines[] = "• Placed on: {$placedStr}";
    $lines[] = "• Payment: {$paymentMethod} · {$paymentStatus}";
    $lines[] = "";

    if ($explicitStatus === 'CANCELLED') {
        $lines[] = "✅ Current status: CANCELLED";
        $lines[] = $statusNote;
    } else {
        $lines[] = "✅ Current status: {$currentStatus}";
        $lines[] = $statusNote;
        $lines[] = "Estimated delivery (approx.): {$expectedDate}";
    }

    if ($shipLine !== '' || $shipName !== '' || $shipPhone !== '') {
        $lines[] = "";
        $lines[] = "Delivery details:";
        if ($shipName !== '') {
            $lines[] = "• Receiver: {$shipName}";
        }
        if ($shipPhone !== '') {
            $lines[] = "• Contact: {$shipPhone}";
        }
        if ($shipLine !== '') {
            $lines[] = "• Address: {$shipLine}";
        }
    }

    $lines[] = "";
    $lines[] = "You can also open the order details page in My Orders to see the same status and full tracking timeline.";

    return implode("\n", $lines);
}

// 1️⃣ First: if this looks like an order query, answer directly from DB
$orderReply = kartify_order_reply_from_message($usr, $user_id, $conn);

if ($orderReply !== null) {
    echo json_encode(["reply" => $orderReply]);
    exit;
}

// 2️⃣ Otherwise: call Gemini with a strong, professional system prompt

$system = "You are Kartify's virtual customer support assistant.\n\n".
"About Kartify:\n".
"- Kartify is a modern Indian e-commerce platform where customers place real orders for products like electronics, fashion and accessories.\n".
"- You are part of the official Kartify support team.\n\n".
"Your style:\n".
"- Always reply in clear, polished, natural ENGLISH only, even if the user writes in another language.\n".
"- Be friendly, calm, and professional.\n".
"- Explain things step by step when needed, but avoid unnecessary long paragraphs.\n".
"- Use short paragraphs and bullet points to keep answers easy to read.\n".
"- Do not mention that you are an AI model or that this is a demo.\n".
"- Speak as \"we\" or \"Kartify\" when referring to the company.\n\n".
"Behaviour rules:\n".
"- Give accurate, logically correct answers based on common e-commerce practices.\n".
"- For policy questions (returns, refunds, delivery, payments), answer like a real support agent of a serious e-commerce brand.\n".
"- If the user asks about an order without an ID, politely ask for the Order ID or KARTIFY code.\n".
"- If anything is uncertain or depends on configuration, say that the exact details can vary and explain the typical behaviour instead of guessing.\n".
"- The user may also ask technical questions about PHP, MySQL or system design; answer those like an experienced developer, but keep the tone friendly.\n";

$payload = [
    "contents" => [[
        "parts" => [
            ["text" => $system],
            ["text" => "Logged in user: {$userName}"],
            ["text" => "User message: {$usr}"],
            ["text" => "Now generate a helpful Kartify support reply in English."]
        ]
    ]]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . urlencode($apiKey);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
]);

$resp = curl_exec($ch);

if ($resp === false) {
    echo json_encode(["error" => "cURL: " . curl_error($ch)]);
    exit;
}

$data = json_decode($resp, true);

if (isset($data["error"])) {
    echo json_encode(["error" => $data["error"]["message"] ?? "Unknown API error"]);
    exit;
}

$reply = $data["candidates"][0]["content"]["parts"][0]["text"] ?? "";

echo json_encode([
    "reply" => $reply ?: "I'm sorry, I could not generate a response right now. Please try again in a moment."
]);