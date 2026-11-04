@props(['width' => 'w-52', 'align' => 'right', 'triggerClass' => ''])

<div x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu" class="{{ $triggerClass }}">
        {{ $trigger }}
    </button>

    <div x-show="open" x-cloak @click="open = false" role="menu" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="{{ $align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right' }} {{ $width }} absolute z-30 mt-2 rounded-2xl bg-white p-1.5 shadow-lg shadow-slate-900/5 ring-1 ring-slate-200">
        {{ $slot }}
    </div>
</div>
