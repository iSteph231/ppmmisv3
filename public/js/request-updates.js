document.addEventListener('DOMContentLoaded', () => {
    const selector = '[data-request-updates]';
    if (!document.querySelector(selector)) {
        return;
    }

    let refreshing = false;
    let stopped = false;

    function isEditing(region) {
        return region.contains(document.activeElement)
            || [...region.querySelectorAll('input:not([type="hidden"]), textarea, select')].some((input) => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    return input.checked !== input.defaultChecked;
                }
                if (input.tagName === 'SELECT') {
                    const defaultOption = [...input.options].find((option) => option.defaultSelected)
                        ?? input.options[0];
                    return input.value !== defaultOption?.value;
                }
                return input.value !== input.defaultValue;
            });
    }

    async function refreshRequests() {
        if (refreshing || stopped || document.hidden || !navigator.onLine) {
            return;
        }

        refreshing = true;
        try {
            const response = await fetch(window.location.href, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { Accept: 'text/html' },
                signal: AbortSignal.timeout(10000),
            });
            if (response.redirected || [401, 403, 404, 419].includes(response.status)) {
                stopped = true;
                return;
            }
            if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) {
                return;
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            document.querySelectorAll(selector).forEach((region) => {
                const updated = [...page.querySelectorAll(selector)].find(
                    (candidate) => candidate.dataset.requestUpdates === region.dataset.requestUpdates,
                );
                if (!updated || isEditing(region)) {
                    return;
                }
                updated.querySelectorAll('script').forEach((script) => script.remove());
                if (region.innerHTML !== updated.innerHTML) {
                    region.replaceChildren(...updated.childNodes);
                }
            });
            if (document.querySelector('.data-table') && typeof window.filterTable === 'function') {
                window.filterTable();
            }
        } catch {
            // Retry after temporary connection failures without interrupting the page.
        } finally {
            refreshing = false;
        }
    }

    document.addEventListener('visibilitychange', refreshRequests);
    window.addEventListener('online', refreshRequests);
    window.setInterval(refreshRequests, 3000);
});
