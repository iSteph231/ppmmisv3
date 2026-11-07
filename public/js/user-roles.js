document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('role-confirmation');

    function confirmRoleChange(form, select) {
        dialog.querySelector('[data-confirm-name]').textContent = form.dataset.userName;
        dialog.querySelector('[data-confirm-email]').textContent = form.dataset.userEmail;
        dialog.querySelector('[data-confirm-role]').textContent = select.selectedOptions[0].textContent;
        dialog.returnValue = '';

        return new Promise((resolve) => {
            dialog.addEventListener('close', () => resolve(dialog.returnValue === 'confirm'), { once: true });
            dialog.showModal();
        });
    }

    document.querySelectorAll('[data-role-form]').forEach((form) => {
        const select = form.querySelector('select[name="role"]');
        const status = form.querySelector('[data-role-status]');
        const button = form.querySelector('button[type="submit"]');
        let saving = false;

        select.addEventListener('change', () => {
            status.textContent = '';
            delete status.dataset.state;
        });
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (saving) {
                return;
            }
            saving = true;
            const confirmed = await confirmRoleChange(form, select);
            if (!confirmed) {
                saving = false;
                return;
            }
            button.disabled = true;
            const body = new FormData(form);
            select.disabled = true;
            status.textContent = 'Saving...';
            status.dataset.state = 'saving';
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
                status.dataset.state = 'saved';
                status.className = 'text-sm text-green-700';
            } catch (error) {
                status.dataset.state = 'error';
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
