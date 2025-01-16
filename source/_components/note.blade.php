@props(['type' => 'info'])

@php
$boxClasses = [
    'info' => 'border-blue-50 bg-blue-50 text-blue-900',
    'warning' => 'border-yellow-50 bg-yellow-50 text-yellow-900',
    'danger' => 'border-red-50 bg-red-50 text-red-900',
];
@endphp

<div class="p-6 flex space-x-4 border border-opacity-75 bg-opacity-75 rounded shadow my-8 sm:p-8 {{ $boxClasses[$type] }}">
    <div>
        <svg class="!size-8 mt-3 !shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
    </div>

    <div class="[&_h4]:!my-0 [&_h4]:text-yellow-900">
        <div class="!mt-0 !font-bold !text-2xl flex items-center space-x-1">{{ $heading ?? 'Note' }}</div>
        <div class="!my-0 [&>p]:my-0">{{ $slot }}</div>
    </div>
</div>
