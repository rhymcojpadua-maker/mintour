@extends('layouts.app')
@section('title', 'Events')

@section('content')
    <div class="pt-32 pb-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mb-10">
                <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">What&apos;s on</div>
                <h1 class="mt-3 font-serif text-5xl sm:text-6xl">Events &amp; <span class="italic text-teal-700">festivals</span></h1>
                <p class="mt-4 max-w-2xl text-ink-700/70">Local events and community celebrations in Victoria. Confirm schedules and venue details with the organizers before travelling.</p>
            </div>

            <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($events as $item)
                    <article class="group overflow-hidden rounded-3xl border border-teal-900/5 bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-soft">
                        <div class="relative h-52 overflow-hidden bg-linear-to-br from-teal-900 to-teal-700">
                            @if($item->cover_image)
                        <img src="{{ \App\Support\MediaUrl::resolve($item->cover_image) }}"
                             class="h-full w-full object-cover transition duration-700 group-hover:scale-110"
                             alt="{{ $item->title }}">
                            @else
                                <div class="absolute inset-0 grid place-items-center text-white/20">
                                    <svg class="h-16 w-16" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <path d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-teal-800 backdrop-blur">
                                {{ $item->starts_at->format('M j, Y') }}
                            </div>
                            <div class="absolute bottom-4 right-4 rounded-full px-3 py-1.5 text-xs font-bold {{ ($item->starts_at->isFuture() || $item->starts_at->isToday()) ? 'bg-emerald-500 text-white' : 'bg-ink-900/80 text-white' }}">
                                {{ ($item->starts_at->isFuture() || $item->starts_at->isToday()) ? 'Upcoming' : 'Past event' }}
                            </div>
                        </div>

                        <div class="p-5">
                            @if($item->category)
                                <div class="text-xs font-bold uppercase tracking-wide text-teal-700">{{ $item->category }}</div>
                            @endif
                            <h2 class="mt-1 text-lg font-bold text-ink-900">{{ $item->title }}</h2>
                            <div class="mt-1 text-sm text-slate-500">{{ $item->location }}</div>
                            <p class="mt-3 line-clamp-2 text-sm text-ink-700/80">{{ Str::limit($item->description, 180) }}</p>

                            <a href="{{ route('events.show', $item) }}"
                               class="mt-5 block rounded-xl bg-teal-700 py-2.5 text-center text-xs font-semibold text-white transition hover:shadow-glow">
                                Event details
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl bg-white p-8 text-slate-500 sm:col-span-2 lg:col-span-3">No published events yet. Please check again later.</p>
                @endforelse
            </div>

            <div class="mt-12">{{ $events->links() }}</div>
        </div>
    </div>
@endsection
