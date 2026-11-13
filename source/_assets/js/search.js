import lunr from "lunr";

function showResults(results, store, term) {
  const resultsElement = document.querySelector("#search-results");

  document.querySelector("#search-term").textContent = term;

  let resultsHtml = "";

  if (results.length) {
    resultsHtml += '<ul class="search-results">';
    results.forEach((result) => {
      const item = store[result.ref];

      resultsHtml += `
      <li>
        <a href="${item.url}" class="search-result">
          <span class="search-result-title">${item.title}</span>
          <span class="search-result-excerpt">${item.content.replace("&quot;", "").substring(0, 250)}&hellip;</span>
        </a>
      </li>
      `;
    });

    resultsHtml += "</ul>";
  } else {
    resultsHtml += `
    <p>No results found.</p>
    `;
  }

  resultsElement.innerHTML = resultsHtml;
}

function getQuery(name) {
  const params = new URLSearchParams(window.location.search.substring(1));

  if (params.has(name)) {
    return decodeURIComponent(params.get(name).replace(/\+/g, "%20"));
  }

  return null;
}

let searchTerm = getQuery("q");

if (searchTerm) {
  // We have two search inputs on the page, the sidecar and mobile menu...
  let searchInputs = document.querySelectorAll(".search-input");

  searchInputs.forEach((searchInput) => {
    searchInput.setAttribute("value", searchTerm);
  });

  const index = lunr(function () {
    this.field("title", { boost: 10 });
    this.field("content");

    for (const key in window.store) {
      this.add({
        id: key,
        title: window.store[key].title,
        content: window.store[key].content,
      });
    }
  });

  const results = index.search(searchTerm);

  showResults(results, window.store, searchTerm);
}
