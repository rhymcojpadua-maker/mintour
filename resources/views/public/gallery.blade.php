@extends('layouts.app')
@section('title', 'Gallery')

@section('content')
    <div class="pt-32 pb-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mb-10">
                <div class="text-xs font-bold uppercase tracking-[.28em] text-teal-700">Photo stories</div>
                <h1 class="mt-3 font-serif text-5xl sm:text-6xl">Victoria <span class="italic text-teal-700">Gallery</span></h1>
                <p class="mt-4 text-ink-700/70">A collection of places and moments from Victoria and nearby destinations.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                @forelse($photos as $photo)
                    <figure class="group overflow-hidden rounded-3xl border border-teal-900/5 bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-soft">
                        <div class="aspect-[4/3] overflow-hidden bg-teal-50">
                            <img src="{{ \App\Support\MediaUrl::resolve($photo->path) }}"
                                 class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                 alt="{{ $photo->caption ?: $photo->destination?->name ?: 'Victoria tourism photo' }}"
                                 loading="lazy">
                        </div>
                        <figcaption class="p-4 sm:p-5">
                            <div class="font-bold text-ink-900">{{ $photo->caption ?: $photo->destination?->name ?: 'Victoria, Oriental Mindoro' }}</div>
                            @if($photo->destination)
                                <a href="{{ route('destinations.show', $photo->destination) }}" class="mt-2 inline-block text-sm font-semibold text-teal-700 hover:text-teal-900">
                                    View destination →
                                </a>
                            @endif
                        </figcaption>
                    </figure>
                @empty
                    <p class="rounded-2xl bg-white p-8 text-slate-500 sm:col-span-2 lg:col-span-3">No gallery photos yet. Please check again later.</p>
                @endforelse
            </div>

            <div class="mt-12">{{ $photos->links() }}</div>
        </div>
    </div>
@endsection
