<form action="{{ $href }}" method="GET">
  <label for="search" class="sr-only">{{ $label ?? 'Search' }}</label>
  <input data-search-target="web" type="text" name="q" placeholder="{{ $placeholder }}" class="search-input rounded border border-zinc-900/5 px-4 py-2 text-base leading-3 shadow w-full" />
</form>
