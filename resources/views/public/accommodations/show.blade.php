@extends('layouts.app')
@section('title',$accommodation->name)
@section('content')
    <div class="mx-auto max-w-6xl px-6 py-14">
        <a href="{{ route('accommodations.index') }}" class="text-sm text-teal-700">← Back</a>
        <div class="mt-6 grid gap-8 md:grid-cols-2">
            <div class="overflow-hidden rounded-3xl bg-white shadow-card">
                @if($accommodation->cover_image)
                    <img src="{{ \App\Support\MediaUrl::resolve($accommodation->cover_image) }}"
                         class="h-96 w-full object-cover" alt="{{ $accommodation->name }}">
                @else
                    <div class="h-96 grid place-items-center bg-teal-50 text-6xl" aria-hidden="true">🏡</div>
                @endif
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">{{ $accommodation->type }}</p>
                <h1 class="mt-2 text-4xl font-bold">{{ $accommodation->name }}</h1>
                <p class="mt-4 text-slate-600">{{ $accommodation->description }}</p>
                <p class="mt-6 font-semibold">
                    @if($accommodation->price_per_night !== null)
                        ₱{{ number_format($accommodation->price_per_night, 2) }} / night
                    @else
                        Rate not listed; contact the property for current pricing.
                    @endif
                </p>
                <p class="mt-2 text-slate-600">{{ $accommodation->address }}</p>
            </div>
        </div>
    </div>
@endsection
