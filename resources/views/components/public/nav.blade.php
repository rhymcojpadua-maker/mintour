@php
    $siteName = \App\Models\Setting::get('site_name', config('app.name', 'MinTour'));
    $navItems = [
        ['label' => 'Destinations',  'route' => 'destinations.index'],
        ['label' => 'Map',           'route' => 'map'],
        ['label' => 'Stay',          'route' => 'accommodations.index'],
        ['label' => 'Eat',           'route' => 'restaurants.index'],
        ['label' => 'Events',        'route' => 'events.index'],
        ['label' => 'Gallery',       'route' => 'gallery'],
        ['label' => 'FAQs',          'route' => 'faqs'],
    ];
@endphp

<header
    x-data="{ isHome: @js(request()->routeIs('home')), scrolled: false, mobileOpen: false, userMenu: false }"
    x-init="scrolled = !isHome || window.scrollY > 40"
    @scroll.window.throttle="scrolled = !isHome || window.scrollY > 40"
    :class="scrolled ? 'bg-white/85 backdrop-blur-xl shadow-[0_10px_40px_-20px_rgba(4,47,46,.35)]' : 'bg-transparent'"
    class="fixed top-0 inset-x-0 z-40 transition-all duration-500">
    <nav class="max-w-7xl mx-auto px-5 sm:px-8 py-4 flex items-center justify-between">
        {{-- Brand --}}
        <a href="{{ route('home') }}" class="flex items-center">
            <x-public.brand-mark :site-name="$siteName" variant="adaptive" />
        </a>

        {{-- Desktop nav --}}
        <ul :class="scrolled ? 'text-ink-900' : 'text-white'" class="hidden lg:flex items-center gap-7 font-medium text-sm transition-colors">
            @foreach($navItems as $item)
                <li>
                    <a href="{{ route($item['route']) }}"
                       class="u-link {{ request()->routeIs($item['route'].'*') ? 'font-bold' : '' }}">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            {{-- Language --}}
            <div x-data
                 :class="scrolled ? 'bg-teal-50 border-teal-200' : 'bg-white/15 border-white/25'"
                 class="hidden sm:flex items-center backdrop-blur-md border rounded-full p-1 text-xs font-semibold transition-colors">
                <button @click="$store.lang.set('en')"
                        :class="$store.lang.code==='en' ? (scrolled ? 'bg-teal-700 text-white' : 'bg-white text-teal-900') : (scrolled ? 'text-teal-800' : 'text-white/80')"
                        class="px-3 py-1.5 rounded-full transition">EN</button>
                <button @click="$store.lang.set('fil')"
                        :class="$store.lang.code==='fil' ? (scrolled ? 'bg-teal-700 text-white' : 'bg-white text-teal-900') : (scrolled ? 'text-teal-800' : 'text-white/80')"
                        class="px-3 py-1.5 rounded-full transition">FIL</button>
            </div>

            {{-- Favorites --}}
            @auth
                <a href="{{ route('dashboard') }}#favorites"
                   :class="scrolled ? 'bg-teal-50 text-teal-800' : 'bg-white/15 backdrop-blur border border-white/25 text-white'"
                   class="relative hidden sm:grid place-items-center w-11 h-11 rounded-full transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
                    </svg>
                    @php $favCount = auth()->user()->favorites()->count(); @endphp
                    @if($favCount)
                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-coral-500 text-white text-[10px] font-bold grid place-items-center">{{ $favCount }}</span>
                    @endif
                </a>
            @endauth

            {{-- Auth --}}
            @guest
                <a href="{{ route('login') }}"
                   :class="scrolled ? 'text-teal-800 border-teal-700' : 'text-white border-white'"
                   class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-full border-2 font-semibold text-sm hover:bg-teal-700 hover:text-white hover:border-teal-700 transition-all">
                    Sign in
                </a>
            @endguest

            @auth
                <div class="relative" @click.outside="userMenu=false">
                    <button @click="userMenu=!userMenu"
                            :class="scrolled ? 'bg-teal-50 text-teal-900' : 'bg-white/15 text-white'"
                            class="flex items-center gap-2 pl-2 pr-4 py-1.5 rounded-full backdrop-blur">
                        <div class="w-8 h-8 rounded-full from-teal-700 to-teal-500 bg-linear-to-br grid place-items-center text-white text-xs font-bold">
                            {{ Str::of(auth()->user()->name)->explode(' ')->map(fn($n) => $n[0])->take(2)->implode('') }}
                        </div>
                        <span class="text-sm font-semibold hidden sm:inline">{{ Str::of(auth()->user()->name)->before(' ') }}</span>
                    </button>
                    <div x-show="userMenu" x-transition x-cloak
                         class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-soft border border-teal-900/10 p-2 z-50">
                        <div class="px-3 py-2 border-b border-slate-100">
                            <div class="text-sm font-bold text-ink-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-slate-500">{{ auth()->user()->email }}</div>
                            <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ auth()->user()->isAdmin() ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ auth()->user()->role->label() }}
                            </span>
                        </div>
                        <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-teal-50">My dashboard</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold hover:bg-teal-50">Admin panel</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-left px-3 py-2 rounded-xl text-sm font-semibold hover:bg-coral-300/40 text-coral-500">Sign out</button>
                        </form>
                    </div>
                </div>
            @endauth

            {{-- Mobile toggle --}}
            <button @click="mobileOpen=!mobileOpen"
                    :class="scrolled ? 'bg-teal-50 text-teal-900' : 'bg-white/15 border border-white/25 text-white'"
                    class="lg:hidden w-11 h-11 rounded-full backdrop-blur grid place-items-center">
                <svg x-show="!mobileOpen" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="mobileOpen" x-cloak class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </nav>

    {{-- Mobile menu --}}
    <div x-show="mobileOpen" x-transition.opacity.duration.300ms x-cloak class="lg:hidden px-5 pb-4">
        <div class="rounded-3xl bg-white/95 backdrop-blur-xl border border-white/40 shadow-soft p-4 grid gap-1">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}" class="px-4 py-3 rounded-2xl text-ink-900 font-semibold hover:bg-teal-50 transition">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('about') }}" class="px-4 py-3 rounded-2xl text-ink-900 font-semibold hover:bg-teal-50 transition">About &amp; Contact</a>
            @guest
                <a href="{{ route('login') }}" class="px-4 py-3 rounded-2xl bg-teal-700 text-white font-semibold text-center">Sign in</a>
            @endguest
        </div>
    </div>
</header>
