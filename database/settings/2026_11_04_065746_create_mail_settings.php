<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.from_name', config('app.name'));
        $this->migrator->add('mail.from_address', 'no-reply@example.com');
        $this->migrator->add('mail.reply_to', null);
    }
};
