import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["web", "mobile", "aside", "nav"];

  focus(event) {
    if (event.key === "/") {
      event.preventDefault();

      if (getComputedStyle(this.asideTarget).display === "none") {
        if (this.navTarget) this.navTarget.open = true;
        this.mobileTarget?.focus();
      } else {
        this.webTarget?.focus();
      }
    }
  }
}
