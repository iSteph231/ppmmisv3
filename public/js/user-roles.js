document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-role-autosave]').forEach((form) => {
        const select = form.querySelector('select[name="role"]');
        const status = form.querySelector('[data-role-status]');
        let savedRole = select.value;

        form.addEventListener('submit', (event) => event.preventDefault());
        select.addEventListener('change', async () => {
            const body = new FormData(form);
            select.disabled = true;
            status.textContent = 'Saving?';
            status.className = 'text-sm text-gray-600';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                    body,
                });
                if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) {
                    throw new Error('Session expired. Refresh the page and try again.');
                }
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.errors?.role?.[0] ?? result.message ?? 'Could not save. Try again.');
                }
                savedRole = result.role;
                select.value = savedRole;
                status.textContent = 'Saved';
                status.className = 'text-sm text-green-700';
            } catch (error) {
                select.value = savedRole;
                status.textContent = error.message || 'Could not confirm saving. Refresh before trying again.';
                status.className = 'text-sm text-red-700';
            } finally {
                select.disabled = false;
            }
        });
    });
});
