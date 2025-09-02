document.addEventListener("turbo:load", function () {
    const accordionHeaders = document.querySelectorAll(".accordion-header");

    accordionHeaders.forEach((header) => {
        header.addEventListener("click", () => {
            const contents = document.querySelectorAll(".accordion-content");
            contents.forEach((content) => {
                content.classList.add("hidden");
            });

            const accordionContent = header.nextElementSibling;
            const accordionArrow = header.querySelector(".accordion-arrow");
            accordionContent.classList.toggle("hidden");
            accordionArrow.classList.toggle("rotate-180");
        });
    });
});
