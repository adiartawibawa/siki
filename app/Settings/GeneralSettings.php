<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public ?string $site_description;

    public string $contact_email;

    public string $timezone;

    public string $locale;

    public bool $landing_enabled;

    public bool $registration_enabled;

    public static function group(): string
    {
        return 'general';
    }
}
