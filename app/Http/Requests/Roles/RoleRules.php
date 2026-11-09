<?php

namespace App\Http\Requests\Roles;

use App\Enums\Ability;
use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class RoleRules
{
    /** Level tertinggi yang boleh dibuat pengguna ini: selalu di bawah levelnya sendiri. */
    public static function maxLevel(User $actor): int
    {
        return $actor->isSuperAdmin() ? 99 : max(1, $actor->level() - 1);
    }

    /** @return array<string, list<mixed>> */
    public static function for(User $actor, ?Role $editing = null): array
    {
        $max = self::maxLevel($actor);

        return [
            'name' => ['required', 'string', 'min:2', 'max:50', function (string $attribute, mixed $value, Closure $fail) use ($editing) {
                $taken = Role::query()
                    ->whereRaw('lower(name) = ?', [Str::lower(trim((string) $value))])
                    ->when($editing, fn ($q) => $q->whereKeyNot($editing->id))
                    ->exists();

                if ($taken) {
                    $fail('This role name is already in use.');
                }
            }],
            'description' => ['nullable', 'string', 'max:160'],
            // Rentang 1-99 tertulis literal agar terbaca di dokumentasi; batas milik pelaku diperiksa di closure.
            'level' => ['required', 'integer', 'min:1', 'max:99', function (string $attribute, mixed $value, Closure $fail) use ($max) {
                if ((int) $value > $max) {
                    $fail("Level can be at most {$max} for your account.");
                }
            }],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::enum(Ability::class)],
        ];
    }
}
