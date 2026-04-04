@props(['href' => null, 'type' => 'button', 'variant' => 'primary'])

@php
$base = 'inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition';
$variants = [
    'primary' => 'bg-slate-900 text-white hover:bg-slate-800',
    'secondary' => 'bg-slate-200 text-slate-900 hover:bg-slate-300',
    'danger' => 'bg-red-600 text-white hover:bg-red-500',
];
$classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
