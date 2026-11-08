<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    /** Daftar role. */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Role::class);

        return RoleResource::collection($this->roles->list());
    }

    /** Detail satu role beserta permission-nya. */
    public function show(Role $role): RoleResource
    {
        // RolePolicy tidak punya method `view`; melihat satu role cukup dengan hak viewAny.
        Gate::authorize('viewAny', Role::class);

        return new RoleResource($role->load('permissions'));
    }

    /** Buat role baru. */
    public function store(SaveRoleRequest $request): JsonResponse
    {
        $role = $this->roles->save($request->user(), null, $request->validated());

        return (new RoleResource($role->load('permissions')))
            ->response()
            ->setStatusCode(201);
    }

    /** Perbarui role. Jika `permissions` tidak dikirim, permission tidak diubah. */
    public function update(SaveRoleRequest $request, Role $role): RoleResource
    {
        $role = $this->roles->save($request->user(), $role, $request->validated());

        return new RoleResource($role->load('permissions'));
    }

    /** Hapus role. */
    public function destroy(Role $role): Response
    {
        Gate::authorize('delete', $role);

        $this->roles->delete($role);

        return response()->noContent();
    }
}
