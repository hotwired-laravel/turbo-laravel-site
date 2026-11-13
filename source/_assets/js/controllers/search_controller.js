import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["web", "mobile", "aside", "nav"];

  focus(event) {
    if (event.key !== "/") return;
    if (event.target.closest("input, textarea, [contenteditable]")) return;

    event.preventDefault();

    const asideHidden =
      this.hasAsideTarget &&
      getComputedStyle(this.asideTarget).display === "none";

    if (asideHidden) {
      if (this.hasNavTarget) this.navTarget.open = true;
      if (this.hasMobileTarget) this.mobileTarget.focus();
    } else if (this.hasWebTarget) {
      this.webTarget.focus();
    }
  }
}
