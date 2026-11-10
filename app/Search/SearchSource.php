<?php

namespace App\Search;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kelas = satu kelompok hasil di pencarian global (setara "resource" di Filament).
 * Daftarkan di config/admin.php → 'search'. Hanya yang terdaftar yang dicari.
 */
abstract class SearchSource
{
    /** Judul kelompok di daftar hasil. */
    abstract public function label(): string;

    /**
     * Kolom yang dicari. Notasi titik mencari di dalam relasi, mis. 'roles.name'.
     *
     * @return list<string>
     */
    abstract public function attributes(): array;

    /** Siapa yang boleh melihat kelompok ini. Yang tidak berhak tidak pernah melihat satu hasil pun. */
    abstract public function canSearch(User $actor): bool;

    /** Query dasar: eager loading, urutan, dan pembatasan data (mis. visibleTo) ditaruh di sini. */
    abstract public function query(User $actor): Builder;

    abstract public function title(Model $record): string;

    abstract public function url(Model $record): string;

    /**
     * Keterangan di bawah judul: label => nilai. Nilai kosong dilewati.
     *
     * @return array<string, string|null>
     */
    public function details(Model $record): array
    {
        return [];
    }

    public function limit(): int
    {
        return 5;
    }

    /** Kelompok bernilai lebih kecil tampil lebih dulu. */
    public function sort(): int
    {
        return 100;
    }
}
