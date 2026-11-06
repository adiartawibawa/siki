<?php

use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

it('lets only super admin create roles by default', function (string $role, bool $allowed) {
    expect(userWithRole($role)->can('create', Role::class))->toBe($allowed);
})->with([['Super Admin', true], ['Admin', false], ['Operator', false], ['Regular User', false]]);

it('never lets anyone edit or delete a system role', function () {
    $super = userWithRole('Super Admin');
    $system = Role::findByName('Admin', 'web');

    expect($super->can('update', $system))->toBeFalse()
        ->and($super->can('delete', $system))->toBeFalse();
});

it('limits a delegated role manager to levels below their own', function (int $level, bool $allowed) {
    Role::findByName('Admin', 'web')->givePermissionTo('roles.manage');
    $admin = userWithRole('Admin');
    $custom = Role::create(['name' => 'Verifikator', 'guard_name' => 'web', 'level' => $level]);

    expect($admin->can('update', $custom))->toBe($allowed);
})->with([[20, true], [79, true], [80, false], [99, false]]);

it('refuses to delete a role that still has users', function () {
    $super = userWithRole('Super Admin');
    $custom = Role::create(['name' => 'Verifikator', 'guard_name' => 'web', 'level' => 20]);

    expect($super->can('delete', $custom))->toBeTrue();

    userWithRole('Verifikator');

    expect($super->can('delete', $custom->fresh()))->toBeFalse();
});
