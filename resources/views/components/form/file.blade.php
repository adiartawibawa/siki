@props([
    'label',
    'name', // nama properti Livewire yang menampung berkas, mis. "photo"
    'accept' => null, // "image/*", "image/png,image/jpeg", ".pdf"
    'maxMb' => 5, // batas di sisi klien; aturan 'max' di server yang menentukan
    'hint' => null,
    'optional' => false,
    'current' => null, // URL berkas yang sudah tersimpan (opsional)
    'currentName' => null,
])

@php
    $id = 'file-' . Str::slug($name);
    $disabled = $attributes->has('disabled');
    $hint ??= "Up to {$maxMb} MB.";
    $currentIsImage =
        $current && Str::contains(Str::lower($current), ['.png', '.jpg', '.jpeg', '.webp', '.gif', '.avif']);
@endphp

<div x-data="fileUpload({ model: @js($name), accept: @js($accept), maxBytes: {{ (int) ($maxMb * 1024 * 1024) }}, disabled: @js($disabled) })" {{ $attributes->only('class') }}>

    <label for="{{ $id }}" class="mb-1.5 block text-xs font-medium text-slate-600">
        {{ $label }}
        @if ($optional)
            <span class="font-normal text-slate-400">(optional)</span>
        @endif
    </label>

    {{-- Input asli tetap ada (tersembunyi secara visual) agar bisa difokus dan dibaca pembaca layar --}}
    <input id="{{ $id }}" x-ref="input" type="file" class="sr-only"
        @if ($accept) accept="{{ $accept }}" @endif @disabled($disabled)
        aria-describedby="{{ $id }}-hint {{ $id }}-msg"
        @change="pick($event.target.files); $event.target.value = ''">

    {{-- Berkas tersimpan yang sudah ada, tampil selama belum ada pilihan baru --}}
    @if ($current)
        <div x-show="!item" class="mb-3 flex items-center gap-3 rounded-2xl border border-slate-200 p-3">
            @if ($currentIsImage)
                <img src="{{ $current }}" alt="" class="size-12 shrink-0 rounded-xl object-cover">
            @else
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                    <x-ui.icon name="file" />
                </span>
            @endif
            <div class="min-w-0">
                <p class="text-xs text-slate-500">Current file</p>
                <p class="truncate text-sm font-medium text-slate-900">
                    {{ $currentName ?? basename(parse_url($current, PHP_URL_PATH) ?: $current) }}</p>
            </div>
        </div>
    @endif

    {{-- Area jatuhkan berkas --}}
    <label for="{{ $id }}" x-show="!item" @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; pick($event.dataTransfer.files)"
        :class="dragging ? 'border-brand-500 bg-brand-50' : 'border-slate-300 bg-white hover:bg-slate-50'"
        class="{{ $disabled ? 'pointer-events-none opacity-60' : 'cursor-pointer' }} flex flex-col items-center gap-2 rounded-2xl border border-dashed px-6 py-8 text-center transition focus-within:ring-2 focus-within:ring-brand-500 focus-within:ring-offset-2 motion-reduce:transition-none">
        <x-ui.icon name="upload" color="text-slate-400" />
        <span class="text-sm text-slate-700">
            <span class="font-medium text-brand-700">Choose a file</span> or drag it here
        </span>
        <span id="{{ $id }}-hint" class="text-xs text-slate-500">{{ $hint }}</span>
    </label>

    {{-- Berkas yang dipilih --}}
    <div x-show="item" x-cloak class="rounded-2xl border border-slate-200 p-3">
        <div class="flex items-center gap-3">
            <img x-show="item?.preview" :src="item?.preview" alt=""
                class="size-12 shrink-0 rounded-xl object-cover">
            <span x-show="item && !item.preview"
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                <x-ui.icon name="file" />
            </span>

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-slate-900" x-text="item?.name"></p>
                <div class="mt-0.5 flex flex-wrap items-center gap-x-3 text-xs text-slate-500">
                    <span x-text="item?.size"></span>
                    <span x-show="item?.status === 'uploading'"
                        x-text="'Uploading ' + Math.round(item?.progress ?? 0) + '%'"></span>
                    <span x-show="item?.status === 'done'" class="inline-flex items-center gap-1 text-emerald-700">
                        <x-ui.icon name="circle-check" size="xs" /> Ready
                    </span>
                    <span x-show="item?.status === 'error'" class="inline-flex items-center gap-1 text-red-600">
                        <x-ui.icon name="circle-exclamation-point" size="xs" /> <span
                            x-text="item?.message"></span>
                    </span>
                </div>
            </div>

            <button type="button" @click="$refs.input.click()" x-show="item?.status !== 'uploading'"
                class="rounded-lg px-2 py-1 text-xs font-medium text-brand-700 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                Replace
            </button>
            <button type="button" @click="remove()" aria-label="Remove file"
                class="grid size-8 shrink-0 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <x-ui.icon name="x" size="sm" />
            </button>
        </div>

        <div x-show="item?.status === 'uploading'" role="progressbar" aria-label="Upload progress" aria-valuemin="0"
            aria-valuemax="100" :aria-valuenow="Math.round(item?.progress ?? 0)"
            class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
            <div class="h-full rounded-full bg-brand-600 transition-[width] duration-150 motion-reduce:transition-none"
                :style="`width: ${item?.progress ?? 0}%`"></div>
        </div>
    </div>

    <p id="{{ $id }}-msg" x-show="notice" x-text="notice" role="alert" class="mt-2 text-xs text-red-600">
    </p>

    @error($name)
        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
    @enderror

    <p class="sr-only" aria-live="polite" x-text="item?.status === 'done' ? item.name + ' is ready' : ''"></p>
</div>
