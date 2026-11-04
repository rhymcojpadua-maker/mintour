@extends('layouts.admin')
@section('title', $category->exists ? 'Edit category' : 'New category')
@section('heading', $category->exists ? 'Edit category' : 'New category')

@section('content')
    <div class="max-w-2xl">
        <form method="POST"
              action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              class="space-y-6">
            @csrf
            @if($category->exists) @method('PUT') @endif

            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-5">
                <x-admin.form-field name="name" label="Name" :value="$category->name" required />
                <x-admin.form-field name="slug" label="Slug" :value="$category->slug" hint="Auto-generated if blank." />
                <x-admin.form-field name="icon" label="Emoji icon" :value="$category->icon" hint="e.g. 🌊 🌿 ⛪ 🍽️ ⛰️" />
                <x-admin.form-field name="color" label="Color (hex)" :value="$category->color ?? '#0f766e'" required />
                <x-admin.form-field name="description" label="Description" type="textarea" :rows="2"
                                   :value="$category->description" />
                <x-admin.form-field name="sort_order" label="Sort order" type="number"
                                   :value="$category->sort_order ?? 0" />
                <x-admin.form-field name="is_active" label="Active" type="checkbox"
                                   :value="$category->is_active ?? true" hint="Show in navigation" />
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.categories.index') }}"
                   class="px-5 py-3 rounded-xl bg-slate-100 font-semibold text-sm">Cancel</a>
                <button class="px-6 py-3 rounded-xl bg-teal-700 text-white font-semibold text-sm">
                    {{ $category->exists ? 'Save changes' : 'Create category' }}
                </button>
            </div>
        </form>
    </div>
@endsection
