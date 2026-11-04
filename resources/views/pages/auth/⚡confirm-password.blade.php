<x-layouts::guest>
    <x-auth.shell illustration="auth-confirm.svg">
        <x-slot:badge>
            <x-ui.icon name="shield-check" size="md" color="text-violet-700" />
        </x-slot:badge>

        <h2 class="text-xl font-bold text-slate-900">
            Confirm your password
        </h2>

        <p class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
            You’re about to do something sensitive. Enter the password for
            <strong class="font-semibold text-slate-900">
                {{ auth()->user()->email }}
            </strong>
            to continue.
        </p>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <x-form.input label="Password" name="password" type="password" autocomplete="current-password"
                placeholder="Enter your password" required autofocus />

            @error('password')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit"
                class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2">
                Confirm
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            <a href="{{ route('password.request') }}" class="font-medium text-orange-600 hover:text-orange-700">
                Forgot your password?
            </a>
        </p>
    </x-auth.shell>
</x-layouts::guest>
