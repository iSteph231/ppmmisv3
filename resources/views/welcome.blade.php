<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>PPMMIS — Physical Plant Maintenance & Management Information System</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@300;400;500;600&display=swap" rel="stylesheet" />
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --blue-900: #0a1f44;
  --blue-800: #12306b;
  --blue-700: #1a4494;
  --blue-600: #1d5bb8;
  --blue-500: #2470d4;
  --blue-400: #4a8fe8;
  --blue-100: #ddeeff;
  --blue-50:  #f0f6ff;
  --white:    #ffffff;
  --gray-50:  #f8f9fb;
  --gray-100: #eef0f4;
  --gray-300: #c8cdd8;
  --gray-500: #7a8299;
  --gray-700: #3d4460;
  --gold:     #c9922a;
  --gold-light: #fdf3e0;
}

html { scroll-behavior: smooth; }

body {
  font-family: 'Source Sans 3', sans-serif;
  background: var(--white);
  color: var(--blue-900);
  font-size: 16px;
  line-height: 1.7;
  -webkit-font-smoothing: antialiased;
}

/* ── NAV ───────────────────────────────────────── */
nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 200;
  background: rgba(255,255,255,0.96);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--gray-100);
  height: 68px;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 6vw;
}

.nav-brand {
  display: flex; align-items: center; gap: 14px;
  text-decoration: none;
}

.nav-seal {
  width: 40px; height: 40px;
  background: var(--blue-800);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.nav-seal svg { width: 22px; height: 22px; }

.nav-name {
  display: flex; flex-direction: column; gap: 1px;
}

.nav-abbr {
  font-size: 15px; font-weight: 600;
  color: var(--blue-800); letter-spacing: 0.04em;
}

.nav-full {
  font-size: 10px; font-weight: 400;
  color: var(--gray-500); letter-spacing: 0.03em;
  text-transform: uppercase;
}

.nav-right {
  display: flex; align-items: center; gap: 8px;
}

.btn-login {
  font-size: 14px; font-weight: 500;
  color: var(--blue-700);
  text-decoration: none;
  padding: 8px 18px;
  border: 1px solid var(--blue-200, #b3cfee);
  border-radius: 6px;
  transition: background 0.2s, color 0.2s;
}
.btn-login:hover { background: var(--blue-50); }

.btn-register {
  font-size: 14px; font-weight: 600;
  color: var(--white);
  background: var(--blue-700);
  text-decoration: none;
  padding: 8px 20px;
  border-radius: 6px;
  transition: background 0.2s, transform 0.15s;
}
.btn-register:hover { background: var(--blue-800); transform: translateY(-1px); }

/* ── HERO ──────────────────────────────────────── */
.hero {
  padding: 148px 6vw 100px;
  text-align: center;
  max-width: 860px;
  margin: 0 auto;
  position: relative;
}

.hero::before {
  content: '';
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background:
    radial-gradient(ellipse 70% 50% at 50% -10%, rgba(29,91,184,0.09) 0%, transparent 70%);
  pointer-events: none;
  z-index: -1;
}

.hero-badge {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 12px; font-weight: 600;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--blue-700);
  background: var(--blue-50);
  border: 1px solid var(--blue-100);
  padding: 6px 16px; border-radius: 100px;
  margin-bottom: 32px;
}

.hero-badge-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--blue-500); flex-shrink: 0;
  animation: pulse 2s ease infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.8); }
}

.hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(40px, 5.5vw, 68px);
  font-weight: 400;
  line-height: 1.1;
  color: var(--blue-900);
  margin-bottom: 12px;
  letter-spacing: -0.01em;
}

.hero h1 .abbr {
  font-style: italic;
  color: var(--blue-600);
}

.hero-sub {
  font-family: 'Playfair Display', serif;
  font-size: clamp(15px, 1.8vw, 19px);
  font-weight: 400;
  font-style: italic;
  color: var(--gray-500);
  margin-bottom: 28px;
  letter-spacing: 0.01em;
}

.hero-desc {
  font-size: 17px; font-weight: 300;
  color: var(--gray-700);
  line-height: 1.75;
  max-width: 600px;
  margin: 0 auto 44px;
}

.hero-actions {
  display: flex; align-items: center; justify-content: center;
  gap: 14px; flex-wrap: wrap;
  margin-bottom: 64px;
}

