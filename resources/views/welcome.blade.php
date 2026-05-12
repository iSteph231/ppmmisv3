<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>PPMMIS</title>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet" />
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Source Sans 3', sans-serif;
  background: #ffffff;
  color: #111827;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 24px;
  -webkit-font-smoothing: antialiased;
}

.card {
  text-align: center;
  max-width: 720px;
  width: 100%;
}

.logo {
  width: 80px; height: 80px;
  background: #16a34a;
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 40px;
}
.logo svg { width: 44px; height: 44px; }

h1 {
  font-family: 'Source Serif 4', serif;
  font-size: 64px; font-weight: 600;
  color: #1f2937;
  letter-spacing: 0.01em;
  margin-bottom: 10px;
}

.subtitle {
  font-size: 18px; font-weight: 400;
  color: #9ca3af;
  font-style: italic;
  margin-bottom: 32px;
}

.divider {
  width: 56px; height: 1px;
  background: #e5e7eb;
  margin: 0 auto 32px;
}

.overview {
  font-size: 17px; font-weight: 400;
  color: #6b7280;
  line-height: 1.8;
  margin-bottom: 52px;
  max-width: 560px;
  margin-left: auto;
  margin-right: auto;
}

.actions {
  display: flex;
  gap: 14px;
  justify-content: center;
}

.btn-login {
  font-size: 16px; font-weight: 600;
  color: #ffffff;
  background: #16a34a;
  text-decoration: none;
  padding: 14px 40px;
  border-radius: 8px;
  transition: background 0.15s;
}
.btn-login:hover { background: #0a4a1e; }

.btn-register {
  font-size: 16px; font-weight: 500;
  color: #4b5563;
  text-decoration: none;
  padding: 14px 36px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  transition: border-color 0.15s, background 0.15s;
}
.btn-register:hover { border-color: #9ca3af; background: #f9fafb; }

footer {
  position: fixed; bottom: 0; left: 0; right: 0;
  text-align: center;
  padding: 20px;
  font-size: 13px;
  color: #d1d5db;
}
</style>
</head>
<body>

<div class="card">
  <div class="logo">
    <svg viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="2" y="2" width="9" height="9" rx="2" fill="white"/>
      <rect x="15" y="2" width="9" height="9" rx="2" fill="white" opacity="0.6"/>
      <rect x="2" y="15" width="9" height="9" rx="2" fill="white" opacity="0.6"/>
      <rect x="15" y="15" width="9" height="9" rx="2" fill="white" opacity="0.25"/>
    </svg>
  </div>

  <h1>PPMMIS</h1>
  <p class="subtitle">Physical Plant Maintenance &amp; Management Information System</p>

  <div class="divider"></div>

  <p class="overview">
    A web-based system for managing maintenance work orders, inspection reports,
    and physical plant operations within the institution.
  </p>

  <div class="actions">
    <a href="/login" class="btn-login">Log in</a>
    <a href="/register" class="btn-register">Register</a>
  </div>
</div>

<footer>© 2026 PPMMIS. All rights reserved.</footer>

</body>
</html>