<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Ability::ViewUsers->value);
    }

    public function view(User $actor, User $target): bool
    {
        return $this->viewAny($actor) && $target->isVisibleTo($actor);
    }

    public function manageTokens(User $actor, User $target): bool
    {
        return $actor->can(Ability::ManageApiTokens->value) && $this->manages($actor, $target);
    }

    public function create(User $actor): bool
    {
        return $actor->can(Ability::CreateUsers->value);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->can(Ability::UpdateUsers->value) && $this->manages($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        return $actor->can(Ability::DeleteUsers->value)
            && $this->manages($actor, $target)
            && ! $target->isLastSuperAdmin();
    }

    public function restore(User $actor, User $target): bool
    {
        return $actor->can(Ability::RestoreUsers->value) && $this->manages($actor, $target);
    }

    public function grantRole(User $actor, Role $role): bool
    {
        return $actor->can(Ability::CreateUsers->value) && $this->mayAssign($actor, $role);
    }

    public function assignRole(User $actor, User $target, Role $role): bool
    {
        return $actor->can(Ability::UpdateUsers->value)
            && $this->manages($actor, $target)
            && $this->mayAssign($actor, $role)
            && ! ($target->isLastSuperAdmin() && $role->name !== UserRole::SuperAdmin->value);
    }

    /** Tidak pernah pada diri sendiri (itu lewat halaman Account). Selebihnya hanya ke level yang lebih rendah. */
    private function manages(User $actor, User $target): bool
    {
        if ($actor->is($target) || ! $target->isVisibleTo($actor)) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->level() > $target->level();
    }

    private function mayAssign(User $actor, Role $role): bool
    {
        return $actor->isSuperAdmin() || $role->level < $actor->level();
    }
}
