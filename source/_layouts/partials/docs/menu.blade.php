@foreach ($menu as $heading => $section)
  <p @class(['mb-1.5 px-3 text-[13px] leading-[18px] font-semibold text-ink-subtle', 'mt-6' => ! $loop->first])>{{ $heading }}</p>
  <ul>
    @foreach ($section['children'] as $text => $link)
      <li><a href="{{ $link }}" @if ($path === rtrim($link, '/')) aria-current="page" @endif>{{ $text }}</a></li>
    @endforeach
  </ul>
@endforeach
