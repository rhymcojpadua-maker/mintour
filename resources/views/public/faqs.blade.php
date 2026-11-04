@extends('layouts.app')
@section('title', 'FAQs')

@section('content')
<div class="pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-5 sm:px-8">
        <div class="text-center mb-12">
            <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">FAQ</div>
            <h1 class="mt-3 font-serif text-5xl sm:text-6xl">Common <span class="italic text-teal-700">questions</span></h1>
            <p class="mt-4 text-ink-700/70">Can't find your answer? Ask MinTour AI — it's on every page.</p>
        </div>

        <div class="space-y-3" x-data="{ open: 0 }">
            @foreach($faqs as $i => $faq)
                <div x-reveal class="rounded-3xl bg-white border border-teal-900/10 overflow-hidden hover:border-teal-500/40 transition">
                    <button @click="open = open === {{ $i }} ? -1 : {{ $i }}"
                            class="w-full text-left p-6 flex items-center gap-4">
                        <span class="text-xs font-mono text-teal-700/60">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="flex-1 font-bold text-ink-900">{{ $faq->question }}</span>
                        <span :class="open === {{ $i }} ? 'rotate-45 bg-teal-700 text-white' : 'bg-teal-50 text-teal-800'"
                              class="w-8 h-8 rounded-full grid place-items-center transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse class="px-6 pb-6 pl-[64px] text-ink-700/80">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
4.7 User dashboard
