@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Analytics Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl">
        <p class="mb-6 text-sm text-slate-500">A snapshot of content, visitors, and assistant activity.</p>

    {{-- ── Stat grid ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <x-admin.stat-card label="Destinations"    :value="$stats['destinations']"  icon="map-pin"               color="teal"    :href="route('admin.destinations.index')" />
        <x-admin.stat-card label="Businesses"      :value="$stats['businesses']"    icon="building-storefront"  color="coral"   :href="route('admin.accommodations.index')" />
        <x-admin.stat-card label="Events"          :value="$stats['events']"        icon="calendar-days"        color="amber"   :href="route('admin.events.index')" />
        <x-admin.stat-card label="Users"           :value="$stats['users']"         icon="users"                color="violet"  :href="route('admin.users.index')" />
        <x-admin.stat-card label="KB Articles"      :value="$stats['kb_articles']"   icon="book-open"            color="emerald" :href="route('admin.knowledge.index')" />
        <x-admin.stat-card label="Chat sessions"   :value="$stats['chat_sessions']" icon="light-bulb"           color="sky" />
        <x-admin.stat-card label="Reviews"          :value="$stats['reviews']"       icon="star"                 color="rose"    :href="route('admin.reviews.index')" />
        <x-admin.stat-card label="Unanswered"       :value="$stats['unanswered']"    icon="question-mark-circle" color="red"     :href="route('admin.unanswered.index')" />
    </div>

    {{-- ── Charts ── --}}
    <div class="grid lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-ink-900">Popular destinations</h3>
                <span class="text-xs text-slate-500">by reviews</span>
            </div>
            @if($popular->sum('rating_count') > 0)
                <div class="relative h-72">
                    <canvas id="chartPopular"></canvas>
                </div>
            @else
                <div class="grid h-72 place-items-center rounded-xl bg-slate-50 px-6 text-center text-sm text-slate-500">
                    No review activity yet. Destination popularity will appear here once visitors leave reviews.
                </div>
            @endif
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-ink-900">Language usage</h3>
                <span class="text-xs text-slate-500">chat sessions</span>
            </div>
            @if($langUsage->isNotEmpty())
                <div class="relative h-72">
                    <canvas id="chartLang"></canvas>
                </div>
            @else
                <div class="grid h-72 place-items-center rounded-xl bg-slate-50 px-6 text-center text-sm text-slate-500">
                    Chat language statistics will appear after visitors use the assistant.
                </div>
            @endif
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-ink-900">Chatbot activity</h3>
                <span class="text-xs text-slate-500">last 7 days</span>
            </div>
            <div class="relative h-64">
                <canvas id="chartChat"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-ink-900">Frequently asked</h3>
                <span class="text-xs text-slate-500">top 5 user questions</span>
            </div>
            @forelse($topQuestions as $i => $row)
                <div class="flex items-center gap-3 py-2.5 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <div class="w-6 text-xs text-slate-400 font-mono">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="flex-1 text-sm font-medium truncate">{{ $row->content }}</div>
                    <div class="text-xs text-slate-500 w-16 text-right">{{ $row->c }} asks</div>
                    <div class="w-32 h-2 rounded-full bg-teal-100 overflow-hidden">
                        <div class="h-full bg-teal-600" style="width:{{ ($row->c / max($topQuestions->max('c'), 1)) * 100 }}%"></div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-sm">No chatbot activity yet.</div>
            @endforelse
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
const initializeDashboardCharts = () => {
    if (typeof Chart === 'undefined') return;

    const teal = '#0f766e', coral = '#fb7185', sand = '#fcd34d';
    const charts = [];

    const popularCanvas = document.getElementById('chartPopular');
    if (popularCanvas) charts.push(new Chart(popularCanvas, {
        type: 'bar',
        data: {
            labels: @json($popular->pluck('name')->map(fn($n) => Str::limit($n, 18, '…'))),
            datasets: [{
                data: @json($popular->pluck('rating_count')),
                backgroundColor: teal, borderRadius: 8, barThickness: 28,
            }],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' } } } },
    }));

    const languageCanvas = document.getElementById('chartLang');
    if (languageCanvas) charts.push(new Chart(languageCanvas, {
        type: 'bar',
        data: {
            labels: @json($langUsage->keys()->map(fn($lang) => ['en' => 'English', 'fil' => 'Filipino'][$lang] ?? ucfirst($lang))),
            datasets: [{
                data: @json($langUsage->values()),
                backgroundColor: [teal, coral, sand],
                borderWidth: 0,
                borderRadius: 8,
            }],
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } },
    }));

    const chatCanvas = document.getElementById('chartChat');
    if (chatCanvas) charts.push(new Chart(chatCanvas, {
        type: 'line',
        data: {
            labels: @json($chatActivity->keys()),
            datasets: [{
                data: @json($chatActivity->values()),
                borderColor: teal,
                backgroundColor: 'rgba(20,184,166,.15)',
                fill: true, tension: .4, borderWidth: 3,
                pointBackgroundColor: teal, pointRadius: 4,
            }],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' } } } },
    }));

    window.addEventListener('mintour:page-unload', () => charts.forEach(chart => chart.destroy()), { once: true });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDashboardCharts, { once: true });
} else {
    document.addEventListener('mintour:page-loaded', initializeDashboardCharts, { once: true });
}
})();
</script>
@endpush
