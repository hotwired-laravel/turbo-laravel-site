<div class="mx-4 pl-3 border-l-4 border-zinc-900/30">
  <ul class="">
    @foreach ($menu as $heading => $section)
    <li class="px-2 py-1 font-heading font-extrabold uppercase text-sm text-zinc-900/40">{{ $heading }}</li>
    <li class="children mt-2">
      <ul class="list-inside space-y-1 text-lg">
        @foreach ($section['children'] as $text => $link)
        <li><a href="{{ $link }}" class="{{ $page->selected($link) ? 'font-bold' : '' }} transition hover:bg-zinc-900/20 w-full inline-block px-2 py-1">{{ $text }}</a></li>
        @endforeach
      </ul>
    </li>
    @endforeach
  </ul>
</div>
