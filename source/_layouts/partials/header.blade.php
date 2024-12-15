<header class="px-6 py-4 sm:px-20 sm:py-10 flex items-center space-x-2 justify-between">
  <div class="flex items-center space-x-4 sm:space-x-2">
    <h1 class="text-3xl sm:text-4xl font-extrabold font-heading">
      <a href="/">Turbo Laravel</a>
    </h1>
  </div>

  <nav class="hidden sm:block">
    <ul class="flex items-center space-x-6 -my-2">
      <li>
        <div class="group relative py-2">
          <a href="/docs" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90 flex items-center space-x-1">
            <span>Documentation</span>

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </a>

          <div class="absolute p-4 top-14 inset-x opacity-0 scale-0 -translate-y-8 transition group-hover:translate-y-0 group-hover:scale-100 group-hover:opacity-100 rounded bg-white shadow z-10">
            <ul class="space-y-1">
              <li><a href="/docs/" class="block px-2 py-1 {{ ! str_starts_with($page->getPath(), '/1.x/') ? 'bg-zinc-900/20' : '' }} rounded">Version <span class="">{{ $page->current_version }}</span></a></li>
              <li><a href="/1.x/" class="block px-2 py-1 {{ str_starts_with($page->getPath(), '/1.x') ? 'bg-zinc-900/20' : '' }} rounded">Version <span class="">{{ $page->latest_v1 }}</span></a></li>
            </ul>
          </div>
        </div>
      </li>
      <li><a href="{{ $page->github_url }}" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Source Code</a></li>
      <li><a href="/demo" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Demo</a></li>
    </ul>
  </nav>
</header>
