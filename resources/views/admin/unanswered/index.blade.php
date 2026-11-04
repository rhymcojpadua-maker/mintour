@extends('layouts.admin')
@section('title', 'Unanswered questions')
@section('heading', 'Unanswered Questions')

@section('content')
    <div class="mb-6 rounded-2xl bg-coral-300/30 border border-coral-500/30 p-4 flex items-start gap-3">
        <x-admin.icon name="light-bulb" class="w-5 h-5 text-coral-500 shrink-0 mt-0.5" />
        <div class="text-sm text-coral-500">
            These are questions the AI could not answer from the knowledge base. Create KB articles from them to improve future responses.
        </div>
    </div>

    @if($items->isEmpty())
        <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-16 text-center text-slate-400">
            No unanswered questions. The AI is answering everything from the KB.
        </div>
    @else
        <div class="space-y-3">
            @foreach($items as $item)
                <div class="rounded-2xl bg-white border p-5 flex items-start gap-4
                    {{ $item->is_resolved ? 'border-slate-200' : 'border-coral-500/30 bg-coral-300/10' }}">
                    <div class="w-10 h-10 rounded-xl grid place-items-center shrink-0
                        {{ $item->is_resolved ? 'bg-emerald-100 text-emerald-700' : 'bg-coral-300/60 text-coral-500' }}">
                        <x-admin.icon :name="$item->is_resolved ? 'check-circle' : 'question-mark-circle'" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-ink-900">{{ $item->question }}</div>
                        <div class="text-xs text-slate-400 mt-1 flex items-center gap-3">
                            <span>{{ $item->created_at->diffForHumans() }}</span>
                            <span class="uppercase font-bold tracking-wider">{{ $item->lang }}</span>
                            @if($item->session)
                                <span>Session #{{ $item->session->id }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @unless($item->is_resolved)
                            <form method="POST" action="{{ route('admin.unanswered.resolve', $item) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs font-semibold text-teal-700 hover:text-teal-900">Mark resolved</button>
                            </form>
                        @endunless
                        <a href="{{ route('admin.unanswered.convert', $item) }}"
                           class="text-xs font-semibold text-teal-700 hover:text-teal-900 whitespace-nowrap">
                            Create KB article →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $items->links() }}</div>
    @endif
@endsection
5.7 Users
