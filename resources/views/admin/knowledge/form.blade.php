@extends('layouts.admin')
@section('title', $article->exists ? 'Edit article' : 'New article')
@section('heading', $article->exists ? 'Edit KB article' : 'New KB article')

@section('content')
    <div class="max-w-3xl">
        <form method="POST"
              action="{{ $article->exists ? route('admin.knowledge.update', $article) : route('admin.knowledge.store') }}"
              class="space-y-6">
            @csrf
            @if($article->exists) @method('PUT') @endif

            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-5">
                <x-admin.form-field name="title" label="Title" :value="$article->title ?? old('prefill_title')"
                                   required hint="A short, searchable headline." />

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-admin.form-field name="category" label="Category" :value="$article->category ?? 'General'" required
                                       hint="e.g. Beaches, Transport, Hotels, Events, Food, Safety" />
                    <x-admin.form-field name="source" label="Source" :value="$article->source"
                                       hint="Who verified this? e.g. 'Tourism Office verified'" />
                </div>

                <x-admin.form-field name="content" label="Content" type="textarea" :rows="10"
                                   :value="$article->content" required
                                   hint="The AI retrieves this text verbatim. Be specific, factual, and include prices/hours where relevant." />

                <div>
                    <label class="text-sm font-semibold mb-1.5 block">Tags (optional)</label>
                    <div x-data="{ items: @js($article->tags ?? []), draft: '' }">
                        <div class="flex gap-2">
                            <input x-model="draft" @keydown.enter.prevent="if(draft.trim()){ items.push(draft.trim()); draft='' }"
                                   placeholder="beach, silonay, tricycle"
                                   class="flex-1 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 outline-none focus:border-teal-500">
                            <button type="button" @click="if(draft.trim()){ items.push(draft.trim()); draft='' }"
                                    class="px-4 py-2.5 rounded-xl bg-teal-50 text-teal-800 font-semibold text-sm">Add</button>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <template x-for="(item, i) in items" :key="i">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                                    <span x-text="item"></span>
                                    <input type="hidden" :name="`tags[${i}]`" :value="item">
                                    <button type="button" @click="items.splice(i, 1)" class="text-slate-500">✕</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <x-admin.form-field name="is_published" label="Published" type="checkbox"
                                   :value="$article->is_published ?? false"
                                   hint="Only published articles are retrievable by the AI." />

                @if(request('from_question'))
                    <div class="rounded-xl bg-teal-50 border border-teal-100 px-4 py-3 text-sm text-teal-900">
                        This article will resolve the unanswered question
                        <strong>{{ \App\Models\UnansweredQuestion::find(request('from_question'))?->question }}</strong>
                        when saved.
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.knowledge.index') }}" class="px-5 py-3 rounded-xl bg-slate-100 font-semibold text-sm">Cancel</a>
                <button class="px-6 py-3 rounded-xl bg-teal-700 text-white font-semibold text-sm">
                    {{ $article->exists ? 'Save changes' : 'Create article' }}
                </button>
            </div>
        </form>
    </div>
@endsection
5.6 Unanswered questions
