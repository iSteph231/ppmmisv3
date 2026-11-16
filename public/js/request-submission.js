(() => {
    const pendingForms = new Map();

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!form.matches('[data-prevent-duplicate-submit]') || event.defaultPrevented) {
            return;
        }
        if (pendingForms.has(form)) {
            event.preventDefault();
            return;
        }

        const buttons = [...form.querySelectorAll('button[type="submit"], input[type="submit"]')];
        pendingForms.set(form, buttons.map(button => ({button, disabled: button.disabled, text: button.tagName === 'INPUT' ? button.value : button.textContent})));
        form.setAttribute('aria-busy', 'true');
        buttons.forEach(button => {
            button.disabled = true;
            if (button.tagName === 'INPUT') {
                button.value = 'Submitting…';
            } else {
                button.textContent = 'Submitting…';
            }
        });
    });

    window.addEventListener('pageshow', () => {
        pendingForms.forEach((buttons, form) => {
            form.removeAttribute('aria-busy');
            buttons.forEach(({button, disabled, text}) => {
                button.disabled = disabled;
                if (button.tagName === 'INPUT') {
                    button.value = text;
                } else {
                    button.textContent = text;
                }
            });
        });
        pendingForms.clear();
    });
})();
