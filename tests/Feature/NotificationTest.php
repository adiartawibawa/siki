<?php

use App\Actions\Api\RevokeApiTokens;
use App\Actions\Users\ChangeRole;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountNotice;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

function notice(User $user, string $message = 'Hello.', ?string $url = '/account'): DatabaseNotification
{
    $user->notify(new AccountNotice($message, $url));

    return $user->notifications()->get()->first(fn ($n) => $n->data['message'] === $message);
}

// --- Pemicu ---

it('notifies the account whose role changed, and nobody else', function () {
    $admin = userWithRole('Admin');
    $target = userWithRole('Regular User');

    app(ChangeRole::class)->handle($admin, $target, Role::findByName('Operator', 'web'));

    expect($target->notifications()->count())->toBe(1)
        ->and($admin->notifications()->count())->toBe(0)
        ->and($target->notifications()->first()->data['message'])->toBe('Your role is now Operator.');
});

it('sends nothing when the role change is refused', function () {
    $operator = userWithRole('Operator');
    $target = userWithRole('Regular User');

    expect(fn () => app(ChangeRole::class)->handle($operator, $target, Role::findByName('Admin', 'web')))
        ->toThrow(AuthorizationException::class)
        ->and($target->notifications()->count())->toBe(0);
});

it('notifies after a password change and stores no secret', function () {
    $user = User::factory()->create();

    $user->changePassword('a-new-password-123');

    $data = $user->notifications()->first()->data;

    expect(Hash::check('a-new-password-123', $user->fresh()->password))->toBeTrue()
        ->and(array_keys($data))->toBe(['message', 'url'])
        ->and(json_encode($data))->not->toContain('a-new-password-123');
});

it('tells the owner when an administrator revokes tokens, but not for self-service', function () {
    Role::findByName('Admin', 'web')->givePermissionTo('api.tokens.manage');
    $admin = userWithRole('Admin');
    $owner = userWithRole('Operator');

    $owner->createToken('one', ['users:read']);
    app(RevokeApiTokens::class)->handle($admin, $owner);

    expect($owner->notifications()->count())->toBe(1);

    $owner->createToken('two', ['users:read']);
    app(RevokeApiTokens::class)->handle($owner, $owner);

    expect($owner->notifications()->count())->toBe(1);
});

it('tells the owner when confirmed 2FA is turned off, not when an unfinished setup is cancelled', function (bool $confirmed, int $expected) {
    $user = User::factory()->create();
    $user->forceFill([
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt('[]'),
        'two_factor_confirmed_at' => $confirmed ? now() : null,
    ])->save();

    app(DisableTwoFactorAuthentication::class)($user);

    expect($user->notifications()->count())->toBe($expected);
})->with([[true, 1], [false, 0]]);

// --- Endpoint ---

it('requires sign-in', function () {
    $this->getJson('/notifications')->assertUnauthorized();
});

it('lists only the signed-in user’s notifications, with the unread count', function () {
    $me = User::factory()->create();
    notice($me, 'Mine');
    notice(User::factory()->create(), 'Theirs');

    $this->actingAs($me)->getJson('/notifications')
        ->assertOk()
        ->assertJsonPath('unread', 1)
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('items.0.message', 'Mine');
});

it('marks one as read and redirects to its link', function () {
    $me = User::factory()->create();
    $item = notice($me, 'Hello', '/account/security');

    $this->actingAs($me)->get("/notifications/{$item->id}")->assertRedirect('/account/security');

    expect($item->fresh()->read_at)->not->toBeNull();
});

it('refuses to open someone else’s notification', function () {
    $item = notice(User::factory()->create(), 'Theirs');

    $this->actingAs(User::factory()->create())->get("/notifications/{$item->id}")->assertNotFound();

    expect($item->fresh()->read_at)->toBeNull();
});

it('never redirects to an external address', function () {
    $me = User::factory()->create();
    $item = notice($me, 'Hello', 'https://evil.example');

    $this->actingAs($me)->get("/notifications/{$item->id}")->assertRedirect(route('dashboard'));
});

it('marks everything as read for the signed-in user only', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    notice($me, 'One');
    notice($me, 'Two');
    notice($other, 'Theirs');

    $this->actingAs($me)->postJson('/notifications/read-all')->assertNoContent();

    expect($me->unreadNotifications()->count())->toBe(0)
        ->and($other->unreadNotifications()->count())->toBe(1);
});

it('renders the unread count in the topbar', function () {
    $me = userWithRole('Regular User');
    notice($me, 'Hello');

    $this->actingAs($me)->get('/dashboard')->assertOk()->assertSee('unread: 1', false);
});

// --- Pembersihan ---

it('prunes old read and very old unread notifications, nothing else', function () {
    $user = User::factory()->create();

    notice($user, 'old read')->forceFill(['read_at' => now()->subDays(31)])->save();
    notice($user, 'fresh read')->forceFill(['read_at' => now()->subDays(29)])->save();
    notice($user, 'old unread')->forceFill(['created_at' => now()->subDays(181)])->save();
    notice($user, 'fresh unread');

    $this->artisan('notifications:prune')->assertSuccessful();

    expect($user->notifications()->get()->map(fn ($n) => $n->data['message'])->sort()->values()->all())
        ->toBe(['fresh read', 'fresh unread']);
});
