<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userName  = $_SESSION['user_name'] ?? "User";
$initial   = strtoupper(substr($userName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kartify – Support Assistant</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
  :root {
    --primary: #2563eb;
    --primary-deep: #1d4ed8;
    --primary-soft: #eff6ff;
    --accent: #f97316;
    --bg: #e0f2fe;
    --card: #ffffff;
    --border: #e5e7eb;
    --text: #0f172a;
    --text-soft: #6b7280;
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
    min-height: 100vh;
    background:
      radial-gradient(circle at 0 0, #ffffff 0, #e0f2fe 40%, #eef2ff 75%, #fef9c3 100%);
    color: var(--text);
    display: flex;
    flex-direction: column;
    font-size: 14px;
    line-height: 1.6;
    opacity: 0;
    animation: fadeInBody 0.6s ease-out forwards;
  }

  a {
    text-decoration: none;
    color: inherit;
  }

  /* Top bar */
  .top {
    position: sticky;
    top: 0;
    z-index: 20;
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-bottom: 1px solid rgba(209, 213, 219, 0.9);
    box-shadow:
      0 10px 28px rgba(15,23,42,0.18),
      0 0 0 1px rgba(148,163,184,0.22);
  }

  .top-inner {
    max-width: 1040px;
    margin: 0 auto;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }

  .logo-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .logo-icon {
    width: 32px;
    height: 32px;
    border-radius: 11px;
    background: conic-gradient(from 160deg,#22c55e,#22d3ee,#3b82f6,#eab308,#f97316,#22c55e);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: spin-slow 20s linear infinite;
    box-shadow:
      0 0 16px rgba(37,99,235,0.5),
      0 0 0 1px rgba(209,213,219,0.9);
  }

  .logo-icon-inner {
    width: 20px;
    height: 20px;
    border-radius: 7px;
    background: radial-gradient(circle at 0 0,#bfdbfe,#1d4ed8 55%,#020617 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #f9fafb;
  }

  .logo-text-main {
    font-weight: 800;
    letter-spacing: .10em;
    font-size: 18px;
    text-transform: uppercase;
  }

  .logo-dot {
    color: var(--accent);
  }

  .logo-sub {
    font-size: 11px;
    color: var(--text-soft);
  }

  .top-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .user-pill {
    background: rgba(249,250,251,0.95);
    border: 1px solid rgba(229,231,235,0.95);
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-soft);
    max-width: 170px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    backdrop-filter: blur(6px);
  }

  .avatar{
    background: linear-gradient(135deg,#2563eb,#22c55e);
    width: 22px;height: 22px;border-radius: 50%;
    color: white;font-size: 12px;
    display:flex;
    align-items:center;justify-content:center;
    font-weight:bold;
    box-shadow:0 0 8px rgba(37,99,235,0.6);
  }

  .top-btn{
    padding:6px 11px;
    border-radius:999px;
    border:1px solid #e5e7eb;
    background:rgba(255,255,255,0.98);
    font-size:11px;
    color:#374151;
    cursor:pointer;
    transition:background .18s, box-shadow .18s, transform .18s, border-color .18s;
  }

  .top-btn:hover{
    background:#f9fafb;
    box-shadow:0 6px 16px rgba(148,163,184,.45);
    transform:translateY(-1px);
    border-color:#d1d5db;
  }

  main{
    flex:1;
  }

  .shell{
    max-width:1040px;
    margin:20px auto 30px;
    padding:0 16px;
    display:grid;
    grid-template-columns:minmax(0,1.6fr) minmax(0,0.9fr);
    gap:18px;
  }

  /* Chat card */
  .chat-card{
    background:rgba(255,255,255,0.88);
    border-radius:22px;
    border:1px solid rgba(226,232,240,.95);
    box-shadow:
      0 24px 55px rgba(148,163,184,.50),
      0 0 0 1px rgba(148,163,184,.22);
    padding:14px 14px 12px;
    display:flex;
    flex-direction:column;
    height:520px;
    max-height:75vh;
    animation:floatUp .4s ease-out both;
    position:relative;
    overflow:hidden;
    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);
  }

  .chat-card::before{
    content:"";
    position:absolute;
    inset:0;
    background:
      radial-gradient(circle at 0% 0%,rgba(219,234,254,.9),transparent 55%),
      radial-gradient(circle at 100% 100%,rgba(254,243,199,.9),transparent 60%);
    opacity:.5;
    pointer-events:none;
  }

  .chat-inner{
    position:relative;
    z-index:1;
    display:flex;
    flex-direction:column;
    height:100%;
  }

  .chat-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:6px;
    gap:8px;
  }

  .chat-title{
    font-size:15px;
    font-weight:650;
    display:flex;
    align-items:center;
    gap:6px;
    color:#0f172a;
  }

  .chat-badge{
    font-size:10px;
    padding:2px 7px;
    border-radius:999px;
    background:#eff6ff;
    border:1px solid #bfdbfe;
    color:#1d4ed8;
    text-transform:uppercase;
    letter-spacing:.05em;
  }

  .chat-sub{
    font-size:11px;
    color:var(--text-soft);
    margin-top:2px;
  }

  .status-pill{
    font-size:10px;
    padding:4px 9px;
    border-radius:999px;
    background:#ecfdf5;
    border:1px solid #bbf7d0;
    color:#15803d;
    display:inline-flex;
    align-items:center;
    gap:4px;
  }

  .status-dot{
    width:7px;height:7px;border-radius:999px;
    background:#22c55e;
    box-shadow:0 0 10px rgba(34,197,94,.9);
  }

  .chat-box{
    flex:1;
    margin-top:6px;
    border-radius:16px;
    border:1px solid #e5e7eb;
    padding:9px 10px;
    background:
      radial-gradient(circle at top left,#eff6ff 0,#f9fafb 40%);
    overflow-y:auto;
    font-size:13px;
    display:flex;
    flex-direction:column;
    gap:6px;
  }

  .hint{
    font-size:11px;
    color:var(--text-soft);
    margin-bottom:2px;
  }

  .quick-row{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-bottom:4px;
  }

  .chip{
    font-size:11px;
    padding:4px 9px;
    border-radius:999px;
    border:1px solid #d1d5db;
    background:rgba(255,255,255,0.98);
    cursor:pointer;
    transition:background .15s, box-shadow .15s, transform .15s, border-color .15s;
  }

  .chip:hover{
    background:#eff6ff;
    box-shadow:0 6px 16px rgba(148,163,184,.40);
    transform:translateY(-1px);
    border-color:#bfdbfe;
  }

  .msg-wrap{
    display:flex;
    flex-direction:column;
  }

  .msg{
    max-width:78%;
    padding:8px 10px;
    border-radius:14px;
    margin-top:4px;
    white-space:pre-wrap;
    word-wrap:break-word;
    line-height:1.45;
    opacity:0;
    transform:translateY(3px);
    animation:bubbleIn .18s ease-out forwards;
  }

  .bot{
    background:#ffffff;
    border:1px solid #e5e7eb;
    align-self:flex-start;
    box-shadow:0 2px 6px rgba(148,163,184,0.45);
  }

  .you{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#f9fafb;
    border:1px solid #1d4ed8;
    align-self:flex-end;
    box-shadow:0 2px 6px rgba(37,99,235,0.55);
  }

  .meta{
    font-size:10px;
    color:#9ca3af;
    margin-top:1px;
  }

  .typing-bubble{
    max-width:60px;
    padding:6px 8px;
    border-radius:12px;
    background:#ffffff;
    border:1px solid #e5e7eb;
    display:inline-flex;
    align-items:center;
    gap:4px;
    box-shadow:0 2px 6px rgba(148,163,184,0.4);
  }

  .dot{
    width:5px;height:5px;border-radius:999px;
    background:#9ca3af;
    animation:blink 1s infinite ease-in-out;
  }
  .dot:nth-child(2){animation-delay:.15s;}
  .dot:nth-child(3){animation-delay:.3s;}

  form{
    display:flex;
    margin-top:8px;
    gap:8px;
    align-items:flex-end;
  }

  textarea{
    flex:1;
    padding:9px 10px;
    border-radius:16px;
    border:1px solid #d1d5db;
    resize:none;
    min-height:48px;
    max-height:96px;
    font-size:13px;
    font-family:inherit;
    background:#ffffff;
    transition:border-color .18s, box-shadow .18s, background .18s;
  }

  textarea::placeholder{
    color:#9ca3af;
  }

  textarea:focus{
    outline:none;
    border-color:rgba(37,99,235,.9);
    background:#ffffff;
    box-shadow:0 0 0 1px rgba(37,99,235,.16),
               0 0 0 4px rgba(191,219,254,.8);
  }

  button{
    background:radial-gradient(circle at 0 0,#bfdbfe,#60a5fa 35%,#2563eb 100%);
    color:white;
    border:none;
    border-radius:999px;
    padding:9px 14px;
    cursor:pointer;
    font-weight:500;
    font-size:13px;
    display:inline-flex;
    align-items:center;
    gap:6px;
    box-shadow:
      0 12px 26px rgba(37,99,235,.60),
      0 0 0 1px rgba(37,99,235,.80);
    transition:transform .18s, box-shadow .18s, opacity .18s, filter .18s;
  }

  button:hover{
    transform:translateY(-1px);
    box-shadow:
      0 16px 32px rgba(37,99,235,.72),
      0 0 0 1px rgba(37,99,235,.90);
    filter:brightness(1.03);
  }

  button:disabled{
    opacity:.7;
    cursor:default;
    transform:none;
    box-shadow:0 7px 18px rgba(148,163,184,.55);
  }

  .send-icon{
    font-size:14px;
  }

  /* Side info card */
  .side-card{
    background:rgba(255,255,255,0.9);
    border-radius:22px;
    border:1px solid rgba(226,232,240,.95);
    padding:14px 14px 12px;
    box-shadow:
      0 20px 40px rgba(148,163,184,.28),
      0 0 0 1px rgba(148,163,184,.18);
    font-size:12px;
    animation:floatUp .45s ease-out .05s both;
    position:relative;
    overflow:hidden;
    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);
  }

  .side-card::before{
    content:"";
    position:absolute;
    inset:0;
    background:
      radial-gradient(circle at 0% 0%,rgba(219,234,254,.9),transparent 60%),
      radial-gradient(circle at 100% 100%,rgba(254,243,199,.9),transparent 60%);
    opacity:.55;
    pointer-events:none;
  }

  .side-inner{
    position:relative;
    z-index:1;
  }

  .side-title{
    font-size:14px;
    font-weight:600;
    margin-bottom:4px;
    color:#0f172a;
  }

  .side-sub{
    font-size:11px;
    color:var(--text-soft);
    margin-bottom:8px;
  }

  .side-list{
    font-size:11px;
    color:var(--text-soft);
    padding-left:16px;
  }

  .side-list li{
    margin-bottom:4px;
  }

  .side-list strong{
    color:#111827;
  }

  /* Animations */
  @keyframes fadeInBody{
    from{opacity:0;}
    to{opacity:1;}
  }

  @keyframes spin-slow{
    from{transform:rotate(0);}
    to{transform:rotate(360deg);}
  }

  @keyframes bubbleIn{
    from{opacity:0;transform:translateY(3px);}
    to{opacity:1;transform:translateY(0);}
  }

  @keyframes blink{
    0%,80%,100%{opacity:.2;transform:translateY(0);}
    40%{opacity:1;transform:translateY(-1px);}
  }

  @keyframes floatUp{
    from{opacity:0;transform:translateY(10px) scale(.98);}
    to{opacity:1;transform:translateY(0) scale(1);}
  }

  /* Responsive */
  @media(max-width:860px){
    .shell{grid-template-columns:minmax(0,1fr);}
    .chat-card{height:500px;}
  }

  @media(max-width:520px){
    .top-inner{flex-wrap:wrap;}
    .chat-card{height:480px;}
    .shell{padding:0 12px;}
  }
</style>
</head>
<body>

<header class="top">
  <div class="top-inner">
    <div class="logo-wrap">
      <div class="logo-icon">
        <div class="logo-icon-inner">K</div>
      </div>
      <div>
        <div>
          <span class="logo-text-main">KARTIFY</span><span class="logo-dot">.</span>
        </div>
        <div class="logo-sub">Customer Support Assistant</div>
      </div>
    </div>

    <div class="top-right">
      <div class="user-pill">
        <div class="avatar"><?php echo htmlspecialchars($initial); ?></div>
        <span><?php echo htmlspecialchars($userName); ?></span>
      </div>
      <a href="index.php" class="top-btn">Home</a>
      <a href="dashboard.php" class="top-btn">Dashboard</a>
      <a href="cart.php" class="top-btn">My Orders</a>
    </div>
  </div>
</header>

<main>
  <div class="shell">
    <!-- Chat -->
    <section class="chat-card">
      <div class="chat-inner">
        <div class="chat-header">
          <div>
            <div class="chat-title">
              Kartify Support Assistant
              <span class="chat-badge">Live help</span>
            </div>
            <div class="chat-sub">
              Ask anything about your orders, delivery, payments or technical questions.  
              All replies are in clear, professional English.
            </div>
          </div>
          <div class="status-pill">
            <span class="status-dot"></span>
            <span>Online</span>
          </div>
        </div>

        <div class="chat-box" id="chatBox">
          <div class="hint">
            Tip: You can type things like <strong>&ldquo;Track order 5&rdquo;</strong> or
            <strong>&ldquo;KARTIFY-000005&rdquo;</strong> to get your order summary.
          </div>
          <div class="quick-row">
            <div class="chip" data-text="Track order 1">Track an order</div>
            <div class="chip" data-text="What is your return policy?">Return policy</div>
            <div class="chip" data-text="Explain the payment and refund flow for Kartify.">Payment & refund flow</div>
            <div class="chip" data-text="Help me improve the database design for orders and payments.">DB design help</div>
          </div>

          <div class="msg-wrap">
            <div class="msg bot">
Hi <?php echo htmlspecialchars($userName); ?> 👋
I am the Kartify Support Assistant.

I can help you with:
• Checking the status of a specific order by ID or KARTIFY code  
• Explaining cancellation, return and refund rules  
• Answering questions about how your order, payment and delivery flows work  
• Helping with technical doubts about your Kartify implementation (PHP, MySQL, UI)

Just type your question, or share something like: "Track order 3" or "KARTIFY-000003".
            </div>
            <div class="meta">Assistant · <?php echo date('H:i'); ?></div>
          </div>
        </div>

        <form id="chatForm">
          <textarea
            id="msg"
            placeholder="Type your question here… (for example: “Track order 3” or “KARTIFY-000003 status?”)"
          ></textarea>
          <button type="submit" id="sendBtn">
            <span class="send-icon">➤</span>
            <span>Send</span>
          </button>
        </form>
      </div>
    </section>

    <!-- Side info -->
    <aside class="side-card">
      <div class="side-inner">
        <div class="side-title">About this assistant</div>
        <div class="side-sub">
          This assistant is designed to respond like a real Kartify support agent – always in polished English.
        </div>
        <ul class="side-list">
          <li><strong>Language:</strong> Replies are always in clear, professional English, even if you type in another language.</li>
          <li><strong>Orders:</strong> Share an Order ID or KARTIFY code to get a concise status summary and delivery guidance.</li>
          <li><strong>Support style:</strong> Friendly, precise and honest – focused on what is actually known from your data and standard e-commerce flows.</li>
          <li><strong>Tech help:</strong> You can also ask about PHP, MySQL, architecture, or how to improve your Kartify project.</li>
        </ul>
      </div>
    </aside>
  </div>
</main>

<script>
const box  = document.getElementById("chatBox");
const form = document.getElementById("chatForm");
const msg  = document.getElementById("msg");
const sendBtn = document.getElementById("sendBtn");
const chips = document.querySelectorAll(".chip");

function scrollToBottom(){
  box.scrollTop = box.scrollHeight;
}

function addMessage(text, who){
  let wrap = document.createElement("div");
  wrap.className = "msg-wrap";

  let div = document.createElement("div");
  div.className = "msg " + who;
  div.textContent = text;

  let meta = document.createElement("div");
  meta.className = "meta";
  let now = new Date();
  let hh = String(now.getHours()).padStart(2, "0");
  let mm = String(now.getMinutes()).padStart(2, "0");
  meta.textContent = (who === "you" ? "You" : "Assistant") + " · " + hh + ":" + mm;

  wrap.appendChild(div);
  wrap.appendChild(meta);
  box.appendChild(wrap);
  scrollToBottom();
}

function addTyping(){
  let wrap = document.createElement("div");
  wrap.className = "msg-wrap";
  wrap.id = "typingWrap";

  let bubble = document.createElement("div");
  bubble.className = "typing-bubble";
  bubble.innerHTML = '<span class="dot"></span><span class="dot"></span><span class="dot"></span>';

  wrap.appendChild(bubble);
  box.appendChild(wrap);
  scrollToBottom();
}

function removeTyping(){
  let el = document.getElementById("typingWrap");
  if(el) el.remove();
}

form.addEventListener("submit", async(e)=>{
  e.preventDefault();
  let text = msg.value.trim();
  if(!text) return;

  addMessage(text,"you");
  msg.value = "";
  msg.focus();

  sendBtn.disabled = true;
  addTyping();

  try{
    let res = await fetch("chat_backend.php",{
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body:JSON.stringify({ message:text })
    });

    let raw = await res.text();
    let json;

    try{
      json = JSON.parse(raw);
    } catch(parseErr){
      removeTyping();
      sendBtn.disabled = false;
      addMessage("The server returned an unexpected response:\n\n" + raw,"bot");
      return;
    }

    removeTyping();
    sendBtn.disabled = false;

    if(json.reply){
      addMessage(json.reply,"bot");
    } else if(json.error){
      addMessage("Error: " + json.error,"bot");
    } else {
      addMessage("Sorry, I could not understand the response.","bot");
    }
  }
  catch(err){
    removeTyping();
    sendBtn.disabled = false;
    addMessage("Network error: " + err.message,"bot");
  }
});

// Enter to send, Shift+Enter newline
msg.addEventListener("keydown", (e)=>{
  if(e.key === "Enter" && !e.shiftKey){
    e.preventDefault();
    form.dispatchEvent(new Event("submit"));
  }
});

// Quick chips fill text
chips.forEach(chip => {
  chip.addEventListener("click", () => {
    const text = chip.getAttribute("data-text") || chip.textContent;
    msg.value = text;
    msg.focus();
  });
});

scrollToBottom();
</script>

</body>
</html>
