<div x-data x-show="$store.toast.show" x-transition x-cloak
     class="fixed top-6 right-6 z-[70] px-5 py-4 rounded-2xl bg-ink-900 text-white shadow-soft flex items-center gap-3">
    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
    <span class="text-sm font-semibold" x-text="$store.toast.msg"></span>
</div>

@if(session('status'))
    @push('scripts')
        <script>
            (() => {
                const showStatusToast = () => setTimeout(() => window.Alpine.store('toast').push(@json(session('status'))), 200);
                if (window.Alpine) showStatusToast();
                else document.addEventListener('alpine:init', showStatusToast, { once: true });
            })();
        </script>
    @endpush
@endif
