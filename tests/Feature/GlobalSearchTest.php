<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function groupTitles(array $groups): array
{
    return collect($groups)->pluck('title')->all();
}

it('requires a signed-in user', function () {
    $this->getJson('/search?q=users')->assertUnauthorized();
});

it('stays quiet for terms shorter than two characters', function () {
    $this->actingAs(userWithRole('Admin'))->getJson('/search?q=a')
        ->assertOk()
        ->assertExactJson(['groups' => []]);
});

it('hides pages the user cannot open', function (string $role, array $labels) {
    $groups = $this->actingAs(userWithRole($role))->getJson('/search?q=settings')->assertOk()->json('groups');
    $pages = collect($groups)->firstWhere('title', 'Pages')['items'] ?? [];

    expect(collect($pages)->pluck('label')->sort()->values()->all())->toBe($labels);
})->with([
    ['Admin', ['Account settings', 'Settings › General', 'Settings › Mail']],
    ['Operator', ['Account settings']],
]);

it('gives a regular user pages only', function () {
    $groups = $this->actingAs(userWithRole('Regular User'))->getJson('/search?q=account')->assertOk()->json('groups');

    expect(groupTitles($groups))->toBe(['Pages']);
});

it('finds accounts by name, username and email', function (string $term) {
    User::factory()->create([
        'first_name' => 'Ayu', 'last_name' => 'Lestari', 'username' => 'ayu.lestari', 'email' => 'ayu@example.test',
    ])->assignRole('Regular User');

    $groups = $this->actingAs(userWithRole('Admin'))->getJson('/search?q='.urlencode($term))->assertOk()->json('groups');
    $accounts = collect($groups)->firstWhere('title', 'Accounts');

    expect(collect($accounts['items'])->pluck('label'))->toContain('Ayu Lestari')
        ->and(collect($accounts['items'])->firstWhere('label', 'Ayu Lestari')['href'])->toContain('search=ayu.lestari');
})->with(['Lestari', 'ayu.lest', 'ayu@example']);

it('never returns deactivated accounts', function () {
    User::factory()->create(['username' => 'zz.gone'])->delete();

    $groups = $this->actingAs(userWithRole('Admin'))->getJson('/search?q=zz.gone')->assertOk()->json('groups');

    expect(groupTitles($groups))->not->toContain('Accounts');
});

it('shows roles only to those who may view them', function (string $role, bool $sees) {
    $groups = $this->actingAs(userWithRole($role))->getJson('/search?q=admin')->assertOk()->json('groups');

    expect(in_array('Roles', groupTitles($groups), true))->toBe($sees);
})->with([['Super Admin', true], ['Admin', false], ['Operator', false]]);

it('treats % and _ literally', function () {
    User::factory()->create(['username' => 'abc.def']);
    $admin = userWithRole('Admin');

    $accounts = fn (string $q) => collect(
        $this->actingAs($admin)->getJson('/search?q='.urlencode($q))->json('groups')
    )->firstWhere('title', 'Accounts');

    expect($accounts('%%'))->toBeNull()
        ->and($accounts('abc_def'))->toBeNull();
});

it('caps each group at five results', function () {
    User::factory()->count(8)->create(['last_name' => 'Pratama']);

    $groups = $this->actingAs(userWithRole('Admin'))->getJson('/search?q=Pratama')->assertOk()->json('groups');

    expect(collect($groups)->firstWhere('title', 'Accounts')['items'])->toHaveCount(5);
});
