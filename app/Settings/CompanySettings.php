<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CompanySettings extends Settings
{
    public array $name;

    public array $legal_name;

    public bool $legal_name_confirmed;

    public array $tagline;

    public bool $tagline_approved;

    public ?string $phone;

    public bool $phone_confirmed;

    public ?string $whatsapp;

    public bool $whatsapp_confirmed;

    public ?string $public_email;

    public bool $public_email_confirmed;

    public ?string $quote_notification_email;

    public array $address;

    public ?string $maps_url;

    public ?float $latitude;

    public ?float $longitude;

    public bool $location_confirmed;

    public array $working_hours;

    public array $working_hours_note;

    public bool $working_hours_confirmed;

    public array $service_areas;

    public array $social;

    public static function group(): string
    {
        return 'company';
    }
}
