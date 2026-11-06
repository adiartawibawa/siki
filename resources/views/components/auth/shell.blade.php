@props([
    'illustration',
    'step' => null,
    'steps' => null,
    'back' => null,

    // Layout configuration
    'minHeight' => 'min-h-screen',
    'gridColumns' => 'lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]',

    // Panel configuration
    'panelPadding' => 'px-10 py-10 xl:px-14',
    'mainPadding' => 'px-6 py-8 sm:px-10',

    // Content configuration
    'contentWidth' => 'max-w-sm',
    'contentPadding' => 'py-10',

    // Illustration configuration
    'illustrationWidth' => 'w-[115%]',
])

<div class="grid {{ $minHeight }} overflow-x-clip {{ $gridColumns }}">

    {{-- Left panel --}}
    <aside class="relative hidden bg-[#E9E5F7] lg:block">
        <div class="flex h-full flex-col {{ $panelPadding }}">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-lg font-semibold text-slate-800">
                <span class="grid size-6 place-items-center rounded-md bg-violet-700 text-xs text-white">
                    {{ Str::substr(config('app.name'), 0, 1) }}
                </span>
                {{ config('app.name') }}
            </a>

            <h1 class="mt-14 max-w-sm text-3xl font-bold leading-tight tracking-tight text-slate-700 xl:text-4xl">
                Learning without limits, right where you are with
                <span class="text-orange-500">{{ config('app.name') }}</span>
            </h1>
        </div>

        <img src="{{ asset('images/' . $illustration) }}" alt=""
            class="pointer-events-none absolute bottom-0 left-1/2 z-10 {{ $illustrationWidth }} max-w-none -translate-x-1/2 select-none">
    </aside>

    {{-- Right panel --}}
    <main class="relative flex flex-col {{ $mainPadding }}">

        {{-- Top bar --}}
        <div class="mx-auto flex min-h-8 w-full {{ $contentWidth }} items-center">
            @if ($step && $steps)
                <div class="flex w-full items-center justify-between gap-6">
                    <div class="flex flex-1 gap-1" role="progressbar" aria-valuemin="1"
                        aria-valuemax="{{ $steps }}" aria-valuenow="{{ $step }}">
                        @for ($i = 1; $i <= $steps; $i++)
                            <span
                                class="h-0.5 flex-1 rounded-full {{ $i <= $step ? 'bg-slate-800' : 'bg-slate-200' }}"></span>
                        @endfor
                    </div>

                    <span class="whitespace-nowrap text-xs text-slate-500">
                        Step {{ $step }} of {{ $steps }}
                    </span>
                </div>
            @elseif ($back)
                <a href="{{ $back }}"
                    class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs text-slate-600 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                    </svg>
                    Back
                </a>
            @endif
        </div>

        {{-- Form content --}}
        <div class="mx-auto flex w-full flex-1 flex-col justify-center {{ $contentWidth }} {{ $contentPadding }}">

            {{-- Mobile logo --}}
            <a href="{{ url('/') }}" class="mb-8 text-lg font-semibold text-slate-800 lg:hidden">
                {{ config('app.name') }}
            </a>

            {{-- Badge --}}
            <div
                class="mb-6 grid size-12 place-items-center rounded-full border border-slate-200 bg-violet-50 shadow-sm">
                @isset($badge)
                    {{ $badge }}
                @else
                    <span class="size-4 rounded-sm bg-violet-600"></span>
                @endisset
            </div>

            {{ $slot }}
        </div>
    </main>
</div>
