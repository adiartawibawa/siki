<?php

namespace App\Actions\Roles;

use App\Enums\Ability;
use App\Http\Requests\Roles\RoleRules;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class SaveRole
{
    /** @param array{name: string, description?: ?string, level: int|string, permissions?: list<string>} $data */
    public function handle(User $actor, ?Role $role, array $data): Role
    {
        Gate::forUser($actor)->authorize($role ? 'update' : 'create', $role ?? Role::class);

        if ((int) $data['level'] > RoleRules::maxLevel($actor)) {
            throw new AuthorizationException('That level is above your own reach.');
        }

        $grantable = self::grantable($actor);
        $requested = array_values(array_intersect($data['permissions'] ?? [], $grantable));

        // Permission yang tidak dipegang pelaku tidak boleh ditambah, tetapi juga tidak boleh hilang diam-diam.
        $kept = $role
            ? $role->permissions->pluck('name')->reject(fn (string $p) => in_array($p, $grantable, true))->all()
            : [];

        return DB::transaction(function () use ($role, $data, $kept, $requested) {
            $role ??= new Role(['guard_name' => 'web']);

            $role->fill([
                'name' => trim($data['name']),
                'description' => ($data['description'] ?? null) ?: null,
                'level' => (int) $data['level'],
            ])->save();

            $role->syncPermissions([...$kept, ...$requested]);

            return $role->load('permissions');
        });
    }

    /** @return list<string> Permission yang dipegang pelaku, jadi boleh dibagikan. */
    public static function grantable(User $actor): array
    {
        return collect(Ability::values())->filter(fn (string $p) => $actor->can($p))->values()->all();
    }
}
