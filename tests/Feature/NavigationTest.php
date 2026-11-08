<?php

use App\Support\Navigation;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function navLabels($user): array
{
    return collect(Navigation::for($user))
        ->flatMap(fn ($group) => collect($group['items'])->flatMap(
            fn ($item) => [$item['label'], ...collect($item['children'])->pluck('label')->all()],
        ))->all();
}

it('shows only items whose route exists and that the user may use', function () {
    config(['admin.nav' => [[
        'title' => 'Test',
        'items' => [
            ['label' => 'Real', 'icon' => 'x', 'route' => 'dashboard'],
            ['label' => 'Ghost', 'icon' => 'x', 'route' => 'route.that.does.not.exist'],
            ['label' => 'Gated', 'icon' => 'x', 'route' => 'users.index', 'can' => 'users.view'],
        ],
    ]]]);

    expect(navLabels(userWithRole('Regular User')))->toBe(['Real'])
        ->and(navLabels(userWithRole('Admin')))->toBe(['Real', 'Gated']);
});

it('hides a parent without visible children, and a group without visible items', function () {
    config(['admin.nav' => [[
        'title' => 'Test',
        'items' => [[
            'label' => 'Parent', 'icon' => 'x',
            'children' => [['label' => 'Child', 'route' => 'users.index', 'can' => 'users.view']],
        ]],
    ]]]);

    expect(Navigation::for(userWithRole('Regular User')))->toBe([])
        ->and(navLabels(userWithRole('Operator')))->toBe(['Parent', 'Child']);
});

it('honours the "when" flag', function () {
    config(['admin.flag' => false, 'admin.nav' => [[
        'title' => 'Test',
        'items' => [['label' => 'Flagged', 'icon' => 'x', 'route' => 'dashboard', 'when' => 'admin.flag']],
    ]]]);

    expect(Navigation::for(userWithRole('Admin')))->toBe([]);

    config(['admin.flag' => true]);

    expect(navLabels(userWithRole('Admin')))->toBe(['Flagged']);
});

it('gives a regular user only the dashboard with the default menu', function () {
    expect(navLabels(userWithRole('Regular User')))->toBe(['Dashboard']);
});
