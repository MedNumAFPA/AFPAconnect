document.addEventListener("turbo:load", () => {
    const toasts = document.getElementsByClassName("toast");
    Array.from(toasts).forEach((toast) => {
        setTimeout(() => {
            toast.classList.remove("flex");
            toast.classList.add("hidden");
        }, 3000);
        const closeBtn = document.getElementById("closeToast");
        closeBtn.addEventListener("click", () => {
            toast.classList.remove("flex");
            toast.classList.add("hidden");
        });
    });
});
