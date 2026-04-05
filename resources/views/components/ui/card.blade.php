@php
    $customClasses = (string) $attributes->get('class', '');
    $hasCustomBackground = str_contains($customClasses, 'bg-') || str_contains($customClasses, '!bg-');
    $baseClasses = 'rounded-lg border border-slate-200 p-4 shadow-sm'.($hasCustomBackground ? '' : ' bg-white');
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</div>
