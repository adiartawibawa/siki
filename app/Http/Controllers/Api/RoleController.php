<?php

namespace App\Http\Controllers\Api;

use App\Actions\Roles\DeleteRole;
use App\Actions\Roles\SaveRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Roles')]
class RoleController extends Controller
{
    /** List roles, highest level first. */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Role::class);

        return RoleResource::collection(
            Role::query()->withCount('users')->orderByDesc('level')->orderBy('name')->get()
        );
    }

    /** Show one role with its permissions. */
    public function show(Role $role): RoleResource
    {
        Gate::authorize('viewAny', Role::class);

        return new RoleResource($role->loadCount('users')->load('permissions'));
    }

    /** Create a role. Only registered when ADMIN_API_WRITES is on. */
    public function store(StoreRoleRequest $request, SaveRole $save): JsonResponse
    {
        $role = $save->handle($request->user(), null, $request->validated());

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    /** Update a role. Only registered when ADMIN_API_WRITES is on. */
    public function update(UpdateRoleRequest $request, Role $role, SaveRole $save): RoleResource
    {
        return new RoleResource($save->handle($request->user(), $role, $request->validated()));
    }

    /** Delete a role that has no users. Only registered when ADMIN_API_WRITES is on. */
    public function destroy(Request $request, Role $role, DeleteRole $delete): Response
    {
        $delete->handle($request->user(), $role);

        return response()->noContent();
    }
}
