@props(['destination', 'isFavorited' => false])

<article {{ $attributes->merge(['class' => 'group zoom rounded-3xl overflow-hidden bg-white shadow-card border border-teal-900/5 hover:-translate-y-2 hover:shadow-glow transition-all duration-500']) }}>
    <a href="{{ route('destinations.show', $destination) }}" class="block relative h-64 overflow-hidden">
        <img src="{{ \App\Support\MediaUrl::resolve($destination->cover_image) ?: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=80' }}"
             alt="{{ $destination->name }}"
             loading="lazy"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-linear-to-t from-ink-900/70 via-transparent"></div>
           <div class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-white/95 backdrop-blur text-xs font-bold"
               data-category-color="{{ $destination->category->color }}"
               x-init="$el.style.color = $el.dataset.categoryColor">
            {{ $destination->category->name }}
        </div>
        <div class="absolute bottom-4 left-5 right-5 text-white">
            <div class="text-xs uppercase tracking-widest text-teal-200">{{ $destination->tagline }}</div>
            <h3 class="text-lg font-bold mt-1">{{ $destination->name }}</h3>
        </div>
    </a>

    @auth
        <button type="button"
                data-favorite-btn
                data-url="{{ route('favorites.toggle', $destination) }}"
                data-initial="{{ $isFavorited ? '1' : '0' }}"
                @click.prevent="
                    const btn = $el;
                    const res = await fetch(btn.dataset.url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' }
                    });
                    const data = await res.json();
                    btn.dataset.initial = data.favorited ? '1' : '0';
                    btn.classList.toggle('bg-coral-500', data.favorited);
                    btn.classList.toggle('text-white', data.favorited);
                    btn.classList.toggle('bg-white/90', !data.favorited);
                    btn.classList.toggle('text-teal-800', !data.favorited);
                    Alpine.store('toast').push(data.favorited ? 'Added to favorites' : 'Removed from favorites');
                "
                :class="btn.dataset.initial==='1' ? 'bg-coral-500 text-white' : 'bg-white/90 text-teal-800'"
                class="absolute top-4 right-4 w-10 h-10 rounded-full grid place-items-center transition-all duration-300 backdrop-blur">
            <svg class="w-5 h-5" viewBox="0 0 24 24" :fill="$el.dataset.initial==='1' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
            </svg>
        </button>
    @endauth

    <div class="p-6">
        <div class="flex items-center justify-between text-sm">
            @if($destination->rating_count > 0)
                <div class="flex items-center gap-1 text-sand-300 font-semibold">
                    @for($i = 1; $i <= 5; $i++)
                        <span>{{ $i <= round($destination->rating_avg) ? '★' : '☆' }}</span>
                    @endfor
                    <span class="text-ink-900 ml-1">{{ number_format($destination->rating_avg, 1) }}</span>
                    <span class="text-ink-700/60 font-normal">({{ $destination->rating_count }})</span>
                </div>
            @else
                <span class="text-xs text-ink-700/60">No reviews yet</span>
            @endif
            @if($destination->distance_km)
                <div class="text-teal-700 font-semibold">{{ number_format($destination->distance_km, 1) }} km</div>
            @endif
        </div>
        <p class="mt-4 text-ink-700/80 leading-relaxed text-[15px] line-clamp-2">{{ $destination->short_description }}</p>
        @if($destination->latitude && $destination->longitude)
            <a href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $destination->latitude }},{{ $destination->longitude }}"
               target="_blank" rel="noopener noreferrer"
               class="mt-4 inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-100">
                <span aria-hidden="true">📍</span>
                Open in Google Maps
            </a>
        @endif
    </div>
</article>
