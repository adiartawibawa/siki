<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

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
