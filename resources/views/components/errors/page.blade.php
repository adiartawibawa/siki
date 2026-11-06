@props(['code' => 500, 'exception' => null])

@php
    $code = (int) $code;

    $copy = [
        401 => ['log-in', __('Please log in'), __('You need to be logged in to open this page.')],
        403 => [
            'shield-x',
            __('Access denied'),
            __('You don’t have permission to open this page. If you think this is a mistake, ask an administrator.'),
        ],
        404 => ['search-x', __('Page not found'), __('The page you’re looking for doesn’t exist, or it was moved.')],
        419 => [
            'timer-off',
            __('Page expired'),
            __('Your session timed out, or the form stayed open for too long. Go back and try again.'),
        ],
        429 => [
            'hourglass',
            __('Too many requests'),
            __('You’ve made too many attempts in a short time. Wait a moment, then try again.'),
        ],
        500 => [
            'server-crash',
            __('Something went wrong on our side'),
            __('We couldn’t complete your request and the problem has been logged. Try again in a moment.'),
        ],
        503 => ['wrench', __('Back in a moment'), __('The application is being updated. Please try again shortly.')],
    ];

    [$icon, $title, $text] =
        $copy[$code] ??
        ($code >= 500
            ? [
                'server-crash',
                __('Server error'),
                __('The server couldn’t complete your request. Try again in a moment.'),
            ]
            : [
                'circle-exclamation-point',
                __('This request couldn’t be processed'),
                __('Something about this request isn’t right. Go back and try again.'),
            ]);

    // Satu-satunya pesan exception yang boleh tampil: 403, karena ditulis sendiri oleh pengembang
    // (abort(403, '…') atau Response::deny('…')). Pesan bawaan Laravel disembunyikan.
    $note = null;
    if ($code === 403 && $exception) {
        $raw = trim((string) $exception->getMessage());
        $note = $raw !== '' && $raw !== 'This action is unauthorized.' ? Str::limit(strip_tags($raw), 160) : null;
    }

    // Lama menunggu (429 dan 503 membawa header Retry-After dalam detik).
    $retry =
        $exception && method_exists($exception, 'getHeaders') ? $exception->getHeaders()['Retry-After'] ?? null : null;
    $wait = null;
    if (in_array($code, [429, 503], true) && is_numeric($retry)) {
        $s = max(1, (int) $retry);
        $m = (int) ceil($s / 60);
        $wait = $s < 60 ? $s . ' ' . Str::plural('second', $s) : $m . ' ' . Str::plural('minute', $m);
    }

    // Halaman error tidak boleh ikut gagal: sesi bisa belum ada (503, 500 karena sesi, dst.).
    try {
        $signedIn = auth()->check();
    } catch (\Throwable) {
        $signedIn = false;
    }

    try {
        $previous = url()->previous();
    } catch (\Throwable) {
        $previous = url('/');
    }

    $home = $signedIn && Route::has('dashboard') ? route('dashboard') : (Route::has('home') ? route('home') : url('/'));
    $homeLabel = $signedIn ? __('Back to dashboard') : __('Go to home page');

    $mode = match (true) {
        $code === 401 => 'login',
        $code === 419 => 'form',
        $code === 429, $code >= 500 => 'retry',
        default => 'lost',
    };

    // Kembali hanya jika asalnya dari situs ini; selain itu pergi ke $home (tanpa JS pun tetap bekerja).
    $back =
        'if (document.referrer && new URL(document.referrer).origin === location.origin) { history.back(); return false }';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-canvas text-slate-900 antialiased">
    <main class="grid min-h-screen place-items-center p-4">
        <div class="w-full max-w-lg">
            <a href="{{ $home }}"
                class="mb-6 flex w-fit items-center gap-2.5 rounded-lg text-lg font-semibold text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <span
                    class="grid size-9 place-items-center rounded-xl bg-linear-to-br from-brand-500 to-brand-700 text-sm font-bold text-white">
                    {{ Str::upper(Str::substr(config('app.name'), 0, 1)) }}
                </span>
                {{ config('app.name') }}
            </a>

            <div class="relative overflow-hidden rounded-3xl bg-white p-8 ring-1 ring-slate-200/70 sm:p-12">
                {{-- Angka besar sebagai latar: hiasan saja, tidak dibaca pembaca layar --}}
                <span aria-hidden="true"
                    class="pointer-events-none absolute -right-3 -top-8 select-none text-[9rem] font-bold leading-none tracking-tighter text-brand-100 sm:text-[11rem]">{{ $code }}</span>

                <div class="relative">
                    <span
                        class="grid size-12 place-items-center rounded-full border border-slate-200 bg-brand-50 text-brand-700">
                        <x-ui.icon :name="$icon" />
                    </span>

                    <h1
                        class="mt-6 max-w-[18ch] text-3xl font-semibold leading-tight tracking-tight text-slate-900 sm:text-4xl">
                        {{ $title }}
                    </h1>
                    <p class="mt-3 max-w-[46ch] text-sm leading-relaxed text-slate-600 sm:text-base">{{ $text }}
                    </p>

                    @if ($note)
                        <p class="mt-4 max-w-[46ch] rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            {{ $note }}</p>
                    @endif

                    @if ($wait)
                        <p class="mt-4 text-sm font-medium text-slate-800">
                            {{ __('Try again in about :time.', ['time' => $wait]) }}</p>
                    @endif

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @switch($mode)
                            @case('login')
                                <x-ui.button variant="dark" :href="Route::has('login') ? route('login') : url('/')">{{ __('Log in') }}</x-ui.button>
                                <x-ui.button :href="$home">{{ __('Go to home page') }}</x-ui.button>
                            @break

                            @case('form')
                                <x-ui.button variant="dark" :href="$previous">{{ __('Back to the form') }}</x-ui.button>
                                <x-ui.button :href="$home">{{ $homeLabel }}</x-ui.button>
                            @break

                            @case('retry')
                                <x-ui.button variant="dark" :href="url()->current()"
                                    onclick="location.reload(); return false">{{ __('Try again') }}</x-ui.button>
                                <x-ui.button :href="$home">{{ $homeLabel }}</x-ui.button>
                            @break

                            @default
                                <x-ui.button variant="dark" :href="$home">{{ $homeLabel }}</x-ui.button>
                                <x-ui.button :href="$home" :onclick="$back">{{ __('Go back') }}</x-ui.button>
                        @endswitch
                    </div>

                    <p class="mt-10 text-xs text-slate-400">{{ __('Error :code', ['code' => $code]) }}</p>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
