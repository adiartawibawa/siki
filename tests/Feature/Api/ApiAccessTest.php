<?php

use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function tokenFor($user, array $abilities): string
{
    return $user->createToken('test', $abilities)->plainTextToken;
}

it('blocks a valid token when the role lacks api.access, and allows it once granted', function () {
    $token = tokenFor(userWithRole('Operator'), ['users:read']);

    $this->withToken($token)->getJson('/api/v1/users')->assertForbidden();

    Role::findByName('Operator', 'web')->givePermissionTo('api.access');

    $this->withToken($token)->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'username', 'email', 'role']], 'links', 'meta']);
});

it('goes dormant, not deleted, when the permission is taken away', function () {
    Role::findByName('Operator', 'web')->givePermissionTo('api.access');
    $operator = userWithRole('Operator');
    $token = tokenFor($operator, ['users:read']);

    $this->withToken($token)->getJson('/api/v1/users')->assertOk();

    Role::findByName('Operator', 'web')->revokePermissionTo('api.access');

    $this->withToken($token)->getJson('/api/v1/users')->assertForbidden();
    expect($operator->tokens()->count())->toBe(1);
});

it('needs the token scope as well as the permission', function () {
    Role::findByName('Operator', 'web')->givePermissionTo('api.access');
    $token = tokenFor(userWithRole('Operator'), ['roles:read']);

    $this->withToken($token)->getJson('/api/v1/users')->assertForbidden();
});
