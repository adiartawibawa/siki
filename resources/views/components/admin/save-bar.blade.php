@props(['saved' => 'settings-saved'])

<div class="flex items-center justify-end gap-3 bg-slate-50/70 px-6 py-4 sm:px-8">
    <span x-data="{ shown: false }" x-on:{{ $saved }}.window="shown = true; setTimeout(() => shown = false, 2500)"
        x-text="shown ? 'Saved.' : ''" role="status" aria-live="polite" class="text-sm text-slate-500"></span>
    <x-ui.button type="submit" variant="dark" wire:loading.attr="disabled">{{ $slot }}</x-ui.button>
</div>
