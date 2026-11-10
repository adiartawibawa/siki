@php
    $user = auth()->user();
    $tabs = [
        ['Profile', 'profile', 'user'],
        ['Account', 'account.details', 'sliders-horizontal'],
        ['Security', 'account.security', 'shield-check'],
        // Tampil bagi yang berhak, atau yang masih punya token untuk dibersihkan.
        $user && ($user->can('api.access') || $user->tokens()->exists()) ? ['API', 'account.api', 'book-key'] : null,
    ];
    // Buang tab yang tidak ditampilkan (null) agar tidak ikut di-loop dan memanggil route(null)
    $tabs = array_filter($tabs);
@endphp

<nav aria-label="Account" class="flex gap-1 overflow-x-auto rounded-2xl bg-white p-1.5 ring-1 ring-slate-200/70">
    @foreach ($tabs as [$label, $route, $icon])
        @php $active = request()->routeIs($route); @endphp
        <a href="{{ route($route) }}" wire:navigate @if ($active) aria-current="page" @endif
            class="{{ $active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            <x-ui.icon :name="$icon" size="sm" />
            {{ $label }}
        </a>
    @endforeach
</nav>
