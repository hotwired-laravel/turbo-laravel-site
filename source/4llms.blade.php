<docs>
@foreach ($docs as $doc)
    <page title="{{ $doc->title }}" url="{{ $doc->getUrl() }}">
        {{ html_entity_decode(strip_tags($doc->getContent()), ENT_QUOTES | ENT_HTML5) }}
    </page>
@endforeach
</docs>
