@extends('layouts.app')

@section('title', 'All destinations')

@section('content')
<div class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="mb-10">
            <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">Explore</div>
            <h1 class="mt-3 font-serif text-5xl sm:text-6xl">All <span class="italic text-teal-700">destinations</span></h1>
            <p class="mt-4 text-ink-700/70">Filter by category, sort, or search across {{ $destinations->total() }} local and nearby places.</p>
        </div>

        <form method="GET" class="rounded-3xl bg-white border border-teal-900/10 p-5 shadow-card mb-8 grid lg:grid-cols-12 gap-4">
            <div class="lg:col-span-6 relative">
                <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                <input name="q" value="{{ $filters['q'] }}" placeholder="Search destinations…"
                       class="w-full pl-12 pr-4 py-3 rounded-2xl bg-teal-50/50 border border-teal-900/10 focus:border-teal-500 outline-none font-medium">
            </div>
            <div class="lg:col-span-3">
                <select name="cat" class="w-full py-3 px-4 rounded-2xl bg-teal-50/50 border border-teal-900/10 focus:border-teal-500 outline-none font-medium">
                    <option value="">All categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->slug }}" @selected($filters['cat'] === $c->slug)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <select name="sort" class="w-full py-3 px-4 rounded-2xl bg-teal-50/50 border border-teal-900/10 focus:border-teal-500 outline-none font-medium">
                    <option value="rating"   @selected($filters['sort'] === 'rating')>Top rated</option>
                    <option value="name"     @selected($filters['sort'] === 'name')>Name A–Z</option>
                    <option value="distance" @selected($filters['sort'] === 'distance')>Nearest</option>
                </select>
            </div>
            <div class="lg:col-span-1">
                <button class="w-full py-3 rounded-2xl bg-teal-700 text-white font-bold hover:shadow-glow transition">Apply</button>
            </div>
        </form>

        @if($destinations->isEmpty())
            <div class="text-center py-20 rounded-3xl bg-white border border-teal-900/10">
                <div class="text-5xl mb-4">🔍</div>
                <div class="font-bold text-lg text-ink-900">No destinations match your filters</div>
                <a href="{{ route('destinations.index') }}" class="mt-4 inline-block text-teal-800 font-semibold u-link">Clear filters</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach($destinations as $destination)
                    <x-public.destination-card :destination="$destination" x-reveal />
                @endforeach
            </div>
            <div class="mt-12">{{ $destinations->links() }}</div>
        @endif
    </div>
</div>
@endsection
