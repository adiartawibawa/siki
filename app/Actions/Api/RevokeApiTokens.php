<?php

namespace App\Actions\Api;

use App\Models\User;
use App\Notifications\AccountNotice;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final class RevokeApiTokens
{
    /** Cabut satu token ($tokenId) atau semua token milik $owner. Mengembalikan jumlah yang dicabut. */
    public function handle(User $actor, User $owner, int|string|null $tokenId = null): int
    {
        // Mencabut milik sendiri selalu boleh, juga setelah izin API dicabut.
        if ($actor->isNot($owner)) {
            Gate::forUser($actor)->authorize('manageTokens', $owner);
        }

        $query = $owner->tokens();

        if ($tokenId !== null) {
            $query->whereKey($tokenId);
        }

        $count = $query->delete();

        // Hanya bila orang lain yang mencabut; mencabut milik sendiri tidak perlu diberitahukan.
        if ($count > 0 && $actor->isNot($owner)) {
            $owner->notify(new AccountNotice(
                $count.' API '.Str::plural('token', $count).' '.($count === 1 ? 'was' : 'were').' revoked by an administrator.',
                route('account.api', absolute: false),
            ));
        }

        return $count;
    }
}
