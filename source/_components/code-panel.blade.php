@props(['accent' => 'wire', 'filename' => null])

<div @if ($filename) data-chrome @endif {{ $attributes->class([
  'code-panel overflow-hidden rounded-2xl bg-code-bg text-code-ink',
  'shadow-wire' => $accent === 'wire',
  'shadow-ember' => $accent === 'ember',
]) }}>
  @if ($filename)
    <div class="flex items-center gap-2 bg-code-chrome px-4 py-2.5 font-code text-[12.5px] leading-4 font-medium text-code-muted">
      <span @class(['size-2 rounded-full', 'bg-wire' => $accent === 'wire', 'bg-ember' => $accent === 'ember'])></span>{{ $filename }}
    </div>
  @endif

  {{ $slot }}
</div>
