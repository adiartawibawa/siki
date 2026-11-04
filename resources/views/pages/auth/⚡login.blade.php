<x-layouts::guest>
    <x-auth.shell illustration="auth-login.svg">
        <h2 class="mb-6 text-xl font-bold text-slate-900">Welcome back</h2>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            <x-form.input label="Email address" name="email" type="email" wire:model="email" autocomplete="email"
                placeholder="name@email.com" required autofocus />

            <x-form.input label="Password" name="password" type="password" wire:model="password"
                autocomplete="current-password" placeholder="Enter your password" required />

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600">
                    <input type="checkbox" wire:model="remember"
                        class="size-3.5 rounded border-slate-300 text-violet-700 focus:ring-violet-300">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}"
                    class="font-medium text-orange-600 hover:text-orange-700">Forgot password?</a>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 disabled:opacity-60">
                Log in
            </button>
        </form>

        @if (app(\App\Settings\GeneralSettings::class)->registration_enabled)
            <p class="mt-5 text-center text-sm text-slate-600">
                New user?
                <a href="{{ route('register') }}" class="font-medium text-orange-600 hover:text-orange-700">Sign up</a>
            </p>

            <x-auth.google>Continue with Google</x-auth.google>
        @endif
    </x-auth.shell>
</x-layouts::guest>
