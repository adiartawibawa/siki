<?php

namespace App\Services;

use App\Enums\Ability;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat aturan bisnis Role.
 * Dipakai bersama oleh komponen Livewire dan API controller,
 * sehingga perilaku keduanya selalu identik.
 *
 * Catatan: otorisasi (Policy) dan validasi TIDAK ada di sini;
 * itu tanggung jawab lapisan pemanggil (Livewire / FormRequest).
 */
class RoleService
{
    /** Daftar role + jumlah user & permission, level tertinggi lebih dulu. */
    public function list(): Collection
    {
        return Role::query()
            ->withCount(['users', 'permissions'])
            ->orderByDesc('level')
            ->orderBy('name')
            ->get();
    }

    /**
     * Permission yang boleh dibagikan oleh $actor:
     * hanya yang dipegang $actor sendiri.
     *
     * @return list<string>
     */
    public function grantableAbilities(User $actor): array
    {
        return collect(Ability::cases())
            ->map(fn (Ability $a) => $a->value)
            ->filter(fn (string $p) => $actor->can($p))
            ->values()
            ->all();
    }

    /** Level tertinggi yang boleh dibuat $actor: selalu di bawah levelnya sendiri. */
    public function maxLevel(User $actor): int
    {
        return $actor->isSuperAdmin() ? 99 : max(1, $actor->level() - 1);
    }

    /**
     * Buat atau perbarui role beserta permission-nya dalam satu transaksi.
     *
     * - $data['permissions'] === null / tidak ada  => permission TIDAK diubah.
     * - Permission yang tidak dipegang $actor tidak bisa ditambah,
     *   dan yang sudah ada pada role tidak hilang diam-diam.
     *
     * @param  array{name: string, description?: ?string, level: int|string, permissions?: ?array<string>}  $data
     */
    public function save(User $actor, ?Role $role, array $data): Role
    {
        $grantable = $this->grantableAbilities($actor);

        return DB::transaction(function () use ($role, $data, $grantable) {
            $role ??= new Role(['guard_name' => 'web']);

            $role->fill([
                'name' => trim($data['name']),
                'description' => ($data['description'] ?? null) ?: null,
                'level' => (int) $data['level'],
            ])->save();

            if (($data['permissions'] ?? null) !== null) {
                // Permission lama yang di luar kuasa $actor dipertahankan apa adanya.
                $kept = $role->permissions->pluck('name')
                    ->reject(fn (string $p) => in_array($p, $grantable, true))
                    ->all();

                $chosen = array_values(array_intersect($data['permissions'], $grantable));

                $role->syncPermissions(array_merge($kept, $chosen));
            }

            return $role;
        });
    }

    /** Hapus role. Hak akses dicek lewat Policy oleh pemanggil. */
    public function delete(Role $role): void
    {
        $role->delete();
    }
}
