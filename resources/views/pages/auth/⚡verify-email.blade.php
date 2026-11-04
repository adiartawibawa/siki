<x-layouts::guest>
    <x-auth.shell illustration="auth-verify.svg" :back="route('register')">
        <x-slot:badge>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-violet-700" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6" />
            </svg>
        </x-slot:badge>

        <h2 class="text-xl font-bold text-slate-900">Verify your email address</h2>
        <p class="mb-6 mt-2 text-xs text-slate-500">
            A 6-digit code has been sent to
            <strong class="font-semibold text-slate-900">{{ $maskedEmail }}</strong>
        </p>

        <form wire:submit="verify" class="space-y-4" x-data="{
            digits: ['', '', '', '', '', ''],
            cooldown: 30,
            get code() { return this.digits.join('') },
            init() { setInterval(() => { if (this.cooldown > 0) this.cooldown-- }, 1000) },
            focus(i) { const el = this.$refs.row.children[i];
                el.focus();
                el.select() },
            type(i, e) {
                const v = e.target.value.replace(/\D/g, '').slice(-1);
                e.target.value = v;
                this.digits[i] = v;
                this.$wire.code = this.code;
                if (v && i < 5) this.focus(i + 1);
            },
            back(i) { if (!this.digits[i] && i > 0) this.focus(i - 1) },
            paste(e) {
                const t = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                if (!t) return;
                e.preventDefault();
                [...t].forEach((c, i) => { this.digits[i] = c;
                    this.$refs.row.children[i].value = c });
                this.$wire.code = this.code;
                this.focus(Math.min(t.length, 5));
            }
        }">

            <div x-ref="row" class="flex gap-2" @paste="paste($event)">
                @foreach (range(0, 5) as $i)
                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                        aria-label="Digit {{ $i + 1 }} of 6" @if ($i === 0) autofocus @endif
                        @input="type({{ $i }}, $event)" @keydown.backspace="back({{ $i }})"
                        class="aspect-square min-w-0 flex-1 rounded-lg border border-transparent bg-slate-100 text-center text-lg font-semibold text-slate-900 transition focus:border-violet-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-200">
                @endforeach
            </div>

            @error('code')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit" :disabled="code.length < 6" wire:loading.attr="disabled"
                class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                Verify
            </button>

            <p class="text-center text-xs text-slate-500">
                Didn’t receive an email?
                <button type="button" :disabled="cooldown > 0" @click="$wire.resend(); cooldown = 60"
                    x-text="cooldown > 0 ? 'Resend in ' + cooldown + 's' : 'Resend'"
                    class="font-medium text-orange-600 hover:text-orange-700 disabled:cursor-not-allowed disabled:text-slate-400">Resend</button>
            </p>
        </form>
    </x-auth.shell>
</x-layouts::guest>
