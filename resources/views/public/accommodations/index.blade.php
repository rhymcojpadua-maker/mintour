@extends('layouts.app')
@section('title', 'Where to stay')

@section('content')
<div class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="mb-10">
            <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">Stay</div>
            <h1 class="mt-3 font-serif text-5xl sm:text-6xl">Where to <span class="italic text-teal-700">sleep</span></h1>
            <p class="mt-4 text-ink-700/70">Resorts, inns, and homestays across Victoria and nearby barangays.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
            @foreach($accommodations as $a)
                <article x-reveal class="group rounded-3xl overflow-hidden bg-white shadow-card border border-teal-900/5 hover:-translate-y-1 hover:shadow-soft transition-all duration-300">
                    <div class="relative h-52 overflow-hidden">
                        @if($image = \App\Support\MediaUrl::resolve($a->cover_image))
                            <img src="{{ $image }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="{{ $a->name }}">
                        @else
                            <div class="grid h-full w-full place-items-center bg-linear-to-br from-teal-50 via-emerald-50 to-slate-100 text-teal-700" aria-hidden="true">
                                <svg class="h-12 w-12 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                                    <path d="M3 21h18M5 21V9l7-5 7 5v12M3 9h18M9 21v-6h6v6"/>
                                </svg>
                            </div>
                        @endif
                        <div class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-white/95 backdrop-blur text-xs font-bold text-teal-800">
                            {{ $a->type }}
                        </div>
                        <div class="absolute bottom-4 right-4 px-3 py-1.5 rounded-full bg-ink-900/80 backdrop-blur text-white text-xs font-bold">
                            @if($a->price_per_night > 0)
                                ₱{{ number_format($a->price_per_night, 0) }}/night
                            @else
                                Ask for current rates
                            @endif
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-ink-900">{{ $a->name }}</h3>
                        <div class="mt-1 flex items-center gap-3 text-sm">
                            @if($a->rating_avg > 0)
                                <div class="text-sand-300 font-semibold">★ {{ number_format($a->rating_avg, 1) }}</div>
                            @endif
                            <div class="text-slate-500 text-xs">{{ $a->address }}</div>
                        </div>
                        <p class="mt-3 text-sm text-ink-700/80 line-clamp-2">{{ $a->description }}</p>
                        @if($a->amenities)
                            <div class="mt-4 flex flex-wrap gap-1.5">
                                @foreach(array_slice($a->amenities, 0, 3) as $am)
                                    <span class="px-2 py-1 rounded-lg bg-teal-50 text-teal-800 text-[11px] font-semibold">{{ $am }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if($a->latitude && $a->longitude)
                            <div class="mt-5 grid grid-cols-2 gap-2">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($a->name . ', ' . $a->address) }}"
                                   target="_blank" rel="noopener"
                                   class="block text-center py-2.5 rounded-xl bg-teal-700 text-white font-semibold text-xs hover:shadow-glow transition">
                                    Check listing
                                </a>
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $a->latitude }},{{ $a->longitude }}"
                                   target="_blank" rel="noopener"
                                   class="block text-center py-2.5 rounded-xl bg-teal-50 text-teal-800 font-semibold text-xs hover:bg-teal-100 transition">
                                    Directions
                                </a>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-12">{{ $accommodations->links() }}</div>
    </div>
</div>
@endsection
