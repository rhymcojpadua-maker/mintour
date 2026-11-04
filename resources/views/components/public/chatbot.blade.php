<div x-data="chatbot()" x-init="init()" @lang-changed.window="lang = $event.detail" x-cloak
     class="fixed bottom-6 right-6 z-50 flex flex-col items-end">

    {{-- Toggle button --}}
    <button @click="open = !open"
            class="relative w-16 h-16 rounded-full from-teal-700 to-teal-500 bg-linear-to-br text-white grid place-items-center shadow-glow hover:scale-110 transition-all btn-shimmer">
        <span class="absolute inset-0 rounded-full border-2 border-teal-400 animate-pulse-ring"></span>
        <svg x-show="!open" class="w-7 h-7 relative z-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <svg x-show="open" class="w-7 h-7 relative z-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 6l12 12M18 6L6 18"/>
        </svg>
    </button>

    {{-- Panel --}}
    <div x-show="open" x-transition.origin.bottom.right x-cloak
         class="mb-4 w-[calc(100vw-3rem)] sm:w-105 max-h-[80vh] rounded-4xl overflow-hidden bg-white shadow-soft border border-teal-900/10 flex flex-col">

        <div class="flex items-center gap-3 p-4 bg-ink-900 text-white">
            <div class="relative">
                <div class="w-10 h-10 rounded-full from-teal-500 to-emerald-500 bg-linear-to-br grid place-items-center font-bold text-sm">AI</div>
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-400 border-2 border-ink-900"></span>
            </div>
            <div class="flex-1">
                <div class="font-bold text-sm">MinTour Assistant</div>
                <div class="text-[11px] text-teal-300">Online · RAG-powered</div>
            </div>
            <div class="flex items-center gap-1 text-[11px]">
                <button @click="lang='en'" :class="lang==='en'?'bg-white/20':'bg-white/5 text-white/60'" class="px-2 py-1 rounded-full font-bold">EN</button>
                <button @click="lang='fil'" :class="lang==='fil'?'bg-white/20':'bg-white/5 text-white/60'" class="px-2 py-1 rounded-full font-bold">FIL</button>
            </div>
        </div>

        <div x-ref="log" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/60 min-h-70">
            <template x-for="(m, i) in messages" :key="i">
                <div class="flex gap-3 animate-fade-up" :class="m.role === 'user' ? 'flex-row-reverse' : ''">
                    <div class="w-7 h-7 rounded-full grid place-items-center text-[10px] font-bold shrink-0"
                         :class="m.role === 'user' ? 'bg-coral-500 text-white' : 'from-teal-500 to-emerald-500 bg-linear-to-br text-white'"
                         x-text="m.role === 'user' ? 'You' : 'AI'"></div>
                    <div class="max-w-[80%] rounded-2xl px-3.5 py-2.5 text-sm leading-relaxed"
                         :class="m.role === 'user' ? 'rounded-tr-md bg-teal-100' : (m.error ? 'rounded-tl-md bg-amber-50 border border-amber-200 text-amber-950' : 'rounded-tl-md bg-white border border-slate-200')"
                         x-html="m.html"></div>
                </div>
            </template>

            <div x-show="typing" class="flex gap-3">
                <div class="w-7 h-7 rounded-full from-teal-500 to-emerald-500 bg-linear-to-br grid place-items-center text-[10px] font-bold text-white shrink-0">AI</div>
                <div class="typing rounded-2xl rounded-tl-md bg-white border border-slate-200 px-4 py-3"><i></i><i></i><i></i></div>
            </div>
        </div>

        <div class="px-3 pt-3 pb-1 flex flex-wrap gap-1.5 bg-white border-t border-slate-100">
            <template x-for="q in (lang === 'en'
                ? ['Tell me about Mabalbay Falls','Where to eat in Victoria?','What can I do at Bato Creek?','When is Kapakyanan Festival?','Where is the Tourism Office?']
                : ['Ano ang Mabalbay Falls?','Saan puwedeng kumain sa Victoria?','Ano ang puwedeng gawin sa Bato Creek?','Kailan ang Kapakyanan Festival?','Saan ang Tourism Office ng Victoria?'])">
                <button @click="send(q)"
                        class="text-[11px] px-2.5 py-1.5 rounded-full bg-teal-50 text-teal-800 hover:bg-teal-100 font-semibold transition"
                        x-text="q"></button>
            </template>
        </div>

        <form method="post" data-no-spa @submit.prevent="send($refs.input.value); $refs.input.value=''" class="p-3 border-t border-slate-100 flex items-center gap-2 bg-white">
            <input x-ref="input"
                   :placeholder="lang === 'en' ? 'Type your question…' : 'I-type ang tanong…'"
                   class="flex-1 bg-slate-50 border border-slate-200 rounded-full px-4 py-3 text-sm outline-none focus:border-teal-500 focus:bg-white transition">
            <button :disabled="typing"
                    class="w-11 h-11 rounded-full from-teal-700 to-teal-500 bg-linear-to-br grid place-items-center text-white shrink-0 hover:shadow-glow transition disabled:opacity-50">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
window.chatbot = function chatbot() {
    return {
        open: false,
        lang: document.documentElement.dataset.lang || 'en',
        typing: false,
        messages: [],
        csrf: document.querySelector('meta[name=csrf-token]').content,

        init() {
            const saved = sessionStorage.getItem('mintour.chat');
            if (saved) {
                this.messages = JSON.parse(saved);
            } else {
                this.messages = [{
                    role: 'ai',
                    html: "Kumusta! 👋 I'm your MinTour guide. Ask me about verified Victoria destinations, activities, festivals, places to eat, and tourism contacts."
                }];
            }
        },

        persist() {
            sessionStorage.setItem('mintour.chat', JSON.stringify(this.messages));
        },

        async send(text) {
            text = (text || '').trim();
            if (!text || this.typing) return;

            this.messages.push({ role: 'user', html: this.escape(text) });
            this.persist();
            this.scrollDown();
            this.typing = true;

            try {
                const res = await fetch('/api/chat/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: JSON.stringify({ message: text, lang: this.lang }),
                });

                const data = await res.json();
                const error = data.error || (!res.ok ? (this.lang === 'fil'
                    ? 'Nagkaroon ng problema sa server. Pakisubukan muli.'
                    : 'The server could not process your message. Please try again.') : null);
                this.messages.push({
                    role: 'ai',
                    html: data.html || this.escape(data.reply || error || (this.lang === 'fil' ? 'May naganap na error.' : 'Something went wrong.')),
                    error: Boolean(error),
                });
            } catch {
                this.messages.push({
                    role: 'ai',
                    html: this.lang === 'fil'
                        ? 'Hindi makakonekta sa server. Pakisuri ang koneksyon at subukang muli.'
                        : "Couldn't connect to the server. Check your connection and try again.",
                    error: true,
                });
            } finally {
                this.typing = false;
                this.persist();
                this.scrollDown();
            }
        },

        escape(s) {
            return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        },

        scrollDown() {
            this.$nextTick(() => {
                const el = this.$refs.log;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    };
};
</script>
@endpush
