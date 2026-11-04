@php($siteName = \App\Models\Setting::get('site_name', config('app.name', 'MinTour')))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') · {{ $siteName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-[#f7fbfa] text-ink-900 font-sans">
    <div class="min-h-screen grid lg:grid-cols-2">
        <div class="hidden lg:block relative overflow-hidden">
            <img src="@yield('auth_image', 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1400&q=80')"
                 class="auth-image absolute inset-0 w-full h-full object-cover object-center" alt="">
            <div class="auth-image-tint absolute inset-0"></div>
            <div class="absolute inset-0 bg-linear-to-b from-teal-950/15 via-transparent to-teal-950/75"></div>
            <div class="relative h-full flex flex-col justify-between p-12 text-white">
                <a href="{{ route('home') }}" class="inline-flex">
                    <x-public.brand-mark :site-name="$siteName" size="hero" />
                </a>
                <div>
                    <h2 class="font-serif text-4xl leading-tight max-w-md">
                        Save favorites, write reviews, plan your trip.
                    </h2>
                    <p class="mt-4 text-white/70 max-w-md">One account, all of Victoria at your fingertips.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="lg:hidden inline-flex mb-8">
                    <x-public.brand-mark :site-name="$siteName" variant="dark" size="compact" />
                </a>
                @yield('content')
            </div>
        </div>
    </div>
    <x-public.toast />
</body>
</html>
