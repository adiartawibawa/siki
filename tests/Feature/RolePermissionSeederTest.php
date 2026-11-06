<?php

use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolePermissionSeeder;

it('gives system roles their abilities through DatabaseSeeder', function () {
    $this->seed(DatabaseSeeder::class); // jalur nyata; menangkap bug cache karena WithoutModelEvents

    expect(Role::findByName('Admin', 'web')->hasPermissionTo('users.view'))->toBeTrue()
        ->and(Role::findByName('Regular User', 'web')->permissions->count())->toBe(0);
});

it('does not overwrite permissions edited in the UI', function () {
    $this->seed(RolePermissionSeeder::class);
    Role::findByName('Operator', 'web')->revokePermissionTo('users.delete');

    $this->seed(RolePermissionSeeder::class);

    expect(Role::findByName('Operator', 'web')->hasPermissionTo('users.delete'))->toBeFalse();
});

it('always restores every ability to Super Admin', function () {
    $this->seed(RolePermissionSeeder::class);
    Role::findByName('Super Admin', 'web')->revokePermissionTo('manage-settings');

    $this->seed(RolePermissionSeeder::class);

    expect(Role::findByName('Super Admin', 'web')->hasPermissionTo('manage-settings'))->toBeTrue();
});
