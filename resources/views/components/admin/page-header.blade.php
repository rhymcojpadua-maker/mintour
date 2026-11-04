@props(['title', 'subtitle' => null, 'action' => null, 'actionUrl' => null, 'actionLabel' => 'New', 'searchRoute' => null, 'search' => ''])

<div class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-ink-900">{{ $title }}</h2>
            @if($subtitle)<p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>@endif
        </div>

        @if($actionUrl)
            <a href="{{ $actionUrl }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-700 text-white font-semibold text-sm hover:shadow-glow transition">
                <x-admin.icon name="plus" class="w-4 h-4" />
                {{ $actionLabel }}
            </a>
        @endif
    </div>

    @if($searchRoute)
        <form method="GET" action="{{ $searchRoute }}" class="mt-5 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <x-admin.icon name="magnifying-glass" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ $search }}" placeholder="Search…"
                       class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none transition">
            </div>
            {{ $filters ?? '' }}
            <button class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 font-semibold text-sm hover:border-teal-500 transition">
                Filter
            </button>
            @if(request()->has('q') || request()->has('cat'))
                <a href="{{ $searchRoute }}" class="text-sm font-semibold text-slate-500 hover:text-coral-500">Clear</a>
            @endif
        </form>
    @endif
</div>
