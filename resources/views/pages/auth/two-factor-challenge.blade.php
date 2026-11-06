<x-layouts::guest>
    <x-auth.shell illustration="auth-verify.svg">
        <x-slot:badge>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-brand-700" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3 5 6v5c0 4.4 2.9 8.3 7 10 4.1-1.7 7-5.6 7-10V6l-7-3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.5 12 2 2 3.5-4" />
            </svg>
        </x-slot:badge>

        <div x-data="{
            recovery: {{ $errors->has('recovery_code') ? 'true' : 'false' }},
            digits: ['', '', '', '', '', ''],
            get code() { return this.digits.join('') },
            focus(i) { const el = this.$refs.row.children[i];
                el.focus();
                el.select() },
            type(i, e) {
                const v = e.target.value.replace(/\D/g, '').slice(-1);
                e.target.value = v;
                this.digits[i] = v;
                if (v && i < 5) this.focus(i + 1);
                if (this.code.length === 6) this.$nextTick(() => this.$refs.form.requestSubmit());
            },
            back(i) { if (!this.digits[i] && i > 0) this.focus(i - 1) },
            paste(e) {
                const t = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                if (!t) return;
                e.preventDefault();
                [...t].forEach((c, i) => { this.digits[i] = c;
                    this.$refs.row.children[i].value = c });
                if (t.length === 6) this.$nextTick(() => this.$refs.form.requestSubmit());
                else this.focus(t.length);
            },
        }">
            <h2 class="text-xl font-bold text-slate-900">Two-factor authentication</h2>
            <p x-show="!recovery" class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
                Enter the 6-digit code from your authenticator app.
            </p>
            <p x-show="recovery" x-cloak class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
                Enter one of your recovery codes. Each one works only once.
            </p>

            <form x-ref="form" method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
                @csrf

                <div x-show="!recovery">
                    <input type="hidden" name="code" :value="code">
                    <div x-ref="row" class="flex gap-2" @paste="paste($event)">
                        @foreach (range(0, 5) as $i)
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                aria-label="Digit {{ $i + 1 }} of 6"
                                @if ($i === 0) autofocus @endif
                                @input="type({{ $i }}, $event)"
                                @keydown.backspace="back({{ $i }})"
                                class="aspect-square min-w-0 flex-1 rounded-lg border border-transparent bg-slate-100 text-center text-lg font-semibold text-slate-900 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-200">
                        @endforeach
                    </div>
                    @error('code')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="recovery" x-cloak>
                    <x-form.input label="Recovery code" name="recovery_code" autocomplete="one-time-code" />
                </div>

                <x-ui.button type="submit" variant="primary" class="w-full">Verify</x-ui.button>
            </form>

            <div class="mt-5 flex items-center justify-between text-xs">
                <button type="button" @click="recovery = !recovery"
                    class="font-medium text-orange-600 hover:text-orange-700"
                    x-text="recovery ? 'Use an authenticator code' : 'Use a recovery code'"></button>
                <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-800">Back to log in</a>
            </div>
        </div>
    </x-auth.shell>
</x-layouts::guest>
