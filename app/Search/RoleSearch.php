<?php

namespace App\Search;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

final class RoleSearch extends SearchSource
{
    public function label(): string
    {
        return 'Roles';
    }

    public function attributes(): array
    {
        return ['name', 'description'];
    }

    public function canSearch(User $actor): bool
    {
        return Route::has('roles.index') && $actor->can('viewAny', Role::class);
    }

    public function query(User $actor): Builder
    {
        return Role::query()->orderByDesc('level');
    }

    public function title(Model $record): string
    {
        return $record->name;
    }

    public function details(Model $record): array
    {
        return ['Level' => (string) $record->level];
    }

    public function url(Model $record): string
    {
        return route('roles.index');
    }

    public function sort(): int
    {
        return 20;
    }
}
