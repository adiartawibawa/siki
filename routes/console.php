<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:prune', function () {
    $deleted = DatabaseNotification::query()
        ->where(function ($q) {
            // Sudah dibaca lebih dari 30 hari, atau tidak pernah dibaca lebih dari 180 hari.
            $q->where('read_at', '<', now()->subDays(30))
                ->orWhere(fn ($q) => $q->whereNull('read_at')->where('created_at', '<', now()->subDays(180)));
        })->delete();

    $this->info("Pruned {$deleted} notifications.");
})->purpose('Delete old notifications');

Schedule::command('notifications:prune')->daily();
