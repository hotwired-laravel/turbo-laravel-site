@extends('_layouts.main')

@section('sub-nav-bootcamp-mobile')
  @include('_layouts.partials.subnav-mobile-menu', ['menu' => $page->navigation['bootcamp']])
@endsection

@section('body')
  <div class="bg-white/90 custom-border px-6 py-20 sm:flex space-x-6">
    <div class="sm:flex sm:space-x-6 sm:max-w-7xl w-full mx-auto">
      <aside class="relative w-1/4 hidden lg:block" data-search-target="aside">
        <div class="sticky top-4 block space-y-4">
            @include('_layouts.partials.search', ['href' => '/bootcamp/search', 'placeholder' => 'Search bootcamp... (press "/" to focus)'])

            @include('_layouts.partials.sidebar-menu', ['menu' => $page->navigation['bootcamp']])
        </div>
      </aside>

      <main class="flex-1 min-w-0">
        <div class="prose prose-xl overflow-hidden prose-zinc [&_code:not(pre_code)]:text-wrap [&_code:not(pre_code)]:break-words prose-headings:font-heading prose-headings:font-extrabold max-w-none mx-auto w-full">
            @yield('content')
        </div>

        @unless ($page->getPath() === '/bootcamp/search')
            @include('_layouts.partials.next-previous-links')
        @endunless
      </main>
    </div>
  </div>
@endsection
