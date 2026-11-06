<x-layouts::guest>
    <x-auth.shell illustration="auth-confirm.svg">
        <h2 class="text-xl font-bold text-slate-900">Confirm your password</h2>
        <p class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
            You’re about to do something sensitive. Enter your password to continue.
        </p>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf
            <x-form.input label="Password" name="password" type="password" autocomplete="current-password" required
                autofocus />
            <x-ui.button type="submit" variant="primary" class="w-full">Confirm</x-ui.button>
        </form>
    </x-auth.shell>
</x-layouts::guest>
