<?php

namespace App\Livewire\Concerns;

trait Notifies
{
    /** @param 'success'|'info'|'warning'|'danger' $type */
    public function notify(string $message, string $type = 'success', ?string $title = null, ?int $duration = null): void
    {
        $this->dispatch('toast', type: $type, message: $message, title: $title, duration: $duration);
    }
}
