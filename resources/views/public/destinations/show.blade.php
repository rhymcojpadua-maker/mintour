@extends('layouts.app')

@section('title', $destination->name)
@section('meta_description', $destination->short_description)

@section('content')
<section class="relative h-[60vh] min-h-105 overflow-hidden">
    <img src="{{ \App\Support\MediaUrl::resolve($destination->cover_image) ?: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1600&q=80' }}"
         class="absolute inset-0 w-full h-full object-cover" alt="">
    <div class="absolute inset-0 bg-linear-to-t from-ink-900/90 via-ink-900/40 to-transparent"></div>

    <div class="absolute bottom-0 inset-x-0 pb-10">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 text-white">
            <a href="{{ route('destinations.index') }}"
               class="text-xs uppercase tracking-widest text-white/80 hover:text-white inline-flex items-center gap-2 mb-4">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                Back to destinations
            </a>

            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <div class="inline-block px-3 py-1.5 rounded-full bg-white/15 backdrop-blur text-xs font-bold">
                        {{ $destination->category->name }}
                    </div>
                    <h1 class="mt-3 font-serif text-5xl sm:text-6xl leading-[1.05]">{{ $destination->name }}</h1>
                    <div class="mt-4 flex flex-wrap items-center gap-6 text-sm">
                        <div class="text-sand-300 font-semibold text-base">
                            ★ <span class="text-white">{{ number_format($destination->rating_avg, 1) }}</span>
                            <span class="text-white/60">({{ $destination->rating_count }} reviews)</span>
                        </div>
                        @if($destination->address)
                            <div class="text-white/70">📍 {{ $destination->address }}</div>
                        @endif
                        @if($destination->distance_km)
                            <div class="text-white/70">🚗 {{ number_format($destination->distance_km, 1) }} km from town</div>
                        @endif
                    </div>
                </div>

                <div class="flex gap-3">
                    @auth
                        <button data-favorite-btn
                                data-url="{{ route('favorites.toggle', $destination) }}"
                                data-initial="{{ $isFavorited ? '1' : '0' }}"
                                @click.prevent="
                                    const res = await fetch($el.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' } });
                                    const d = await res.json();
                                    $el.dataset.initial = d.favorited ? '1' : '0';
                                    $el.classList.toggle('bg-coral-500', d.favorited);
                                    $el.classList.toggle('text-white', d.favorited);
                                    Alpine.store('toast').push(d.favorited ? 'Added to favorites' : 'Removed from favorites');
                                "
                                :class="$el.dataset.initial==='1' ? 'bg-coral-500 text-white' : 'bg-white/15 backdrop-blur border border-white/25 text-white'"
                                class="w-12 h-12 rounded-full grid place-items-center transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" :fill="$el.dataset.initial==='1' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
                            </svg>
                        </button>
                    @endauth
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $destination->latitude }},{{ $destination->longitude }}"
                       target="_blank" rel="noopener"
                       class="px-6 py-3 rounded-full bg-white text-teal-900 font-bold text-sm hover:shadow-glow transition">
                        Get directions
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-5 sm:px-8 py-16 grid lg:grid-cols-12 gap-12">
    <div class="lg:col-span-8">
        <h2 class="font-serif text-3xl mb-4">About this place</h2>
        <div class="prose prose-slate max-w-none text-ink-700/90 leading-relaxed text-[17px]">
            {!! nl2br(e($destination->long_description)) !!}
        </div>

        @if($destination->photos->isNotEmpty())
            <h3 class="font-serif text-3xl mt-14 mb-6">Photos</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach($destination->photos as $photo)
                    <button type="button"
                            onclick="document.getElementById('lb-img').src='{{ \App\Support\MediaUrl::resolve($photo->path) }}'; document.getElementById('lightbox').style.display='grid';"
                            class="aspect-square rounded-2xl overflow-hidden group">
                        <img src="{{ \App\Support\MediaUrl::resolve($photo->path) }}" alt="{{ $photo->caption }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Reviews --}}
        <div class="mt-14">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-serif text-3xl">
                    Reviews <span class="text-teal-700">({{ $destination->reviews->count() }})</span>
                </h3>
                @auth
                    <button onclick="document.getElementById('review-modal').style.display='grid'"
                            class="px-5 py-2.5 rounded-full bg-teal-700 text-white font-semibold text-sm hover:shadow-glow transition">
                        Write a review
                    </button>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-full bg-teal-700 text-white font-semibold text-sm">Sign in to review</a>
                @endauth
            </div>

            @if($destination->reviews->isEmpty())
                <div class="rounded-2xl bg-white border border-dashed border-teal-900/20 p-10 text-center text-slate-500">
                    No reviews yet. Be the first!
                </div>
            @else
                <div class="space-y-4">
                    @foreach($destination->reviews as $review)
                        <div class="rounded-2xl bg-white border border-teal-900/10 p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-full bg-linear-to-br from-teal-700 to-teal-500 grid place-items-center text-white font-bold text-sm">
                                    {{ Str::of($review->user->name)->explode(' ')->map(fn($n) => $n[0])->take(2)->implode('') }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="font-bold text-ink-900">{{ $review->user->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="text-sand-300 text-sm mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                        @endfor
                                    </div>
                                    <p class="mt-2 text-ink-700/90">{{ $review->body }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <aside class="lg:col-span-4 space-y-6">
        <div class="rounded-3xl bg-white border border-teal-900/10 p-6 shadow-card">
            <h4 class="font-bold text-lg mb-4">Details</h4>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Category</dt><dd class="font-semibold">{{ $destination->category->name }}</dd></div>
                @if($destination->hours)<div class="flex justify-between"><dt class="text-slate-500">Hours</dt><dd class="font-semibold">{{ $destination->hours }}</dd></div>@endif
                @if($destination->entrance_fee)<div class="flex justify-between"><dt class="text-slate-500">Entrance</dt><dd class="font-semibold">{{ $destination->entrance_fee }}</dd></div>@endif
                @if($destination->best_time)<div class="flex justify-between"><dt class="text-slate-500">Best time</dt><dd class="font-semibold">{{ $destination->best_time }}</dd></div>@endif
            </dl>
        </div>

        <div class="rounded-3xl bg-white border border-teal-900/10 overflow-hidden shadow-card">
              <div id="detailMap"
                  data-map="{{ json_encode(['latitude' => $destination->latitude, 'longitude' => $destination->longitude, 'color' => $destination->category->color, 'name' => $destination->name], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                  class="w-full h-64"></div>
            <div class="p-5">
                <div class="text-sm text-slate-500 mb-3">Location</div>
                <div class="text-sm font-semibold text-ink-900 mb-4">{{ $destination->address }}</div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $destination->latitude }},{{ $destination->longitude }}"
                       target="_blank" rel="noopener"
                       class="text-center py-2.5 rounded-xl bg-teal-50 text-teal-800 font-semibold text-xs hover:bg-teal-100 transition">
                        Google Maps
                    </a>
                    <a href="https://waze.com/ul?ll={{ $destination->latitude }},{{ $destination->longitude }}&navigate=yes"
                       target="_blank" rel="noopener"
                       class="text-center py-2.5 rounded-xl bg-teal-50 text-teal-800 font-semibold text-xs hover:bg-teal-100 transition">
                        Waze
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-linear-to-br from-teal-700 to-teal-500 text-white p-6 shadow-glow">
            <div class="text-xs uppercase tracking-widest text-white/70 mb-2">Ask about this place</div>
            <div class="font-bold text-lg mb-3">Get instant answers from MinTour AI</div>
            <button onclick="document.querySelector('[x-data*=chatbot] button').click()"
                    class="w-full py-3 rounded-xl bg-white text-teal-800 font-bold text-sm hover:shadow-lg transition">
                Open AI chat →
            </button>
        </div>

        @if($nearby->isNotEmpty())
            <div class="rounded-3xl bg-white border border-teal-900/10 p-6 shadow-card">
                <h4 class="font-bold text-lg mb-4">Nearby places</h4>
                <div class="space-y-3">
                    @foreach($nearby as $n)
                        <a href="{{ route('destinations.show', $n) }}"
                           class="flex items-center gap-3 p-2 rounded-2xl hover:bg-teal-50 transition">
                            <img src="{{ \App\Support\MediaUrl::resolve($n->cover_image) ?: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=400&q=80' }}"
                                 class="w-14 h-14 rounded-xl object-cover" alt="">
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-sm text-ink-900 truncate">{{ $n->name }}</div>
                                <div class="text-xs text-slate-500">
                                    ★ {{ number_format($n->rating_avg, 1) }} ·
                                    {{ number_format($n->distance_from_here, 1) }} km
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </aside>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="this.style.display='none'"
    class="fixed inset-0 z-90 bg-ink-900/95 grid place-items-center p-4 cursor-zoom-out" style="display:none">
    <img id="lb-img" class="max-w-full max-h-full rounded-2xl" alt="">
</div>

{{-- Review modal --}}
@auth
<div id="review-modal" class="fixed inset-0 z-80 grid place-items-center p-4" style="display:none; background:rgba(4,47,46,.35); backdrop-filter:blur(4px)">
    <div class="w-full max-w-md rounded-3xl bg-white p-6">
        <div class="font-bold text-lg mb-4">Write a review</div>
        <form method="POST" action="{{ route('reviews.store', $destination) }}">
            @csrf
            <div class="flex items-center gap-1 mb-4" x-data="{rating: 5}">
                <template x-for="s in 5">
                    <button type="button" @click="rating = s" class="text-3xl transition"
                            :class="s <= rating ? 'text-sand-300' : 'text-slate-300'">★</button>
                </template>
                <input type="hidden" name="rating" x-model="rating">
            </div>
            <textarea name="body" rows="4" placeholder="Share your experience…" required
                      class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 outline-none focus:border-teal-500"></textarea>
            @error('body')<div class="text-sm text-coral-500 mt-1">{{ $message }}</div>@enderror
            <div class="mt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('review-modal').style.display='none'"
                        class="flex-1 py-3 rounded-2xl bg-slate-100 font-semibold">Cancel</button>
                <button class="flex-1 py-3 rounded-2xl bg-teal-700 text-white font-semibold">Submit</button>
            </div>
        </form>
    </div>
</div>
@endauth
@endsection

@push('scripts')
<script>
(() => {
const initializeDestinationMap = () => {
    const mapElement = document.getElementById('detailMap');
    if (!mapElement) return;
    const markerData = JSON.parse(mapElement.dataset.map);
    const map = L.map(mapElement, { scrollWheelZoom: false })
        .setView([markerData.latitude, markerData.longitude], 14);
    window.addEventListener('mintour:page-unload', () => map.remove(), { once: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>', maxZoom: 19,
    }).addTo(map);

    const icon = L.divIcon({
        className: 'custom-pin',
        html: `<div class="pin" style="background:linear-gradient(135deg, ${markerData.color}, ${markerData.color}dd)"></div>`,
        iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -30],
    });
    const popup = document.createElement('b');
    popup.textContent = markerData.name;
    L.marker([markerData.latitude, markerData.longitude], { icon })
        .addTo(map).bindPopup(popup).openPopup();
};

if (document.readyState === 'loading') {
    document.addEventListener('mintour:map-ready', initializeDestinationMap, { once: true });
} else {
    document.addEventListener('mintour:page-loaded', initializeDestinationMap, { once: true });
}
})();
</script>
@endpush
