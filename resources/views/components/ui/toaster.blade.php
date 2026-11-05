@php
    // Pesan yang diteruskan lewat session setelah redirect:
    //   session()->flash('toast', ['type' => 'success', 'message' => '…', 'title' => '…'])
    //   session()->flash('error', '…')
    //   session('status') dari Fortify, hanya bila berupa kalimat (bukan kode seperti
    //   "two-factor-authentication-enabled").
    $status = session('status');
    $statusText = is_string($status) ? __($status) : null;

    $flash = collect([
        session('toast'),
        $statusText && str_contains($statusText, ' ') ? ['type' => 'success', 'message' => $statusText] : null,
        session('error') ? ['type' => 'danger', 'message' => session('error')] : null,
    ])
        ->filter()
        ->flatMap(fn($t) => isset($t['message']) ? [$t] : $t)
        ->values()
        ->all();
@endphp

<div x-data="toaster(@js($flash))" x-on:toast.window="add($event.detail)" role="region" aria-label="Notifications"
    class="pointer-events-none fixed inset-x-3 bottom-3 z-[90] flex flex-col gap-2 sm:inset-x-auto sm:bottom-4 sm:right-4 sm:w-96">

    <template x-for="t in items" :key="t.id">
        <div x-data="{ shown: false }" x-init="$nextTick(() => shown = true)" x-show="shown && !t.leaving"
            x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
            x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            :role="t.type === 'danger' || t.type === 'warning' ? 'alert' : 'status'" @mouseenter="pause(t)"
            @mouseleave="resume(t)" @focusin="pause(t)" @focusout="resume(t)"
            class="pointer-events-auto flex items-start gap-3 rounded-2xl bg-white p-4 shadow-lg shadow-slate-900/10 ring-1 ring-slate-200">

            <span class="grid size-8 shrink-0 place-items-center rounded-full"
                :class="{
                    'bg-emerald-50 text-emerald-600': t.type === 'success',
                    'bg-red-50 text-red-600': t.type === 'danger',
                    'bg-amber-50 text-amber-600': t.type === 'warning',
                    'bg-brand-50 text-brand-600': t.type === 'info',
                }">
                <x-ui.icon name="circle-check" size="18" x-show="t.type === 'success'" />
                <x-ui.icon name="circle-exclamation-point" size="18" x-show="t.type === 'danger'" />
                <x-ui.icon name="triangle-exclamation-point" size="18" x-show="t.type === 'warning'" />
                <x-ui.icon name="info" size="18" x-show="t.type === 'info'" />
            </span>

            <div class="min-w-0 flex-1 pt-1">
                <p x-show="t.title" x-text="t.title" class="text-sm font-medium text-slate-900"></p>
                <p x-text="t.message" class="text-sm leading-relaxed"
                    :class="t.title ? 'mt-0.5 text-slate-600' : 'text-slate-800'"></p>
            </div>

            <button type="button" @click="dismiss(t)" aria-label="Dismiss notification"
                class="-mr-1 -mt-1 grid size-8 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <x-ui.icon name="x" size="sm" />
            </button>
        </div>
    </template>
</div>
