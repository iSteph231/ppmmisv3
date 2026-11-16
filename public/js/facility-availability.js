document.addEventListener('DOMContentLoaded', () => {
    const facility = document.querySelector('[data-availability-url]');
    if (!facility) {
        return;
    }

    const date = document.getElementById('requested_date');
    const time = document.getElementById('requested_time');
    const message = document.getElementById('facility-availability');
    const submit = facility.form.querySelector('[type="submit"]');
    const options = [...facility.options].filter((option) => option.value);
    options.forEach((option) => { option.dataset.label = option.textContent; });
    let controller;
    let checking = false;

    async function checkAvailability(changed = false) {
        if (document.hidden || (checking && !changed)) {
            return;
        }
        controller?.abort();
        const current = new AbortController();
        controller = current;
        const slot = `${date.value}|${time.value}`;

        if (changed || !date.value || !time.value) {
            facility.disabled = true;
            submit.disabled = true;
        }
        if (!date.value || !time.value) {
            checking = false;
            message.textContent = 'Select a date and time to check available facilities.';
            return;
        }

        checking = true;
        if (changed) {
            message.textContent = 'Checking facility availability...';
        }
        const url = new URL(facility.dataset.availabilityUrl, window.location.origin);
        url.searchParams.set('requested_date', date.value);
        url.searchParams.set('requested_time', time.value);

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { Accept: 'application/json' },
                signal: AbortSignal.any([current.signal, AbortSignal.timeout(10000)]),
            });
            if (!response.ok || response.redirected) {
                throw new Error('Availability check failed');
            }
            const data = await response.json();
            if (current.signal.aborted || slot !== `${date.value}|${time.value}`) {
                return;
            }
            const unavailable = new Set(data.unavailable);
            const wasBooked = unavailable.has(facility.value);
            options.forEach((option) => {
                option.disabled = unavailable.has(option.value);
                option.textContent = option.dataset.label + (option.disabled ? ' - Reserved for this day' : '');
            });
            if (wasBooked) {
                facility.value = '';
            }
            const allBooked = options.every((option) => option.disabled);
            facility.disabled = allBooked;
            submit.disabled = allBooked;
            message.textContent = allBooked
                ? 'No facilities are available on this date. Choose another date.'
                : wasBooked
                    ? 'Your selected facility is reserved for this entire day. Choose another date or facility.'
                    : 'Facilities reserved for the selected day are disabled, regardless of time.';
        } catch {
            if (!current.signal.aborted) {
                facility.disabled = true;
                submit.disabled = true;
                message.textContent = 'Unable to check availability. Please check your connection; we will retry automatically.';
            }
        } finally {
            if (controller === current) {
                checking = false;
            }
        }
    }

    date.addEventListener('input', () => checkAvailability(true));
    date.addEventListener('change', () => checkAvailability(true));
    time.addEventListener('input', () => checkAvailability(true));
    time.addEventListener('change', () => checkAvailability(true));
    facility.addEventListener('focus', () => checkAvailability());
    window.addEventListener('focus', () => checkAvailability(true));
    document.addEventListener('visibilitychange', () => checkAvailability(true));
    window.addEventListener('online', () => checkAvailability(true));
    checkAvailability(true);
    window.setInterval(() => checkAvailability(), 1000);
});
