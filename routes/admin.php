<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'pages.admin.dashboard')->name('dashboard');

    Route::view('/profile', 'pages.admin.account.profile')->name('profile');
    Route::livewire('/account', 'pages::admin.account.details')->name('account');
    Route::livewire('/account/security', 'pages::admin.account.security')->name('account.security');

});

Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::redirect('/', '/settings/general');
    Route::livewire('/general', 'pages::admin.settings.general')->name('general');
    Route::livewire('/mail', 'pages::admin.settings.mail')->name('mail');
});

Route::middleware(['auth', 'can:viewAny,'.User::class])->group(function () {
    Route::livewire('/users', 'pages::admin.users.index')->name('users.index');
});

Route::middleware(['auth', 'can:viewAny,'.Role::class])->group(function () {
    Route::livewire('/roles', 'pages::admin.roles.index')->name('roles.index');
});
