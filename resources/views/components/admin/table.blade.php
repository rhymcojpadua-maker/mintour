@props(['headers' => [], 'empty' => 'No records found.'])

<div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                <tr>
                    @foreach($headers as $h)
                        <th class="text-left px-4 py-3 font-semibold {{ $h === '' ? 'text-right' : '' }}">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @if(trim($slot) === '')
                    <tr><td colspan="{{ count($headers) }}" class="px-4 py-16 text-center text-slate-400">
                        {{ $empty }}
                    </td></tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>
</div>

@if(isset($paginator) && $paginator->hasPages())
    <div class="mt-6">{{ $paginator->links() }}</div>
@endif
