import { Application } from "@hotwired/stimulus";

import SearchController from "./controllers/search_controller.js";
import ClipboardController from "./controllers/clipboard_controller.js";

window.Stimulus = Application.start();

Stimulus.register("search", SearchController);
Stimulus.register("clipboard", ClipboardController);
