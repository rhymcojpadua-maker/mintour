@extends('layouts.admin')
@section('title', $destination->exists ? 'Edit destination' : 'New destination')
@section('heading', $destination->exists ? 'Edit destination' : 'New destination')

@section('content')
    <div class="max-w-4xl">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('admin.destinations.index') }}"
               class="w-10 h-10 rounded-xl bg-white border border-slate-200 grid place-items-center hover:border-teal-500 transition">
                <x-admin.icon name="arrow-left" class="w-5 h-5" />
            </a>
            <div>
                <h2 class="text-xl font-extrabold">{{ $destination->exists ? $destination->name : 'New destination' }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $destination->exists ? 'Update details, photos, and location.' : 'Add a new spot to the public map and directory.' }}
                </p>
            </div>
        </div>

        <form method="POST"
              action="{{ $destination->exists ? route('admin.destinations.update', $destination) : route('admin.destinations.store') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @if($destination->exists) @method('PUT') @endif

            {{-- Basic info --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-5">
                <h3 class="font-bold text-ink-900">Basic information</h3>

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-admin.form-field name="name" label="Name" :value="$destination->name" required />
                    <x-admin.form-field name="slug" label="Slug" :value="$destination->slug"
                                       hint="Leave blank to auto-generate from name." />

                    <x-admin.form-field name="category_id" label="Category" type="select" required
                                       :value="$destination->category_id"
                                       :options="$categories->pluck('name', 'id')" />

                    <x-admin.form-field name="tagline" label="Tagline" :value="$destination->tagline"
                                       hint="Short label like 'Nature Reserve' or 'Heritage Site'." />

                    <x-admin.form-field name="sort_order" label="Sort order" type="number"
                                       :value="$destination->sort_order ?? 0" />
                    <x-admin.form-field name="distance_km" label="Distance from town (km)" type="number" step="0.1"
                                       :value="$destination->distance_km" />

                    <div class="sm:col-span-2">
                        <x-admin.form-field name="short_description" label="Short description"
                                           type="textarea" :rows="2" :value="$destination->short_description"
                                           required hint="Max 400 characters. Shown on cards and search results." />
                    </div>

                    <div class="sm:col-span-2">
                        <x-admin.form-field name="long_description" label="Long description"
                                           type="textarea" :rows="8" :value="$destination->long_description"
                                           required hint="Full story. Blank lines create paragraphs." />
                    </div>
                </div>
            </div>

            {{-- Location & details --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-5">
                <h3 class="font-bold text-ink-900">Location & details</h3>
                <div class="grid sm:grid-cols-2 gap-5">
                    <x-admin.form-field name="latitude"  label="Latitude"  type="number" step="any" :value="$destination->latitude" required />
                    <x-admin.form-field name="longitude" label="Longitude" type="number" step="any" :value="$destination->longitude" required />
                    <div class="sm:col-span-2">
                        <x-admin.form-field name="address" label="Address" :value="$destination->address" />
                    </div>
                    <x-admin.form-field name="hours"        label="Opening hours" :value="$destination->hours" />
                    <x-admin.form-field name="entrance_fee" label="Entrance fee"  :value="$destination->entrance_fee" />
                    <x-admin.form-field name="best_time"    label="Best time to visit" :value="$destination->best_time" />
                </div>
            </div>

            {{-- Media --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-5">
                <h3 class="font-bold text-ink-900">Media</h3>

                <x-admin.image-dropzone name="cover_image" label="Cover image"
                    :current="$destination->cover_image" />

                <div>
                    <label class="text-sm font-semibold mb-2 block">Add gallery photos</label>
                    <input type="file" name="photos[]" accept="image/*" multiple
                           class="w-full text-sm file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:bg-teal-50 file:text-teal-800 file:font-semibold hover:file:bg-teal-100">
                    <div class="text-xs text-slate-500 mt-1">Up to 12 images. JPG, PNG, WebP · 4 MB each.</div>
                </div>
            </div>

            {{-- Visibility --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-4">
                <h3 class="font-bold text-ink-900">Visibility</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-admin.form-field name="is_featured"  label="Featured"  type="checkbox" :value="$destination->is_featured"
                                       hint="Show on home page" />
                    <x-admin.form-field name="is_published" label="Published" type="checkbox"
                                       :value="$destination->is_published ?? true" hint="Visible on public site" />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.destinations.index') }}"
                   class="px-5 py-3 rounded-xl bg-slate-100 font-semibold text-sm hover:bg-slate-200 transition">
                    Cancel
                </a>
                <button class="px-6 py-3 rounded-xl bg-teal-700 text-white font-semibold text-sm hover:shadow-glow transition">
                    {{ $destination->exists ? 'Save changes' : 'Create destination' }}
                </button>
            </div>
        </form>

        @if($destination->exists && $destination->photos->isNotEmpty())
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="mb-4 font-bold text-ink-900">Gallery photos</h3>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                    @foreach($destination->photos as $photo)
                        <div class="group relative aspect-square overflow-hidden rounded-xl">
                            <img src="{{ \App\Support\MediaUrl::resolve($photo->path) }}"
                                 class="h-full w-full object-cover" alt="{{ $photo->caption ?: $destination->name }}">
                            <form method="POST" action="{{ route('admin.destinations.photos.destroy', [$destination, $photo]) }}"
                                  onsubmit="return confirm('Remove this gallery photo?')"
                                  class="absolute inset-x-0 bottom-0 bg-ink-900/80 p-2 text-center opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100 sm:focus-within:opacity-100">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-white">Remove photo</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
5.3 Categories
