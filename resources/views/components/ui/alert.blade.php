@props(['type' => 'info'])

@php
$colors = [
    'info' => 'border-blue-200 bg-blue-50 text-blue-700',
    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
    'error' => 'border-red-200 bg-red-50 text-red-700',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border px-3 py-2 text-sm '.($colors[$type] ?? $colors['info'])]) }}>
    {{ $slot }}
</div>
