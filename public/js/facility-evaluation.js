document.addEventListener("DOMContentLoaded", () => {
    const age = document.getElementById("evaluation-age");
    if (!age) return;
    age.addEventListener("beforeinput", (event) => {
        if (event.data && /[^0-9]/.test(event.data)) {
            event.preventDefault();
        }
    });
    age.addEventListener("input", () => {
        age.value = age.value.replace(/[^0-9]/g, "").slice(0, 2);
    });
});
