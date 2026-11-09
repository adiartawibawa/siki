<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::landing')->name('home');

require __DIR__.'/admin.php';

// Tanpa ini, URL yang tidak dikenal tidak melewati middleware web: sesi tidak dimulai,
// sehingga halaman 404 menganggap pengguna yang sudah login sebagai tamu.
Route::fallback(fn () => abort(404));

// Pratinjau tampilan semua halaman error saat pengembangan.
if (app()->isLocal()) {
    Route::get('/_errors/{code}', fn (int $code) => abort($code))->where('code', '[45]\d\d');
}
