@props(['name', 'title', 'description' => null])

{{-- wire:ignore.self: agar Livewire tidak menutup dialog saat re-render --}}
<dialog x-data x-ref="dialog" wire:ignore.self @modal-open-{{ $name }}.window="$refs.dialog.showModal()"
    @modal-close-{{ $name }}.window="$refs.dialog.close()" @click="if ($event.target === $el) $el.close()"
    aria-labelledby="modal-{{ $name }}-title"
    {{ $attributes->class('m-auto w-[calc(100%-2rem)] max-w-md rounded-3xl bg-white p-0 shadow-xl backdrop:bg-slate-900/40') }}>
    <div class="p-6">
        <h2 id="modal-{{ $name }}-title" class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $description }}</p>
        @endif
        <div class="mt-5">{{ $slot }}</div>
    </div>
</dialog>
