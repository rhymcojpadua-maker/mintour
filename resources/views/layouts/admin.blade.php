<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · MinTour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="antialiased bg-slate-50 text-ink-900 font-sans" x-data="{ sidebar:false }" x-cloak>

<div class="min-h-screen flex lg:h-screen lg:overflow-hidden">

    {{-- ─── SIDEBAR ─── --}}
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 w-72 min-h-0 bg-ink-900 text-white/80 flex flex-col transition-transform duration-300 lg:static lg:h-screen lg:shrink-0">

        <div class="p-6 border-b border-white/10">
            <a href="{{ route('home') }}" class="inline-flex">
                <x-public.brand-mark variant="light" size="compact" />
            </a>
        </div>

        @php
            $navGroups = [
                'Overview' => [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'chart-square-bar'],
                ],
                'Content' => [
                    ['label' => 'Destinations',     'route' => 'admin.destinations.index',     'icon' => 'map-pin'],
                    ['label' => 'Categories',       'route' => 'admin.categories.index',       'icon' => 'squares-2x2'],
                    ['label' => 'Accommodations',   'route' => 'admin.accommodations.index',   'icon' => 'home-modern'],
                    ['label' => 'Restaurants',      'route' => 'admin.restaurants.index',      'icon' => 'building-storefront'],
                    ['label' => 'Local Products',   'route' => 'admin.products.index',         'icon' => 'shopping-bag'],
                    ['label' => 'Events',           'route' => 'admin.events.index',           'icon' => 'calendar-days'],
                    ['label' => 'Announcements',    'route' => 'admin.announcements.index',    'icon' => 'megaphone'],
                    ['label' => 'FAQs',             'route' => 'admin.faqs.index',             'icon' => 'question-mark-circle'],
                    ['label' => 'Emergency',        'route' => 'admin.emergency-contacts.index','icon' => 'phone'],
                ],
                'AI Assistant' => [
                    ['label' => 'Knowledge Base',   'route' => 'admin.knowledge.index',        'icon' => 'book-open'],
                    ['label' => 'Unanswered',       'route' => 'admin.unanswered.index',       'icon' => 'light-bulb', 'badge' => \App\Models\UnansweredQuestion::open()->count()],
                ],
                'Community' => [
                    ['label' => 'Reviews', 'route' => 'admin.reviews.index', 'icon' => 'star'],
                    ['label' => 'Users',   'route' => 'admin.users.index',   'icon' => 'users'],
                ],
                'System' => [
                    ['label' => 'Settings', 'route' => 'admin.settings.edit', 'icon' => 'cog-6-tooth'],
                ],
            ];
        @endphp

        <nav class="flex-1 min-h-0 overflow-y-auto p-4 space-y-5">
            @foreach($navGroups as $group => $items)
                <div>
                    <div class="px-4 mb-2 text-[10px] font-bold uppercase tracking-widest text-white/30">{{ $group }}</div>
                    <div class="space-y-1">
                        @foreach($items as $item)
                            @php
                                $isActive = request()->routeIs($item['route']) || str_starts_with(request()->route()?->getName() ?? '', str_replace('.index','', $item['route']));
                            @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition
                                    {{ $isActive ? 'bg-teal-700 text-white' : 'hover:bg-white/5 text-white/70' }}">
                                <x-admin.icon :name="$item['icon']" class="w-5 h-5" />
                                <span>{{ $item['label'] }}</span>
                                @if(!empty($item['badge']))
                                    <span class="ml-auto px-2 py-0.5 rounded-full bg-coral-500 text-white text-[10px] font-bold">
                                        {{ $item['badge'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-9 h-9 rounded-full bg-linear-to-br from-teal-500 to-teal-700 grid place-items-center text-white text-xs font-bold">
                    {{ Str::of(auth()->user()->name)->explode(' ')->map(fn($n) => $n[0])->take(2)->implode('') }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-teal-400 uppercase tracking-widest">{{ auth()->user()->role->label() }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="w-full py-2 rounded-xl bg-white/5 hover:bg-white/10 text-sm font-semibold transition">Sign out</button>
            </form>
            <a href="{{ route('home') }}" class="block w-full mt-2 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-sm font-semibold text-center transition">
                ← Back to site
            </a>
        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div x-show="sidebar" @click="sidebar=false" x-cloak
         class="lg:hidden fixed inset-0 z-30 bg-teal-950/35 backdrop-blur-sm"></div>

    {{-- ─── MAIN ─── --}}
    <div class="flex-1 min-w-0 lg:h-screen lg:overflow-y-auto">
        <header class="sticky top-0 z-20 bg-white border-b border-slate-200 px-5 sm:px-8 py-4 flex items-center justify-between">
            <button @click="sidebar=true" class="lg:hidden w-10 h-10 rounded-xl bg-slate-100 grid place-items-center">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <h1 class="font-bold text-lg text-ink-900">@yield('heading', 'Dashboard')</h1>

            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 hidden sm:block">
                    {{ now()->format('l, M j') }}
                </span>
                <a href="{{ route('home') }}" target="_blank"
                   class="px-4 py-2 rounded-xl bg-teal-50 text-teal-800 font-semibold text-xs hover:bg-teal-100 transition">
                    View site ↗
                </a>
            </div>
        </header>

        <main class="p-5 sm:p-8">
            @if(session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5L20 7"/></svg>
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl bg-coral-300/30 border border-coral-500/40 text-coral-500 px-4 py-3 text-sm">
                    <div class="font-bold mb-1">Please fix the following:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<x-public.toast />

@stack('scripts')
</body>
</html>
