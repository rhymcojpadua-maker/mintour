@extends('layouts.admin')
@section('title', 'Knowledge Base')
@section('heading', 'AI Knowledge Base')

@section('content')
    <x-admin.page-header
        title="Knowledge Base"
        subtitle="Articles the chatbot retrieves answers from. Publish to make them live."
        :action-url="route('admin.knowledge.create')"
        action-label="New article"
        :search-route="route('admin.knowledge.index')"
        :search="$search ?? ''"
    >
        <select name="status" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-teal-500 outline-none text-sm">
            <option value="">All status</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
            <option value="draft"     @selected(request('status') === 'draft')>Draft</option>
        </select>
    </x-admin.page-header>

    @if($articles->isEmpty())
        <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-16 text-center text-slate-400">
            No articles yet. The chatbot will fall back to "I don't have verified info on that" until you add some.
        </div>
    @else
        <div class="grid lg:grid-cols-2 gap-4">
            @foreach($articles as $a)
                <div class="rounded-2xl bg-white border border-slate-200 p-5 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-800 text-[10px] font-bold uppercase tracking-wider">
                                    {{ $a->category }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $a->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $a->is_published ? 'Published' : 'Draft' }}
                                </span>
                                @if($a->tags)
                                    @foreach(array_slice($a->tags, 0, 3) as $tag)
                                        <span class="text-[10px] text-slate-500">#{{ $tag }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="font-bold text-ink-900">{{ $a->title }}</div>
                            <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $a->content }}</p>
                            @if($a->source)
                                <div class="text-xs text-slate-400 mt-2">Source: {{ $a->source }}</div>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <form method="POST" action="{{ route('admin.knowledge.update', $a) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="toggle_publish" value="1">
                                <input type="hidden" name="is_published" value="{{ $a->is_published ? 0 : 1 }}">
                                <input type="hidden" name="title" value="{{ $a->title }}">
                                <input type="hidden" name="category" value="{{ $a->category }}">
                                <input type="hidden" name="content" value="{{ $a->content }}">
                                <button class="text-[11px] font-bold text-teal-700 hover:text-teal-900">
                                    {{ $a->is_published ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.knowledge.edit', $a) }}" class="text-[11px] font-semibold text-teal-700">Edit</a>
                            <form method="POST" action="{{ route('admin.knowledge.destroy', $a) }}"
                                  onsubmit="return confirm('Delete this article?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-[11px] font-semibold text-coral-500">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $articles->links() }}</div>
    @endif
@endsection