.btn-primary {
  background: var(--blue-700);
  color: var(--white);
  padding: 14px 32px;
  border-radius: 8px;
  font-size: 15px; font-weight: 600;
  text-decoration: none;
  display: inline-flex; align-items: center; gap: 8px;
  transition: background 0.2s, transform 0.15s;
  letter-spacing: 0.01em;
}
.btn-primary:hover { background: var(--blue-800); transform: translateY(-2px); }

.btn-ghost {
  color: var(--blue-700);
  padding: 14px 28px;
  border-radius: 8px;
  border: 1px solid var(--blue-100);
  font-size: 15px; font-weight: 500;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s;
}
.btn-ghost:hover { background: var(--blue-50); border-color: var(--blue-400); }

/* Stats row */
.stats-row {
  display: flex; justify-content: center;
  gap: 0;
  border: 1px solid var(--gray-100);
  border-radius: 12px;
  overflow: hidden;
  max-width: 600px;
  margin: 0 auto;
}

.stat-cell {
  flex: 1;
  padding: 20px 16px;
  text-align: center;
  border-right: 1px solid var(--gray-100);
}
.stat-cell:last-child { border-right: none; }

.stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 28px; font-weight: 600;
  color: var(--blue-700);
  display: block;
}

.stat-label {
  font-size: 12px; font-weight: 400;
  color: var(--gray-500);
  text-transform: uppercase;
  letter-spacing: 0.07em;
}

/* ── DIVIDER ───────────────────────────────────── */
.divider {
  max-width: 960px; margin: 0 auto;
  border: none; border-top: 1px solid var(--gray-100);
}

/* ── FEATURES ──────────────────────────────────── */
.features {
  padding: 100px 6vw;
  max-width: 960px;
  margin: 0 auto;
  text-align: center;
}

.section-eyebrow {
  font-size: 11px; font-weight: 600;
  letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--blue-500);
  margin-bottom: 14px;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(28px, 3vw, 40px);
  font-weight: 400;
  color: var(--blue-900);
  margin-bottom: 14px;
  line-height: 1.2;
}

.section-desc {
  font-size: 16px; font-weight: 300;
  color: var(--gray-500);
  max-width: 500px;
  margin: 0 auto 60px;
  line-height: 1.7;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: var(--gray-100);
  border: 1px solid var(--gray-100);
  border-radius: 14px;
  overflow: hidden;
  text-align: left;
}

.feat {
  background: var(--white);
  padding: 32px 28px;
  transition: background 0.2s;
}
.feat:hover { background: var(--blue-50); }

.feat-icon {
  width: 40px; height: 40px;
  background: var(--blue-50);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 18px;
}
.feat-icon svg { width: 20px; height: 20px; stroke: var(--blue-600); }

.feat-title {
  font-size: 14px; font-weight: 600;
  color: var(--blue-900);
  margin-bottom: 8px;
  letter-spacing: 0.01em;
}

.feat-desc {
  font-size: 13px; font-weight: 300;
  color: var(--gray-500);
  line-height: 1.65;
}

/* ── PROCESS ───────────────────────────────────── */
.process {
  background: var(--blue-900);
  padding: 100px 6vw;
  text-align: center;
}

.process .section-title { color: var(--white); }
.process .section-eyebrow { color: var(--blue-400); }
.process .section-desc { color: rgba(255,255,255,0.5); }

.steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 32px;
  max-width: 900px;
  margin: 0 auto;
  position: relative;
}

.steps::before {
  content: '';
  position: absolute;
  top: 22px; left: 11%; right: 11%;
  height: 1px;
  background: rgba(255,255,255,0.1);
}

.step { text-align: center; position: relative; }

.step-circle {
  width: 44px; height: 44px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px;
  font-size: 14px; font-weight: 600;
  color: rgba(255,255,255,0.5);
  position: relative; z-index: 1;
  background: var(--blue-900);
}

.step:first-child .step-circle {
  background: var(--blue-600);
  border-color: var(--blue-500);
  color: var(--white);
}

.step-title {
  font-size: 14px; font-weight: 600;
  color: var(--white);
  margin-bottom: 8px;
}

