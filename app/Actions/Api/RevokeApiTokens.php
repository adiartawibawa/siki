<?php

namespace App\Actions\Api;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

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

        return $query->delete();
    }
}
