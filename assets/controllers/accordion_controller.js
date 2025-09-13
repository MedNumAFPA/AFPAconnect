// assets/controllers/accordion_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // Find all accordion headers within this controller's element
        this.headers = this.element.querySelectorAll('.accordion-header');

        // Ensure that 'this' inside the toggle function refers to the controller
        this.toggle = this.toggle.bind(this);

        this.headers.forEach(header => {
            header.addEventListener('click', this.toggle);
        });
    }

    toggle(event) {
        const clickedHeader = event.currentTarget;
        const accordionContent = clickedHeader.parentElement.querySelector('.accordion-content');
        const accordionArrow = clickedHeader.querySelector('.accordion-arrow');

        // Check if the clicked item was already open
        const wasOpen = accordionContent && !accordionContent.classList.contains('hidden');

        // First, close all accordions within this controller's scope
        this.closeAll();

        // If the one we clicked was closed, open it.
        if (!wasOpen && accordionContent && accordionArrow) {
            accordionContent.classList.remove('hidden');
            accordionArrow.classList.add('rotate-180');
        }
    }

    closeAll() {
        this.element.querySelectorAll('.accordion-content').forEach(content => {
            content.classList.add('hidden');
        });
        this.element.querySelectorAll('.accordion-arrow').forEach(arrow => {
            arrow.classList.remove('rotate-180');
        });
    }

    disconnect() {
        // Clean up event listeners when the controller is removed from the page
        this.headers.forEach(header => {
            header.removeEventListener('click', this.toggle);
        });
    }
}
