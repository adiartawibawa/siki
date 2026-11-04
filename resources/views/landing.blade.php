@php
    $ring = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2';

    $steps = [
        ['Clone and install', "git clone <repo> my-app\ncd my-app\ncomposer install\nnpm install"],
        ['Set up the environment', "cp .env.example .env\nphp artisan key:generate\nphp artisan migrate"],
        ['Make it yours', 'Rename the app, swap the logo and illustrations, then edit the copy on each auth page.'],
        ['Build the part that’s different', 'Add your own routes and modules. The auth flow is already there.'],
    ];

    $tree = <<<'TREE'
    resources/views
    ├─ layouts
    │  ├─ base.blade.php
    │  └─ guest.blade.php
    ├─ components
    │  ├─ auth
    │  │  ├─ shell.blade.php
    │  │  └─ google.blade.php
    │  └─ form
    │     └─ input.blade.php
    └─ pages/auth
       ├─ register
       ├─ login
       ├─ verify-email
       ├─ forgot-password
       ├─ reset-password
       └─ confirm-password
    TREE;
@endphp

<x-layouts::guest>
    <a href="#main"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:shadow">
        Skip to content
    </a>

    <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
        <a href="{{ route('home') }}"
            class="flex items-center gap-2 rounded-md text-lg font-semibold text-slate-800 {{ $ring }}">
            <span class="grid size-6 place-items-center rounded-md bg-violet-700 text-xs text-white">
                {{ Str::substr(config('app.name'), 0, 1) }}
            </span>
            {{ config('app.name') }}
        </a>

        <nav aria-label="Main" class="flex items-center gap-2 sm:gap-6">
            <a href="#included" class="hidden text-sm text-slate-600 hover:text-slate-900 sm:inline">What’s included</a>
            <a href="#start" class="hidden text-sm text-slate-600 hover:text-slate-900 sm:inline">Get started</a>
            <a href="{{ route('login') }}"
                class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 {{ $ring }}">Log
                in</a>
            @if (app(\App\Settings\GeneralSettings::class)->registration_enabled)
                <a href="{{ route('register') }}"
                    class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-violet-800 {{ $ring }}">Sign
                    up</a>
            @endif
        </nav>
    </header>

    <main id="main">
        {{-- Hero: struktur file yang nyata --}}
        <section
            class="mx-auto grid max-w-6xl items-center gap-10 px-6 pb-16 pt-8 lg:grid-cols-[minmax(0,6fr)_minmax(0,5fr)] lg:gap-14 lg:pb-24 lg:pt-14">
            <div>
                <h1
                    class="max-w-[18ch] text-4xl font-bold leading-[1.05] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    {{ config('starter.tagline') }}
                </h1>
                <p class="mt-6 max-w-[52ch] text-base leading-relaxed text-slate-600 sm:text-lg">
                    Auth pages, layouts and form components, already wired together.
                    Clone it, rename it, and spend your time on what makes the app different.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @if (app(\App\Settings\GeneralSettings::class)->registration_enabled)
                        <a href="{{ route('register') }}"
                            class="rounded-lg bg-violet-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-violet-800 {{ $ring }}">
                            Try the sign up flow
                        </a>
                    @endif
                    <a href="{{ route('login') }}"
                        class="rounded-lg bg-violet-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-violet-800 {{ $ring }}">
                        Log in
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto rounded-[2rem] bg-[#E9E5F7] p-6 sm:p-8">
                <pre class="min-w-max rounded-2xl bg-white p-5 font-mono text-xs leading-relaxed text-slate-700 shadow-sm sm:text-[13px]"
                    aria-label="Project file structure"><code>{{ $tree }}</code></pre>
            </div>
        </section>

        {{-- Isi: daftar bergaris, status dari config --}}
        <section id="included" class="bg-slate-50 py-16 lg:py-24">
            <div class="mx-auto grid max-w-6xl gap-10 px-6 lg:grid-cols-[minmax(0,4fr)_minmax(0,7fr)] lg:gap-16">
                <div class="lg:sticky lg:top-8 lg:self-start">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">What’s included</h2>
                    <p class="mt-4 max-w-[40ch] text-sm leading-relaxed text-slate-600">
                        Status is read from <code
                            class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">config/starter.php</code>,
                        so this list is only as honest as that file.
                    </p>
                </div>

                <ul class="divide-y divide-slate-200 border-y border-slate-200">
                    @foreach (config('starter.modules') as [$title, $text, $status])
                        <li
                            class="grid gap-2 py-5 sm:grid-cols-[minmax(0,3fr)_minmax(0,4fr)_auto] sm:items-baseline sm:gap-6">
                            <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                            <p class="text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                            @if ($status === 'ready')
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                    </svg>
                                    Ready
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                    <span class="h-px w-3 bg-slate-400" aria-hidden="true"></span>
                                    Planned
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Memulai: urutan nyata, jadi bernomor --}}
        <section id="start" class="mx-auto max-w-4xl px-6 py-16 lg:py-24">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Start a new project</h2>

            <ol class="mt-10 space-y-8">
                @foreach ($steps as $i => [$title, $body])
                    <li class="grid gap-4 sm:grid-cols-[2rem_minmax(0,1fr)] sm:gap-6">
                        <span
                            class="grid size-8 place-items-center rounded-full bg-violet-700 text-sm font-semibold text-white">{{ $i + 1 }}</span>
                        <div class="min-w-0">
                            <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                            @if (str_contains($body, "\n") || str_starts_with($body, 'git ') || str_starts_with($body, 'cp '))
                                <pre
                                    class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-4 font-mono text-xs leading-relaxed text-slate-700"><code>{{ $body }}</code></pre>
                            @else
                                <p class="mt-2 max-w-[60ch] text-sm leading-relaxed text-slate-600">{{ $body }}
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Sebelum rilis --}}
        <section class="mx-auto max-w-4xl px-6 pb-16 lg:pb-24">
            <div class="rounded-3xl border border-slate-200 p-8 sm:p-10">
                <h2 class="text-xl font-bold text-slate-900">Replace before you ship</h2>
                <ul class="mt-5 space-y-3">
                    @foreach (config('starter.replace_before_shipping') as $item)
                        <li class="flex items-start gap-3 text-sm leading-relaxed text-slate-700">
                            <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-orange-500" aria-hidden="true"></span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200">
        <div
            class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-3 px-6 py-8 text-xs text-slate-500 sm:flex-row sm:items-center">
            <p>{{ config('app.name') }} starter</p>
            <a href="{{ route('login') }}" class="hover:text-slate-800">Log in</a>
        </div>
    </footer>
</x-layouts::guest>
