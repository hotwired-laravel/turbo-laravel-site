@extends('_layouts.bootcamp')

@section('content')
  <h2>Search results for <em id="search-term" class="text-zinc-900/40"></em></h2>

  <div id="search-results"></div>

  <script>
  window.store = {
    @foreach ($bootcamp as $guide)
      "{{ Illuminate\Support\Str::slug($guide->title) }}": {
        "title": "{{ $guide->title }}",
        "content": "{{ json_encode(trim(Illuminate\Support\Str::after(strip_tags($guide->getContent()), "\n")), JSON_HEX_TAG, 512) }}",
        "url": "{{ $guide->getUrl() }}",
      },
    @endforeach
  }
  </script>
@endsection
