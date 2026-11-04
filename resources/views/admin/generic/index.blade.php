@extends('layouts.admin')
@section('title', $title)
@section('heading', $title)

@section('content')
    @php
        $displayName = fn ($item) => $item->name ?? $item->title ?? $item->question ?? '#' . $item->id;
        $detail = fn ($item) => $item->address ?? $item->location ?? $item->email ?? $item->phone ?? $item->category ?? $item->type ?? null;
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-teal-700">Admin</p>
                <h2 class="mt-1 text-2xl font-extrabold text-ink-900">{{ $title }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $items->total() }} {{ \Illuminate\Support\Str::plural(strtolower($title), $items->total()) }}</p>
            </div>
            <a href="{{ route('admin.' . $resource . '.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                <x-admin.icon name="plus" class="h-4 w-4" />
                Add {{ strtolower($title) }}
            </a>
        </div>

        <form method="GET" action="{{ route('admin.' . $resource . '.index') }}"
              class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <label for="admin-search" class="sr-only">Search {{ strtolower($title) }}</label>
            <input id="admin-search" name="q" value="{{ $search }}" placeholder="Search {{ strtolower($title) }}…"
                   class="min-w-52 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
            <button type="submit" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-teal-500 hover:text-teal-800">
                Search
            </button>
            @if($search !== '')
                <a href="{{ route('admin.' . $resource . '.index') }}" class="text-sm font-semibold text-slate-500 hover:text-teal-800">Clear</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">Record</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Details</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Created</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                            @php
                                $active = $item->is_published ?? $item->is_active ?? null;
                                $secondary = $detail($item);
                                if ($resource === 'accommodations' && $item->price_per_night > 0) {
                                    $secondary = '₱' . number_format($item->price_per_night, 2) . '/night';
                                } elseif ($resource === 'products' && isset($item->price)) {
                                    $secondary = '₱' . number_format($item->price, 2);
                                } elseif ($resource === 'restaurants') {
                                    $secondary = $item->cuisine . ($item->price_range ? ' · ' . $item->price_range : '');
                                } elseif ($resource === 'events') {
                                    $secondary = $item->starts_at?->format('M j, Y · g:i A') . ($item->location ? ' · ' . $item->location : '');
                                } elseif ($resource === 'announcements') {
                                    $priority = is_object($item->priority) ? $item->priority->value : $item->priority;
                                    $secondary = ucfirst($priority);
                                }
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="max-w-sm px-5 py-4">
                                    <div class="truncate font-semibold text-ink-900">{{ $displayName($item) }}</div>
                                    @if(isset($item->slug))
                                        <div class="mt-0.5 truncate font-mono text-xs text-slate-400">{{ $item->slug }}</div>
                                    @endif
                                </td>
                                <td class="max-w-xs px-5 py-4 text-slate-500">
                                    <div class="truncate">{{ $secondary ?: '—' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($active !== null)
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $active ? 'Active' : 'Hidden' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">
                                    {{ $item->created_at?->format('M j, Y') ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a href="{{ route('admin.' . $resource . '.edit', $item) }}"
                                       class="mr-3 font-semibold text-teal-700 hover:text-teal-900">Edit</a>
                                    <form class="inline" method="POST"
                                          action="{{ route('admin.' . $resource . '.destroy', $item) }}"
                                          onsubmit="return confirm('Delete this record? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <p class="font-semibold text-slate-600">{{ $search !== '' ? 'No matching records found.' : 'No records yet.' }}</p>
                                    <p class="mt-1 text-sm text-slate-400">Add a {{ strtolower($title) }} to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($items->hasPages())
            <div>{{ $items->links() }}</div>
        @endif
    </div>
@endsection
