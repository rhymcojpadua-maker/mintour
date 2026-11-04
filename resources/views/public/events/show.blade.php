@extends('layouts.app')
@section('title', $event->title)

@section('content')
    <article class="mx-auto max-w-5xl px-6 py-14">
        <a href="{{ route('events.index') }}" class="font-semibold text-teal-700">← Back to events</a>
        @if($event->cover_image)
            <img src="{{ \App\Support\MediaUrl::resolve($event->cover_image) }}"
                 class="mt-6 max-h-[28rem] w-full rounded-3xl object-cover"
                 alt="{{ $event->title }}">
        @endif
        @if($event->category)
            <p class="mt-8 text-xs font-bold uppercase tracking-[.25em] text-teal-700">{{ $event->category }}</p>
        @endif
        <h1 class="mt-3 text-4xl font-bold text-ink-900 sm:text-5xl">{{ $event->title }}</h1>
        <p class="mt-4 font-semibold text-teal-800">
            {{ $event->starts_at?->format('F j, Y') }}
            @if($event->starts_at && $event->starts_at->format('H:i') !== '00:00')
                · {{ $event->starts_at->format('g:i A') }}
            @endif
            @if($event->ends_at && $event->starts_at && $event->ends_at->toDateString() !== $event->starts_at->toDateString())
                – {{ $event->ends_at->format('F j, Y') }}
            @endif
        </p>
        <p class="mt-1 text-slate-600">{{ $event->location }}</p>
        <p class="mt-6 whitespace-pre-line leading-relaxed text-slate-700">{{ $event->description }}</p>
    </article>
@endsection
