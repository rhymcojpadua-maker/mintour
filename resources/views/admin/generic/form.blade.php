@extends('layouts.admin')
@section('title', $title)
@section('heading', $title)

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('admin.' . $resource . '.index') }}"
               class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-teal-800 transition hover:border-teal-500"
               aria-label="Back to {{ strtolower($title) }} list">
                <x-admin.icon name="arrow-left" class="h-5 w-5" />
            </a>
            <div>
                <h2 class="text-xl font-extrabold text-ink-900">{{ $item ? ($item->name ?? $item->title ?? $item->question ?? $title) : $title }}</h2>
                <p class="text-sm text-slate-500">Enter the details below and save your changes.</p>
            </div>
        </div>

        <form method="POST"
              enctype="multipart/form-data"
              action="{{ $item ? route('admin.' . $resource . '.update', $item) : route('admin.' . $resource . '.store') }}"
              class="space-y-6">
            @csrf
            @if($item) @method('PUT') @endif

            <div class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2">
                @foreach($fields as $field)
                    @php
                        $name = $field['name'];
                        $type = $field['type'];
                        $value = old($name, data_get($item, $name, $prefill[$name] ?? $field['default'] ?? ''));
                        if ($type === 'password') {
                            $value = '';
                        }
                        if ($value instanceof \BackedEnum) {
                            $value = $value->value;
                        }
                        if ($type === 'datetime-local' && $value instanceof \DateTimeInterface) {
                            $value = $value->format('Y-m-d\TH:i');
                        } elseif ($name === 'amenities' && is_array($value)) {
                            $value = implode(', ', $value);
                        } elseif ($name === 'tags' && is_array($value)) {
                            $value = implode(', ', $value);
                        }
                        $isWide = in_array($type, ['textarea', 'image'], true);
                    @endphp

                    <div class="{{ $isWide ? 'sm:col-span-2' : '' }}">
                        @if($type === 'checkbox')
                            <input type="hidden" name="{{ $name }}" value="0">
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
                                <input type="checkbox" name="{{ $name }}" value="1"
                                       @checked((bool) $value)
                                       class="rounded border-slate-300 text-teal-700 focus:ring-teal-500">
                                <span>
                                    <span class="block text-sm font-semibold text-ink-900">{{ $field['label'] }}</span>
                                    @if(!empty($field['hint']))
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ $field['hint'] }}</span>
                                    @endif
                                </span>
                            </label>
                        @else
                            <label for="field-{{ $name }}" class="mb-1.5 block text-sm font-semibold text-ink-900">
                                {{ $field['label'] }}
                                @if(!empty($field['required']))<span class="text-rose-600" aria-hidden="true">*</span>@endif
                            </label>

                            @if($type === 'textarea')
                                <textarea id="field-{{ $name }}" name="{{ $name }}" rows="6"
                                          @if(!empty($field['required'])) required @endif
                                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">{{ $value }}</textarea>
                            @elseif($type === 'select')
                                <select id="field-{{ $name }}" name="{{ $name }}"
                                        @if(!empty($field['required'])) required @endif
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                                    @unless(!empty($field['required']))
                                        <option value="">Select {{ strtolower($field['label']) }}</option>
                                    @endunless
                                    @foreach($field['options'] as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            @elseif($type === 'image')
                                @if($item?->cover_image)
                                    <img src="{{ \App\Support\MediaUrl::resolve($item->cover_image) }}"
                                         alt="Current {{ strtolower($field['label']) }}"
                                         class="mb-3 h-40 w-full rounded-xl object-cover sm:w-72">
                                @endif
                                <input id="field-{{ $name }}" type="file" name="{{ $name }}" accept="image/*"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800 hover:file:bg-teal-100">
                            @else
                                <input id="field-{{ $name }}" type="{{ $type }}" name="{{ $name }}"
                                       value="{{ $value }}"
                                       @if(!empty($field['required'])) required @endif
                                       @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                       @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                       @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                                       @if($type === 'password') autocomplete="new-password" @endif
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            @endif

                            @if(!empty($field['hint']))
                                <p class="mt-1.5 text-xs text-slate-500">{{ $field['hint'] }}</p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('admin.' . $resource . '.index') }}"
                   class="rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                    Cancel
                </a>
                <button type="submit"
                        class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                    Save {{ strtolower($title) }}
                </button>
            </div>
        </form>
    </div>
@endsection
