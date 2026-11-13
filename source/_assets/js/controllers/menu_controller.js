import { Controller } from "@hotwired/stimulus";

// The site header's hamburger menu (below the lg breakpoint).
export default class extends Controller {
  static targets = ["button", "panel"];

  toggle() {
    this.#set(!this.#isOpen);
  }

  open() {
    this.#set(true);
  }

  close() {
    this.#set(false);
  }

  // Escape closes the menu and hands focus back to the toggle.
  dismiss() {
    if (!this.#isOpen) return;

    this.close();
    this.buttonTarget.focus();
  }

  closeOnOutsideClick(event) {
    if (this.#isOpen && !this.element.contains(event.target)) this.close();
  }

  get #isOpen() {
    return this.panelTarget.hasAttribute("data-open");
  }

  #set(open) {
    this.panelTarget.toggleAttribute("data-open", open);
    this.buttonTarget.setAttribute("aria-expanded", open);
  }
}
