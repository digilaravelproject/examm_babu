<?php
declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public string $host;
    public string $port;
    public string $username;
    public string $password;
    public string $encryption;
    public string $from_address;
    public string $from_name;

    public static function group(): string
    {
        return 'email';
    }
}
