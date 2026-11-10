<div x-data="globalSearch('{{ route('search') }}')" @click.outside="open = false" class="relative">
    <x-ui.search placeholder="Search anything…" shortcut maxlength="100" autocomplete="off" x-model="q"
        @input.debounce.250ms="open = true; search()" @focus="open = true" @keydown.down.prevent="open = true; move(1)"
        @keydown.up.prevent="move(-1)" @keydown.enter.prevent="choose()" @keydown.escape="open = false" role="combobox"
        aria-autocomplete="list" aria-haspopup="listbox" aria-controls="global-search-panel"
        x-bind:aria-expanded="open" x-bind:aria-activedescendant="active >= 0 ? 'gs-' + active : null" />

    <div id="global-search-panel" x-show="open" x-cloak role="listbox" aria-label="Search results"
        class="absolute inset-x-0 top-full z-40 mt-2 max-h-[min(28rem,70vh)] overflow-y-auto rounded-2xl bg-white p-2 shadow-lg shadow-slate-900/5 ring-1 ring-slate-200">

        <p x-show="!ready" class="px-3 py-4 text-sm text-slate-500">
            Type at least 2 characters to search.
        </p>
        <p x-show="ready && loading && !groups.length" class="px-3 py-4 text-sm text-slate-500">Searching…</p>
        <p x-show="ready && !loading && failed" role="alert" class="px-3 py-4 text-sm text-red-600">
            Search is unavailable. Try again.
        </p>
        <p x-show="ready && !loading && !failed && !groups.length" class="px-3 py-4 text-sm text-slate-500">
            No results for “<span x-text="term"></span>”.
        </p>

        {{-- `group` hanya ada di dalam template ini; semua yang memakainya harus berada sebelum </template> di bawah --}}
        <template x-for="group in groups" :key="group.title">
            <div class="py-1">
                <p class="px-3 pb-1 pt-2 text-xs font-medium text-slate-500" x-text="group.title"></p>

                <template x-for="item in group.items" :key="item.url + item.title">
                    <a :id="'gs-' + item.n" :href="item.url" role="option" :aria-selected="active === item.n"
                        @click="click($event, item)" @mousemove="active = item.n"
                        :class="active === item.n ? 'bg-slate-100' : ''" class="block rounded-xl px-3 py-2 text-sm">
                        <span class="block truncate font-medium text-slate-900" x-text="item.title"></span>
                        <span x-show="item.details.length" class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-slate-500">
                            <template x-for="d in item.details" :key="d.label">
                                <span><span x-text="d.label + ':'"></span> <span x-text="d.value"></span></span>
                            </template>
                        </span>
                    </a>
                </template>
            </div>
        </template>

        <p class="sr-only" aria-live="polite" x-text="ready && !loading ? flat.length + ' results' : ''"></p>
    </div>
</div>
