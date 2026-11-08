<?php

use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

it('sends guests to the login page', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

it('shows every signed-in user their account card', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/dashboard')->assertOk()->assertSee('Your account');
})->with(['Super Admin', 'Admin', 'Operator', 'Regular User']);

it('shows the user overview only to those who may view users', function (string $role, bool $sees) {
    $response = $this->actingAs(userWithRole($role))->get('/dashboard')->assertOk();

    $sees ? $response->assertSee('Active accounts') : $response->assertDontSee('Active accounts');
})->with([['Super Admin', true], ['Admin', true], ['Operator', true], ['Regular User', false]]);
