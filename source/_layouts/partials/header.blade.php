<header class="px-4 py-6 lg:px-10 xl:px-20 lg:py-10 flex items-center space-x-2 justify-between">
  <div class="relative flex-1 lg:flex-auto flex justify-between items-center space-x-4 sm:space-x-2">
    <h1 class="text-3xl sm:text-4xl font-extrabold font-heading">
      <a href="/">Turbo Laravel</a>
    </h1>

    <nav class="inline-block lg:hidden">
      <details class="group" data-search-target="nav">
        <summary class="list-none">
          <span>
            <span class="sr-only">Navigation</span>

            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 rotate-0 transition group-open:rotate-90">
              <path class="opacity-100 transition group-open:opacity-0" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
              <path class="transition opacity-0 group-open:opacity-100" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </span>
        </summary>

        <div class="bg-white/50 z-10 absolute top-12 -inset-x-4 backdrop-blur-xl">
          <div class="bg-zinc-700/5 px-4 py-6 backdrop-blur">
            @include('_layouts.partials.nav-search-mobile', [
              'url' => str_starts_with($page->getPath(), '/1.x/') ? '/1.x/docs/search' : '/docs/search',
              'placeholder' => str_starts_with($page->getPath(), '/1.x/') ? 'Search 1.x docs...' : 'Search 2.x docs...',
            ])

            <ul class="space-y-6 -mb-2 mt-4">
              <li>
                <a href="/docs" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90 flex items-center space-x-1">
                  <span>Documentation</span>
                </a>

                @yield('sub-nav-docs-mobile')
              </li>
              <li>
                <a href="{{ $page->github_url }}" class="px-4 py-2 font-medium rounded-full transition hover:bg-zinc-900/20">Source Code</a>
              </li>
              <li class="space-y-6">
                <a href="{{ $page->bootcamp_index }}" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Bootcamp</a>
                @yield('sub-nav-bootcamp-mobile')
              </li>
              <!-- <li><a href="/demo" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Demo</a></li> -->
            </ul>
          </div>
        </div>
      </details>
    </nav>
  </div>

  <nav class="hidden lg:block">
    <ul class="flex items-center space-x-6 -my-2">
      <li>
        <div class="group relative py-2">
          <a href="/docs" class="{{ str_contains($page->getPath(), '/docs/') ? 'bg-white/90' : '' }} px-4 py-2 font-medium rounded-full transition hover:bg-white/90 flex items-center space-x-1">
            <span>Documentation</span>

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </a>

          <div class="absolute p-4 top-14 inset-x opacity-0 scale-0 -translate-y-8 transition group-hover:translate-y-0 group-hover:scale-100 group-hover:opacity-100 rounded bg-white shadow z-10">
            <ul class="space-y-1">
              <li><a href="{{ $page->current_docs_index }}" class="block px-2 py-1 {{ ! str_starts_with($page->getPath(), '/1.x/') ? 'bg-zinc-900/20' : '' }} rounded">Version <span class="">{{ $page->current_version }}</span></a></li>
              <li><a href="{{ $page->v1_docs_index }}" class="block px-2 py-1 {{ str_starts_with($page->getPath(), '/1.x') ? 'bg-zinc-900/20' : '' }} rounded">Version <span class="">{{ $page->latest_v1 }}</span></a></li>
            </ul>
          </div>
        </div>
      </li>
      <li><a href="{{ $page->github_url }}" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Source Code</a></li>
      <li><a href="{{ $page->bootcamp_index }}" class="{{ str_contains($page->getPath(), '/bootcamp/') ? 'bg-white/90' : '' }} px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Bootcamp</a></li>
      <!-- <li><a href="/demo" class="{{ str_starts_with($page->getPath(), '/demo') ? 'bg-white/90' : '' }} px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Demo</a></li> -->
    </ul>
  </nav>
</header>
