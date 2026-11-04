<!DOCTYPE html>
@php($siteName = \App\Models\Setting::get('site_name', config('app.name', 'MinTour')))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'AI-powered bilingual tourism guide for Victoria, Oriental Mindoro.')">

    <title>@yield('title', $siteName) · Discover Victoria, Oriental Mindoro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="antialiased selection:bg-teal-500 selection:text-white bg-[#f7fbfa] text-ink-900 font-sans">
    <x-public.nav />

    <main class="min-h-screen">
        @yield('content')
    </main>

    <x-public.footer />
    <x-public.toast />
    <x-public.chatbot />

    @stack('scripts')
</body>
</html>
