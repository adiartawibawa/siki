<x-layouts::guest>
    <x-auth.shell illustration="auth-verify.svg" illustrationWidth="w-[80%]">
        <x-slot:badge>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-brand-700" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6" />
            </svg>
        </x-slot:badge>

        <h2 class="text-xl font-bold text-slate-900">Verify your email address</h2>
        <p class="mb-6 mt-2 text-xs leading-relaxed text-slate-500">
            We sent a link to <strong class="font-semibold text-slate-900">{{ auth()->user()->email }}</strong>.
            Open it to continue.
        </p>

        @if (session('status') === 'verification-link-sent')
            <x-ui.alert tone="success" class="mb-4">A new link is on its way.</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit" variant="primary" class="w-full">Resend link</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
            @csrf
            <button type="submit"
                class="text-sm font-medium text-slate-600 underline underline-offset-2 hover:text-slate-900">Log
                out</button>
        </form>
    </x-auth.shell>
</x-layouts::guest>
