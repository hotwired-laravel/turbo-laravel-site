<header class="px-6 py-4 sm:px-20 sm:py-10 flex items-center space-x-2 justify-between">
  <div class="flex items-center space-x-4 sm:space-x-2">
    <h1 class="text-3xl sm:text-4xl font-extrabold font-heading">
      <a href="/">Turbo Laravel</a>
    </h1>
  </div>

  <nav class="hidden sm:block">
    <ul class="flex items-center space-x-6">
      <li><a href="/docs" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Documentation</a></li>
      <li><a href="{{ $page->github_url }}" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Source Code</a></li>
      <li><a href="/demo" class="px-4 py-2 font-medium rounded-full transition hover:bg-white/90">Demo</a></li>
    </ul>
  </nav>
</header>
