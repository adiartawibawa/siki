<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'Super Admin';
    case Admin = 'Admin';
    case Operator = 'Operator';
    case RegularUser = 'Regular User';

    public function level(): int
    {
        return match ($this) {
            self::SuperAdmin => 100,
            self::Admin => 80,
            self::Operator => 50,
            self::RegularUser => 10,
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Full access, including other super admins.',
            self::Admin => 'Manages users below Admin and application settings.',
            self::Operator => 'Manages regular users.',
            self::RegularUser => 'Regular account with no administrative access.',
        };
    }

    /** @return list<Ability> */
    public function abilities(): array
    {
        $users = [Ability::ViewUsers, Ability::CreateUsers, Ability::UpdateUsers, Ability::DeleteUsers, Ability::RestoreUsers];

        return match ($this) {
            self::SuperAdmin => Ability::cases(),
            self::Admin => [...$users, Ability::ManageSettings],
            self::Operator => $users,
            self::RegularUser => [],
        };
    }
}
