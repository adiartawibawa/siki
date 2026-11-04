<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public string $from_name;

    public string $from_address;

    public ?string $reply_to;

    public static function group(): string
    {
        return 'mail';
    }
}
