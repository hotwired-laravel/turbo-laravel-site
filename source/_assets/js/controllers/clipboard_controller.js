import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  #timeout;

  copy() {
    if (this.#timeout) {
      clearTimeout(this.#timeout);
    }

    navigator.clipboard.writeText(this.#code);
    this.element.setAttribute("data-copied", true);

    this.#timeout = setTimeout(
      () => this.element.removeAttribute("data-copied"),
      3000,
    );
  }

  get #code() {
    return this.element.querySelector("pre code").innerText;
  }
}
