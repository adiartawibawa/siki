<?php

use App\Actions\Roles\SaveRole;
use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Role::findByName('Admin', 'web')->givePermissionTo('roles.manage');
});

it('never hands out a permission the actor does not hold', function () {
    $admin = userWithRole('Admin'); // tidak memegang roles.view

    $role = app(SaveRole::class)->handle($admin, null, [
        'name' => 'Sneaky', 'level' => 20, 'permissions' => ['users.view', 'roles.view'],
    ]);

    expect($role->permissions->pluck('name')->all())->toBe(['users.view']);
});

it('keeps permissions the actor cannot see when editing', function () {
    $custom = Role::create(['name' => 'Verifikator', 'guard_name' => 'web', 'level' => 20]);
    $custom->givePermissionTo('roles.view');

    $role = app(SaveRole::class)->handle(userWithRole('Admin'), $custom, [
        'name' => 'Verifikator', 'level' => 20, 'permissions' => [],
    ]);

    expect($role->permissions->pluck('name')->all())->toContain('roles.view');
});

it('refuses a level at or above the actor level', function () {
    expect(fn () => app(SaveRole::class)->handle(userWithRole('Admin'), null, [
        'name' => 'Terlalu tinggi', 'level' => 80, 'permissions' => [],
    ]))->toThrow(AuthorizationException::class);
});

it('refuses an actor without the roles.manage permission', function () {
    expect(fn () => app(SaveRole::class)->handle(userWithRole('Operator'), null, [
        'name' => 'X', 'level' => 20, 'permissions' => [],
    ]))->toThrow(AuthorizationException::class);
});
