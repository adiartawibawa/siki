<?php

use Database\Seeders\RolePermissionSeeder;

it('renders every showcase example', function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
    $this->actingAs(userWithRole('Admin'));

    expect(view('showcase')->render())->not->toContain('Komponen ini gagal dirender');
});
