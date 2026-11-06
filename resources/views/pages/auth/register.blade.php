<x-layouts::guest>
    <x-auth.shell illustration="auth-register.svg">
        <h2 class="mb-6 text-xl font-bold text-slate-900">Let’s get you onboard</h2>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <x-form.input label="First name" name="first_name" :value="old('first_name')" autocomplete="given-name" required
                    autofocus />
                <x-form.input label="Last name" name="last_name" :value="old('last_name')" autocomplete="family-name"
                    :optional="true" />
            </div>
            <x-form.input label="Username" name="username" :value="old('username')" autocomplete="username" required />
            <x-form.input label="Email address" name="email" type="email" :value="old('email')" autocomplete="email"
                required />
            <x-form.input label="Password" name="password" type="password" autocomplete="new-password" required />

            <div>
                <label class="flex items-center gap-2 text-xs text-slate-600">
                    <input type="checkbox" name="terms" value="1" required
                        class="size-3.5 rounded accent-brand-600">
                    <span>I agree to the <a href="#"
                            class="underline underline-offset-2 hover:text-slate-900">terms and conditions</a></span>
                </label>
                @error('terms')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" variant="primary" class="w-full">Sign up</x-ui.button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-orange-600 hover:text-orange-700">Log in</a>
        </p>
    </x-auth.shell>
</x-layouts::guest>
