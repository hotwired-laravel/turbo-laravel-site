@php
  $path = $page->getPath();
  $isV1 = str_starts_with($path, '/1.x/');
  $section = str_starts_with($path, '/bootcamp') ? 'bootcamp' : (str_contains($path, '/docs') ? 'docs' : null);

  [$searchAction, $searchLabel] = match (true) {
    $section === 'bootcamp' => ['/bootcamp/search', 'Search bootcamp'],
    $isV1 => ['/1.x/docs/search', 'Search 1.x docs'],
    default => ['/docs/search', 'Search the docs'],
  };

  $navlink = 'text-ink decoration-wire decoration-2 underline-offset-[6px] hover:underline aria-[current=page]:underline aria-[current=page]:decoration-ember focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-focus';
@endphp

<header class="border-b border-line bg-paper">
  <nav aria-label="Main" class="mx-auto flex max-w-[1248px] flex-wrap items-center justify-between gap-x-8 gap-y-4 px-6 py-[18px]">
    <a href="/" class="font-display text-2xl font-bold tracking-[-0.02em] text-ink"><span class="text-wire-strong">Turbo</span> Laravel</a>

    <div class="flex flex-wrap items-center gap-x-7 gap-y-3 text-[15px] font-semibold">
      <div class="group relative">
        <a href="{{ $page->current_docs_index }}" @if ($section === 'docs') aria-current="page" @endif class="{{ $navlink }} inline-flex items-center gap-1.5">
          Documentation
          <svg class="transition group-hover:rotate-180 group-focus-within:rotate-180" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
        </a>

        <div class="invisible absolute left-0 top-full z-10 pt-3 opacity-0 transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
          <ul class="min-w-52 rounded-[10px] border border-line bg-paper-raised p-2 shadow-lift">
            <li>
              <a href="{{ $page->current_docs_index }}" class="flex items-center justify-between gap-4 rounded-md px-3 py-2 text-ink hover:bg-paper-sunken">
                Version {{ $page->current_version }}
                <span class="rounded-full bg-wire-soft px-2 font-code text-[11px] leading-[18px] font-medium text-wire-strong">latest</span>
              </a>
            </li>
            <li>
              <a href="{{ $page->v1_docs_index }}" class="flex rounded-md px-3 py-2 text-ink hover:bg-paper-sunken">Version {{ $page->latest_v1 }}</a>
            </li>
          </ul>
        </div>
      </div>

      <a href="{{ $page->bootcamp_index }}" @if ($section === 'bootcamp') aria-current="page" @endif class="{{ $navlink }}">Bootcamp</a>

      <a href="{{ $page->github_url }}" class="{{ $navlink }} inline-flex items-center gap-1.5">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 19c-4 1.3-4-2-6-2.5m12 5v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1-.3-3.4 1.3a11.7 11.7 0 0 0-6.2 0C6.6 3.3 5.6 3.6 5.6 3.6a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4.2 10c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"></path></svg>
        Source code
      </a>

      <span class="rounded-full border-[1.5px] border-line-control px-2.5 py-0.5 font-code text-[12.5px] leading-[18px] font-medium text-ink-muted">v{{ $isV1 ? '1' : strtok($page->current_version, '.') }}.x</span>

      <form action="{{ $searchAction }}" method="GET" role="search">
        <label class="flex min-h-11 min-w-[216px] cursor-text items-center gap-2 rounded-[10px] border-[1.5px] border-line-control bg-paper-raised px-3 py-[9px] text-ink-subtle focus-within:outline-2 focus-within:outline-offset-3 focus-within:outline-focus">
          <svg class="shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m20 20-4.4-4.4"></path></svg>
          <span class="sr-only">{{ $searchLabel }}</span>
          <input data-search-target="web" type="search" name="q" placeholder="{{ $searchLabel }}" class="search-input w-0 min-w-0 flex-1 bg-transparent text-[15px] leading-5 font-normal text-ink placeholder:text-ink-subtle focus:outline-none">
          <kbd class="rounded-md border border-line bg-paper-sunken px-1.5 py-px font-code text-xs font-medium text-ink-subtle">/</kbd>
        </label>
      </form>
    </div>
  </nav>
</header>
