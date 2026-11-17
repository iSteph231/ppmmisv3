(() => {
    const gallery = document.getElementById("photo");
    const camera = document.getElementById("camera-photo");
    const buttons = document.getElementById("photo-source-buttons");
    const takePhoto = document.getElementById("take-photo");
    const choosePhoto = document.getElementById("choose-photo");
    const status = document.getElementById("photo-status");
    const error = document.getElementById("photo-error");
    if (!gallery || !camera || !buttons) {
        return;
    }
    const mobileViewport = window.matchMedia("(max-width: 768px)");
    let selectedInput = gallery;
    function updatePickerVisibility() {
        buttons.hidden = !mobileViewport.matches;
        gallery.hidden = mobileViewport.matches || selectedInput !== gallery;
        camera.hidden = mobileViewport.matches || selectedInput !== camera;
        if (mobileViewport.matches) {
            camera.setAttribute("capture", "environment");
        } else {
            camera.removeAttribute("capture");
        }
    }
    takePhoto.addEventListener("click", () => camera.click());
    choosePhoto.addEventListener("click", () => gallery.click());
    [gallery, camera].forEach((input) => {
        input.addEventListener("change", () => {
            if (!input.files.length) {
                return;
            }
            const other = input === gallery ? camera : gallery;
            other.value = "";
            other.required = false;
            other.removeAttribute("name");
            selectedInput = input;
            input.name = "photo";
            input.required = true;
            status.textContent = `Selected: ${input.files[0].name}`;
            error.hidden = true;
            updatePickerVisibility();
        });
        input.addEventListener("invalid", (event) => {
            if (mobileViewport.matches) {
                event.preventDefault();
                error.hidden = false;
                choosePhoto.focus();
            }
        });
    });
    updatePickerVisibility();
    mobileViewport.addEventListener("change", updatePickerVisibility);
})();
