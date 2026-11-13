@extends('_layouts.site')

@php
  $config = [
    'docs' => [
      'menu' => $page->navigation['v2'],
      'label' => 'Docs',
      'home' => $page->current_docs_index,
      'nav' => 'Documentation',
      'edit' => 'https://github.com/hotwired-laravel/turbo-laravel/edit/2.x/docs/',
    ],
    'v1' => [
      'menu' => $page->navigation['v1'],
      'label' => 'Docs',
      'home' => $page->v1_docs_index,
      'nav' => 'Documentation',
      'edit' => 'https://github.com/hotwired-laravel/turbo-laravel/edit/1.x/docs/',
    ],
    'bootcamp' => [
      'menu' => $page->navigation['bootcamp'],
      'label' => 'Bootcamp',
      'home' => $page->bootcamp_index,
      'nav' => 'Bootcamp chapters',
      'edit' => 'https://github.com/hotwired-laravel/turbo-laravel-site/edit/main/source/_bootcamp/',
    ],
  ][$kind];

  $path = rtrim($page->getPath(), '/');
  $isSearch = str_ends_with($path, '/search');
  $doc = \App\DocContent::make($__env->yieldContent('content'));

  $currentSection = collect($config['menu'])
    ->filter(fn ($section) => in_array($path, collect($section['children'])->all(), true))
    ->keys()
    ->first();

  $neighbors = $isSearch ? [] : array_filter([
    'Previous' => $page->getPrevious(),
    'Next' => $page->getNext(),
  ]);

  $neighborTitle = function ($neighbor) use ($kind) {
    $number = $kind === 'bootcamp' ? \App\DocContent::chapterNumber($neighbor->getContent()) : null;

    return trim("{$number} {$neighbor->title}");
  };
@endphp

@section('title', $isSearch ? "Search | {$config['label']} | Turbo Laravel" : ($kind === 'bootcamp' ? sprintf('%s | Bootcamp | Turbo Laravel', $page->title) : sprintf('%s | Turbo Laravel', $page->title)))

@section('body')
<div class="mx-auto flex max-w-[1248px] flex-wrap items-start gap-x-12 gap-y-8 px-6 pt-12 pb-24">
  {{-- Sidebar (collapsible on small screens) --}}
  <details class="doc-side-mobile w-full rounded-[10px] border border-line bg-paper-raised lg:hidden">
    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 px-4 py-2.5 text-[15px] font-semibold [&::-webkit-details-marker]:hidden">
      <span class="min-w-0 truncate"><span class="text-ink-subtle">{{ $currentSection ?? $config['label'] }} /</span> {{ $isSearch ? 'Search' : $page->title }}</span>
      <svg class="shrink-0 transition [[open]>summary>&]:rotate-180" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
    </summary>
    <nav aria-label="{{ $config['nav'] }}" class="doc-side border-t border-line px-2 pt-4 pb-3">
      @include('_layouts.partials.docs.menu', ['menu' => $config['menu'], 'path' => $path])
    </nav>
  </details>

  <nav aria-label="{{ $config['nav'] }}" class="doc-side sticky top-6 hidden max-h-[calc(100vh-3rem)] max-w-[232px] min-w-0 flex-[1_1_220px] overflow-y-auto lg:block">
    @include('_layouts.partials.docs.menu', ['menu' => $config['menu'], 'path' => $path])
  </nav>

  {{-- Article --}}
  <main class="max-w-[720px] min-w-0 flex-[999_1_560px]">
    <article class="doc-article" data-kind="{{ $kind }}">
      @if ($currentSection)
        <p class="mb-3 text-sm leading-5 text-ink-subtle">
          <a href="{{ $config['home'] }}" class="text-ink-subtle hover:text-ink">{{ $config['label'] }}</a>
          <span aria-hidden="true">/</span>
          {{ $currentSection }}
        </p>
      @endif

      @if ($doc->title)
        <h1 id="top" class="doc-title">{!! $doc->title !!}</h1>
      @endif

      @if ($page->video)
        <a href="{{ $page->video }}" aria-label="Play video: {{ $doc->plainTitle() }} (YouTube)" class="relative mb-3 block aspect-video overflow-hidden rounded-2xl bg-ink text-night-ink transition hover:brightness-[1.08] focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-focus">
          <svg aria-hidden="true" viewBox="0 0 720 405" preserveAspectRatio="xMidYMid slice" class="absolute inset-0 size-full"><path d="M0 300 H720" stroke="#5cd8e5" stroke-width="3" stroke-linecap="round" stroke-dasharray="0 12" opacity="0.5"></path><circle cx="640" cy="60" r="120" fill="#18253a"></circle></svg>
          <span class="absolute top-6 left-7 text-sm leading-5 font-semibold text-night-muted">Turbo Laravel Bootcamp{{ $doc->number ? ' · Chapter '.rtrim($doc->number, '.') : '' }}</span>
          <span class="absolute bottom-6 left-7 font-display text-[clamp(28px,4vw,44px)] leading-[1.05] font-extrabold tracking-[-0.03em]">{{ $doc->plainTitle() }}</span>
          <span class="absolute top-1/2 left-1/2 flex size-20 -translate-1/2 items-center justify-center rounded-full bg-ember-strong pl-1"><svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z" fill="#ffffff"></path></svg></span>
          <span class="absolute right-6 bottom-7 hidden text-[13px] leading-[18px] font-semibold text-night-muted sm:inline">Watch on YouTube</span>
        </a>
        <p class="mb-10 text-sm leading-5 text-ink-subtle">Prefer reading? The written guide covers the same steps below.</p>
      @endif

      <div class="doc-body">
        {!! $doc->body !!}
      </div>
    </article>

    @if ($neighbors)
      <nav aria-label="Pagination" class="mt-14 grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 border-t border-line pt-8">
        @foreach ($neighbors as $direction => $neighbor)
          <a href="{{ $neighbor->getPath() }}" @class([
            'flex flex-col gap-1 rounded-xl border border-line bg-paper-raised px-5 py-4 text-ink transition-colors hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-focus',
            'items-end text-right' => $direction === 'Next',
          ])>
            <span class="inline-flex items-center gap-1 text-[13px] leading-[18px] text-ink-subtle">
              @if ($direction === 'Previous')
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"></path></svg>Previous
              @else
                Next<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"></path></svg>
              @endif
            </span>
            <span class="font-display text-xl leading-7 font-bold">{{ $neighborTitle($neighbor) }}</span>
          </a>
        @endforeach
      </nav>
    @endif
  </main>

  {{-- On this page --}}
  @unless ($isSearch)
    <aside aria-label="On this page" class="sticky top-6 hidden flex-[0_0_200px] flex-col gap-2.5 text-sm leading-5 min-[1100px]:flex">
      <p class="mb-0.5 text-[13px] leading-[18px] font-semibold text-ink-subtle">On this page</p>
      <a href="#top" class="font-semibold text-ink hover:underline hover:underline-offset-3">{{ $kind === 'bootcamp' ? 'Introduction' : $doc->plainTitle() }}</a>
      @foreach ($doc->toc as $item)
        <a href="#{{ $item['id'] }}" class="text-ink-muted hover:text-ink hover:underline hover:underline-offset-3">{{ $item['text'] }}</a>
      @endforeach
      <a href="{{ $config['edit'] . $page->getFilename() }}.md" class="mt-3.5 inline-flex items-center gap-1.5 border-t border-line pt-3.5 text-ink-muted hover:text-ink hover:underline hover:underline-offset-3">Edit this page on GitHub</a>
    </aside>
  @endunless
</div>
@endsection
