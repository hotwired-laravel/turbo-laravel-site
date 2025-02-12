import { Application } from "@hotwired/stimulus";

import SearchController from "./controllers/search_controller.js";

window.Stimulus = Application.start();

Stimulus.register("search", SearchController);
