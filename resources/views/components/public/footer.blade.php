@php
    $siteName = \App\Models\Setting::get('site_name', config('app.name', 'MinTour'));
    $contactEmail = \App\Models\Setting::get('contact_email');
@endphp
<footer class="bg-ink-900 text-white/70 pt-16 pb-8 mt-24">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid md:grid-cols-4 gap-10">
        <div class="md:col-span-2">
            <x-public.brand-mark :site-name="$siteName" variant="light" />
            <p class="mt-5 max-w-sm leading-relaxed text-sm">
                AI-powered bilingual tourism guide for Victoria, Oriental Mindoro.
            </p>
            @if($contactEmail)
                <a href="mailto:{{ $contactEmail }}" class="inline-block mt-3 text-sm hover:text-teal-300">{{ $contactEmail }}</a>
            @endif
        </div>
        <div>
            <div class="text-white font-bold mb-4">Explore</div>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('destinations.index') }}" class="hover:text-teal-300">Destinations</a></li>
                <li><a href="{{ route('map') }}" class="hover:text-teal-300">Map</a></li>
                <li><a href="{{ route('events.index') }}" class="hover:text-teal-300">Events</a></li>
                <li><a href="{{ route('gallery') }}" class="hover:text-teal-300">Gallery</a></li>
            </ul>
        </div>
        <div>
            <div class="text-white font-bold mb-4">Support</div>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('faqs') }}" class="hover:text-teal-300">FAQs</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-teal-300">Contact</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-teal-300">Sign in</a></li>
            </ul>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-5 sm:px-8 mt-12 pt-6 border-t border-white/10 flex flex-wrap items-center justify-between gap-4 text-xs">
        <div>© {{ now()->year }} {{ $siteName }} · Victoria Tourism Office</div>
        <div class="flex gap-6">
            <a href="#" class="hover:text-teal-300">Privacy</a>
            <a href="#" class="hover:text-teal-300">Terms</a>
        </div>
    </div>
</footer>
