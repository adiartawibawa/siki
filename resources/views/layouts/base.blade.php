<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    {{-- Stack untuk Style Tambahan Khusus di Halaman Tertentu --}}
    @stack('styles')
</head>

<body>
    {{-- Top progress bar. @persist menjaga elemennya tetap ada saat wire:navigate. --}}
    @persist('top-progress')
        <div id="top-progress" aria-hidden="true"
            class="pointer-events-none fixed inset-x-0 top-0 z-[100] h-[3px] opacity-0 transition-opacity duration-300 motion-reduce:transition-none">
            <div style="transform: scaleX(0)"
                class="h-full w-full origin-left bg-brand-600 shadow-[0_0_8px_0] shadow-brand-600/50 transition-transform duration-200 ease-out motion-reduce:transition-none">
            </div>
        </div>
    @endpersist

    @yield('body')

    <x-ui.toaster />

    @livewireScripts

    {{-- Stack untuk Script Tambahan Khusus di Halaman Tertentu --}}
    @stack('scripts')
</body>

</html>
