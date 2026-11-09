<?php

namespace App\Enums;

use App\Models\User;

enum ApiScope: string
{
    case UsersRead = 'users:read';
    case RolesRead = 'roles:read';
    case RolesWrite = 'roles:write';

    public function label(): string
    {
        return match ($this) {
            self::UsersRead => 'Read accounts',
            self::RolesRead => 'Read roles and permissions',
            self::RolesWrite => 'Create, edit and delete roles',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UsersRead => 'List accounts you may see and view one account.',
            self::RolesRead => 'List roles and the permissions you can assign.',
            self::RolesWrite => 'Change roles. Only exists when write endpoints are enabled.',
        };
    }

    /** Permission pemilik yang diperlukan agar scope ini ada gunanya. */
    public function requires(): Ability
    {
        return match ($this) {
            self::UsersRead => Ability::ViewUsers,
            self::RolesRead => Ability::ViewRoles,
            self::RolesWrite => Ability::ManageRoles,
        };
    }

    public function writes(): bool
    {
        return $this === self::RolesWrite;
    }

    /** @return list<self> Scope yang boleh dipilih pengguna ini saat ini. */
    public static function availableTo(User $user): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $s) => $user->can($s->requires()->value) && (! $s->writes() || config('admin.api.writes')),
        ));
    }
}
