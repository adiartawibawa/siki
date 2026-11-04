<x-layouts::guest>
    @php
        $sent = session('status') === 'passwords.sent';
        $email = session('password_reset_email', '');
    @endphp

    <x-auth.shell illustration="auth-forgot.svg" :back="route('login')">
        <x-slot:badge>
            @if (!$sent)
                <x-ui.icon name="key" size="md" color="text-violet-700" />
            @else
                <x-ui.icon name="mail-check" size="md" color="text-violet-700" />
            @endif
        </x-slot:badge>

        @if (!$sent)
            <h2 class="text-xl font-bold text-slate-900">
                Forgot your password?
            </h2>

            <p class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
                Enter the email you signed up with and we’ll send you a link to set a new password.
            </p>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <x-form.input label="Email address" name="email" type="email" :value="old('email')" autocomplete="email"
                    placeholder="name@email.com" required autofocus />

                @error('email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit"
                    class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2">
                    Send reset link
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">
                Remembered it?
                <a href="{{ route('login') }}" class="font-medium text-orange-600 hover:text-orange-700">
                    Log in
                </a>
            </p>
        @else
            <h2 class="text-xl font-bold text-slate-900">
                Check your email
            </h2>

            <p class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
                If an account exists for
                <strong class="font-semibold text-slate-900">{{ $email }}</strong>,
                a reset link is on its way. The link expires in
                {{ config('auth.passwords.users.expire', 60) }} minutes.
            </p>

            <a href="{{ route('login') }}"
                class="block w-full rounded-lg bg-violet-700 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-violet-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2">
                Back to log in
            </a>

            <p class="mt-5 text-center text-xs text-slate-500">
                Didn’t receive an email? Check your spam folder or
            </p>

            <form method="POST" action="{{ route('password.email') }}" class="mt-1 text-center"
                x-data="{
                    cooldown: 30,
                    timer: null,

                    init() {
                        this.timer = setInterval(() => {
                            if (this.cooldown > 0) this.cooldown--;
                            else clearInterval(this.timer);
                        }, 1000);
                    },

                    destroy() {
                        clearInterval(this.timer);
                    }
                }">
                @csrf

                <input type="hidden" name="email" value="{{ $email }}">

                <button type="submit" :disabled="cooldown > 0" @click="cooldown = 30"
                    x-text="cooldown > 0 ? 'resend in ' + cooldown + 's' : 'resend'"
                    class="font-medium text-orange-600 hover:text-orange-700 disabled:cursor-not-allowed disabled:text-slate-400">
                    resend
                </button>
            </form>
        @endif
    </x-auth.shell>
</x-layouts::guest>
