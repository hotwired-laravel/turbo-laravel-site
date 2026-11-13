@extends('_layouts.bootcamp')

@section('content')
  <h1>Search results for <span id="search-term" class="text-wire-strong"></span></h1>

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
