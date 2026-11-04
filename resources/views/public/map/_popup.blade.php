<div class="min-w-[200px]">
    <strong class="text-base">{{ $name }}</strong>
    <div class="text-sm text-slate-600">{{ $category }}</div>
    @if($address)
        <div class="mt-1 text-xs text-slate-600">{{ $address }}</div>
    @endif
    @if($outsideVictoria)
        <div class="mt-2 text-xs font-semibold text-amber-800">The directory address appears to be outside Victoria.</div>
    @endif
    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-sm">
        <a class="text-teal-700" href="{{ $detailsUrl }}">View details</a>
        <a class="text-teal-700" href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer">Directions</a>
    </div>
</div>
