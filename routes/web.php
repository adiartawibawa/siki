<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! config('starter.landing')) {
        // Ganti 'dashboard' dengan nama route beranda aplikasi setelah login.
        return redirect()->route(auth()->check() ? 'dashboard' : 'login');
    }

    return view('landing');
})->name('home');

require __DIR__.'/admin.php';
