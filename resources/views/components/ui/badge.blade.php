@props(['status' => 'default'])

@php
$colors = [
    'draft' => 'bg-slate-100 text-slate-700',
    'sent' => 'bg-blue-100 text-blue-700',
    'partial' => 'bg-amber-100 text-amber-700',
    'paid' => 'bg-emerald-100 text-emerald-700',
    'overdue' => 'bg-red-100 text-red-700',
    'cancelled' => 'bg-slate-200 text-slate-600',
    'void' => 'bg-slate-900 text-white',
    'pending' => 'bg-yellow-100 text-yellow-700',
    'confirmed' => 'bg-emerald-100 text-emerald-700',
    'reversed' => 'bg-orange-100 text-orange-700',
    'default' => 'bg-slate-100 text-slate-600',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2 py-1 text-xs font-semibold '.($colors[$status] ?? $colors['default'])]) }}>{{ $slot }}</span>
