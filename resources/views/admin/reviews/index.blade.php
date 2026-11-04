@extends('layouts.admin')
@section('title', 'Reviews')
@section('heading', 'Reviews')

@section('content')
    <x-admin.page-header
        title="Reviews"
        subtitle="Moderate user reviews across all destinations."
        :search-route="route('admin.reviews.index')"
        :search="$search ?? ''"
    />

    <x-admin.table :headers="['User', 'Destination', 'Rating', 'Comment', 'Date', '']" :paginator="$reviews">
        @foreach($reviews as $r)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3 font-semibold">{{ $r->user->name }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('destinations.show', $r->destination) }}"
                       target="_blank" class="text-teal-700 hover:underline">{{ $r->destination->name }}</a>
                </td>
                <td class="px-4 py-3">
                    <span class="text-sand-300">
                        @for($i = 1; $i <= 5; $i++){{ $i <= $r->rating ? '★' : '☆' }}@endfor
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-500 max-w-md"><div class="line-clamp-2">{{ $r->body }}</div></td>
                <td class="px-4 py-3 text-xs text-slate-400">{{ $r->created_at->format('M j, Y') }}</td>
                <td class="px-4 py-3 text-right">
                    <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}"
                          onsubmit="return confirm('Delete this review?')" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-coral-500 font-semibold text-xs">Remove</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-admin.table>
@endsection
5.9 Settings
