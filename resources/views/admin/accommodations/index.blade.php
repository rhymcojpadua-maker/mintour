@extends('layouts.admin')
@section('title', 'Accommodations')
@section('heading', 'Accommodations')

@section('content')
    <x-admin.page-header
        title="Accommodations"
        subtitle="Resorts, inns, and homestays."
        :action-url="route('admin.accommodations.create')"
        action-label="New accommodation"
        :search-route="route('admin.accommodations.index')"
        :search="$search ?? ''"
    />

    @if($accommodations->isEmpty())
        <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-16 text-center text-slate-400">
            No accommodations yet. <a href="{{ route('admin.accommodations.create') }}" class="text-teal-700 font-semibold">Create one →</a>
        </div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($accommodations as $a)
                <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="relative h-40">
                        <img src="{{ \App\Support\MediaUrl::resolve($a->cover_image) ?: 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=600&q=80' }}"
                             class="w-full h-full object-cover" alt="">
                        <span class="absolute top-3 left-3 px-2 py-1 rounded-lg bg-white/95 text-[10px] font-bold text-teal-800 uppercase">{{ $a->type }}</span>
                        <span class="absolute bottom-3 right-3 px-2 py-1 rounded-lg bg-ink-900/80 text-white text-[11px] font-bold">
                            @if($a->price_per_night !== null)
                                ₱{{ number_format($a->price_per_night, 0) }}/night
                            @else
                                Rate not listed
                            @endif
                        </span>
                    </div>
                    <div class="p-4">
                        <div class="font-bold truncate">{{ $a->name }}</div>
                        <div class="text-xs text-slate-500 mt-1">★ {{ number_format($a->rating_avg, 1) }} · {{ $a->address }}</div>
                        <div class="flex justify-between items-center mt-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold
                                {{ $a->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $a->is_published ? 'Live' : 'Hidden' }}
                            </span>
                            <div class="flex gap-3 text-xs">
                                <a href="{{ route('admin.accommodations.edit', $a) }}" class="text-teal-700 font-semibold">Edit</a>
                                <form method="POST" action="{{ route('admin.accommodations.destroy', $a) }}"
                                      onsubmit="return confirm('Delete this accommodation?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-coral-500 font-semibold">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $accommodations->links() }}</div>
    @endif
@endsection
