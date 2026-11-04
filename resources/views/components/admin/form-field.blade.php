@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'step' => null, 'options' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-ink-900 mb-1.5">
        {{ $label }} @if($required)<span class="text-coral-500">*</span>@endif
    </label>

    @if($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows ?? 4 }}" @if($required) required @endif
                  {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 outline-none focus:border-teal-500 focus:bg-white transition']) }}>{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 outline-none focus:border-teal-500 focus:bg-white transition']) }}>
            @foreach($options as $val => $label)
                <option value="{{ $val }}" @selected(old($name, $value) == $val)>{{ $label }}</option>
            @endforeach
        </select>
    @elseif($type === 'checkbox')
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="{{ $name }}" value="0">
            <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $value))
                   {{ $attributes->merge(['class' => 'w-5 h-5 rounded-md border-2 border-teal-700/30 text-teal-700 focus:ring-teal-500']) }}>
            <span class="text-sm text-slate-600">{{ $hint ?? 'Enabled' }}</span>
        </label>
    @else
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
               @if($required) required @endif @if($step) step="{{ $step }}" @endif
               {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 outline-none focus:border-teal-500 focus:bg-white transition']) }}>
    @endif

    @if($hint && $type !== 'checkbox')
        <div class="text-xs text-slate-500 mt-1">{{ $hint }}</div>
    @endif
    @error($name)
        <div class="text-xs text-coral-500 mt-1">{{ $message }}</div>
    @enderror
</div>
