// assets/controllers/accordion_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {
        // `this.element` is the div with `data-controller="accordion"`
        this.headers = this.element.querySelectorAll('.accordion-header');

        // Bind `this` so `this` in `toggleAccordion` refers to the controller
        this.toggleAccordion = this.toggleAccordion.bind(this);

        this.headers.forEach(header => {
            header.addEventListener('click', this.toggleAccordion);
        });
    }

    toggleAccordion(event) {
        // `event.currentTarget` is the specific button/header that was clicked
        const header = event.currentTarget;
        const content = header.nextElementSibling;
        const icon = header.querySelector('.accordion-icon');

        if (!content || !content.classList.contains('accordion-content')) {
            console.error("Could not find the accordion-content element for", header);
            return;
        }

        const isHidden = content.classList.contains('hidden');

        // Optional: Close all other accordions when one is opened
        this.closeAllAccordions();

        // Toggle the one that was clicked
        if (isHidden) {
            content.classList.remove('hidden');
            if (icon) icon.classList.add('rotate-180');
        }
    }

    closeAllAccordions() {
        this.headers.forEach(header => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.accordion-icon');

            if (content && content.classList.contains('accordion-content')) {
                content.classList.add('hidden');
                if (icon) icon.classList.remove('rotate-180');
            }
        });
    }

    disconnect() {
        // Clean up the listeners when the controller is removed from the page
        this.headers.forEach(header => {
            header.removeEventListener('click', this.toggleAccordion);
        });
    }
}
