@extends('layouts.admin')
@section('title', $accommodation->exists ? 'Edit accommodation' : 'New accommodation')
@section('heading', $accommodation->exists ? 'Edit accommodation' : 'New accommodation')

@section('content')
    <div class="max-w-3xl">
        <form method="POST"
              action="{{ $accommodation->exists ? route('admin.accommodations.update', $accommodation) : route('admin.accommodations.store') }}"
              enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($accommodation->exists) @method('PUT') @endif

            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-5">
                <div class="grid sm:grid-cols-2 gap-5">
                    <x-admin.form-field name="name" label="Name" :value="$accommodation->name" required />
                    <x-admin.form-field name="type" label="Type" :value="$accommodation->type"
                                       required hint="Resort, Inn, Homestay, Eco-Lodge" />
                    <x-admin.form-field name="price_per_night" label="Price per night (₱)" type="number" step="0.01"
                                       :value="$accommodation->price_per_night"
                                       hint="Leave blank if the current rate is not listed." />
                    <x-admin.form-field name="contact" label="Contact number" :value="$accommodation->contact" />
                    <div class="sm:col-span-2">
                        <x-admin.form-field name="address" label="Address" :value="$accommodation->address" />
                    </div>
                    <x-admin.form-field name="latitude"  label="Latitude"  type="number" step="any" :value="$accommodation->latitude" />
                    <x-admin.form-field name="longitude" label="Longitude" type="number" step="any" :value="$accommodation->longitude" />
                    <div class="sm:col-span-2">
                        <x-admin.form-field name="description" label="Description" type="textarea" :rows="4"
                                           :value="$accommodation->description" required />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold mb-1.5 block">Amenities</label>
                        <div x-data="{ items: @js($accommodation->amenities ?? []) , draft:'' }">
                            <div class="flex gap-2">
                                <input x-model="draft" @keydown.enter.prevent="if(draft.trim()){ items.push(draft.trim()); draft='' }"
                                       placeholder="e.g. WiFi, Pool, Aircon"
                                       class="flex-1 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 outline-none focus:border-teal-500">
                                <button type="button"
                                        @click="if(draft.trim()){ items.push(draft.trim()); draft='' }"
                                        class="px-4 py-2.5 rounded-xl bg-teal-50 text-teal-800 font-semibold text-sm">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <template x-for="(item, i) in items" :key="i">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-teal-50 text-teal-800 text-xs font-semibold">
                                        <span x-text="item"></span>
                                        <input type="hidden" :name="`amenities[${i}]`" :value="item">
                                        <button type="button" @click="items.splice(i, 1)" class="text-teal-600">✕</button>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-6">
                <x-admin.image-dropzone name="cover_image" :current="$accommodation->cover_image" />
                <div class="mt-4">
                    <x-admin.form-field name="is_published" label="Published" type="checkbox"
                                       :value="$accommodation->is_published ?? true" hint="Visible on public site" />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.accommodations.index') }}" class="px-5 py-3 rounded-xl bg-slate-100 font-semibold text-sm">Cancel</a>
                <button class="px-6 py-3 rounded-xl bg-teal-700 text-white font-semibold text-sm">
                    {{ $accommodation->exists ? 'Save changes' : 'Create accommodation' }}
                </button>
            </div>
        </form>
    </div>
@endsection
Restaurants, Products, Events, Announcements, FAQs, Emergency Contacts index + form views follow the identical layout using the field sets from Part 2's FormRequests. They ship in the repo. Event form uses <input type="datetime-local"> for starts_at/ends_at; FAQ and Emergency Contacts use the simple table+modal-free-form pattern.
