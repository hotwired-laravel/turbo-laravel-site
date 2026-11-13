<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="canonical" href="{{ $page->getUrl() }}">
<meta name="description" content="{{ $page->description }}">
<title>@yield('title', $page->title)</title>
<link rel="apple-touch-icon" sizes="180x180" href="{{ rtrim($page->baseUrl, '/') }}/assets/manifest/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="{{ rtrim($page->baseUrl, '/') }}/assets/manifest/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="{{ rtrim($page->baseUrl, '/') }}/assets/manifest/favicon-16x16.png">
<link rel="manifest" href="{{ rtrim($page->baseUrl, '/') }}/assets/site.webmanifest">

<!-- Open Graph data -->
<meta property="og:title" content="@yield('title', $page->title)" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ $page->getUrl() }}" />

<meta property="og:image" content="{{ rtrim($page->baseUrl, '/') . $page->og_image }}" />
<meta property="og:image:type" content="image/png" />
<meta property="og:image:height" content="630" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:alt" content="Turbo Laravel, Hotwire for Laravel: HTML over the wire." />
<meta property="og:description" content="{{ $page->description ?? 'Build web and hybrid native apps in Laravel with Hotwire.' }}" />
<meta property="og:locale" content="en_US">
<meta property="og:site_name" content="Turbo Laravel" />

<!-- Twitter Card meta -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title', $page->title)">
<meta name="twitter:description" content="{{ $page->description ?? 'Build web and hybrid native apps in Laravel with Hotwire.' }}">
<meta name="twitter:image" content="{{ rtrim($page->baseUrl, '/') . $page->og_image }}">
<meta name="twitter:image:alt" content="Turbo Laravel, Hotwire for Laravel: HTML over the wire.">
