@extends('layouts.app')

@section('title', 'Discover Victoria')

@section('content')
    {{-- ── HERO ── --}}
    <section class="relative min-h-[92vh] overflow-hidden flex items-center">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=2400&q=80"
                 class="w-full h-full object-cover" alt="">
            <div class="absolute inset-0 bg-linear-to-br from-teal-950/85 via-teal-900/65 to-ink-900/80"></div>
        </div>

        <div class="pointer-events-none absolute -top-24 -left-24 w-105 h-105 bg-teal-500/30 blur-3xl animate-blob"></div>
        <div class="pointer-events-none absolute bottom-10 right-0 w-95 h-95 bg-coral-500/25 blur-3xl animate-blob" style="animation-delay:-6s"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-14 items-center w-full pt-32 pb-16">
            <div class="lg:col-span-7 text-white">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur border border-white/20 text-sm font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    AI-powered bilingual tourism guide
                </div>

                <h1 class="mt-7 font-serif font-normal leading-[1.02] text-[clamp(2.6rem,6vw,5rem)] tracking-tight">
                    <span class="block">Discover the</span>
                    <span class="block italic bg-linear-to-r from-teal-200 via-teal-300 to-emerald-200 bg-clip-text text-transparent">hidden paradise</span>
                    <span class="block">of Victoria.</span>
                </h1>

                <p class="mt-6 max-w-xl text-lg text-white/80 leading-relaxed">
                    From mangrove forests to quiet shores — ask our AI guide anything, in English or Filipino, and get answers you can trust.
                </p>

                <form action="{{ route('destinations.index') }}" method="GET" class="mt-9 max-w-2xl">
                    <div class="flex items-center bg-white/95 backdrop-blur-xl rounded-full p-2 shadow-soft border border-white/50 focus-within:ring-4 focus-within:ring-teal-400/40 transition-all">
                        <svg class="w-5 h-5 text-teal-700 ml-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                        <input name="q" placeholder="Search beaches, resorts, events…"
                               class="flex-1 bg-transparent px-4 py-3 outline-none text-ink-900 font-medium placeholder:text-ink-700/50">
                        <button class="px-6 sm:px-8 py-3 rounded-full bg-linear-to-br from-teal-700 to-teal-500 text-white font-semibold btn-shimmer">Search</button>
                    </div>
                </form>

                <div class="mt-12 grid grid-cols-3 gap-6 max-w-xl">
                    <div>
                        <div class="text-3xl sm:text-4xl font-extrabold">{{ $stats['destinations'] }}<span class="text-teal-300">+</span></div>
                        <div class="text-xs uppercase tracking-widest text-white/60 mt-1">Destinations</div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-extrabold">{{ $stats['businesses'] }}<span class="text-teal-300">+</span></div>
                        <div class="text-xs uppercase tracking-widest text-white/60 mt-1">Businesses</div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-extrabold">24/7</div>
                        <div class="text-xs uppercase tracking-widest text-white/60 mt-1">AI Guide</div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 hidden lg:block">
                <div class="relative animate-float-slow">
                    <div class="absolute -inset-6 bg-linear-to-tr from-teal-400/40 to-coral-500/30 blur-3xl rounded-full"></div>
                    <a href="{{ route('about') }}"
                       class="relative block rounded-4xl overflow-hidden shadow-soft border border-white/25 bg-white/5 backdrop-blur-xl p-3">
                        <img src="{{ asset('images/victoria-municipality.jpg') }}"
                             class="rounded-[1.6rem] w-full h-105 object-cover object-[center_58%]" alt="Municipality of Victoria, Oriental Mindoro" fetchpriority="high">
                        <div class="absolute bottom-6 left-6 right-6 rounded-2xl bg-white/90 backdrop-blur-xl p-4 flex items-center gap-4 shadow-lg">
                            <div class="w-12 h-12 rounded-xl bg-teal-700 grid place-items-center text-white shrink-0">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 21h18M5 21V9l7-5 7 5v12M3 9h18"/>
                                    <path d="M9 21v-6h6v6M12 4V2"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-ink-900 truncate">Municipality of Victoria</div>
                                <div class="text-xs text-ink-700/70">Oriental Mindoro · Philippines</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ── MARQUEE ── --}}
    <section class="py-6 bg-ink-900 text-white overflow-hidden">
        <div class="fade-x">
            <div class="flex gap-16 whitespace-nowrap animate-marquee">
                @for($i = 0; $i < 2; $i++)
                    <div class="flex gap-16 items-center text-2xl font-serif italic text-white/70">
                        <span>Silonay Mangrove</span><span class="text-teal-400">✦</span>
                        <span>Victoria Port</span><span class="text-teal-400">✦</span>
                        <span>Local Delicacies</span><span class="text-teal-400">✦</span>
                        <span>Sunset Piers</span><span class="text-teal-400">✦</span>
                        <span>Fisherman's Wharf</span><span class="text-teal-400">✦</span>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- ── FEATURED DESTINATIONS ── --}}
    <section class="py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="flex flex-wrap items-end justify-between gap-6 mb-12">
                <div x-reveal>
                    <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">01 — Discover</div>
                    <h2 class="mt-3 font-serif text-5xl sm:text-6xl leading-[1.05] tracking-tight">
                        Curated places,<br>
                        <span class="italic text-teal-700">handpicked</span> for you.
                    </h2>
                </div>
                <a href="{{ route('destinations.index') }}" x-reveal=".2s" class="u-link font-semibold text-teal-800">
                    View all destinations →
                </a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach($featured as $destination)
                    <x-public.destination-card :destination="$destination" x-reveal />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CATEGORIES ── --}}
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="text-center mb-10" x-reveal>
                <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">Browse by category</div>
                <h3 class="mt-2 font-serif text-4xl">Find your <span class="italic text-teal-700">vibe</span></h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $cat)
                    <a href="{{ route('destinations.index', ['cat' => $cat->slug]) }}"
                       x-reveal
                       class="group rounded-3xl p-6 text-left bg-linear-to-br from-white to-teal-50/40 border border-teal-900/5 hover:-translate-y-1 hover:shadow-soft transition-all duration-300">
                        <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-teal-50 text-teal-700 transition-colors group-hover:bg-teal-700 group-hover:text-white">
                            <x-public.category-icon :slug="$cat->slug" class="h-6 w-6" />
                        </div>
                        <div class="font-bold text-ink-900">{{ $cat->name }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $cat->destinations_count }} places</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── MAP PREVIEW ── --}}
    <section class="py-20 sm:py-28 bg-linear-to-b from-white to-teal-50/50">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-4" x-reveal>
                <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">02 — Interactive Map</div>
                <h2 class="mt-3 font-serif text-5xl leading-[1.05]">
                    Every spot,<br>
                    <span class="italic text-teal-700">on one map.</span>
                </h2>
                <p class="mt-6 text-ink-700/80 leading-relaxed">
                    Filter by category, tap a pin to see details, and get directions via Google Maps or Waze.
                </p>
                <a href="{{ route('map') }}"
                   class="mt-8 inline-flex items-center gap-3 px-6 py-3 rounded-full bg-teal-700 text-white font-semibold hover:shadow-glow transition-all">
                    Open full map
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <div class="lg:col-span-8" x-reveal=".2s">
                <div class="relative rounded-4xl overflow-hidden shadow-soft border border-teal-900/10 bg-white p-3">
                    <div id="homeMap" data-markers="{{ json_encode($mapMarkers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}" class="w-full h-105 sm:h-125 rounded-[1.6rem]"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── AI SECTION ── --}}
    <section class="relative py-24 bg-ink-900 text-white overflow-hidden">
        <div class="pointer-events-none absolute -top-40 left-1/3 w-130 h-130 rounded-full bg-teal-500/20 blur-3xl animate-blob"></div>
        <div class="relative max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-14 items-center">
            <div class="lg:col-span-5" x-reveal>
                <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-300">03 — AI Guide</div>
                <h2 class="mt-3 font-serif text-5xl leading-[1.05]">
                    Ask anything.<br>
                    <span class="italic text-teal-300">In your language.</span>
                </h2>
                <p class="mt-6 text-white/70 leading-relaxed">
                    Our AI uses verified tourism listings and clearly says when details like current prices or schedules are not available.
                </p>
                <button onclick="document.querySelector('[x-data*=chatbot] button').click()"
                        class="mt-8 px-8 py-4 rounded-full bg-white text-teal-900 font-bold hover:shadow-glow hover:-translate-y-0.5 transition-all">
                    Chat with AI →
                </button>
            </div>
            <div class="lg:col-span-7" x-reveal=".2s">
                <div class="rounded-4xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur-xl p-6 shadow-soft space-y-4">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-linear-to-br from-teal-500 to-emerald-500 grid place-items-center text-xs font-bold shrink-0">AI</div>
                        <div class="rounded-2xl rounded-tl-md bg-white/10 border border-white/10 px-4 py-3 text-sm">Kumusta! 👋 Ask me about verified destinations, activities, festivals, places to eat, or tourism contacts.</div>
                    </div>
                    <div class="flex gap-3 flex-row-reverse">
                        <div class="w-8 h-8 rounded-full bg-coral-500 grid place-items-center text-xs font-bold shrink-0">You</div>
                        <div class="rounded-2xl rounded-tr-md bg-teal-500/25 border border-teal-400/30 px-4 py-3 text-sm">Tell me about Mabalbay Falls</div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-linear-to-br from-teal-500 to-emerald-500 grid place-items-center text-xs font-bold shrink-0">AI</div>
                        <div class="rounded-2xl rounded-tl-md bg-white/10 border border-white/10 px-4 py-3 text-sm">
                            <strong>Mabalbay Falls</strong> is in Villa Cerveza, Victoria. Its tourism listing describes about 40 minutes of trekking and stream crossing to reach the falls. The listing says there is no entrance fee; confirm current trail conditions before visiting.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── EVENTS PREVIEW ── --}}
    @if($upcoming->isNotEmpty())
        <section class="py-20 sm:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8">
                <div class="flex flex-wrap items-end justify-between gap-6 mb-12">
                    <div x-reveal>
                        <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">04 — What's On</div>
                        <h2 class="mt-3 font-serif text-5xl leading-[1.05]">
                            Events &amp; <span class="italic text-teal-700">festivals.</span>
                        </h2>
                    </div>
                    <a href="{{ route('events.index') }}" x-reveal=".2s" class="u-link font-semibold text-teal-800">View full calendar →</a>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($upcoming as $event)
                        <article x-reveal class="group p-6 rounded-3xl border border-teal-900/10 hover:border-teal-500/50 hover:shadow-soft hover:-translate-y-1 bg-white transition-all duration-300">
                            <div class="flex items-start gap-5">
                                <div class="w-16 h-16 rounded-2xl bg-linear-to-br from-teal-700 to-teal-500 grid place-items-center text-white shrink-0 group-hover:scale-110 transition">
                                    <div class="text-center leading-none">
                                        <div class="text-xl font-extrabold">{{ $event->starts_at->format('d') }}</div>
                                        <div class="text-[10px] tracking-widest mt-0.5">{{ strtoupper($event->starts_at->format('M Y')) }}</div>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    @if($event->category)
                                        <div class="inline-block px-2.5 py-1 rounded-full bg-teal-50 text-teal-800 text-[10px] font-bold uppercase tracking-wider">
                                            {{ $event->category }}
                                        </div>
                                    @endif
                                    <h3 class="mt-2 text-lg font-bold text-ink-900 leading-tight">
                                        <a href="{{ route('events.show', $event) }}" class="hover:text-teal-700">{{ $event->title }}</a>
                                    </h3>
                                    <div class="mt-1 text-sm text-ink-700/70">{{ $event->location }}</div>
                                    <p class="mt-2 text-xs text-slate-500">{{ $event->starts_at->format('F j, Y') }}</p>
                                    <p class="mt-2 text-sm text-slate-600">{{ Str::limit($event->description, 120) }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ── CTA ── --}}
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div x-reveal class="relative rounded-[2.5rem] overflow-hidden bg-ink-900 text-white p-12 sm:p-20 text-center">
                <div class="absolute inset-0 opacity-40">
                    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2000&q=80" class="w-full h-full object-cover" alt="">
                    <div class="absolute inset-0 bg-linear-to-br from-teal-950/90 to-ink-900/90"></div>
                </div>
                <div class="relative max-w-2xl mx-auto">
                    <h2 class="font-serif text-5xl leading-[1.05]">
                        Ready to explore<br>
                        <span class="italic text-teal-300">Victoria?</span>
                    </h2>
                    <p class="mt-6 text-white/70 text-lg">Ask our AI guide anything — or browse the full destination map.</p>
                    <div class="mt-10 flex flex-wrap gap-4 justify-center">
                        <button onclick="document.querySelector('[x-data*=chatbot] button').click()"
                                class="px-8 py-4 rounded-full bg-white text-teal-900 font-bold hover:shadow-glow hover:-translate-y-0.5 transition-all">
                            Chat with AI
                        </button>
                        <a href="{{ route('map') }}"
                           class="px-8 py-4 rounded-full border-2 border-white/30 font-bold hover:bg-white/10 transition">
                            Open the map
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    (() => {
    const initializeHomeMap = () => {
        const el = document.getElementById('homeMap');
        if (!el) return;
        const map = L.map(el, { scrollWheelZoom: false }).setView([13.1794, 121.2831], 12);
        window.addEventListener('mintour:page-unload', () => map.remove(), { once: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>', maxZoom: 19,
        }).addTo(map);

        const markers = JSON.parse(el.dataset.markers || '[]');

        markers.forEach(m => {
            const icon = L.divIcon({
                className: 'custom-pin',
                html: `<div class="pin" style="background:linear-gradient(135deg, ${m.color}, ${m.color}dd)"></div>`,
                iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -30],
            });
            L.marker([m.lat, m.lng], { icon }).addTo(map)
                .bindPopup(`<div style="min-width:180px"><div style="font-weight:800;font-size:14px">${m.name}</div><div style="font-size:11px;color:#6b7280;margin-top:2px">★ ${m.rating}</div><a href="${m.url}" style="display:inline-block;margin-top:8px;font-size:11px;font-weight:700;color:#0f766e">View details →</a></div>`);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('mintour:map-ready', initializeHomeMap, { once: true });
    } else {
        document.addEventListener('mintour:page-loaded', initializeHomeMap, { once: true });
    }
    })();
</script>
@endpush
