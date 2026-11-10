<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', config('app.name'));
        $this->migrator->add('general.site_description', null);
        $this->migrator->add('general.contact_email', 'admin@example.com');
        $this->migrator->add('general.timezone', 'UTC');
        $this->migrator->add('general.locale', 'en');
        $this->migrator->add('general.landing_enabled', true);
        $this->migrator->add('general.registration_enabled', false);
    }
};
