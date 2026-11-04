@extends('layouts.admin')
@section('title', 'Settings')
@section('heading', 'Settings')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-4xl space-y-6">
        @csrf @method('PUT')

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Site settings --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-6">
                <h3 class="font-bold text-ink-900 mb-4">Site</h3>
                <div class="space-y-4">
                    <x-admin.form-field name="settings[site_name]" label="Site name"
                                       :value="$settings['site_name'] ?? 'MinTour'" required />
                    <x-admin.form-field name="settings[contact_email]" label="Contact email" type="email"
                                       :value="$settings['contact_email'] ?? ''" />
                    <x-admin.form-field name="settings[default_lang]" label="Default language" type="select"
                                       :value="$settings['default_lang'] ?? 'en'"
                                       :options="['en' => 'English', 'fil' => 'Filipino']" />
                </div>
            </div>

            {{-- AI settings --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-6">
                <h3 class="font-bold text-ink-900 mb-4">AI assistant</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold">Groq API key</label>
                        <input type="password" disabled value="{{ config('groq.key') ? 'Configured' : 'Not configured' }}"
                               class="mt-1 w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-mono text-sm">
                        <div class="text-xs text-slate-500 mt-1">
                            Configure in <code class="font-mono">.env</code> as <code class="font-mono">GROQ_API_KEY</code>.
                        </div>
                    </div>
                    <x-admin.form-field name="settings[groq_model]" label="Model"
                                       :value="$settings['groq_model']" required />
                    <x-admin.form-field name="settings[rate_limit]" label="Rate limit (messages / minute / user)" type="number" min="1" max="120"
                                       :value="$settings['rate_limit']" />
                    <x-admin.form-field name="settings[max_kb]" label="Max KB articles retrieved per query" type="number" min="1" max="20"
                                       :value="$settings['max_kb']" />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button class="px-6 py-3 rounded-xl bg-teal-700 text-white font-semibold text-sm">Save settings</button>
        </div>
    </form>
@endsection
