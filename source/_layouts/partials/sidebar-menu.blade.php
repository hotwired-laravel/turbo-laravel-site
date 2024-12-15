<ul class="[&_.children]:mb-4">
  @foreach ($menu as $heading => $section)
  <li class="px-2 py-1 font-heading font-extrabold uppercase text-sm text-zinc-900/40">{{ $heading }}</li>
  <li class="children mt-2">
    <ul class="list-inside space-y-1 text-lg">
      @foreach ($section['children'] as $text => $link)
      <li><a href="{{ $link }}" class="{{ $page->selected($link) ? 'bg-zinc-900/20' : '' }} transition hover:bg-zinc-900/20 w-full inline-block px-2 py-1 rounded">{{ $text }}</a></li>
      @endforeach
    </ul>
  </li>
  @endforeach
</ul>
