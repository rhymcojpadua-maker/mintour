@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="pt-32 pb-24" x-data="{ tab: 'favorites' }">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="mb-10">
            <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">Your account</div>
            <h1 class="mt-3 font-serif text-5xl">Hi, <span class="italic text-teal-700">{{ Str::of(auth()->user()->name)->before(' ') }}</span></h1>
        </div>

        <div class="flex gap-2 mb-8 overflow-x-auto">
            <button @click="tab='favorites'" :class="tab==='favorites' ? 'bg-teal-700 text-white' : 'bg-white text-ink-900 border border-teal-900/10'"
                    class="px-5 py-2.5 rounded-full font-semibold text-sm whitespace-nowrap transition">❤ Favorites</button>
            <button @click="tab='reviews'" :class="tab==='reviews' ? 'bg-teal-700 text-white' : 'bg-white text-ink-900 border border-teal-900/10'"
                    class="px-5 py-2.5 rounded-full font-semibold text-sm whitespace-nowrap transition">📝 My Reviews</button>
            <button @click="tab='chats'" :class="tab==='chats' ? 'bg-teal-700 text-white' : 'bg-white text-ink-900 border border-teal-900/10'"
                    class="px-5 py-2.5 rounded-full font-semibold text-sm whitespace-nowrap transition">💬 Chat History</button>
        </div>

        <div x-show="tab==='favorites'">
            @if($favorites->isEmpty())
                <div class="text-center py-20 rounded-3xl bg-white border border-dashed border-teal-900/20">
                    <div class="text-5xl mb-3">🤍</div>
                    <div class="font-bold">No favorites yet</div>
                    <a href="{{ route('destinations.index') }}" class="mt-4 inline-block text-teal-800 font-semibold u-link">Browse destinations</a>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($favorites as $fav)
                        <x-public.destination-card :destination="$fav->destination" :is-favorited="true" />
                    @endforeach
                </div>
            @endif
        </div>

        <div x-show="tab==='reviews'" x-cloak class="space-y-3">
            @forelse($reviews as $review)
                <div class="rounded-3xl bg-white border border-teal-900/10 p-5">
                    <div class="flex items-center gap-3">
                        <div class="font-bold">{{ $review->destination->name }}</div>
                        <div class="text-sand-300">
                            @for($i = 1; $i <= 5; $i++)<span>{{ $i <= $review->rating ? '★' : '☆' }}</span>@endfor
                        </div>
                        <div class="ml-auto text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</div>
                    </div>
                    <p class="mt-2 text-ink-700/80">{{ $review->body }}</p>
                </div>
            @empty
                <div class="text-center py-20 rounded-3xl bg-white border border-dashed border-teal-900/20">
                    <div class="text-5xl mb-3">📝</div>
                    <div class="font-bold">You haven't written any reviews yet</div>
                </div>
            @endforelse
        </div>

        <div x-show="tab==='chats'" x-cloak class="space-y-3">
            @forelse($sessions as $session)
                <div class="rounded-3xl bg-white border border-teal-900/10 p-5">
                    <div class="text-xs text-slate-400 mb-2">{{ $session->created_at->format('M j, Y · g:i A') }}</div>
                    <div class="flex justify-between items-center">
                        <div class="font-semibold">{{ $session->messages_count }} messages</div>
                        <span class="text-xs uppercase tracking-wider px-2 py-1 rounded-full bg-teal-50 text-teal-800 font-bold">
                            {{ $session->lang }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 rounded-3xl bg-white border border-dashed border-teal-900/20">
                    <div class="text-5xl mb-3">💬</div>
                    <div class="font-bold">No conversations yet</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
5. Auth pages
