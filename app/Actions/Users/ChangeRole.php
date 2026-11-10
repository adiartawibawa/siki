<?php

namespace App\Actions\Users;

use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountNotice;
use Illuminate\Support\Facades\Gate;

final class ChangeRole
{
    public function handle(User $actor, User $target, Role $role): void
    {
        Gate::forUser($actor)->authorize('assignRole', [$target, $role]);

        $target->syncRoles([$role]);

        // Pelaku tidak boleh mengubah rolenya sendiri, tetapi penjagaan ini murah dan menjaga arti pesannya.
        if ($actor->isNot($target)) {
            $target->notify(new AccountNotice("Your role is now {$role->name}.", route('account.details', absolute: false)));
        }
    }
}
