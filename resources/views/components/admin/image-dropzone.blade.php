@props(['name', 'label' => 'Cover image', 'current' => null, 'hint' => 'JPG, PNG, or WebP · max 4 MB'])

<div x-data="{
        preview: @js(\App\Support\MediaUrl::resolve($current)),
        fileName: null,
        handle(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.fileName = file.name;
            const r = new FileReader();
            r.onload = ev => this.preview = ev.target.result;
            r.readAsDataURL(file);
        },
        clear() { this.preview = null; this.fileName = null; $refs.input.value = ''; }
     }">
    <label class="block text-sm font-semibold text-ink-900 mb-2">{{ $label }}</label>

    <div class="relative rounded-2xl border-2 border-dashed border-slate-300 hover:border-teal-500 transition bg-slate-50 overflow-hidden">

        {{-- Dropzone --}}
        <template x-if="!preview">
            <label class="block cursor-pointer p-8 text-center">
                <input x-ref="input" type="file" name="{{ $name }}" accept="image/*" class="hidden" @change="handle">
                <div class="w-14 h-14 rounded-full bg-white mx-auto grid place-items-center mb-3 shadow-sm">
                    <x-admin.icon name="photo" class="w-6 h-6 text-teal-700" />
                </div>
                <div class="font-semibold text-ink-900">Click to upload</div>
                <div class="text-xs text-slate-500 mt-1">{{ $hint }}</div>
            </label>
        </template>

        {{-- Preview --}}
        <template x-if="preview">
            <div class="relative">
                <img :src="preview" class="w-full h-56 object-cover">
                <div class="absolute inset-x-0 bottom-0 p-3 bg-linear-to-t from-ink-900/90 to-transparent flex items-end justify-between gap-3">
                    <div class="text-white text-xs font-semibold truncate" x-text="fileName || 'Current image'"></div>
                    <div class="flex gap-2">
                        <label class="px-3 py-1.5 rounded-lg bg-white/90 text-teal-900 text-xs font-bold cursor-pointer hover:bg-white">
                            Replace
                            <input type="file" name="{{ $name }}" accept="image/*" class="hidden" @change="handle">
                        </label>
                        <button type="button" @click="clear"
                                class="px-3 py-1.5 rounded-lg bg-coral-500 text-white text-xs font-bold hover:bg-coral-600">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
