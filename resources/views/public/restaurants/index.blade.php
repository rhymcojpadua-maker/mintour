@extends('layouts.app')
@section('title', 'Where to eat')

@section('content')
    <div class="pt-32 pb-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mb-10">
                <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">Eat</div>
                <h1 class="mt-3 font-serif text-5xl sm:text-6xl">Where to <span class="italic text-teal-700">eat</span></h1>
                <p class="mt-4 text-ink-700/70">Map-listed places to eat in Victoria. Check current opening hours, menus and prices before visiting.</p>
            </div>

            <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($restaurants as $item)
                    <article class="group overflow-hidden rounded-3xl border border-teal-900/5 bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-soft">
                        <div class="relative h-52 overflow-hidden bg-teal-50">
                            <img src="{{ \App\Support\MediaUrl::resolve($item->cover_image) ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80' }}"
                                 class="h-full w-full object-cover transition duration-700 group-hover:scale-110"
                                 alt="{{ $item->name }}{{ $item->cover_image ? ' — photo source is credited in its description' : '' }}">
                            <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-teal-800 backdrop-blur">
                                {{ $item->cuisine }}
                            </div>
                            @if($item->price_range)
                                <div class="absolute bottom-4 right-4 rounded-full bg-ink-900/80 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">
                                    {{ $item->price_range }}
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <h2 class="text-lg font-bold text-ink-900">{{ $item->name }}</h2>
                            <div class="mt-1 flex items-center gap-3 text-sm">
                                @if($item->rating_avg > 0)
                                    <span class="font-semibold text-amber-600">★ {{ number_format($item->rating_avg, 1) }}</span>
                                @endif
                                <span class="text-xs text-slate-500">{{ $item->address ?: 'Victoria, Oriental Mindoro' }}</span>
                            </div>
                            <p class="mt-3 line-clamp-2 text-sm text-ink-700/80">{{ $item->description }}</p>
                            @if($item->hours)
                                <p class="mt-3 text-xs text-slate-500">Hours: {{ $item->hours }}</p>
                            @endif

                            <div class="mt-5 grid grid-cols-2 gap-2">
                                <a href="{{ route('restaurants.show', $item) }}"
                                   class="block rounded-xl bg-teal-700 py-2.5 text-center text-xs font-semibold text-white transition hover:shadow-glow">
                                    View details
                                </a>
                                @if($item->latitude && $item->longitude)
                                    <a href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $item->latitude }},{{ $item->longitude }}"
                                       target="_blank" rel="noopener"
                                       class="block rounded-xl bg-teal-50 py-2.5 text-center text-xs font-semibold text-teal-800 transition hover:bg-teal-100">
                                        Directions
                                    </a>
                                @else
                                    <span class="grid place-items-center rounded-xl bg-slate-50 py-2.5 text-center text-xs font-semibold text-slate-400">
                                        Location not available
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl bg-white p-8 text-slate-500 sm:col-span-2 lg:col-span-3">No published dining listings yet. Please check again later.</p>
                @endforelse
            </div>

            <div class="mt-12">{{ $restaurants->links() }}</div>
        </div>
    </div>
@endsection
