@props(['file'])

{{-- The code block inside already carries its copy button (see App\DocsMarkdownParser); this adds the filename bar. --}}
<div class="code-file">
    <div class="code-file-bar"><span class="truncate">{{ $file }}</span></div>

    {{ $slot }}
</div>
