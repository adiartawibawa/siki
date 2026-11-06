<?php

use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

it('keeps the users page behind a permission', function (string $role, int $status) {
    $this->actingAs(userWithRole($role))->get('/users')->assertStatus($status);
})->with([['Super Admin', 200], ['Admin', 200], ['Operator', 200], ['Regular User', 403]]);

it('keeps application settings for those who may manage them', function (string $role, int $status) {
    $this->actingAs(userWithRole($role))->get('/settings/general')->assertStatus($status);
})->with([['Super Admin', 200], ['Admin', 200], ['Operator', 403], ['Regular User', 403]]);

it('sends guests to the login page', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with(['/users', '/roles', '/settings/general', '/account']);
