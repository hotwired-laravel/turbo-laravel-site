import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  copy() {
    navigator.clipboard.writeText(this.#code);
  }

  get #code() {
    return this.element.querySelector("pre code").innerText;
  }
}
