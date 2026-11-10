<?php

namespace App\Search;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

final class UserSearch extends SearchSource
{
    public function label(): string
    {
        return 'Accounts';
    }

    public function attributes(): array
    {
        return ['first_name', 'last_name', 'username', 'email', 'roles.name'];
    }

    public function canSearch(User $actor): bool
    {
        return Route::has('users.index') && $actor->can('viewAny', User::class);
    }

    /** Akun dinonaktifkan tidak ikut (soft delete); jangkauan mengikuti titik sambung visibleTo(). */
    public function query(User $actor): Builder
    {
        return User::query()->visibleTo($actor)->with('roles')->orderBy('first_name')->orderBy('username');
    }

    public function title(Model $record): string
    {
        return $record->name;
    }

    public function details(Model $record): array
    {
        return ['Username' => '@'.$record->username, 'Role' => $record->primaryRole()?->name];
    }

    /** Core tidak punya halaman detail akun: buka daftar dengan kolom cari terisi. */
    public function url(Model $record): string
    {
        return route('users.index', ['search' => $record->username]);
    }

    public function sort(): int
    {
        return 10;
    }
}
