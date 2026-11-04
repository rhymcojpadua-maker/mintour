@extends('layouts.app')

@section('title', 'Interactive map')

@section('content')
<div class="pt-32 pb-20"
     x-data="mapPage()"
     data-map-features-url="{{ route('api.map.features') }}"
     data-map-tile-url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="mb-8">
            <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">Interactive Map</div>
            <h1 class="mt-3 font-serif text-5xl sm:text-6xl">Explore <span class="italic text-teal-700">Victoria</span></h1>
            <p class="mt-4 text-ink-700/70">Explore mapped destinations, stays, and dining places in Victoria. Pins use the coordinates published for each listing; places without coordinates are not pinned.</p>
        </div>

        <div class="grid lg:grid-cols-12 gap-6">
            <aside class="lg:col-span-3 rounded-3xl bg-white border border-teal-900/10 p-5 shadow-card h-fit lg:sticky lg:top-32">
                <div class="font-bold mb-3">Categories</div>
                <div class="space-y-2">
                    @foreach($categories as $cat)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" value="{{ $cat->slug }}" x-model="active" @change="refresh()"
                                   class="w-4 h-4 rounded border-2 border-teal-700/30 text-teal-700 focus:ring-teal-500">
                            <span class="w-3 h-3 rounded-full" style="background:{{ $cat->color }}"></span>
                            <span class="text-sm font-medium group-hover:text-teal-800">{{ $cat->name }}</span>
                            <span class="ml-auto text-xs text-slate-400">{{ $cat->destinations_count ?? '' }}</span>
                        </label>
                    @endforeach
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" value="accommodation" x-model="active" @change="refresh()"
                               class="w-4 h-4 rounded border-2 border-amber-700/30 text-amber-700 focus:ring-amber-500">
                        <span class="w-3 h-3 rounded-full bg-amber-600"></span>
                        <span class="text-sm font-medium group-hover:text-amber-800">Hotels & Resorts</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" value="restaurant" x-model="active" @change="refresh()"
                               class="w-4 h-4 rounded border-2 border-rose-700/30 text-rose-700 focus:ring-rose-500">
                        <span class="w-3 h-3 rounded-full bg-rose-600"></span>
                        <span class="text-sm font-medium group-hover:text-rose-800">Restaurants & Cafés</span>
                    </label>
                </div>
            </aside>

            <div class="lg:col-span-9 rounded-3xl bg-white border border-teal-900/10 p-3 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 px-2 pb-3">
                    <p class="text-sm text-slate-600" role="status" aria-live="polite" x-text="status"></p>
                    <button type="button"
                            @click="locateMe()"
                            :disabled="locating"
                            class="inline-flex items-center gap-2 rounded-full bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:cursor-wait disabled:opacity-60">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
                        </svg>
                        <span x-text="locating ? 'Finding you…' : 'Use my location'"></span>
                    </button>
                </div>
                <p x-show="error" x-cloak x-text="error" class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert"></p>
                <div id="fullMap" class="w-full h-[640px] rounded-[1.4rem]"></div>
                <script id="mapActiveCategories" type="application/json">@json($activeFilters)</script>
                <script id="mapInitialFeatures" type="application/json">@json($initial)</script>
                <p class="px-2 pt-3 text-xs text-slate-500">Pins are based on listing coordinates from Travel Oriental Mindoro; listings without coordinates are omitted. Check each address in the popup, as some directory entries may refer to places outside Victoria. Map data © OpenStreetMap contributors. Your location is shown for this session and is not saved by MinTour.</p>
            </div>
        </div>
    </div>
</div>
@endsection
