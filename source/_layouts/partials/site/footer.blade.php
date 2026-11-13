<footer class="border-t border-[#243046] bg-ink text-night-muted">
  <div class="mx-auto flex max-w-[1248px] flex-wrap items-center justify-between gap-x-12 gap-y-6 px-6 py-12 text-[15px] leading-6">
    <div class="flex max-w-[560px] flex-col gap-1.5">
      <span class="font-display text-xl font-bold tracking-[-0.02em] text-night-ink"><span class="text-syntax-keyword">Turbo</span> Laravel</span>
      <span>Maintained by <a href="{{ $page->tony_site_url }}" class="underline hover:text-night-ink focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-syntax-keyword">Tony Messias</a>. A <em>community project</em>, not affiliated with Laravel or 37signals.</span>
    </div>

    <nav aria-label="Footer" class="flex flex-wrap gap-x-7 gap-y-3 font-semibold">
      @foreach ([
        'Documentation' => $page->current_docs_index,
        'Bootcamp' => $page->bootcamp_index,
        'GitHub' => $page->github_url,
        'Hotwire' => $page->hotwire_site_url,
      ] as $label => $href)
        <a href="{{ $href }}" class="hover:text-night-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-syntax-keyword">{{ $label }}</a>
      @endforeach
    </nav>
  </div>
</footer>
