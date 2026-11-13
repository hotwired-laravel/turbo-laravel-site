import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["source"];

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
    const source = this.hasSourceTarget
      ? this.sourceTarget
      : this.element.querySelector("pre code");

    return source.innerText;
  }
}
