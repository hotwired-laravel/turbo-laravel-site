@extends('_layouts.v1-docs')

@section('content')
  <h1>Search results for <span id="search-term" class="text-wire-strong"></span></h1>

  <div id="search-results"></div>

  <script>
  window.store = {
    @foreach ($v1 ?? [] as $docPage)
      "{{ Illuminate\Support\Str::slug($docPage->title) }}": {
        "title": "{{ $docPage->title }}",
        "content": "{{ json_encode(trim(Illuminate\Support\Str::after(strip_tags($docPage->getContent()), "\n")), JSON_HEX_TAG, 512) }}",
        "url": "{{ $docPage->getUrl() }}",
      },
    @endforeach
  }
  </script>
@endsection
