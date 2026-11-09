<?php

namespace App\Http\Resources;

use App\Enums\Ability;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ability */
class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->value,
            'label' => $this->label(),
            'group' => $this->group(),
            // Apakah pemanggil sendiri memegang permission ini, jadi boleh membagikannya.
            'grantable' => (bool) $request->user()?->can($this->value),
        ];
    }
}
