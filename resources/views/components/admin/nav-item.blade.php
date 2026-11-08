@props(['item', 'child' => false])

@php
    $hasChildren = !$child && $item['children'] !== [];
    $active = $item['active'];

    $ring = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500';
    $idle = 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
    $on = 'font-medium text-slate-900';
    $panelId = 'sub-' . Str::slug($item['label']) . '-' . Str::random(4);
@endphp

@if ($hasChildren)
    <li class="relative" x-data="{ open: {{ $active ? 'true' : 'false' }} }">
        @if ($active)
            <span aria-hidden="true"
                class="absolute -left-4 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-brand-600"></span>
        @endif

        <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="{{ $panelId }}"
            :class="{ 'bg-slate-100': {{ $active ? 'true' : 'false' }} && !open }"
            class="{{ $active ? $on : $idle }} flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition {{ $ring }}">
            @if ($item['icon'])
                <x-ui.icon :name="$item['icon']" size="18" />
            @endif
            <span class="flex-1">{{ $item['label'] }}</span>
            <span class="transition-transform duration-200 motion-reduce:transition-none" :class="open && 'rotate-180'">
                <x-ui.icon name="chevron-down" size="sm" color="text-slate-400" />
            </span>
        </button>

        <div id="{{ $panelId }}" :inert="!open"
            class="grid transition-[grid-template-rows] duration-200 motion-reduce:transition-none"
            :class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
            <div class="min-h-0 overflow-hidden">
                <ul class="ml-[21px] mt-1 space-y-0.5 border-l border-slate-200 pl-3">
                    @foreach ($item['children'] as $sub)
                        <x-admin.nav-item :item="$sub" child />
                    @endforeach
                </ul>
            </div>
        </div>
    </li>
@else
    <li class="relative">
        @if ($active)
            <span aria-hidden="true"
                class="{{ $child ? 'size-1.5 rounded-full' : 'h-6 w-1 rounded-r-full' }} absolute -left-4 top-1/2 -translate-y-1/2 bg-brand-600"></span>
        @endif

        <a href="{{ $item['href'] }}" wire:navigate @if ($active) aria-current="page" @endif
            class="{{ $active ? ($child ? $on : $on . ' bg-slate-100') : $idle }} {{ $child ? 'py-2' : 'py-2.5' }} flex items-center gap-3 rounded-xl px-3 text-sm transition {{ $ring }}">
            @if (!$child && $item['icon'])
                <x-ui.icon :name="$item['icon']" size="18" />
            @endif
            <span class="flex-1">{{ $item['label'] }}</span>
            @isset($item['badge'])
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">{{ $item['badge'] }}</span>
            @endisset
        </a>
    </li>
@endif
