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
    @yield('body')

    @livewireScripts

    {{-- Stack untuk Script Tambahan Khusus di Halaman Tertentu --}}
    @stack('scripts')
</body>

</html>
