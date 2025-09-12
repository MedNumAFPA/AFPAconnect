import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // The `connect()` method is automatically called every time
    // the data-controller appears on the page, even on "back" navigation.
    connect() {
        // `this.element` is the button itself
        this.element.addEventListener('click', this.toggleMenu);
    }

    toggleMenu() {
        const mobileMenu = document.getElementById("mobileMenu");
        if (mobileMenu) {
            mobileMenu.classList.toggle("hidden");
        }
    }

    // The `disconnect()` method is called when the element is removed,
    // which is good practice for cleaning up listeners, though not strictly necessary here.
    disconnect() {
        this.element.removeEventListener('click', this.toggleMenu);
    }
}
