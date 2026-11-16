(() => {
    const pendingForms = new Map();

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented
            || (form.method !== 'post' && !form.matches('[data-prevent-duplicate-submit]'))) {
            return;
        }
        if (pendingForms.has(form)) {
            event.preventDefault();
            return;
        }

        const buttons = [...form.elements].filter(element =>
            (element instanceof HTMLButtonElement || element instanceof HTMLInputElement)
            && element.type === 'submit');
        pendingForms.set(form, buttons.map(button => ({
            button,
            disabled: button.disabled,
            value: button.value,
            children: [...button.childNodes],
        })));
        if (event.submitter?.name) {
            const submittedValue = document.createElement('input');
            submittedValue.type = 'hidden';
            submittedValue.name = event.submitter.name;
            submittedValue.value = event.submitter.value;
            submittedValue.dataset.pendingSubmitter = '';
            form.append(submittedValue);
        }
        form.setAttribute('aria-busy', 'true');
        buttons.forEach(button => {
            button.disabled = true;
            if (button.tagName === 'INPUT') {
                button.value = 'Sending…';
            } else {
                const spinner = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                spinner.setAttribute('viewBox', '0 0 24 24');
                spinner.setAttribute('width', '18');
                spinner.setAttribute('height', '18');
                spinner.setAttribute('aria-hidden', 'true');
                spinner.style.cssText = 'display: inline-block; vertical-align: middle; margin-right: 0.5rem;';
                const arc = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                Object.entries({cx: 12, cy: 12, r: 9, fill: 'none', stroke: 'currentColor', 'stroke-width': 3, 'stroke-dasharray': '42 15'}).forEach(([name, value]) => arc.setAttribute(name, value));
                spinner.append(arc);
                button.replaceChildren(spinner, document.createTextNode('Sending…'));
                if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    spinner.animate([{transform: 'rotate(0deg)'}, {transform: 'rotate(360deg)'}], {duration: 800, iterations: Infinity});
                }
            }
        });
    });

    window.addEventListener('pageshow', () => {
        pendingForms.forEach((buttons, form) => {
            form.removeAttribute('aria-busy');
            form.querySelectorAll('[data-pending-submitter]').forEach(input => input.remove());
            buttons.forEach(({button, disabled, value, children}) => {
                button.disabled = disabled;
                if (button.tagName === 'INPUT') {
                    button.value = value;
                } else {
                    button.replaceChildren(...children);
                }
            });
        });
        pendingForms.clear();
    });
})();
