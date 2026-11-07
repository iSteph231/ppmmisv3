document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-role-form]').forEach((form) => {
        const select = form.querySelector('select[name="role"]');
        const status = form.querySelector('[data-role-status]');
        const button = form.querySelector('button[type="submit"]');
        let saving = false;

        select.addEventListener('change', () => {
            status.textContent = 'Unsaved changes';
            status.className = 'text-sm text-gray-600';
        });
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (saving) {
                return;
            }
            saving = true;
            button.disabled = true;
            const body = new FormData(form);
            select.disabled = true;
            status.textContent = 'Saving...';
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
                select.value = result.role;
                status.textContent = 'Saved';
                status.className = 'text-sm text-green-700';
            } catch (error) {
                status.textContent = error.message || 'Could not confirm saving. Refresh before trying again.';
                status.className = 'text-sm text-red-700';
            } finally {
                select.disabled = false;
                button.disabled = false;
                saving = false;
            }
        });
    });
});
