<x-layouts::guest>
    <x-auth.shell illustration="auth-login.svg" illustrationWidth="w-[80%]">
        <h2 class="mb-6 text-xl font-bold text-slate-900">Welcome back</h2>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <x-form.input label="Email or username" name="email" :value="old('email')" autocomplete="username" required
                autofocus />
            <x-form.input label="Password" name="password" type="password" autocomplete="current-password" required />

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="size-3.5 rounded accent-brand-600">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}"
                    class="font-medium text-orange-600 hover:text-orange-700">Forgot password?</a>
            </div>

            <x-ui.button type="submit" variant="primary" class="w-full">Log in</x-ui.button>
        </form>

        @if (Route::has('register') && app(\App\Settings\GeneralSettings::class)->registration_enabled)
            <p class="mt-5 text-center text-sm text-slate-600">
                New user?
                <a href="{{ route('register') }}" class="font-medium text-orange-600 hover:text-orange-700">Sign up</a>
            </p>
        @endif
    </x-auth.shell>
</x-layouts::guest>
