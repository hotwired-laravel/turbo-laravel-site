@php
  $path = $page->getPath();
  $isV1 = str_starts_with($path, '/1.x/');
  $section = str_starts_with($path, '/bootcamp') ? 'bootcamp' : (str_contains($path, '/docs') ? 'docs' : null);

  [$searchAction, $searchLabel] = match (true) {
    $section === 'bootcamp' => ['/bootcamp/search', 'Search bootcamp'],
    $isV1 => ['/1.x/docs/search', 'Search 1.x docs'],
    default => ['/docs/search', 'Search the docs'],
  };

  // On small screens the links stack in the menu panel; from lg up they sit in one row.
  $row = 'flex min-h-12 items-center border-b border-line lg:min-h-0 lg:border-0';
  $navlink = 'text-ink decoration-wire decoration-2 underline-offset-[6px] hover:underline aria-[current=page]:underline aria-[current=page]:decoration-ember focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-focus';
@endphp

<header class="relative border-b border-line bg-paper" data-controller="menu" data-action="keydown.esc@window->menu#dismiss click@window->menu#closeOnOutsideClick search:reveal@window->menu#open">
  <nav aria-label="Main" class="mx-auto flex max-w-[1248px] items-center justify-between gap-x-8 px-6 py-[18px]">
    <a href="/" class="font-display text-2xl font-bold tracking-[-0.02em] text-ink"><span class="text-wire-strong">Turbo</span> Laravel</a>

    <button type="button" class="group -mr-2 inline-flex size-11 items-center justify-center rounded-[10px] text-ink hover:bg-paper-sunken focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus lg:hidden" aria-expanded="false" aria-controls="site-menu" data-menu-target="button" data-action="menu#toggle">
      <span class="sr-only">Menu</span>
      <svg class="group-aria-expanded:hidden" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
      <svg class="hidden group-aria-expanded:block" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg>
    </button>

    <div id="site-menu" data-menu-target="panel" class="absolute inset-x-0 top-full z-30 hidden flex-col border-b border-line bg-paper px-6 pt-2 pb-6 text-base font-semibold shadow-lift data-open:flex lg:static lg:flex lg:flex-row lg:flex-wrap lg:items-center lg:gap-x-7 lg:gap-y-3 lg:border-0 lg:bg-transparent lg:p-0 lg:text-[15px] lg:shadow-none">
      <div class="group relative border-b border-line lg:border-0">
        <a href="{{ $page->current_docs_index }}" @if ($section === 'docs') aria-current="page" @endif class="{{ $navlink }} flex min-h-12 items-center gap-1.5 lg:inline-flex lg:min-h-0">
          Documentation
          <svg class="hidden transition group-hover:rotate-180 group-focus-within:rotate-180 lg:block" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
        </a>

        <div class="pb-3 lg:invisible lg:absolute lg:top-full lg:left-0 lg:z-10 lg:pt-3 lg:pb-0 lg:opacity-0 lg:transition lg:group-focus-within:visible lg:group-focus-within:opacity-100 lg:group-hover:visible lg:group-hover:opacity-100">
          <ul class="flex flex-col gap-1 border-l-2 border-line pl-3 text-[15px] lg:min-w-52 lg:rounded-[10px] lg:border lg:border-line lg:bg-paper-raised lg:p-2 lg:shadow-lift">
            <li>
              <a href="{{ $page->current_docs_index }}" @if ($section === 'docs' && ! $isV1) aria-current="true" @endif class="flex items-center justify-between gap-4 rounded-md px-3 py-2 text-ink hover:bg-paper-sunken aria-current:bg-wire-soft">
                Version {{ $page->current_version }}
                <span class="rounded-full bg-wire-soft px-2 font-code text-[11px] leading-[18px] font-medium text-wire-strong">latest</span>
              </a>
            </li>
            <li>
              <a href="{{ $page->v1_docs_index }}" @if ($isV1) aria-current="true" @endif class="flex rounded-md px-3 py-2 text-ink hover:bg-paper-sunken aria-current:bg-wire-soft">Version {{ $page->latest_v1 }}</a>
            </li>
          </ul>
        </div>
      </div>

      <div class="{{ $row }}">
        <a href="{{ $page->bootcamp_index }}" @if ($section === 'bootcamp') aria-current="page" @endif class="{{ $navlink }}">Bootcamp</a>
      </div>

      <div class="{{ $row }}">
        <a href="{{ $page->github_url }}" class="{{ $navlink }} inline-flex items-center gap-1.5">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 19c-4 1.3-4-2-6-2.5m12 5v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1-.3-3.4 1.3a11.7 11.7 0 0 0-6.2 0C6.6 3.3 5.6 3.6 5.6 3.6a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4.2 10c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"></path></svg>
          Source code
        </a>
      </div>

      <span class="hidden rounded-full border-[1.5px] border-line-control px-2.5 py-0.5 font-code text-[12.5px] leading-[18px] font-medium text-ink-muted lg:inline">v{{ $isV1 ? '1' : strtok($page->current_version, '.') }}.x</span>

      <form action="{{ $searchAction }}" method="GET" role="search" class="order-first py-3 lg:order-none lg:py-0">
        <label class="flex min-h-11 min-w-[216px] cursor-text items-center gap-2 rounded-[10px] border-[1.5px] border-line-control bg-paper-raised px-3 py-[9px] text-ink-subtle focus-within:outline-2 focus-within:outline-offset-3 focus-within:outline-focus">
          <svg class="shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m20 20-4.4-4.4"></path></svg>
          <span class="sr-only">{{ $searchLabel }}</span>
          <input data-search-target="web" type="search" name="q" placeholder="{{ $searchLabel }}" class="search-input w-0 min-w-0 flex-1 bg-transparent text-base leading-5 font-normal text-ink placeholder:text-ink-subtle focus:outline-none lg:text-[15px]">
          <kbd class="hidden rounded-md border border-line bg-paper-sunken px-1.5 py-px font-code text-xs font-medium text-ink-subtle lg:inline">/</kbd>
        </label>
      </form>
    </div>
  </nav>
</header>
