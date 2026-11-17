(() => {
    const modal = document.getElementById("maintenance-completion-modal");
    const trigger = document.querySelector("[data-open-maintenance-completion]");
    if (!modal || !trigger) {
        return;
    }
    trigger.addEventListener("click", () => modal.showModal());
    modal.querySelector("[data-close-maintenance-completion]").addEventListener("click", () => modal.close());
    modal.addEventListener("close", () => trigger.focus());
    modal.addEventListener("click", (event) => {
        const bounds = modal.getBoundingClientRect();
        if (event.target === modal && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) {
            modal.close();
        }
    });
    if (modal.hasAttribute("data-reopen")) {
        modal.showModal();
    }
})();
