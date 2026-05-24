<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PPMMIS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

:root {
    --psu-blue: #003087;
    --psu-blue-dark: #001f5b;
    --psu-blue-deep: #071a3f;
    --psu-blue-soft: #e8efff;
    --psu-gold: #f5a800;
    --psu-gold-soft: #fff4cc;
    --text: #243044;
}

body {
    min-height: 100vh;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--text);
    background:
        radial-gradient(circle at top right, rgba(245, 168, 0, 0.18), transparent 30rem),
        linear-gradient(135deg, #f8fbff 0%, #f2f5fb 45%, #edf3ff 100%);
}

.page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.topbar {
    width: 100%;
    padding: 20px clamp(20px, 5vw, 72px);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--psu-blue-deep);
    font-weight: 800;
    letter-spacing: 0.06em;
}

.brand img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
}

.nav-actions {
    display: flex;
    gap: 10px;
}

.nav-link {
    color: var(--psu-blue);
    font-weight: 700;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 999px;
    transition: all 0.2s ease;
}

.nav-link:hover {
    background: var(--psu-blue-soft);
}

.hero {
    flex: 1;
    display: grid;
    place-items: center;
    padding: 24px clamp(20px, 5vw, 72px) 56px;
}

.hero-panel {
    width: min(100%, 1060px);
    display: grid;
    grid-template-columns: 1.08fr 0.92fr;
    overflow: hidden;
    border-radius: 18px;
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.09);
    box-shadow: 0 24px 60px rgba(0, 31, 91, 0.18);
}

.hero-content {
    padding: clamp(34px, 6vw, 64px);
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 22px;
    color: var(--psu-blue);
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.eyebrow::before {
    content: '';
    width: 34px;
    height: 3px;
    border-radius: 999px;
    background: var(--psu-gold);
}

h1 {
    max-width: 620px;
    color: var(--psu-blue-deep);
    font-size: clamp(2.7rem, 6vw, 5rem);
    line-height: 0.98;
    font-weight: 800;
    letter-spacing: 0;
}

.subtitle {
    margin-top: 18px;
    color: var(--psu-blue);
    font-size: clamp(1rem, 2vw, 1.2rem);
    font-weight: 700;
}

.description {
    max-width: 560px;
    margin-top: 18px;
    color: #64748b;
    font-size: 1rem;
    line-height: 1.7;
}

.actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 34px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 0 24px;
    border-radius: 999px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, var(--psu-gold) 0%, #ffd467 100%);
    color: var(--psu-blue-deep);
    box-shadow: 0 12px 24px rgba(184, 117, 0, 0.22);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(184, 117, 0, 0.28);
}

.btn-secondary {
    color: var(--psu-blue);
    border: 1px solid #cbdaf5;
    background: #ffffff;
}

.btn-secondary:hover {
    background: var(--psu-blue-soft);
}

.hero-visual {
    position: relative;
    min-height: 420px;
    display: grid;
    place-items: center;
    background:
        linear-gradient(135deg, rgba(0, 48, 135, 0.94), rgba(0, 31, 91, 0.98)),
        url('{{ asset('images/logo.png') }}') center / 260px no-repeat;
    border-left: 5px solid var(--psu-gold);
    overflow: hidden;
}

.hero-visual::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(245, 168, 0, 0.22), transparent 48%);
}

.seal {
    position: relative;
    width: min(58%, 230px);
    aspect-ratio: 1;
    border-radius: 50%;
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.26);
}

.footer {
    padding: 18px 24px 26px;
    color: #94a3b8;
    font-size: 0.8rem;
    text-align: center;
}

@media (max-width: 820px) {
    .topbar {
        align-items: flex-start;
        gap: 16px;
    }

    .hero-panel {
        grid-template-columns: 1fr;
    }

    .hero-visual {
        min-height: 260px;
        border-left: 0;
        border-top: 5px solid var(--psu-gold);
    }
}

@media (max-width: 520px) {
    .topbar {
        flex-direction: column;
    }

    .nav-actions,
    .actions {
        width: 100%;
    }

    .nav-link,
    .btn {
        flex: 1;
    }
}
</style>
</head>
<body>
<div class="page">
    <header class="topbar">
        <div class="brand">
            <img src="{{ asset('images/logo.png') }}" alt="Pangasinan State University Logo">
            <span>PPMMIS</span>
        </div>
        <nav class="nav-actions" aria-label="Primary">
            <a href="{{ route('login') }}" class="nav-link">Log in</a>
            <a href="{{ route('register') }}" class="nav-link">Register</a>
        </nav>
    </header>

    <main class="hero">
        <section class="hero-panel">
            <div class="hero-content">
                <p class="eyebrow">Pangasinan State University</p>
                <h1>PPMMIS</h1>
                <p class="subtitle">Physical Plant Maintenance and Management Information System</p>
                <p class="description">A simple system for tracking work requests, inspections, and maintenance activities.</p>
                <div class="actions">
                    <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary">Create account</a>
                </div>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <img src="{{ asset('images/logo.png') }}" alt="" class="seal">
            </div>
        </section>
    </main>

    <footer class="footer">&copy; {{ date('Y') }} PPMMIS. Pangasinan State University.</footer>
</div>
</body>
</html>
