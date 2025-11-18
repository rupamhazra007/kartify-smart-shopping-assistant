<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kartify - Smart Shopping with AI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #4f46e5;
      --primary-soft: #eff6ff;
      --primary-glow: rgba(79, 70, 229, 0.2);
      --accent: #f59e0b;
      --bg-light: #f8fafc;
      --card-bg: #ffffff;
      --border-soft: #e2e8f0;
      --text-main: #0f172a;
      --text-soft: #64748b;
      --success: #10b981;
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
      font-family: 'Outfit', system-ui, -apple-system, sans-serif;
      background-color: var(--bg-light);
      background-image: url('images/A.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: var(--text-main);
      min-height: 100vh;
      line-height: 1.6;
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 10px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    a { text-decoration: none; color: inherit; }
    .page { min-height: 100vh; display: flex; flex-direction: column; }

    /* Glassmorphism Navbar */
    .nav {
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      background: rgba(255, 255, 255, 0.86);
      border-bottom: 1px solid rgba(226, 232, 240, 0.9);
      transition: all 0.3s ease;
    }
    .nav-inner {
      max-width: 1120px;
      margin: 0 auto;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    /* Logo */
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo-icon {
      width: 34px; height: 34px; border-radius: 12px;
      background: conic-gradient(from 160deg, #22c55e, #22d3ee, #3b82f6, #eab308, #f97316, #22c55e);
      display: flex; align-items: center; justify-content: center;
      animation: spin-slow 16s linear infinite;
      box-shadow: 0 0 18px rgba(37, 99, 235, 0.45);
    }
    .logo-icon-inner {
      width: 22px; height: 22px; border-radius: 9px;
      background: radial-gradient(circle at 0 0, #bfdbfe, #1d4ed8 55%, #111827 100%);
      display: flex; align-items: center; justify-content: center;
      font-size: 15px; font-weight: 700; color: #f9fafb;
    }
    .logo-text-main {
      font-weight: 700; letter-spacing: 0.04em;
      font-size: 22px; color: var(--text-main);
    }
    .logo-dot { color: var(--accent); font-size: 22px; }
    .logo-tagline { font-size: 12px; color: var(--text-soft); margin-top: 2px; }
    .logo-dev-text {
      margin-top: 2px;
      font-size: 12px;
      color: var(--text-soft);
      font-weight: 500;
    }

    /* Nav Links */
    .nav-links { display: flex; align-items: center; gap: 22px; font-size: 14px; color: var(--text-soft); }
    .nav-link { position: relative; padding: 4px 0; cursor: pointer; font-weight: 500; }
    .nav-link span { opacity: 0.95; }
    .nav-link::after {
      content: ""; position: absolute; left: 0; bottom: -4px;
      width: 0; height: 2px; border-radius: 999px;
      background: var(--primary);
      transition: width 0.3s ease-out;
    }
    .nav-link:hover::after { width: 100%; }
    .nav-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }

    /* Animated User Badge */
    .user-badge {
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(226, 232, 240, 0.9);
      padding: 7px 16px 7px 9px;
      border-radius: 50px;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 8px 20px rgba(15,23,42,0.08);
      backdrop-filter: blur(8px);
      animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .user-badge:hover {
      transform: scale(1.04);
      box-shadow: 0 12px 26px rgba(15,23,42,0.12);
    }
    .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 999px;
      background: linear-gradient(135deg, var(--success), #34d399);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      font-weight: 600;
      color: #f9fafb;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .user-name-wrapper { display: flex; flex-direction: column; }
    .user-name {
      font-weight: 600;
      font-size: 14px;
      line-height: 1.1;
      background: linear-gradient(120deg, #4f46e5, #10b981, #f59e0b);
      background-size: 200% 200%;
      -webkit-background-clip: text;
      color: transparent;
      animation: gradientFlow 5s ease-in-out infinite;
    }
    .user-status {
      font-size: 11px; 
      color: var(--text-soft); 
      display: flex; 
      align-items: center; 
      gap: 4px;
      line-height: 1;
      margin-top: 2px;
    }
    .user-status-dot {
      width: 7px;
      height: 7px;
      background: var(--success);
      border-radius: 50%;
      box-shadow: 0 0 0 2px white, 0 0 8px var(--success);
      animation: pulseDot 2s infinite;
    }

    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; justify-content: center;
      gap: 6px; padding: 8px 16px; border-radius: 999px;
      font-size: 14px; border: 1px solid transparent; cursor: pointer;
      background: none; color: inherit;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      text-decoration: none; white-space: nowrap;
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), #4338ca);
      color: #f9fafb;
      box-shadow: 0 10px 24px var(--primary-glow);
    }
    .btn-primary:hover {
      transform: translateY(-2px) scale(1.03);
      box-shadow: 0 16px 32px var(--primary-glow);
    }
    .btn-outline {
      border-color: var(--border-soft);
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(4px);
      color: var(--text-main);
    }
    .btn-outline:hover {
      background: #f8fafc;
      border-color: #94a3b8;
      transform: translateY(-2px);
    }
    .btn-subtle {
      border-color: rgba(79, 70, 229, 0.4);
      background: rgba(239,246,255,0.9);
      backdrop-filter: blur(4px);
      color: var(--primary);
    }
    .btn-subtle:hover {
      background: #dbeafe;
      border-color: rgba(79, 70, 229, 0.7);
      box-shadow: 0 10px 24px var(--primary-glow);
      transform: translateY(-2px);
    }
    .btn-ghost {
      border-color: var(--border-soft);
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(4px);
      color: var(--text-main);
    }
    .btn-ghost:hover {
      background: #f8fafc;
      border-color: #cbd5e1;
      transform: translateY(-2px);
    }
    .btn-icon { font-size: 16px; margin-top: 1px; }

    /* Main Layout */
    .main { flex: 1; }
    .hero-shell {
      position: relative; max-width: 1120px;
      margin: 22px auto 42px; padding: 18px 20px 24px;
    }
    .hero-ring {
      position: absolute; inset: 0; border-radius: 26px;
      border: 1px solid rgba(209, 213, 219, 0.8);
      pointer-events: none;
    }
    .hero-blur {
      position: absolute; inset: 0; border-radius: 26px;
      background:
        radial-gradient(circle at top left, rgba(191, 219, 254, 0.7), transparent 55%),
        radial-gradient(circle at bottom right, rgba(221, 239, 253, 0.9), transparent 60%);
      opacity: 0.7; pointer-events: none; z-index: -1;
    }
    .hero {
      position: relative; border-radius: 22px;
      background: rgba(255,255,255,0.82);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      padding: 28px 28px 24px;
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
      gap: 28px; overflow: hidden;
      box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15),
                  0 0 0 1px rgba(226, 232, 240, 0.7);
    }
    .hero-left {
      display: flex; flex-direction: column;
      gap: 18px; justify-content: center;
    }
    .hero-badge-row {
      display: flex; flex-wrap: wrap; gap: 10px;
      align-items: center; font-size: 12px;
    }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 12px; border-radius: 999px;
      border: 1px solid rgba(22, 163, 74, 0.6);
      background: rgba(236,253,245,0.96); color: #15803d;
      animation: fade-slide 0.8s ease-out both;
    }
    .hero-badge-dot {
      width: 7px; height: 7px; border-radius: 999px;
      background: radial-gradient(circle at 30% 0, #bbf7d0, #22c55e);
      box-shadow: 0 0 10px rgba(34, 197, 94, 0.8);
    }
    .hero-badge-pill {
      padding: 5px 12px; border-radius: 999px;
      border: 1px dashed #d1d5db; background: rgba(249,250,251,0.96);
      color: var(--text-soft); display: inline-flex;
      align-items: center; gap: 6px; font-size: 12px;
    }
    .hero-title {
      font-size: clamp(26px, 3.3vw, 32px);
      line-height: 1.25; font-weight: 700;
      letter-spacing: -0.5px;
      color: var(--text-main);
    }
    .hero-title span.highlight {
      background: linear-gradient(120deg, #4f46e5, #10b981, #f59e0b);
      -webkit-background-clip: text;
      color: transparent;
    }
    .hero-subtext {
      font-size: 15px;
      color: var(--text-soft); max-width: 480px;
    }

    .hero-list {
      margin-top: 4px;
      padding-left: 18px;
      font-size: 14px;
      color: var(--text-soft);
    }
    .hero-list li { margin-bottom: 4px; }

    .hero-ctas { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 10px; }
    .hero-meta-row {
      margin-top: 12px; display: flex; flex-wrap: wrap;
      gap: 16px; font-size: 12px; color: var(--text-soft);
    }
    .hero-meta-pill {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 6px 11px; border-radius: 999px;
      background: rgba(249,250,251,0.94); border: 1px solid var(--border-soft);
    }
    .dot-soft {
      width: 6px; height: 6px; border-radius: 999px; background: #9ca3af;
    }

    /* Hero Right (cards) */
    .hero-right {
      position: relative; display: flex;
      align-items: center; justify-content: center;
    }
    .product-panel {
      position: relative; width: 100%; max-width: 340px;
      border-radius: 18px; background: rgba(255,255,255,0.88);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(226, 232, 240, 0.9); padding: 13px 13px 15px;
      box-shadow: 0 14px 40px rgba(15, 23, 42, 0.18);
      transform: translateY(2px);
    }
    .product-panel-header {
      display: flex; align-items: center; justify-content: space-between;
      font-size: 12px; color: var(--text-soft); margin-bottom: 10px;
    }
    .product-chip {
      padding: 3px 9px; border-radius: 999px;
      background: rgba(236,253,245,0.96); border: 1px solid var(--success);
      display: inline-flex; align-items: center; gap: 5px; color: #15803d;
      font-size: 11px;
    }
    .product-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px;
    }
    .p-card {
      border-radius: 12px; background: rgba(249,250,251,0.96);
      border: 1px solid var(--border-soft); padding: 8px;
      font-size: 12px; display: flex; flex-direction: column;
      gap: 5px; cursor: pointer;
      transition: all 0.25s ease-out;
      backdrop-filter: blur(6px);
    }
    .p-card:hover {
      transform: translateY(-4px) scale(1.03);
      border-color: var(--primary);
      box-shadow: 0 10px 25px var(--primary-glow);
      background: rgba(255,255,255,0.98);
      z-index: 10;
    }
    .p-img {
      border-radius: 9px; height: 82px;
      background: radial-gradient(circle at 30% 0, #fee2e2, #dbeafe);
      display: flex; align-items: center; justify-content: center;
      font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em;
      font-weight: 600; color: var(--text-main);
      text-align: center;
      padding: 4px;
      overflow: hidden;
    }
    .p-img-alt1 { background: radial-gradient(circle at 0 0, #d1fae5, #dbeafe); }
    .p-img-alt2 { background: radial-gradient(circle at 0 0, #e0f2fe, #fef9c3); }
    .p-img-alt3 { background: radial-gradient(circle at 0 0, #fef3c7, #e0e7ff); }

    .p-img-inner {
      width: 100%;
      height: auto;
      max-height: 100%;
      object-fit: contain;
      border-radius: inherit;
      display: block;
    }

    .p-name { font-weight: 600; color: var(--text-main); font-size: 13px; }
    .p-meta-row {
      display: flex; justify-content: space-between;
      align-items: center; margin-top: 2px;
    }
    .p-price { font-weight: 700; font-size: 13px; }
    .p-price span {
      font-size: 11px; color: var(--text-soft);
      text-decoration: line-through; margin-left: 4px;
    }
    .p-tag {
      padding: 2px 7px; border-radius: 999px;
      background: #ecfdf5; border: 1px solid var(--success);
      font-size: 10px; color: #15803d;
    }
    .p-tag-alt {
      background: var(--primary-soft); border-color: var(--primary); color: #1d4ed8;
    }
    .p-footer-row {
      margin-top: 4px; display: flex; justify-content: space-between;
      align-items: center; font-size: 11px; color: var(--text-soft);
    }
    .rating-stars { letter-spacing: 0.04em; }
    .mini-btn {
      padding: 3px 8px; border-radius: 999px;
      border: 1px solid var(--border-soft); background: rgba(255,255,255,0.96);
      font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em;
    }
    .mini-btn:hover { border-color: #cbd5f5; }

    /* Chatbot status dot */
    .bot-dot {
      position: absolute; width: 10px; height: 10px;
      border-radius: 999px; background: var(--success);
      border: 2px solid #ffffff; right: 6px; bottom: 8px;
      box-shadow: 0 0 12px rgba(34, 197, 94, 0.8);
    }

    /* Sections */
    .section {
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 20px 34px;
    }
    .section-header {
      display: flex; align-items: baseline; justify-content: space-between;
      gap: 10px; margin-bottom: 16px;
      padding: 10px 14px;
      border-radius: 18px;
      background: rgba(255,255,255,0.78);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(226,232,240,0.9);
      box-shadow: 0 10px 30px rgba(15,23,42,0.12);
    }
    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--text-main);
    }
    .section-sub {
      font-size: 14px;
      color: var(--text-soft);
      margin-top: 4px;
      max-width: 560px;
    }
    .section-extra {
      font-size: 14px;
      color: var(--text-soft);
      margin-top: 8px;
      max-width: 700px;
    }
    .pill-row { display: flex; flex-wrap: wrap; gap: 7px; font-size: 12px; }
    .pill {
      padding: 4px 10px; border-radius: 999px;
      background: rgba(255,255,255,0.9); border: 1px solid var(--border-soft);
      color: var(--text-soft);
      backdrop-filter: blur(4px);
    }

    .feature-list {
      margin-top: 6px;
      padding-left: 18px;
      font-size: 12px;
      color: var(--text-soft);
    }
    .feature-list li { margin-bottom: 3px; }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 13px;
      margin-top: 10px;
    }
    .feature-card {
      border-radius: 16px; 
      background: rgba(255,255,255,0.8);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid var(--border-soft); padding: 11px 12px 12px;
      font-size: 13px; display: flex; flex-direction: column;
      gap: 6px; box-shadow: 0 10px 26px rgba(15, 23, 42, 0.12);
      position: relative; overflow: hidden;
      transition: all 0.25s ease-out;
    }
    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 14px 36px rgba(15, 23, 42, 0.18);
      border-color: #bfdbfe;
    }
    .feature-card:nth-child(2) { border-color: #bfdbfe; }
    .feature-card:nth-child(3) { border-color: #fde68a; }
    .feature-icon { font-size: 20px; margin-bottom: 2px; }
    .feature-title { font-weight: 600; font-size: 14px; color: var(--text-main); }
    .feature-desc { font-size: 13px; color: var(--text-soft); }
    .feature-chip {
      margin-top: 4px; font-size: 11px; padding: 3px 8px;
      border-radius: 999px; background: rgba(249,250,251,0.96);
      border: 1px solid var(--border-soft); align-self: flex-start;
    }
    .feature-glow {
      position: absolute; width: 80px; height: 80px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(79, 70, 229, 0.22), transparent 70%);
      right: -24px; bottom: -20px; opacity: 0.85;
    }
    .feature-glow-alt { background: radial-gradient(circle, rgba(22, 163, 74, 0.28), transparent 70%); }
    .feature-glow-alt2 { background: radial-gradient(circle, rgba(234, 179, 8, 0.26), transparent 70%); }

    /* Categories strip */
    .categories-strip {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 13px;
      margin-top: 12px;
    }
    .cat-card {
      border-radius: 14px; 
      background: rgba(255,255,255,0.8);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid var(--border-soft); padding: 11px;
      font-size: 13px; display: flex; flex-direction: column;
      gap: 5px; cursor: pointer;
      transition: all 0.25s ease-out;
      box-shadow: 0 10px 24px rgba(15,23,42,0.12);
    }
    .cat-card:hover {
      transform: translateY(-4px);
      border-color: var(--primary);
      box-shadow: 0 14px 32px var(--primary-glow);
    }
    .cat-label { font-weight: 600; color: var(--text-main); font-size: 14px; }
    .cat-meta { font-size: 13px; color: var(--text-soft); }

    /* Products row */
    .product-row {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 13px;
      margin-top: 12px;
    }
    .product-card-main {
      border-radius: 16px; 
      background: rgba(255,255,255,0.8);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid var(--border-soft); padding: 11px 11px 12px;
      font-size: 13px; display: flex; flex-direction: column;
      gap: 6px; cursor: pointer;
      transition: all 0.25s ease-out;
      box-shadow: 0 12px 30px rgba(15,23,42,0.14);
    }
    .product-card-main:hover {
      transform: translateY(-6px);
      border-color: var(--primary);
      box-shadow: 0 18px 40px var(--primary-glow);
    }
    .product-thumb {
      border-radius: 12px; height: 124px;
      background: radial-gradient(circle at top, #dbeafe, #eff6ff);
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em;
      font-weight: 600; color: var(--text-main);
      text-align: center;
      padding: 4px;
      overflow: hidden;
    }
    .product-title { font-weight: 600; color: var(--text-main); font-size: 14px; }
    .product-meta { font-size: 13px; color: var(--text-soft); }
    .product-footer {
      margin-top: 4px; display: flex; justify-content: space-between;
      align-items: center; font-size: 12px; gap: 8px;
    }
    .price-main { font-weight: 700; font-size: 14px; color: var(--text-main); }
    .price-main span {
      font-size: 11px; color: var(--text-soft);
      text-decoration: line-through; margin-left: 4px;
    }
    .badge-deal {
      padding: 3px 8px; border-radius: 999px;
      background: rgba(236,253,245,0.96); border: 1px solid var(--success);
      color: #15803d; font-size: 11px;
    }
    .badge-hot {
      background: rgba(254,242,242,0.96); border-color: #f97316; color: #ea580c;
    }
    .mini-cta {
      padding: 4px 8px; border-radius: 999px;
      border: 1px solid var(--border-soft);
      font-size: 12px; white-space: nowrap; background: rgba(255,255,255,0.96);
    }

    /* Footer */
    .footer {
      border-top: 1px solid var(--border-soft); 
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(8px);
      padding: 11px 20px 14px; font-size: 12px;
      color: var(--text-soft);
    }
    .footer-inner {
      max-width: 1120px; margin: 0 auto;
      display: flex; justify-content: space-between;
      align-items: center; gap: 10px; flex-wrap: wrap;
    }

    /* Reveal Animation */
    .reveal {
      opacity: 0;
      transform: translateY(25px);
      transition: opacity 0.7s ease-out, transform 0.7s ease-out;
    }
    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* ---------- Animations ---------- */
    @keyframes spin-slow {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    @keyframes float {
      0%, 100% { transform: translateY(-50%) translateY(0px); }
      50% { transform: translateY(-50%) translateY(-6px); }
    }
    @keyframes fade-slide {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes bounceIn {
      0% { opacity: 0; transform: scale(0.3); }
      50% { opacity: 1; transform: scale(1.05); }
      70% { transform: scale(0.9); }
      100% { transform: scale(1); }
    }
    @keyframes pulseDot {
      0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
      70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
      100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Responsive */
    @media (max-width: 900px) {
      .hero { grid-template-columns: minmax(0, 1fr); padding: 22px 18px 18px; }
      .hero-right { margin-top: 8px; }
      .hero-shell { padding: 16px 14px 20px; }
      .hero-ring { border-radius: 20px; }
    }
    @media (max-width: 768px) {
      .nav-inner { padding-inline: 14px; }
      .nav-links { display: none; }
      .hero-shell { margin-top: 14px; }
      .features-grid { grid-template-columns: repeat(1, minmax(0, 1fr)); }
      .product-row, .categories-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 520px) {
      .product-row, .categories-strip { grid-template-columns: minmax(0, 1fr); }
      .hero { padding: 18px 14px 16px; }
      .hero-title { font-size: 24px; }
    }
  </style>
</head>
<body>

<div class="page">
  <header class="nav">
    <div class="nav-inner">
      <div class="logo">
        <div class="logo-icon">
          <div class="logo-icon-inner">K</div>
        </div>
        <div>
          <div>
            <span class="logo-text-main">Kartify</span><span class="logo-dot">.</span>
          </div>
          <div class="logo-tagline">Flipkart-inspired smart store with AI support</div>
          <div class="logo-dev-text">Developed by Rupam</div>
        </div>
      </div>

      <nav class="nav-links">
        <a href="#home" class="nav-link"><span>Home</span></a>
        <a href="#deals" class="nav-link"><span>Today’s Deals</span></a>
        <a href="#categories" class="nav-link"><span>Categories</span></a>
        <a href="#about" class="nav-link"><span>About Us</span></a>
        <a href="#support" class="nav-link"><span>Support</span></a>
      </nav>

      <div class="nav-right">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div class="user-badge">
            <div class="user-avatar">
              <?php
                $name = trim($_SESSION['user_name'] ?? 'User');
                $initial = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
                echo htmlspecialchars($initial);
              ?>
            </div>
            <div class="user-name-wrapper">
              <div class="user-name">
                Hi, <?php echo htmlspecialchars($name); ?>!
              </div>
              <div class="user-status">
                <div class="user-status-dot"></div> Online
              </div>
            </div>
          </div>
          
          <a href="status.php" class="btn btn-subtle">
            <span class="btn-icon">📦</span>
            <span>My Orders</span>
          </a>
          <a href="chat.php" class="btn btn-ghost">
            <span class="btn-icon">💬</span>
            <span>Customer Assistant</span>
          </a>
          <a href="logout.php" class="btn btn-outline">
            <span>Logout</span>
          </a>
        <?php else: ?>
          <a href="login.php" class="btn btn-outline">
            <span>Login</span>
          </a>
          <a href="register.php" class="btn btn-primary">
            <span class="btn-icon">✨</span>
            <span>Create free account</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="main">
    <!-- HOME SECTION -->
    <div id="home" class="hero-shell reveal">
      <div class="hero-blur"></div>
      <div class="hero-ring"></div>
      <section class="hero">
        <div class="hero-left">
          <div class="hero-badge-row">
            <div class="hero-badge">
              <span class="hero-badge-dot"></span>
              <span>Flipkart-inspired · Clean &amp; light</span>
            </div>
            <div class="hero-badge-pill">
              <span>⚡ Smart shopping</span>
              <span>•</span>
              <span>AI Customer Support</span>
            </div>
          </div>

          <h1 class="hero-title">
            Build a <span class="highlight">modern e-commerce</span> flow<br>
            with a friendly AI support experience.
          </h1>

          <p class="hero-subtext">
            Welcome to Kartify — a modern shopping experience where you can browse curated products,
            manage your account and get instant help from our AI-powered assistant.
          </p>

          <ul class="hero-list">
            <li>Stay signed in securely while you move between pages and devices.</li>
            <li>Discover featured collections, offers and layouts inspired by leading e-commerce brands.</li>
            <li>Get quick answers about products, orders and more from the built-in customer assistant.</li>
          </ul>

          <div class="hero-ctas">
            <?php if (!empty($_SESSION['user_id'])): ?>
              <a href="dashboard.php" class="btn btn-primary">
                <span class="btn-icon">🛍️</span>
                <span>Continue shopping</span>
              </a>
              <a href="chat.php" class="btn btn-outline">
                <span class="btn-icon">🤖</span>
                <span>Ask Customer Assistant</span>
              </a>
            <?php else: ?>
              <a href="register.php" class="btn btn-primary">
                <span class="btn-icon">🚀</span>
                <span>Start your Kartify journey</span>
              </a>
              <a href="login.php" class="btn btn-outline">
                <span class="btn-icon">👤</span>
                <span>I already have an account</span>
              </a>
            <?php endif; ?>
          </div>

          <div class="hero-meta-row">
            <div class="hero-meta-pill">
              <span class="dot-soft"></span>
              <span>Flipkart-style cards &amp; offers layout</span>
            </div>
            <div class="hero-meta-pill">
              <span class="dot-soft"></span>
              <span>Fast, lightweight front page for smooth browsing</span>
            </div>
          </div>
        </div>

        <div class="hero-right">
          <div class="product-panel">
            <div class="product-panel-header">
              <div>
                <div style="font-weight:600;color:var(--text-main);">Today’s Top Picks</div>
                <div style="font-size:11px;">Popular flagship phones with competitive India pricing</div>
              </div>
              <div class="product-chip">
                <span style="width:6px;height:6px;border-radius:999px;background:var(--success);box-shadow:0 0 8px rgba(34,197,94,0.9);"></span>
                <span>Trending now</span>
              </div>
            </div>

            <div class="product-grid">
              <!-- iPhone 15 Pro Max -->
              <a href="dashboard.php" class="p-card">
                <div class="p-img">
                  <img src="images/F.jpg" alt="Apple iPhone 15 Pro Max" class="p-img-inner">
                </div>
                <div class="p-name">iPhone 15 Pro Max (256 GB)</div>
                <div class="p-meta-row">
                  <div class="p-price">₹1,59,900 <span>₹1,75,900</span></div>
                  <div class="p-tag p-tag-alt">Premium flagship</div>
                </div>
                <div class="p-footer-row">
                  <span class="rating-stars">⭐ 4.8 · 5.6k</span>
                  <span class="mini-btn">View details</span>
                </div>
              </a>

              <!-- OnePlus 12 5G -->
              <a href="dashboard.php" class="p-card">
                <div class="p-img p-img-alt1">
                  <img src="images/G.jpg" alt="OnePlus 12 5G" class="p-img-inner">
                </div>
                <div class="p-name">OnePlus 12 (12GB · 256GB)</div>
                <div class="p-meta-row">
                  <div class="p-price">₹64,999 <span>₹69,999</span></div>
                  <div class="p-tag p-tag-alt">Bank offer</div>
                </div>
                <div class="p-footer-row">
                  <span class="rating-stars">⭐ 4.7 · 3.2k</span>
                  <span class="mini-btn">View deal</span>
                </div>
              </a>

              <!-- vivo V30 Pro 5G -->
              <a href="dashboard.php" class="p-card">
                <div class="p-img p-img-alt2">
                  <img src="images/H.jpg" alt="vivo V30 Pro 5G" class="p-img-inner">
                </div>
                <div class="p-name">vivo V30 Pro (8GB · 256GB)</div>
                <div class="p-meta-row">
                  <div class="p-price">₹44,999 <span>₹51,999</span></div>
                  <div class="p-tag">Camera-focused</div>
                </div>
                <div class="p-footer-row">
                  <span class="rating-stars">⭐ 4.6 · 1.1k</span>
                  <span class="mini-btn">Quick view</span>
                </div>
              </a>

              <!-- Samsung Galaxy S24 Ultra -->
              <a href="dashboard.php" class="p-card">
                <div class="p-img p-img-alt3">
                  <img src="images/E.jpg" alt="Samsung Galaxy S24 Ultra" class="p-img-inner">
                </div>
                <div class="p-name">Samsung Galaxy S24 Ultra</div>
                <div class="p-meta-row">
                  <div class="p-price">₹84,999 <span>₹99,999</span></div>
                  <div class="p-tag p-tag-alt">AI-powered</div>
                </div>
                <div class="p-footer-row">
                  <span class="rating-stars">⭐ 4.8 · 4.3k</span>
                  <span class="mini-btn">Add to cart</span>
                </div>
              </a>
            </div>
          </div>
          <div class="bot-dot"></div>
        </div>
      </section>
    </div>

    <!-- ABOUT US SECTION -->
    <section id="about" class="section reveal">
      <div class="section-header">
        <div>
          <div class="section-title">About Kartify</div>
          <div class="section-sub">
            Kartify is a modern, India-focused shopping platform built to make online buying simple,
            fast and transparent — from the first product search to the final delivery update.
          </div>
          <div class="section-extra">
            We bring together carefully selected electronics, lifestyle products and daily essentials,
            and combine them with secure payments and a smart support experience powered by AI.
            Our goal is to give you a smooth, Flipkart-style interface with our own clean, minimal twist.
          </div>
        </div>
        <div class="pill-row">
          <div class="pill">Pan-India ready*</div>
          <div class="pill">Secure checkout</div>
          <div class="pill">AI-enabled assistance</div>
          <div class="pill">Customer-first design</div>
        </div>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">🛍️</div>
          <div class="feature-title">Curated product selection</div>
          <div class="feature-desc">
            From flagship smartphones to everyday accessories, Kartify focuses on popular,
            high-value products that Indian shoppers actually look for.
          </div>
          <ul class="feature-list">
            <li>Carefully chosen electronics and lifestyle items.</li>
            <li>Clear pricing and highlight of key benefits.</li>
            <li>Layouts inspired by leading marketplaces.</li>
          </ul>
          <div class="feature-chip">Focused on what matters</div>
          <div class="feature-glow"></div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🔒</div>
          <div class="feature-title">Secure &amp; reliable platform</div>
          <div class="feature-desc">
            Accounts, orders and payments are handled through a structured backend,
            designed to keep your journey consistent and safe end-to-end.
          </div>
          <ul class="feature-list">
            <li>Session-based login keeps you signed in securely.</li>
            <li>Order details and history easily accessible anytime.</li>
            <li>Architecture ready for scale and new features.</li>
          </ul>
          <div class="feature-chip">Built with robustness in mind</div>
          <div class="feature-glow feature-glow-alt"></div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🤖</div>
          <div class="feature-title">Smart customer experience</div>
          <div class="feature-desc">
            Our AI-powered assistant is designed to answer questions about products,
            payments and orders, helping you resolve issues without waiting in a queue.
          </div>
          <ul class="feature-list">
            <li>Instant responses to common support queries.</li>
            <li>Guidance on choosing the right device or accessory.</li>
            <li>Order tracking information at your fingertips.</li>
          </ul>
          <div class="feature-chip">Always-on digital support</div>
          <div class="feature-glow feature-glow-alt2"></div>
        </div>
      </div>
    </section>

    <!-- SUPPORT SECTION -->
    <section id="support" class="section reveal">
      <div class="section-header">
        <div>
          <div class="section-title">Support &amp; AI experience</div>
          <div class="section-sub">
            Our support area is designed like a real customer help center — so you can quickly
            check order updates, ask questions and get clear answers without any hassle.
          </div>
          <div class="section-extra">
            Use the Customer Assistant to ask about delivery timelines, returns, payment options
            or general product queries. The interface is inspired by chat-based help centers,
            so you always know where to go when you need assistance.
          </div>
        </div>
        <div class="pill-row">
          <div class="pill">AI chat assistance</div>
          <div class="pill">Secure sign-in</div>
          <div class="pill">Clean, minimal interface</div>
          <div class="pill">Order tracking &amp; help</div>
        </div>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">⚡</div>
          <div class="feature-title">Familiar shopping layout</div>
          <div class="feature-desc">
            Card-based sections for offers, categories and recommendations give you the feel
            of a mature marketplace while staying lightweight and easy to navigate.
          </div>
          <ul class="feature-list">
            <li>Responsive design that works across devices.</li>
            <li>Clear product tiles with price and highlights.</li>
            <li>Perfect for fast browsing and quick decisions.</li>
          </ul>
          <div class="feature-chip">Comfortable visual experience</div>
          <div class="feature-glow"></div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🤖</div>
          <div class="feature-title">AI Customer Assistant</div>
          <div class="feature-desc">
            The built-in assistant is ready to guide you through your shopping journey —
            from picking the right product to understanding offers and order status.
          </div>
          <ul class="feature-list">
            <li>Chat interface similar to real support portals.</li>
            <li>Instant help for common questions.</li>
            <li>Designed to feel natural and conversational.</li>
          </ul>
          <div class="feature-chip">Chat-first support</div>
          <div class="feature-glow feature-glow-alt"></div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🧩</div>
          <div class="feature-title">End-to-end consistency</div>
          <div class="feature-desc">
            From login and dashboard to order tracking and support, Kartify keeps your experience
            consistent so you always know what to expect on each page.
          </div>
          <ul class="feature-list">
            <li>Single account across shopping and support.</li>
            <li>Orders and payment details tied to your profile.</li>
            <li>Ready to grow with wishlists, reviews and more.</li>
          </ul>
          <div class="feature-chip">Unified platform</div>
          <div class="feature-glow feature-glow-alt2"></div>
        </div>
      </div>
    </section>

    <!-- CATEGORIES SECTION -->
    <section id="categories" class="section reveal" style="padding-top:0;">
      <div class="section-header">
        <div>
          <div class="section-title">Popular categories</div>
          <div class="section-sub">
            Browse Kartify by category and jump straight into the products that matter to you —
            whether you’re upgrading your phone or refreshing your wardrobe.
          </div>
          <div class="section-extra">
            Use these sections as quick entry points into curated lists. Each category is designed
            to bundle together devices, accessories and lifestyle products that naturally belong
            with each other.
          </div>
        </div>
      </div>
      <div class="categories-strip">
        <div class="cat-card">
          <div class="cat-label">Mobiles &amp; Accessories</div>
          <div class="cat-meta">
            5G phones, chargers, power banks and earphones — everything you need to stay connected
            at work, home or on the move.
          </div>
        </div>
        <div class="cat-card">
          <div class="cat-label">Laptops &amp; Tablets</div>
          <div class="cat-meta">
            Devices for work, study and content creation. Compare specs and pick the machine that
            fits your day-to-day life.
          </div>
        </div>
        <div class="cat-card">
          <div class="cat-label">Fashion &amp; Lifestyle</div>
          <div class="cat-meta">
            Sneakers, shirts, backpacks and more — ideal for experimenting with different looks,
            colors and styles.
          </div>
        </div>
        <div class="cat-card">
          <div class="cat-label">Home &amp; Essentials</div>
          <div class="cat-meta">
            Smart home devices, décor and kitchen basics to make everyday living more comfortable
            and organised.
          </div>
        </div>
      </div>
    </section>

    <!-- TODAY'S DEALS SECTION -->
    <section id="deals" class="section reveal" style="padding-top:0;">
      <div class="section-header">
        <div>
          <div class="section-title">Today’s Deals</div>
          <div class="section-sub">
            Discover some of our most popular picks with attractive pricing and strong everyday value
            for Indian shoppers.
          </div>
          <div class="section-extra">
            From big-screen TVs and powerful laptops to audio gear and wearables, this section highlights
            products that combine features, performance and price in a balanced way.
          </div>
        </div>
        <a href="<?php echo !empty($_SESSION['user_id']) ? 'dashboard.php' : 'login.php'; ?>" class="btn btn-outline">
          <span class="btn-icon">🛒</span>
          <span>Go to shopping dashboard</span>
        </a>
      </div>
      <div class="product-row">
        <!-- Samsung 55" 4K TV -->
        <div class="product-card-main">
          <div class="product-thumb">
            <img src="images/sam.jpg" alt="Samsung 55&quot; Crystal UHD 4K Smart TV" class="p-img-inner">
          </div>
          <div class="product-title">Samsung 55&quot; Crystal UHD 4K Smart TV</div>
          <div class="product-meta">
            4K LED panel with smart apps, ideal for living rooms and binge-watchers looking for a big-screen upgrade.
          </div>
          <div class="product-footer">
            <div>
              <div class="price-main">₹45,199 <span>₹64,400</span></div>
              <div style="font-size:12px;color:var(--text-soft);">Approximate online price in India</div>
            </div>
            <div class="badge-deal">Best value TV</div>
          </div>
        </div>

        <!-- HP Victus Gaming Laptop -->
        <div class="product-card-main">
          <div class="product-thumb" style="background:radial-gradient(circle at top,#bbf7d0,#eff6ff);">
            <img src="images/hp.jpg" alt="HP Victus 15 Gaming Laptop" class="p-img-inner">
          </div>
          <div class="product-title">HP Victus 15, Ryzen 5, 16GB, 512GB SSD</div>
          <div class="product-meta">
            A powerful everyday and gaming laptop with ample RAM and fast SSD — great for study, work and play.
          </div>
          <div class="product-footer">
            <div>
              <div class="price-main">₹69,990 <span>₹81,013</span></div>
              <div style="font-size:12px;color:var(--text-soft);">EMI &amp; bank offers may vary</div>
            </div>
            <div class="mini-cta">Great for work &amp; gaming</div>
          </div>
        </div>

        <!-- boAt Airdopes 141 ANC -->
        <div class="product-card-main">
          <div class="product-thumb" style="background:radial-gradient(circle at top,#fee2e2,#fef9c3);">
            <img src="images/boat.jpg" alt="boAt Airdopes 141 ANC TWS Earbuds" class="p-img-inner">
          </div>
          <div class="product-title">boAt Airdopes 141 ANC TWS Earbuds</div>
          <div class="product-meta">
            Popular budget TWS earbuds with active noise cancellation — perfect for music, calls and travel.
          </div>
          <div class="product-footer">
            <div>
              <div class="price-main">₹1,994 <span>₹5,990</span></div>
              <div style="font-size:12px;color:var(--text-soft);">Limited-time offer pricing</div>
            </div>
            <div class="badge-deal badge-hot">🔥 Hot audio pick</div>
          </div>
        </div>

        <!-- Fastrack Reflex Beat+ -->
        <div class="product-card-main">
          <div class="product-thumb" style="background:radial-gradient(circle at top,#dbeafe,#e0f2fe);">
            <img src="images/fastrack.jpg" alt="Fastrack Reflex Beat+ Smartwatch" class="p-img-inner">
          </div>
          <div class="product-title">Fastrack Reflex Beat+ Smartwatch</div>
          <div class="product-meta">
            A stylish wearable with health tracking and smart notifications — designed for everyday use and fitness.
          </div>
          <div class="product-footer">
            <div>
              <div class="price-main">₹1,795 <span>₹3,495</span></div>
              <div style="font-size:12px;color:var(--text-soft);">Typical offer price online</div>
            </div>
            <div class="mini-cta">Show as wearable</div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-inner">
      <div>© <?php echo date('Y'); ?> Kartify · Smart shopping with AI assistance.</div>
      <div>Designed &amp; developed by Rupam to deliver a clean, modern e-commerce experience.</div>
    </div>
  </footer>
</div>

<script>
  // Smooth reveal on scroll
  const revealElements = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1 }
  );
  revealElements.forEach(el => observer.observe(el));

  // Navbar background change on scroll
  window.addEventListener('scroll', () => {
    const nav = document.querySelector('.nav');
    if (window.scrollY > 30) {
      nav.style.boxShadow = '0 10px 30px rgba(0,0,0,0.08)';
      nav.style.background = 'rgba(255, 255, 255, 0.94)';
    } else {
      nav.style.boxShadow = 'none';
      nav.style.background = 'rgba(255, 255, 255, 0.86)';
    }
  });
</script>
</body>
</html>
