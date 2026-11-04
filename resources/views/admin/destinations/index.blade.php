@extends('layouts.admin')
@section('title', 'Destinations')
@section('heading', 'Destinations')

@section('content')
    <x-admin.page-header
        title="Destinations"
        subtitle="Manage all tourist spots across Victoria."
        :action-url="route('admin.destinations.create')"
        action-label="New destination"
        :search-route="route('admin.destinations.index')"
        :search="$search"
    >
        <select name="cat" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-teal-500 outline-none text-sm">
            <option value="">All categories</option>
            @foreach(\App\Models\Category::active()->ordered()->get() as $c)
                <option value="{{ $c->slug }}" @selected(request('cat') === $c->slug)>{{ $c->name }}</option>
            @endforeach
        </select>
    </x-admin.page-header>

    <x-admin.table
        :headers="['Destination', 'Category', 'Rating', 'Featured', 'Published', '']"
        :paginator="$destinations"
        empty="No destinations yet."
    >
        @foreach($destinations as $d)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ \App\Support\MediaUrl::resolve($d->cover_image) ?: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=100&q=80' }}"
                             class="w-11 h-11 rounded-lg object-cover" alt="">
                        <div class="min-w-0">
                            <div class="font-semibold text-ink-900 truncate">{{ $d->name }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ $d->address }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-lg text-xs font-semibold"
                          style="background:{{ $d->category->color }}20; color:{{ $d->category->color }}">
                        {{ $d->category->name }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sand-300 text-xs">★★★★★</div>
                    <div class="text-xs text-slate-500">{{ number_format($d->rating_avg, 1) }} ({{ $d->rating_count }})</div>
                </td>
                <td class="px-4 py-3">
                    <x-admin.toggle-pill
                        :active="$d->is_featured"
                        :route="route('admin.destinations.toggle-featured', $d)"
                        label-on="Featured" label-off="Draft"
                    />
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold
                        {{ $d->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $d->is_published ? 'Live' : 'Hidden' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <x-admin.row-actions
                        :edit-route="route('admin.destinations.edit', $d)"
                        :delete-route="route('admin.destinations.destroy', $d)"
                    />
                </td>
            </tr>
        @endforeach
    </x-admin.table>
@endsection
5.2 Destinations form
