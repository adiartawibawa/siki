<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

it('logs in with email or username', function (string $field) {
    $user = User::factory()->create(['password' => 'secret-pass-123']);

    $this->post('/login', ['email' => $user->{$field}, 'password' => 'secret-pass-123'])
        ->assertRedirect(config('fortify.home'));

    $this->assertAuthenticatedAs($user);
})->with(['email', 'username']);

it('does not log in a deactivated account', function () {
    $user = User::factory()->create(['password' => 'secret-pass-123']);
    $user->delete();

    $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass-123'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('confirms the password through Fortify', function () {
    $user = User::factory()->create(['password' => 'secret-pass-123']);

    $this->actingAs($user)
        ->post(route('password.confirm'), ['password' => 'wrong'])
        ->assertSessionHasErrors('password');

    $this->actingAs($user)
        ->post(route('password.confirm'), ['password' => 'secret-pass-123'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('auth.password_confirmed_at');
});

it('keeps registration closed unless the setting allows it', function () {
    $this->get('/register')->assertNotFound();

    $settings = app(GeneralSettings::class);
    $settings->registration_enabled = true;
    $settings->save();

    $this->get('/register')->assertOk();
});

it('answers the same for known and unknown emails on password reset', function () {
    $known = User::factory()->create();

    foreach ([$known->email, 'nobody@example.com'] as $email) {
        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => $email])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();
    }
});
