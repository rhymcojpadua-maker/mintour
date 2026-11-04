@props([
    'siteName' => 'MinTour',
    'variant' => 'light',
    'size' => 'nav',
])

@php
    $iconSize = match ($size) {
        'hero' => 'h-16',
        'compact' => 'h-10',
        default => 'h-12',
    };
    $titleSize = match ($size) {
        'hero' => 'text-4xl sm:text-5xl',
        'compact' => 'text-lg',
        default => 'text-2xl',
    };
    $taglineSize = $size === 'compact' ? 'text-[8px]' : 'text-[9px]';
@endphp

<div class="flex items-center gap-2.5">
    <img src="{{ asset('images/mintour-logo-user.png') }}"
         class="{{ $iconSize }} w-auto shrink-0 rounded-2xl object-contain"
         alt="MinTour logo"
         width="825"
         height="768"
         decoding="async">

    <span class="leading-none">
        <span class="block {{ $titleSize }} font-extrabold tracking-tight whitespace-nowrap">
            @if($siteName === 'MinTour')
                @if($variant === 'adaptive')
                    <span :class="scrolled ? 'text-ink-900' : 'text-white'">Min</span><span :class="scrolled ? 'text-teal-700' : 'text-teal-300'" class="font-serif italic">Tour</span>
                @elseif($variant === 'dark')
                    <span class="text-slate-900">Min</span><span class="font-serif italic text-teal-700">Tour</span>
                @else
                    <span class="text-white">Min</span><span class="font-serif italic text-teal-300">Tour</span>
                @endif
            @else
                @if($variant === 'adaptive')
                    <span :class="scrolled ? 'text-ink-900' : 'text-white'">{{ $siteName }}</span>
                @elseif($variant === 'dark')
                    <span class="text-slate-900">{{ $siteName }}</span>
                @else
                    <span class="text-white">{{ $siteName }}</span>
                @endif
            @endif
        </span>
        @if($variant === 'adaptive')
            <span :class="scrolled ? 'text-teal-700' : 'text-teal-200/90'" class="{{ $taglineSize }} mt-1 block uppercase tracking-[.16em] whitespace-nowrap">Victoria · Or. Mindoro</span>
        @elseif($variant === 'dark')
            <span class="{{ $taglineSize }} mt-1 block uppercase tracking-[.16em] text-teal-700 whitespace-nowrap">Victoria · Or. Mindoro</span>
        @else
            <span class="{{ $taglineSize }} mt-1 block uppercase tracking-[.16em] text-teal-200/90 whitespace-nowrap">Victoria · Or. Mindoro</span>
        @endif
    </span>
</div>
