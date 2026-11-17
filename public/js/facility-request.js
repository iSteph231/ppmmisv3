document.addEventListener("DOMContentLoaded", () => {
    const phone = document.getElementById("contact_number");
    if (!phone) return;
    phone.addEventListener("beforeinput", (event) => {
        if (event.data && /[^0-9]/.test(event.data)) {
            event.preventDefault();
        }
    });
    phone.addEventListener("input", () => {
        phone.value = phone.value.replace(/[^0-9]/g, "").slice(0, 11);
    });
});
