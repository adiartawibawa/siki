<?php

namespace App\Actions\Api;

use App\Enums\Ability;
use App\Enums\ApiScope;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateApiToken
{
    public const MAX_TOKENS = 5;

    /** Masa berlaku yang boleh dipilih, dalam hari. Tidak ada "selamanya". */
    public const LIFETIMES = [30, 90, 180];

    /**
     * @param  list<string>  $scopes
     * @return string Token mentah; hanya ada saat ini.
     */
    public function handle(User $actor, string $name, array $scopes, int $days): string
    {
        Gate::forUser($actor)->authorize(Ability::ApiAccess->value);

        $allowed = array_map(fn (ApiScope $s) => $s->value, ApiScope::availableTo($actor));
        $scopes = array_values(array_intersect($scopes, $allowed));

        if ($scopes === []) {
            throw ValidationException::withMessages(['scopes' => 'Choose at least one scope you are allowed to use.']);
        }

        if (! in_array($days, self::LIFETIMES, true)) {
            throw ValidationException::withMessages(['days' => 'Choose one of the available lifetimes.']);
        }

        if ($actor->tokens()->count() >= self::MAX_TOKENS) {
            throw ValidationException::withMessages(['name' => 'You already have '.self::MAX_TOKENS.' tokens. Revoke one first.']);
        }

        return $actor->createToken(trim($name), $scopes, now()->addDays($days))->plainTextToken;
    }
}
