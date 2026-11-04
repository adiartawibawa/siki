<x-layouts::guest>
    <x-auth.shell illustration="auth-register.svg" :step="1" :steps="4">
        <h2 class="mb-6 text-xl font-bold text-slate-900">Let’s get you onboard</h2>

        <form wire:submit="register" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <x-form.input label="First name" name="first_name" wire:model="first_name" autocomplete="given-name"
                    placeholder="Arowoka" required />
                <x-form.input label="Last name" name="last_name" wire:model="last_name" autocomplete="family-name"
                    placeholder="John" required />
            </div>

            <x-form.input label="Email address" name="email" type="email" wire:model="email" autocomplete="email"
                placeholder="name@email.com" required />

            <x-form.input label="Password" name="password" type="password" wire:model="password"
                autocomplete="new-password" placeholder="Create a password" required />

            <x-form.input label="Referral ID" name="referral_id" wire:model="referral_id" placeholder="John123ref"
                :optional="true" />

            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" wire:model="terms" required
                    class="size-3.5 rounded border-slate-300 text-violet-700 focus:ring-violet-300">
                <span>I agree to the <a href="#" class="underline underline-offset-2 hover:text-slate-900">terms
                        and conditions</a></span>
            </label>

            <button type="submit" wire:loading.attr="disabled"
                class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 disabled:opacity-60">
                Sign up
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-orange-600 hover:text-orange-700">Log in</a>
        </p>

        <x-auth.google>Sign up with Google</x-auth.google>
    </x-auth.shell>
</x-layouts::guest>
