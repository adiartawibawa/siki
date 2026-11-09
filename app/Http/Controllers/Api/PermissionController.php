<?php

namespace App\Http\Controllers\Api;

use App\Enums\Ability;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Role;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Roles')]
class PermissionController extends Controller
{
    /** All permissions that can be assigned to a role. */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Role::class);

        return PermissionResource::collection(Ability::cases());
    }
}
