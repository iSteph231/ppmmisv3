(() => {
    let activeDots = [];
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    function refreshRequestDots() {
        const now = Date.now();
        activeDots = [...document.querySelectorAll("[data-new-request-expires]")].filter((dot) => {
            dot.hidden = Number(dot.dataset.newRequestExpires) <= now;
            return !dot.hidden;
        });
    }
    function animateRequestDots() {
        const now = Date.now();
        const breath = reducedMotion.matches ? 0 : (1 - Math.cos(now % 2200 / 2200 * Math.PI * 2)) / 2;
        if (!document.hidden) {
            activeDots.forEach((dot) => {
                if (!dot.isConnected || Number(dot.dataset.newRequestExpires) <= now) {
                    dot.hidden = true;
                    return;
                }
                const marker = dot.querySelector(".new-request-dot");
                if (!marker) {
                    return;
                }
                marker.style.transform = `scale(${1 + breath * .12})`;
                marker.style.boxShadow = `0 0 0 2px #eab308, 0 0 ${6 + breath * 12}px rgba(250, 204, 21, ${.4 + breath * .4})`;
                const ring = dot.querySelector(".new-request-ring");
                if (ring) {
                    ring.style.transform = `scale(${.85 + breath * .85})`;
                    ring.style.opacity = String(.85 - breath * .7);
                }
            });
        }
        window.requestAnimationFrame(animateRequestDots);
    }
    refreshRequestDots();
    window.setInterval(refreshRequestDots, 1e3);
    document.addEventListener("visibilitychange", refreshRequestDots);
    new MutationObserver(refreshRequestDots).observe(document.body, {
        childList: true,
        subtree: true
    });
    window.requestAnimationFrame(animateRequestDots);
})();
