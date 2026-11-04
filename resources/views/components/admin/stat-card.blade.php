@props(['label', 'value', 'icon' => 'chart-square-bar', 'color' => 'teal', 'trend' => null, 'href' => null])

@php
    $bg = match($color) {
        'coral'   => 'bg-coral-300/40 text-coral-500',
        'amber'   => 'bg-sand-100 text-amber-700',
        'violet'  => 'bg-violet-100 text-violet-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'sky'     => 'bg-sky-100 text-sky-700',
        'rose'    => 'bg-rose-100 text-rose-700',
        'red'     => 'bg-red-100 text-red-600',
        default   => 'bg-teal-100 text-teal-800',
    };
@endphp

@php
    $cardClasses = 'h-full rounded-2xl bg-white border border-slate-200 p-5 transition-all duration-300 '
        . ($href ? 'block hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-500' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cardClasses]) }}>
@else
    <div {{ $attributes->merge(['class' => $cardClasses]) }}>
@endif
    <div class="flex items-start justify-between">
        <div class="w-11 h-11 rounded-xl grid place-items-center {{ $bg }}">
            <x-admin.icon :name="$icon" class="w-5 h-5" />
        </div>
        @if($trend)
            <span class="text-[11px] font-bold px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700">{{ $trend }}</span>
        @endif
    </div>
    <div class="text-3xl font-extrabold mt-4 text-ink-900">{{ $value }}</div>
    <div class="text-xs text-slate-500 mt-1">{{ $label }}</div>
@if($href)
    </a>
@else
</div>
@endif
