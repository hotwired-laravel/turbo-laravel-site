<!DOCTYPE html>
<html lang="{{ $page->language ?? 'en' }}">
    <head>
        @include('_layouts.partials.head-meta')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600..800&family=Figtree:ital,wght@0,400..700;1,400&family=JetBrains+Mono:wght@400;500&display=swap">
        @viteRefresh()
        <link rel="stylesheet" href="{{ vite('source/_assets/css/main.css') }}">
        <script defer type="module" src="{{ vite('source/_assets/js/main.js') }}"></script>
    </head>
    <body class="min-h-screen bg-paper font-body text-ink antialiased" data-controller="search" data-action="keydown@window->search#focus">
        @include('_layouts.partials.site.header')

        @yield('body')

        @include('_layouts.partials.site.footer')
    </body>
</html>