.step-desc {
  font-size: 12px; font-weight: 300;
  color: rgba(255,255,255,0.45);
  line-height: 1.6;
}

/* ── MODULES ───────────────────────────────────── */
.modules {
  padding: 100px 6vw;
  max-width: 960px;
  margin: 0 auto;
  text-align: center;
}

.modules-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  text-align: left;
  margin-top: 56px;
}

.module-row {
  display: flex; align-items: flex-start; gap: 16px;
  padding: 22px 24px;
  border: 1px solid var(--gray-100);
  border-radius: 10px;
  transition: border-color 0.2s, background 0.2s;
}
.module-row:hover { border-color: var(--blue-100); background: var(--blue-50); }

.module-num {
  font-family: 'Playfair Display', serif;
  font-size: 22px; font-weight: 600;
  color: var(--blue-100);
  line-height: 1;
  min-width: 28px;
  padding-top: 2px;
}

.module-info {}

.module-name {
  font-size: 14px; font-weight: 600;
  color: var(--blue-900);
  margin-bottom: 4px;
}

.module-detail {
  font-size: 13px; font-weight: 300;
  color: var(--gray-500);
  line-height: 1.55;
}

/* ── CTA ───────────────────────────────────────── */
.cta {
  background: var(--blue-50);
  border-top: 1px solid var(--blue-100);
  border-bottom: 1px solid var(--blue-100);
  padding: 100px 6vw;
  text-align: center;
}

.cta-inner {
  max-width: 560px;
  margin: 0 auto;
}

.cta h2 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(28px, 3vw, 40px);
  font-weight: 400;
  color: var(--blue-900);
  margin-bottom: 16px;
  line-height: 1.2;
}

.cta p {
  font-size: 16px; font-weight: 300;
  color: var(--gray-500);
  margin-bottom: 36px;
  line-height: 1.7;
}

.cta-buttons {
  display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap;
}

/* ── FOOTER ────────────────────────────────────── */
footer {
  background: var(--blue-900);
  padding: 40px 6vw;
}

.footer-inner {
  max-width: 960px;
  margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 16px;
}

.footer-brand {
  display: flex; align-items: center; gap: 12px;
}

.footer-seal {
  width: 32px; height: 32px;
  background: var(--blue-700);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}
.footer-seal svg { width: 16px; height: 16px; }

.footer-name {
  font-size: 13px; font-weight: 600;
  color: rgba(255,255,255,0.7);
  letter-spacing: 0.04em;
}

.footer-links {
  display: flex; gap: 24px; list-style: none;
}
.footer-links a {
  font-size: 13px; font-weight: 300;
  color: rgba(255,255,255,0.35);
  text-decoration: none;
  transition: color 0.2s;
}
.footer-links a:hover { color: rgba(255,255,255,0.7); }

.footer-copy {
  font-size: 12px; font-weight: 300;
  color: rgba(255,255,255,0.25);
}

/* ── ANIMATIONS ────────────────────────────────── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(28px); }
  to   { opacity: 1; transform: translateY(0); }
}

.hero > * { animation: fadeUp 0.65s ease both; }
.hero > *:nth-child(1) { animation-delay: 0.0s; }
.hero > *:nth-child(2) { animation-delay: 0.1s; }
.hero > *:nth-child(3) { animation-delay: 0.18s; }
.hero > *:nth-child(4) { animation-delay: 0.26s; }
.hero > *:nth-child(5) { animation-delay: 0.34s; }
.hero > *:nth-child(6) { animation-delay: 0.42s; }

/* ── RESPONSIVE ────────────────────────────────── */
@media (max-width: 768px) {
  .features-grid { grid-template-columns: 1fr 1fr; }
  .steps { grid-template-columns: 1fr 1fr; gap: 24px; }
  .steps::before { display: none; }
  .modules-list { grid-template-columns: 1fr; }
  .nav-full { display: none; }
}

