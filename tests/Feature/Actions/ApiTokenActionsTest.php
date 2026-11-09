<?php

use App\Actions\Api\CreateApiToken;
use App\Actions\Api\RevokeApiTokens;
use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Role::findByName('Operator', 'web')->givePermissionTo('api.access');
});

it('refuses an account without api.access', function () {
    expect(fn () => app(CreateApiToken::class)->handle(userWithRole('Regular User'), 'x', ['users:read'], 90))
        ->toThrow(AuthorizationException::class);
});

it('keeps only the scopes the owner may use', function () {
    $operator = userWithRole('Operator'); // punya users.view, tidak punya roles.view

    app(CreateApiToken::class)->handle($operator, 'ci', ['users:read', 'roles:read'], 90);

    expect($operator->tokens()->first()->abilities)->toBe(['users:read']);
});

it('rejects a request that leaves no usable scope', function () {
    expect(fn () => app(CreateApiToken::class)->handle(userWithRole('Operator'), 'x', ['roles:read'], 90))
        ->toThrow(ValidationException::class);
});

it('never issues a token without an expiry from the list', function () {
    expect(fn () => app(CreateApiToken::class)->handle(userWithRole('Operator'), 'x', ['users:read'], 3650))
        ->toThrow(ValidationException::class);
});

it('caps the number of tokens per account', function () {
    $operator = userWithRole('Operator');

    foreach (range(1, CreateApiToken::MAX_TOKENS) as $i) {
        app(CreateApiToken::class)->handle($operator, "t{$i}", ['users:read'], 30);
    }

    expect(fn () => app(CreateApiToken::class)->handle($operator, 'one more', ['users:read'], 30))
        ->toThrow(ValidationException::class);
});

it('lets owners revoke their own tokens, even without api.access', function () {
    $user = userWithRole('Regular User');
    $user->createToken('old', ['users:read']);

    expect(app(RevokeApiTokens::class)->handle($user, $user))->toBe(1);
});

it('lets only a permitted manager revoke tokens of accounts below them', function (string $actorRole, string $ownerRole, bool $allowed) {
    Role::findByName('Admin', 'web')->givePermissionTo('api.tokens.manage');
    Role::findByName('Operator', 'web')->givePermissionTo('api.tokens.manage');

    $actor = userWithRole($actorRole);
    $owner = userWithRole($ownerRole);
    $owner->createToken('t', ['users:read']);

    $call = fn () => app(RevokeApiTokens::class)->handle($actor, $owner);

    $allowed ? expect($call())->toBe(1) : expect($call)->toThrow(AuthorizationException::class);
})->with([
    ['Admin', 'Operator', true],
    ['Admin', 'Admin', false],       // setingkat
    ['Operator', 'Operator', false],
    ['Operator', 'Regular User', true],
    ['Regular User', 'Regular User', false],
]);
