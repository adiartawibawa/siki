@props(['closable' => false])

@php $brand = config('app.name'); @endphp

<aside class="flex h-full flex-col rounded-3xl bg-white p-4 ring-1 ring-slate-200/70">
    <div class="flex items-center justify-between px-2 pt-1">
        <a href="{{ url('/') }}"
            class="flex items-center gap-2.5 rounded-lg text-xl font-semibold text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            <span
                class="grid size-10 place-items-center rounded-xl bg-linear-to-br from-brand-500 to-brand-700 text-base font-bold text-white">
                {{ Str::upper(Str::substr($brand, 0, 1)) }}
            </span>
            {{ $brand }}
        </a>

        @if ($closable)
            <button type="button" @click="drawer = false" aria-label="Close menu"
                class="grid size-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <x-ui.icon name="x" size="sm" />
            </button>
        @endif
    </div>

    <nav aria-label="Sidebar" class="-mx-4 mt-8 flex-1 space-y-6 overflow-y-auto px-4">
        @foreach (config('admin.nav') as $group)
            @continue(collect($group['items'])->every(fn($i) => isset($i['can']) && !auth()->user()?->can($i['can'])))
            <div>
                <p class="px-3 text-sm text-slate-500">{{ $group['title'] }}</p>

                <ul class="mt-2 space-y-1">
                    @foreach ($group['items'] as $item)
                        <x-admin.nav-item :item="$item" />
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    @if (config('admin.promo.enabled'))
        <x-admin.promo class="mt-4" />
    @endif
</aside>
