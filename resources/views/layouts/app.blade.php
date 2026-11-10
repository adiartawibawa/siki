@extends('layouts.base')

@section('body')
    <a href="#main"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:shadow">
        Skip to content
    </a>

    <div x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="min-h-screen bg-canvas p-3 lg:p-4">
        <div class="mx-auto grid max-w-[1600px] items-start gap-4 lg:grid-cols-[16.5rem_minmax(0,1fr)]">

            {{-- Sidebar desktop --}}
            <div class="sticky top-4 hidden h-[calc(100vh-2rem)] lg:block">
                <x-admin.sidebar />
            </div>

            {{-- Drawer mobile --}}
            <div x-show="drawer" x-cloak class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true"
                aria-label="Navigation">
                <div x-show="drawer" x-transition.opacity class="absolute inset-0 bg-slate-900/40" @click="drawer = false">
                </div>
                <div x-show="drawer" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="absolute inset-y-3 left-3 w-72 max-w-[calc(100%-1.5rem)]">
                    <x-admin.sidebar closable />
                </div>
            </div>

            <div class="min-w-0 space-y-4">
                <x-admin.topbar :title="$title ?? 'Overview'" />

                <main id="main" class="space-y-4">
                    {{ $slot }}
                </main>

                <footer class="flex flex-col items-center mx-auto w-full mt-24">
                    <div class="w-full max-w-6xl px-6 py-4 border-t border-gray-200/70 flex flex-col items-center gap-4">
                        <a href="/"
                            class="text-lg font-medium tracking-[0.35em] uppercase text-gray-400 hover:text-gray-500 transition-colors">
                            PRADStudio
                        </a>
                        <p class="text-xs text-gray-300 tracking-wide">
                            Crafted with care &copy; {{ date('Y') }} — All rights reserved.
                        </p>
                    </div>
                </footer>
            </div>
        </div>
    </div>
@endsection
