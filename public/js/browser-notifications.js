document.addEventListener("DOMContentLoaded", () => {
    const panel = document.getElementById("browser-notifications");
    if (!panel || !("Notification" in window) || !window.isSecureContext) {
        return;
    }
    const button = panel.querySelector("[data-enable-notifications]");
    const status = panel.querySelector("[data-notification-status]");
    const key = `ppmmis-browser-notifications-${panel.dataset.userId}`;
    let memoryCursor = null;
    let busy = false;
    let stopped = false;
    let workerPromise;
    function readCursor() {
        try {
            const saved = localStorage.getItem(key);
            return saved !== null && /^\d+$/.test(saved) ? Number(saved) : memoryCursor;
        } catch {
            return memoryCursor;
        }
    }
    function saveCursor(cursor) {
        memoryCursor = cursor;
        try {
            localStorage.setItem(key, String(cursor));
        } catch {}
    }
    function updatePermission() {
        panel.hidden = Notification.permission === "granted";
        panel.style.display = panel.hidden ? "none" : "flex";
        button.hidden = Notification.permission === "denied";
        status.textContent = Notification.permission === "denied" ? "Browser notifications are blocked. Allow notifications in your browser’s site settings to receive new request alerts." : "Enable browser notifications for new work and facility requests.";
    }
    async function showNotification(item) {
        const options = {
            body: item.message,
            icon: panel.dataset.icon,
            tag: `${key}-${item.id}`,
            data: { url: item.url }
        };
        if ("serviceWorker" in navigator) {
            workerPromise ??= navigator.serviceWorker.register(panel.dataset.worker).then(async (registration) => {
                if (!registration.active) {
                    await new Promise((resolve, reject) => {
                        const worker = registration.installing || registration.waiting;
                        if (!worker) {
                            reject(new Error("Notification worker unavailable"));
                            return;
                        }
                        worker.addEventListener("statechange", () => {
                            if (worker.state === "activated") resolve();
                            if (worker.state === "redundant") reject(new Error("Notification worker failed"));
                        });
                    });
                }
                return registration;
            }).catch(() => null);
            const registration = await workerPromise;
            if (registration) {
                await registration.showNotification(item.title, options);
                return;
            }
        }
        const notification = new Notification(item.title, options);
        notification.onclick = () => {
            const url = new URL(item.url, window.location.href);
            if (url.origin === window.location.origin) {
                window.focus();
                window.location.assign(url.href);
            }
            notification.close();
        };
    }
    async function poll() {
        if (stopped || !navigator.onLine) return;
        const granted = Notification.permission === "granted";
        const cursor = granted ? readCursor() : null;
        const url = new URL(panel.dataset.endpoint, window.location.href);
        if (cursor !== null) url.searchParams.set("after", cursor);
        const response = await fetch(url, {
            credentials: "same-origin",
            cache: "no-store",
            headers: { Accept: "application/json" },
            signal: AbortSignal.timeout(1e4)
        });
        if (response.redirected || [
            401,
            403,
            419
        ].includes(response.status)) {
            stopped = true;
            return;
        }
        if (!response.ok) return;
        const payload = await response.json();
        if (String(payload.user_id) !== panel.dataset.userId) {
            stopped = true;
            return;
        }
        for (const item of payload.data) {
            if (Notification.permission !== "granted") return;
            await showNotification(item);
            saveCursor(item.id);
        }
        saveCursor(payload.cursor);
    }
    async function refresh() {
        if (busy || stopped) return;
        busy = true;
        try {
            updatePermission();
            if (navigator.locks) {
                await navigator.locks.request(key, { ifAvailable: true }, async (lock) => {
                    if (lock) await poll();
                });
            } else {
                await poll();
            }
        } catch {} finally {
            busy = false;
        }
    }
    button.addEventListener("click", async () => {
        button.disabled = true;
        try {
            await Notification.requestPermission();
            await refresh();
        } finally {
            button.disabled = false;
            updatePermission();
        }
    });
    updatePermission();
    refresh();
    window.setInterval(refresh, 1e4);
    window.addEventListener("online", refresh);
    document.addEventListener("visibilitychange", refresh);
});
