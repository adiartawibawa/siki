@php
    $sent = session('status') !== null;
    session()->forget('status'); // ditampilkan di halaman ini; cegah toast ganda
@endphp

<x-layouts::guest>
    <x-auth.shell illustration="auth-forgot.svg" :back="route('login')">
        <h2 class="text-xl font-bold text-slate-900">Forgot your password?</h2>
        <p class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
            Enter the email you signed up with and we’ll send you a link to set a new password.
        </p>

        @if ($sent)
            <x-ui.alert tone="success" class="mb-4">
                If an account exists for that address, a reset link is on its way.
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <x-form.input label="Email address" name="email" type="email" :value="old('email')" autocomplete="email"
                required autofocus />
            <x-ui.button type="submit" variant="primary" class="w-full">Send reset link</x-ui.button>
        </form>
    </x-auth.shell>
</x-layouts::guest>
