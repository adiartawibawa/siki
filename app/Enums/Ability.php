<?php

namespace App\Enums;

enum Ability: string
{
    case ViewUsers = 'users.view';
    case CreateUsers = 'users.create';
    case UpdateUsers = 'users.update';
    case DeleteUsers = 'users.delete';
    case RestoreUsers = 'users.restore';
    case ViewRoles = 'roles.view';
    case ManageRoles = 'roles.manage';
    case ManageSettings = 'manage-settings';
    case ApiAccess = 'api.access';
    case ManageApiTokens = 'api.tokens.manage';

    public function label(): string
    {
        return match ($this) {
            self::ViewUsers => 'View users',
            self::CreateUsers => 'Add users',
            self::UpdateUsers => 'Edit users',
            self::DeleteUsers => 'Deactivate users',
            self::RestoreUsers => 'Restore users',
            self::ViewRoles => 'View roles',
            self::ManageRoles => 'Create and edit roles',
            self::ManageSettings => 'Change application settings',
            self::ApiAccess => 'Use the API with personal tokens',
            self::ManageApiTokens => 'Revoke API tokens of other accounts',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::ViewUsers, self::CreateUsers, self::UpdateUsers, self::DeleteUsers, self::RestoreUsers => 'Users',
            self::ViewRoles, self::ManageRoles => 'Roles',
            self::ManageSettings => 'Settings',
            self::ApiAccess, self::ManageApiTokens => 'API',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $a) => $a->value, self::cases());
    }
}
