<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $level
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Role extends SpatieRole
{
    protected function casts(): array
    {
        return ['level' => 'integer', 'is_system' => 'boolean'];
    }

    /** Warna badge untuk <x-ui.badge>. */
    public function tone(): string
    {
        return match (true) {
            $this->level >= 100 => 'brand',
            $this->level >= 80 => 'success',
            $this->level >= 50 => 'warning',
            default => 'neutral',
        };
    }
}