@media (max-width: 520px) {
  .features-grid { grid-template-columns: 1fr; }
  .steps { grid-template-columns: 1fr; }
  .stats-row { flex-wrap: wrap; }
  .stat-cell { border-right: none; border-bottom: 1px solid var(--gray-100); }
  .stat-cell:last-child { border-bottom: none; }
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="#" class="nav-brand">
    <div class="nav-seal">
      <svg viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="11" cy="11" r="9" stroke="white" stroke-width="1.2"/>
        <path d="M11 6v5l3 3" stroke="white" stroke-width="1.4" stroke-linecap="round"/>
        <circle cx="11" cy="11" r="1.5" fill="white"/>
      </svg>
    </div>
    <div class="nav-name">
      <span class="nav-abbr">PPMMIS</span>
      <span class="nav-full">Physical Plant Maintenance &amp; MIS</span>
    </div>
  </a>
  <div class="nav-right">
    <a href="/login" class="btn-login">Log in</a>
    <a href="/register" class="btn-register">Register</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge">
    <span class="hero-badge-dot"></span>
    Facility Management Platform
  </div>

  <h1><span class="abbr">PPMMIS</span></h1>
  <p class="hero-sub">Physical Plant Maintenance &amp; Management Information System</p>

  <p class="hero-desc">
    A centralized digital platform designed to streamline work order management,
    preventive maintenance scheduling, and asset tracking for your institution's physical plant operations.
  </p>

  <div class="hero-actions">
    <a href="/login" class="btn-primary">
      Log in to System
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
    <a href="#features" class="btn-ghost">Learn more</a>
  </div>

  <div class="stats-row">
    <div class="stat-cell">
      <span class="stat-num">6</span>
      <span class="stat-label">Core Modules</span>
    </div>
    <div class="stat-cell">
      <span class="stat-num">100%</span>
      <span class="stat-label">Web-Based</span>
    </div>
    <div class="stat-cell">
      <span class="stat-num">24/7</span>
      <span class="stat-label">Accessible</span>
    </div>
  </div>
</section>

<hr class="divider" />

<!-- FEATURES -->
<section class="features" id="features">
  <p class="section-eyebrow">What it does</p>
  <h2 class="section-title">Built for facility teams</h2>
  <p class="section-desc">Everything your maintenance and physical plant staff need, unified in one system.</p>

  <div class="features-grid">
    <div class="feat">
      <div class="feat-icon">
        <svg viewBox="0 0 20 20" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
          <rect x="3" y="4" width="14" height="13" rx="2"/><path d="M7 2v4M13 2v4M3 9h14"/>
        </svg>
      </div>
      <div class="feat-title">Preventive Maintenance</div>
      <p class="feat-desc">Schedule and automate recurring maintenance tasks so no inspection or service is ever missed.</p>
    </div>
    <div class="feat">
      <div class="feat-icon">
        <svg viewBox="0 0 20 20" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
          <path d="M8 5H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="8" y="3" width="4" height="4" rx="1"/><path d="M8 12l2 2 4-4"/>
        </svg>
      </div>
      <div class="feat-title">Work Order Management</div>
      <p class="feat-desc">Create, assign, and track work orders from request to resolution with full audit trails.</p>
    </div>
    <div class="feat">
      <div class="feat-icon">
        <svg viewBox="0 0 20 20" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
          <path d="M3 9l8-5 8 5v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9z"/><polyline points="8 20 8 11 12 11 12 20"/>
        </svg>
      </div>
      <div class="feat-title">Asset Registry</div>
      <p class="feat-desc">Maintain a complete inventory of all physical assets with location, condition, and service history.</p>
    </div>
    <div class="feat">
      <div class="feat-icon">
        <svg viewBox="0 0 20 20" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
          <path d="M16 20v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8" cy="7" r="4"/><path d="M20 20v-2a4 4 0 0 0-3-3.87"/><path d="M14 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </div>
      <div class="feat-title">Personnel Management</div>
      <p class="feat-desc">Assign technicians by skill set and availability. Track workloads and manage staff responsibilities.</p>
    </div>
    <div class="feat">
      <div class="feat-icon">
        <svg viewBox="0 0 20 20" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
          <line x1="17" y1="19" x2="17" y2="9"/><line x1="11" y1="19" x2="11" y2="3"/><line x1="5" y1="19" x2="5" y2="13"/>
        </svg>
      </div>
      <div class="feat-title">Reports &amp; Analytics</div>
      <p class="feat-desc">Generate maintenance summaries, cost reports, and compliance logs for management review.</p>
    </div>
    <div class="feat">
      <div class="feat-icon">
        <svg viewBox="0 0 20 20" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
          <circle cx="10" cy="10" r="8"/><path d="M10 6v4l3 3"/>
        </svg>
      </div>
      <div class="feat-title">Real-Time Tracking</div>
      <p class="feat-desc">Monitor live status of all active jobs and equipment across the entire physical plant.</p>
    </div>
  </div>
</section>

<!-- PROCESS -->
<section class="process" id="how-it-works">
  <p class="section-eyebrow">Workflow</p>
  <h2 class="section-title">How it works</h2>
  <p class="section-desc">A simple four-step process from request to resolution.</p>

  <div class="steps">
    <div class="step">
      <div class="step-circle">01</div>
      <div class="step-title">Submit Request</div>
      <p class="step-desc">Log a maintenance request with location, priority, and description.</p>
    </div>
    <div class="step">
      <div class="step-circle">02</div>
      <div class="step-title">Assign &amp; Schedule</div>
      <p class="step-desc">Route to the right technician based on skill and availability.</p>
    </div>
    <div class="step">
      <div class="step-circle">03</div>
      <div class="step-title">Execute &amp; Document</div>
      <p class="step-desc">Technicians complete the job and log all actions and materials used.</p>
    </div>
    <div class="step">
      <div class="step-circle">04</div>
      <div class="step-title">Review &amp; Report</div>
      <p class="step-desc">Management reviews results and generates compliance-ready reports.</p>
    </div>
  </div>
</section>

<!-- MODULES -->
<section class="modules" id="modules">
  <p class="section-eyebrow">System Modules</p>
  <h2 class="section-title">Everything in one place</h2>
  <p class="section-desc">Six integrated modules covering every aspect of physical plant operations.</p>

  <div class="modules-list">
    <div class="module-row">
      <span class="module-num">01</span>
      <div class="module-info">
        <div class="module-name">Work Order Management</div>
        <p class="module-detail">End-to-end tracking of maintenance requests, job assignments, and resolutions.</p>
      </div>
    </div>
    <div class="module-row">
      <span class="module-num">02</span>
      <div class="module-info">
        <div class="module-name">Preventive Maintenance</div>
        <p class="module-detail">Scheduled inspections and recurring service tasks with automated reminders.</p>
      </div>
    </div>
    <div class="module-row">
      <span class="module-num">03</span>
      <div class="module-info">
        <div class="module-name">Asset &amp; Equipment Registry</div>
        <p class="module-detail">Complete inventory of facilities assets, condition monitoring, and service history.</p>
      </div>
    </div>
    <div class="module-row">
      <span class="module-num">04</span>
      <div class="module-info">
        <div class="module-name">Personnel &amp; Assignment</div>
        <p class="module-detail">Manage technician profiles, skill sets, workloads, and shift assignments.</p>
      </div>
    </div>
    <div class="module-row">
      <span class="module-num">05</span>
      <div class="module-info">
        <div class="module-name">Inventory &amp; Supplies</div>
        <p class="module-detail">Track spare parts, materials, and supply levels for maintenance operations.</p>
      </div>
    </div>
    <div class="module-row">
      <span class="module-num">06</span>
      <div class="module-info">
        <div class="module-name">Reports &amp; Audit Logs</div>
        <p class="module-detail">Generate detailed maintenance reports, compliance logs, and management dashboards.</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta" id="access">
  <div class="cta-inner">
    <h2>Ready to get started?</h2>
    <p>Log in to your account or register to access the Physical Plant Maintenance &amp; Management Information System.</p>
    <div class="cta-buttons">
      <a href="/login" class="btn-primary">
        Log in
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <a href="/register" class="btn-ghost">Create an account</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-seal">
        <svg viewBox="0 0 16 16" fill="none">
          <circle cx="8" cy="8" r="6.5" stroke="white" stroke-width="1"/>
          <path d="M8 4.5v3.5l2 2" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="footer-name">PPMMIS</span>
    </div>
    <ul class="footer-links">
      <li><a href="#features">Features</a></li>
      <li><a href="#how-it-works">How it works</a></li>
      <li><a href="#modules">Modules</a></li>
      <li><a href="/login">Log in</a></li>
    </ul>
    <span class="footer-copy">© 2026 PPMMIS. All rights reserved.</span>
  </div>
</footer>

</body>
</html>