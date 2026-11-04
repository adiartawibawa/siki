@props(['label', 'name', 'optional' => false])

@php
    $id = $attributes->get('id', $name);
    $isPassword = $attributes->get('type') === 'password';
@endphp

<div @if ($isPassword) x-data="{ show: false }" @endif>
    <label for="{{ $id }}" class="mb-1.5 block text-xs font-medium text-slate-600">
        {{ $label }}
        @if ($optional)
            <span class="font-normal text-slate-400">(optional)</span>
        @endif
    </label>

    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}"
            type="{{ $isPassword ? 'password' : $attributes->get('type', 'text') }}"
            @if ($isPassword) :type="show ? 'text' : 'password'" @endif
            @error($name) aria-invalid="true" @enderror
            {{ $attributes->except(['id', 'type'])->class([
                    'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:outline-none focus:ring-2',
                    'border-slate-200 focus:border-violet-500 focus:ring-violet-200' => !$errors->has($name),
                    'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has($name),
                    'pr-10' => $isPassword,
                ]) }}>

        @if ($isPassword)
            <button type="button" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'"
                class="absolute inset-y-0 right-0 grid w-10 place-items-center rounded-r-lg text-slate-400 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-300">
                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <svg x-show="show" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="size-4"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 6.1A9.8 9.8 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-3.2 3.9M6.6 6.6A16.6 16.6 0 0 0 2 12s3.5 7 10 7c1.7 0 3.2-.4 4.5-1M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                </svg>
            </button>
        @endif
    </div>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
