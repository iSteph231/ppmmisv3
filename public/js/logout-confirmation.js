(() => {
    const modal = document.getElementById("logout-confirmation-modal");
    if (!modal) {
        return;
    }
    let pendingForm = null;
    let trigger = null;
    const confirmedForms = new WeakSet();
    document.addEventListener("submit", (event) => {
        if (!(event.target instanceof HTMLFormElement) || !event.target.matches("[data-confirm-logout]") || event.defaultPrevented) {
            return;
        }
        if (confirmedForms.has(event.target)) {
            confirmedForms.delete(event.target);
            return;
        }
        event.preventDefault();
        if (!modal.open) {
            pendingForm = event.target;
            trigger = event.submitter;
            modal.showModal();
        }
    }, true);
    modal.querySelector("[data-cancel-logout]").addEventListener("click", () => modal.close());
    modal.querySelector("[data-accept-logout]").addEventListener("click", () => {
        if (!pendingForm) {
            return;
        }
        const form = pendingForm;
        pendingForm = null;
        confirmedForms.add(form);
        modal.close();
        form.requestSubmit();
    });
    modal.addEventListener("close", () => {
        pendingForm = null;
        trigger?.focus();
    });
    modal.addEventListener("click", (event) => {
        const bounds = modal.getBoundingClientRect();
        if (event.target === modal && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) {
            modal.close();
        }
    });
})();
