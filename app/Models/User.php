<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['first_name', 'last_name', 'username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: $this->username);
    }

    public function primaryRole(): ?Role
    {
        return $this->roles->sortByDesc('level')->first();
    }

    public function level(): int
    {
        return (int) ($this->primaryRole()?->level ?? 0);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SuperAdmin->value);
    }

    public function isLastSuperAdmin(): bool
    {
        return $this->isSuperAdmin()
            && static::role(UserRole::SuperAdmin->value)->count() <= 1;
    }

    /** Hook untuk aplikasi turunan: batasi akun yang terlihat oleh $actor (mis. per unit). Core: semua. */
    public function scopeVisibleTo(Builder $query, User $actor): void
    {
        // Super Admin melihat semua akun, tidak perlu dibatasi
        if ($actor->isSuperAdmin()) {
            return;
        }

        // Selain Super Admin: sembunyikan semua akun yang memiliki role Super Admin
        $query->whereDoesntHave('roles', fn (Builder $roles) => $roles->where('name', UserRole::SuperAdmin->value));
    }

    /** Pasangan scope di atas untuk satu akun. Keduanya harus selalu konsisten. */
    public function isVisibleTo(User $actor): bool
    {
        // Terlihat jika actor adalah Super Admin, atau akun ini bukan Super Admin
        return $actor->isSuperAdmin() || ! $this->isSuperAdmin();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
