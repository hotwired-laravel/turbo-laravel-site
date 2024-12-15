<form action="{{ $url }}" method="GET" class="px-4">
  <label for="search" class="sr-only">{{ $label ?? 'Search' }}</label>
  <input type="search" name="q" class="search-input rounded border border-zinc-900/5 px-4 py-2 text-base leading-3 shadow w-full" placeholder="{{ $placeholder }}" />
  <button type="submit" class="sr-only">Search</button>
</form>
