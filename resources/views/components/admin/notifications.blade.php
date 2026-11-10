@php
    $unread = auth()->user()?->unreadNotifications()->count() ?? 0;
@endphp

<div x-data="notifications({
    listUrl: '{{ route('notifications.index') }}',
    readAllUrl: '{{ route('notifications.read-all') }}',
    csrf: '{{ csrf_token() }}',
    unread: {{ $unread }},
})" @click.outside="open = false" @keydown.escape="open = false" class="relative">

    <x-ui.button variant="outline" size="icon" pill class="relative" @click="toggle()" aria-haspopup="dialog"
        aria-controls="notifications-panel" x-bind:aria-expanded="open"
        x-bind:aria-label="unread ? 'Notifications, ' + unread + ' unread' : 'Notifications'">
        <x-ui.icon name="bell" size="sm" />
        <span x-show="unread" x-cloak x-text="unread > 9 ? '9+' : unread"
            class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-semibold leading-5 text-white ring-2 ring-white"></span>
    </x-ui.button>

    <div id="notifications-panel" x-show="open" x-cloak
        class="absolute right-0 top-full z-40 mt-2 w-[min(22rem,calc(100vw-2rem))] rounded-2xl bg-white shadow-lg shadow-slate-900/5 ring-1 ring-slate-200">

        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-900">Notifications</h2>
            <button type="button" x-show="unread" @click="readAll()"
                class="rounded-sm text-xs font-medium text-brand-700 hover:text-brand-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                Mark all as read
            </button>
        </div>

        <div class="max-h-96 overflow-y-auto border-t border-slate-100">
            <p x-show="loading && !loaded" class="px-4 py-6 text-sm text-slate-500">Loading…</p>
            <p x-show="failed" role="alert" class="px-4 py-6 text-sm text-red-600">
                Couldn’t reach the server. Try again.
            </p>
            <p x-show="loaded && !failed && !items.length" class="px-4 py-6 text-sm text-slate-500">
                You’re all caught up.
            </p>

            <ul class="divide-y divide-slate-100">
                <template x-for="item in items" :key="item.id">
                    <li>
                        <a :href="item.href"
                            class="flex items-start gap-3 px-4 py-3 text-sm hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500">
                            <span class="mt-1.5 size-2 shrink-0 rounded-full" aria-hidden="true"
                                :class="item.read ? 'bg-transparent' : 'bg-brand-600'"></span>
                            <span class="min-w-0">
                                <span class="block text-slate-900" :class="item.read ? '' : 'font-medium'"
                                    x-text="item.message"></span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-text="item.ago"></span>
                                <span x-show="!item.read" class="sr-only">Unread</span>
                            </span>
                        </a>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
