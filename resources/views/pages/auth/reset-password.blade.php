<x-layouts::guest>
    <x-auth.shell illustration="auth-reset.svg" illustrationWidth="w-[50%]">
        <h2 class="mb-6 text-xl font-bold text-slate-900">Set a new password</h2>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <input type="hidden" name="email" value="{{ $request->email }}">

            {{-- Token kedaluwarsa atau tidak valid dilaporkan Laravel pada kunci "email" --}}
            @error('email')
                <x-ui.alert tone="danger">
                    {{ $message }}
                    <a href="{{ route('password.request') }}" class="font-semibold underline underline-offset-2">Request a
                        new link</a>
                </x-ui.alert>
            @enderror

            <x-form.input label="New password" name="password" type="password" autocomplete="new-password" required
                autofocus />
            <x-form.input label="Confirm new password" name="password_confirmation" type="password"
                autocomplete="new-password" required />

            <x-ui.button type="submit" variant="primary" class="w-full">Save new password</x-ui.button>
        </form>
    </x-auth.shell>
</x-layouts::guest>
