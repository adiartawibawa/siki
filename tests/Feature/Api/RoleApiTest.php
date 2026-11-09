<?php

use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

it('requires a token', function () {
    $this->getJson('/api/v1/roles')->assertUnauthorized();
});

it('needs both the token ability and the user permission', function (string $role, array $abilities, int $status) {
    Sanctum::actingAs(userWithRole($role), $abilities);

    $this->getJson('/api/v1/roles')->assertStatus($status);
})->with([
    ['Super Admin', ['roles:read'], 200],
    ['Super Admin', ['users:read'], 403],   // token tanpa kemampuan
    ['Admin', ['roles:read'], 403],         // pemilik tanpa permission
    ['Regular User', ['roles:read'], 403],
]);

it('lists roles with the documented shape, highest level first', function () {
    Sanctum::actingAs(userWithRole('Super Admin'), ['roles:read']);

    $this->getJson('/api/v1/roles')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'description', 'level', 'is_system', 'users_count']]])
        ->assertJsonPath('data.0.name', 'Super Admin');
});

it('shows one role with its permissions', function () {
    Sanctum::actingAs(userWithRole('Super Admin'), ['roles:read']);
    $operator = Role::findByName('Operator', 'web');

    $this->getJson("/api/v1/roles/{$operator->id}")
        ->assertOk()
        ->assertJsonCount(5, 'data.permissions');
});

it('marks which permissions the caller may hand out', function () {
    Sanctum::actingAs(userWithRole('Super Admin'), ['roles:read']);

    $permissions = $this->getJson('/api/v1/permissions')->assertOk()->json('data');

    expect(collect($permissions)->every(fn ($p) => $p['grantable'] === true))->toBeTrue();
});

it('rejects a token whose owner was deactivated', function () {
    $user = userWithRole('Super Admin');
    $token = $user->createToken('t', ['roles:read'])->plainTextToken;

    $user->delete();

    $this->withToken($token)->getJson('/api/v1/roles')->assertUnauthorized();
});

it('does not expose write endpoints unless enabled', function () {
    Sanctum::actingAs(userWithRole('Super Admin'), ['*']);

    $this->postJson('/api/v1/roles', ['name' => 'X', 'level' => 20])
        ->assertStatus(config('admin.api.writes') ? 422 : 405);
});

// Dijalankan hanya bila ADMIN_API_WRITES=true (route terdaftar saat boot, jadi config tidak bisa diubah di tes).
$writesOff = fn () => ! config('admin.api.writes');

it('creates a role', function () {
    Sanctum::actingAs(userWithRole('Super Admin'), ['roles:write']);

    $this->postJson('/api/v1/roles', ['name' => 'Verifikator', 'level' => 20, 'permissions' => ['users.view']])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Verifikator')
        ->assertJsonPath('data.permissions.0', 'users.view');
})->skip($writesOff, 'ADMIN_API_WRITES=false');

it('rejects a level above the caller reach', function () {
    Role::findByName('Admin', 'web')->givePermissionTo('roles.manage');
    Sanctum::actingAs(userWithRole('Admin'), ['roles:write']);

    $this->postJson('/api/v1/roles', ['name' => 'Terlalu tinggi', 'level' => 80])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('level');
})->skip($writesOff, 'ADMIN_API_WRITES=false');

it('refuses to delete a role that still has users', function () {
    $custom = Role::create(['name' => 'Verifikator', 'guard_name' => 'web', 'level' => 20]);
    userWithRole('Verifikator');
    Sanctum::actingAs(userWithRole('Super Admin'), ['roles:write']);

    $this->deleteJson("/api/v1/roles/{$custom->id}")->assertForbidden();
})->skip($writesOff, 'ADMIN_API_WRITES=false');
