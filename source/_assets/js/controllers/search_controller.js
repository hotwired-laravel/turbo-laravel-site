import { Controller } from "@hotwired/stimulus";

// Pressing "/" focuses the header search. On small screens the search sits in
// the collapsed menu, so it asks the menu to open first (search:reveal).
export default class extends Controller {
  static targets = ["web"];

  focus(event) {
    if (event.key !== "/") return;
    if (event.target.closest("input, textarea, [contenteditable]")) return;
    if (!this.hasWebTarget) return;

    event.preventDefault();

    if (!this.webTarget.offsetParent) this.dispatch("reveal");

    this.webTarget.focus();
  }
}
