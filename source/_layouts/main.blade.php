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

        <!-- Open Graph data -->
        <meta property="og:title" content="{{ $page?->title ?? 'Turbo Laravel' }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ $page->url }}" />

        <meta property="og:image" content="{{ rtrim($page->baseUrl, '/') . '/assets/images/turbo-laravel-meta.png' }}" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:description" content="{{ $page->description ?? 'Build web and hybrid native apps in Laravel with Hotwire.' }}" />
        <meta property="og:locale" content="en_US">
        <meta property="og:site_name" content="Turbo Laravel" />

        <!-- Twitter Card meta -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $page->title ?? 'Turbo Laravel' }}">
        <meta name="twitter:description" content="{{ $page->description ?? 'Build web and hybrid native apps in Laravel with Hotwire.' }}">
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
