<?php

namespace Database\Seeders;

use App\Enums\Ability;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (Ability::cases() as $ability) {
            Permission::findOrCreate($ability->value, 'web');
        }

        // Event model bisa dimatikan (WithoutModelEvents), jadi cache dibersihkan manual.
        $registrar->forgetCachedPermissions();

        foreach (UserRole::cases() as $case) {
            $role = Role::firstOrNew(['name' => $case->value, 'guard_name' => 'web']);
            $isNew = ! $role->exists;

            // Atribut struktural role sistem selalu mengikuti kode.
            $role->fill([
                'level' => $case->level(),
                'is_system' => true,
                'description' => $case->description(),
            ])->save();

            // Permission hanya diisi saat role baru dibuat, agar tidak menimpa hasil edit di UI.
            // Super Admin pengecualian: permission-nya mengikuti kode.
            if ($isNew || $case === UserRole::SuperAdmin) {
                $role->syncPermissions(array_map(fn (Ability $a) => $a->value, $case->abilities()));
            }
        }

        $email = config('admin.initial.email');

        if ($email && $password = config('admin.initial.password')) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['first_name' => 'Admin', 'username' => 'admin', 'password' => $password],
            );
            $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
            $user->syncRoles([UserRole::SuperAdmin->value]);
        }

        // Cache hubungan permission-role harus segar saat aplikasi pertama kali dipakai.
        $registrar->forgetCachedPermissions();
    }
}
