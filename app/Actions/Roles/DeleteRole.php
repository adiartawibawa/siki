<?php

namespace App\Actions\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class DeleteRole
{
    public function handle(User $actor, Role $role): void
    {
        Gate::forUser($actor)->authorize('delete', $role);

        $role->delete();
    }
}
