<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Ability::ViewRoles->value);
    }

    public function create(User $actor): bool
    {
        return $actor->can(Ability::ManageRoles->value)
            && ($actor->isSuperAdmin() || $actor->level() > 1);
    }

    public function update(User $actor, Role $role): bool
    {
        return $actor->can(Ability::ManageRoles->value)
            && ! $role->is_system
            && ($actor->isSuperAdmin() || $role->level < $actor->level());
    }

    public function delete(User $actor, Role $role): bool
    {
        return $this->update($actor, $role) && $role->users()->doesntExist();
    }
}
