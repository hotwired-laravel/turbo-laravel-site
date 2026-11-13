<!DOCTYPE html>
<html lang="{{ $page->language ?? 'en' }}">
    <head>
        @include('_layouts.partials.head-meta')
        <link rel="preload" href="{{ rtrim($page->baseUrl, '/') }}/assets/fonts/OpenSans-ExtraBold.ttf" as="font" type="font/ttf" crossorigin>
        <link rel="preload" href="{{ rtrim($page->baseUrl, '/') }}/assets/fonts/Jost-Regular.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ rtrim($page->baseUrl, '/') }}/assets/fonts/Jost-Italic.woff2" as="font" type="font/woff2" crossorigin>
        @viteRefresh()
        <link rel="stylesheet" href="{{ vite('source/_assets/css/main.css') }}">
        <script defer type="module" src="{{ vite('source/_assets/js/main.js') }}"></script>
    </head>
    <body class="text-zinc-900 text-lg font-sans antialiased bg-zinc-700/5 min-h-screen" data-controller="search" data-action="keydown@window->search#focus">
        @include('_layouts.partials.header')

        @yield('body')

        <footer class="px-6 py-4 sm:px-20 sm:py-10 flex items-center space-x-2 justify-between">
          <p class="text-base text-center w-full">
            Turbo Laravel is maintained by <a href="{{ $page->tony_site_url }}" class="underline text-zinc-900/90">Tony Messias</a>. This is a <em>community project</em>, not affiliated with Laravel or Hotwire.
          </p>
        </footer>
    </body>
</html>
