@extends('layouts.admin')
@section('title', 'Categories')
@section('heading', 'Categories')

@section('content')
    <x-admin.page-header
        title="Categories"
        subtitle="Organize destinations into browsable groups."
        :action-url="route('admin.categories.create')"
        action-label="New category"
    />

    <x-admin.table :headers="['Icon', 'Name', 'Slug', 'Places', 'Order', 'Active', '']">
        @foreach($categories as $c)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-teal-50 text-teal-700">
                        <x-public.category-icon :slug="$c->slug" class="h-5 w-5" />
                    </span>
                </td>
                <td class="px-4 py-3 font-semibold">{{ $c->name }}</td>
                <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $c->slug }}</td>
                <td class="px-4 py-3">{{ $c->destinations_count }}</td>
                <td class="px-4 py-3 text-slate-500">{{ $c->sort_order }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold
                        {{ $c->is_active ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $c->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <x-admin.row-actions
                        :edit-route="route('admin.categories.edit', $c)"
                        :delete-route="route('admin.categories.destroy', $c)"
                    />
                </td>
            </tr>
        @endforeach
    </x-admin.table>
@endsection
