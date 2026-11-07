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
    min-height: 100svh;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    -webkit-text-size-adjust: 100%;
    text-size-adjust: 100%;
    color: var(--text);
    background:
        radial-gradient(circle at top right, rgba(245, 168, 0, 0.09), transparent 30rem),
        linear-gradient(135deg, #f8fbff 0%, #f2f5fb 45%, #edf3ff 100%);
}

.page {
    min-height: 100vh;
    min-height: 100svh;
    display: flex;
    flex-direction: column;
}

.topbar {
    width: 100%;
    max-width: 1160px;
    margin: 0 auto;
    padding: 24px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--psu-blue-deep);
    font-weight: 800;
    letter-spacing: 0.06em;
    flex-shrink: 0;
}

.brand img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
}

.nav-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.nav-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    color: var(--psu-blue);
    font-weight: 700;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 999px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.nav-link:hover {
    background: var(--psu-blue-soft);
}

.nav-register {
    border: 1px solid #cbdaf5;
    background: #ffffff;
}

a:focus-visible {
    outline: 3px solid var(--psu-blue);
    outline-offset: 4px;
}

.hero {
    flex: 1;
    display: grid;
    place-items: center;
    padding: 32px 32px 48px;
}

.hero-panel {
    width: min(100%, 1060px);
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr);
    overflow: hidden;
    border-radius: 24px;
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.09);
    box-shadow: 0 20px 48px rgba(0, 31, 91, 0.09);
}

.hero-content {
    padding: clamp(34px, 6vw, 64px);
    min-width: 0;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
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
    flex-shrink: 0;
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
    line-height: 1.5;
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
    text-align: center;
    white-space: nowrap;
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
    background: linear-gradient(145deg, var(--psu-blue), var(--psu-blue-dark));
    border-left: 3px solid var(--psu-gold);
    overflow: hidden;
}

.hero-visual::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), transparent 60%);
}

.seal {
    position: relative;
    width: min(58%, 230px);
    aspect-ratio: 1;
    height: auto;
    object-fit: contain;
    border-radius: 50%;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
}

.footer {
    padding: 16px 24px 24px;
    color: #64748b;
    font-size: 0.8rem;
    line-height: 1.6;
    text-align: center;
}

@media (max-width: 820px) {
    .topbar {
        padding: 20px 24px;
    }

    .hero-panel {
        grid-template-columns: minmax(0, 1fr);
        max-width: 560px;
    }

    .hero-visual {
        min-height: 190px;
        padding: 24px;
        border-left: 0;
        border-top: 3px solid var(--psu-gold);
    }

    .seal {
        width: 140px;
        max-width: 100%;
    }
}

@media (max-width: 520px) {
    .topbar {
        padding: 16px;
        gap: 12px;
    }

    .brand {
        gap: 8px;
        font-size: 14px;
    }

    .brand img {
        width: 36px;
        height: 36px;
    }

    .nav-actions {
        gap: 4px;
    }

    .nav-link {
        padding: 10px;
        font-size: 13px;
    }

    .hero {
        padding: 20px 16px 28px;
    }

    .hero-panel {
        border-radius: 20px;
    }

    .hero-content {
        padding: 28px;
    }

    .eyebrow {
        font-size: 0.65rem;
        letter-spacing: 0.05em;
        margin-bottom: 18px;
    }

    .eyebrow::before {
        width: 24px;
    }

    h1 {
        font-size: clamp(2.5rem, 10vw, 3rem);
        line-height: 1.1;
    }

    .subtitle {
        margin-top: 14px;
        font-size: 1rem;
    }

    .description {
        margin-top: 14px;
        font-size: 0.875rem;
    }

    .actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 24px;
    }

    .btn {
        padding: 12px;
        font-size: 14px;
        font-weight: 700;
    }

    .footer {
        padding: 12px 24px 20px;
        font-size: 0.75rem;
    }
}

@media (max-width: 360px) {
    .hero-content {
        padding: 24px;
    }

    .actions {
        grid-template-columns: minmax(0, 1fr);
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
            <a href="{{ route('register') }}" class="nav-link nav-register">Register</a>
        </nav>
    </header>

    <main class="hero">
        <section class="hero-panel" aria-labelledby="hero-title">
            <div class="hero-content">
                <p class="eyebrow">Pangasinan State University</p>
                <h1 id="hero-title">PPMMIS</h1>
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
