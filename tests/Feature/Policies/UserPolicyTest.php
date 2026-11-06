<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

dataset('hierarchy', function () {
    $roles = ['Super Admin', 'Admin', 'Operator', 'Regular User'];
    $manages = [
        'Super Admin' => $roles,
        'Admin' => ['Operator', 'Regular User'],
        'Operator' => ['Regular User'],
        'Regular User' => [],
    ];

    foreach ($roles as $actor) {
        foreach ($roles as $target) {
            yield "$actor → $target" => [$actor, $target, in_array($target, $manages[$actor], true)];
        }
    }
});

it('follows "only below" for update, delete and restore', function (string $actorRole, string $targetRole, bool $allowed) {
    $actor = userWithRole($actorRole);
    $target = userWithRole($targetRole); // pengguna terpisah, juga untuk peran yang sama

    expect($actor->can('update', $target))->toBe($allowed)
        ->and($actor->can('delete', $target))->toBe($allowed)
        ->and($actor->can('restore', $target))->toBe($allowed);
})->with('hierarchy');

it('never lets anyone manage their own account from the users page', function (string $role) {
    $user = userWithRole($role);

    expect($user->can('update', $user))->toBeFalse()
        ->and($user->can('delete', $user))->toBeFalse();
})->with(['Super Admin', 'Admin', 'Operator', 'Regular User']);

it('assigns only roles below the actor level', function (string $actorRole, string $targetRole, string $newRole, bool $allowed) {
    $actor = userWithRole($actorRole);
    $target = userWithRole($targetRole);
    $role = Role::findByName($newRole, 'web');

    expect($actor->can('assignRole', [$target, $role]))->toBe($allowed);
})->with([
    ['Super Admin', 'Admin', 'Super Admin', true],
    ['Super Admin', 'Operator', 'Regular User', true],
    ['Admin', 'Operator', 'Regular User', true],
    ['Admin', 'Regular User', 'Operator', true],
    ['Admin', 'Regular User', 'Admin', false],        // tidak boleh menyamai level sendiri
    ['Admin', 'Operator', 'Super Admin', false],
    ['Admin', 'Admin', 'Regular User', false],        // target setingkat
    ['Operator', 'Regular User', 'Regular User', true],
    ['Operator', 'Regular User', 'Operator', false],
    ['Operator', 'Regular User', 'Admin', false],
    ['Regular User', 'Regular User', 'Regular User', false],
]);

it('grants roles to new accounts only below the actor level', function (string $actorRole, string $newRole, bool $allowed) {
    $actor = userWithRole($actorRole);
    $role = Role::findByName($newRole, 'web');

    expect($actor->can('grantRole', [User::class, $role]))->toBe($allowed);
})->with([
    ['Super Admin', 'Super Admin', true],
    ['Admin', 'Operator', true],
    ['Admin', 'Admin', false],
    ['Admin', 'Super Admin', false],
    ['Operator', 'Regular User', true],
    ['Operator', 'Operator', false],
    ['Regular User', 'Regular User', false],
]);

it('lets the right roles see and create users', function (string $role, bool $allowed) {
    $user = userWithRole($role);

    expect($user->can('viewAny', User::class))->toBe($allowed)
        ->and($user->can('create', User::class))->toBe($allowed);
})->with([['Super Admin', true], ['Admin', true], ['Operator', true], ['Regular User', false]]);

it('knows when a super admin is the last one', function () {
    $only = userWithRole('Super Admin');
    expect($only->isLastSuperAdmin())->toBeTrue();

    $second = userWithRole('Super Admin');
    expect($only->isLastSuperAdmin())->toBeFalse();

    $second->delete(); // akun yang dinonaktifkan tidak dihitung
    expect($only->fresh()->isLastSuperAdmin())->toBeTrue();
});
