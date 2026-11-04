@props(['type' => 'info'])

@php
    $colors = [
        'info'    => 'bg-teal-50 border-teal-200 text-teal-800',
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'warning' => 'bg-sand-100 border-sand-300 text-amber-800',
        'error'   => 'bg-coral-300/30 border-coral-500/40 text-coral-500',
    ];
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border px-4 py-3 text-sm {$colors[$type]}"]) }}>
    {{ $slot }}
</div>
4. Public pages
