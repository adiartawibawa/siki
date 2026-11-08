<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| Semua route yang memerlukan autentikasi & verifikasi email.
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::livewire('/dashboard', 'pages::admin.dashboard')->name('dashboard');

    // Account
    Route::prefix('account')->name('account.')->group(function () {
        Route::livewire('/', 'pages::admin.account.details')->name('details');
        Route::livewire('/security', 'pages::admin.account.security')->name('security');
    });

    // Profile (view only)
    Route::view('/profile', 'pages.admin.account.profile')->name('profile');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', '/settings/general');
        Route::livewire('/general', 'pages::admin.settings.general')->name('general');
        Route::livewire('/mail', 'pages::admin.settings.mail')->name('mail');
    });

    // Promo
    Route::post('/promo/dismiss', function () {
        session()->put('promo.dismissed', true);

        return response()->noContent();
    })->name('promo.dismiss');
});

/*
|--------------------------------------------------------------------------
| Authorization-Protected Routes
|--------------------------------------------------------------------------
| Route yang memerlukan otorisasi tambahan (policy) per resource.
*/
Route::middleware(['auth', 'can:viewAny,'.User::class])->group(function () {
    Route::livewire('/users', 'pages::admin.users.index')->name('users.index');
});

Route::middleware(['auth', 'can:viewAny,'.Role::class])->group(function () {
    Route::livewire('/roles', 'pages::admin.roles.index')->name('roles.index');
});

/*
|--------------------------------------------------------------------------
| Showcase Routes (Conditional)
|--------------------------------------------------------------------------
| Hanya didaftarkan jika fitur showcase diaktifkan di config.
*/
if (config('admin.showcase')) {
    Route::view('/showcase', 'showcase')
        ->middleware('auth')
        ->name('showcase');
}
