<?php

use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'api.access', 'throttle:api'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->middleware('abilities:users:read');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('abilities:users:read');

    Route::get('permissions', [PermissionController::class, 'index'])->middleware('abilities:roles:read');
    Route::get('roles', [RoleController::class, 'index'])->middleware('abilities:roles:read');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('abilities:roles:read');

    if (config('admin.api.writes')) {
        Route::post('roles', [RoleController::class, 'store'])->middleware('abilities:roles:write');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('abilities:roles:write');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('abilities:roles:write');
    }
});
