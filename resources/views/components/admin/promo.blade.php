@php $promo = config('admin.promo'); @endphp

<div x-data="{ open: true }" x-show="open"
    {{ $attributes->class('rounded-2xl bg-linear-to-tr from-brand-100 to-brand-500 p-4 ring-1 ring-slate-200/70') }}>
    <div class="flex items-start justify-between">
        <span class="grid size-10 place-items-center rounded-xl bg-linear-to-br from-orange-400 to-red-500 text-white">
            <x-ui.icon name="crown" />
        </span>
        <button type="button" @click="open = false" aria-label="Dismiss"
            class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            <x-ui.icon name="x" size="sm" />
        </button>
    </div>

    <p class="mt-4 font-semibold text-slate-900">{{ $promo['title'] }}</p>
    <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $promo['text'] }}</p>

    <x-ui.button variant="dark" :href="$promo['href']" class="mt-4 w-full">{{ $promo['action'] }}</x-ui.button>
</div>
