<!DOCTYPE html>
<html lang="{{ $page->language ?? 'en' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="canonical" href="{{ $page->getUrl() }}">
        <meta name="description" content="{{ $page->description }}">
        <title>{{ $page->title }}</title>
        <link rel="apple-touch-icon" sizes="180x180" href="/assets/manifest/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/assets/manifest/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/assets/manifest/favicon-16x16.png">
        <link rel="manifest" href="/assets/site.webmanifest">
        <link rel="preload" href="/assets/fonts/OpenSans-ExtraBold.ttf" as="font" type="font/ttf" crossorigin>
        <link rel="preload" href="/assets/fonts/Jost-Regular.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/assets/fonts/Jost-Italic.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="stylesheet" href="{{ mix('css/main.css', 'assets/build') }}">
        <script defer src="{{ mix('js/main.js', 'assets/build') }}"></script>
    </head>
    <body class="text-zinc-900 text-lg font-sans antialiased bg-zinc-700/5 min-h-screen" data-controller="search" data-action="keydown@window->search#focus">
        @include('_layouts.partials.header')

        @yield('body')

        <footer class="px-6 py-4 sm:px-20 sm:py-10 flex items-center space-x-2 justify-between">
          <p class="text-base text-center w-full">
            Turbo Laravel is maintained by <a href="{{ $page->tony_site_url }}" class="underline text-zinc-900/90">Tony Messias</a>. This is a <em>community project</em>, therefore not affiliated with Laravel nor Hotwire.
          </p>
        </footer>
    </body>
</html>
