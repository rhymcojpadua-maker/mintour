@props(['active' => false, 'route', 'labelOn' => 'Active', 'labelOff' => 'Hidden'])

<form method="POST" action="{{ $route }}" class="inline">
    @csrf @method('PATCH')
    <button type="submit" class="px-2 py-1 rounded-lg text-xs font-bold transition
        {{ $active ? 'bg-teal-100 text-teal-800 hover:bg-teal-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
        {{ $active ? $labelOn : $labelOff }}
    </button>
</form>
4. Analytics dashboard
