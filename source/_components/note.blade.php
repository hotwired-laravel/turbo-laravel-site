@props(['type' => 'info'])

@php
$styles = [
    'info' => ['box' => 'border-wire/60 bg-wire-soft', 'icon' => 'text-wire-strong'],
    'warning' => ['box' => 'border-[#e8c98a] bg-[#fdf0d2]', 'icon' => 'text-[#8a5300]'],
    'danger' => ['box' => 'border-ember/40 bg-ember-soft', 'icon' => 'text-ember-strong'],
][$type];
@endphp

<div class="doc-note my-8 flex gap-4 rounded-xl border p-5 sm:p-6 {{ $styles['box'] }}">
    <svg class="mt-0.5 size-6 shrink-0 {{ $styles['icon'] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>

    <div class="min-w-0">
        <div class="font-display text-lg leading-6 font-bold text-ink">{{ $heading ?? 'Note' }}</div>
        <div class="doc-note-body mt-1">{{ $slot }}</div>
    </div>
</div>
