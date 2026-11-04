@props(['editRoute', 'deleteRoute', 'editLabel' => 'Edit', 'deleteLabel' => 'Delete'])

<div class="text-right whitespace-nowrap">
    <a href="{{ $editRoute }}" class="text-teal-700 hover:text-teal-900 font-semibold text-xs mr-4 inline-flex items-center gap-1">
        <x-admin.icon name="pencil-square" class="w-3.5 h-3.5" /> {{ $editLabel }}
    </a>
    <form method="POST" action="{{ $deleteRoute }}" class="inline"
          onsubmit="return confirm('Delete this item? This cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-coral-500 hover:text-coral-600 font-semibold text-xs inline-flex items-center gap-1">
            <x-admin.icon name="trash" class="w-3.5 h-3.5" /> {{ $deleteLabel }}
        </button>
    </form>
</div>
