<?php

namespace App\Domain\Company;

use App\Domain\Shared\Support\PhoneNumber;
use App\Settings\CompanySettings;

final class CompanyProfile
{
    public function __construct(private readonly CompanySettings $settings) {}

    public function toArray(string $locale): array
    {
        $settings = $this->settings;

        return [
            'name' => $this->translated($settings->name, $locale),
            'legalName' => $settings->legal_name_confirmed ? $this->translated($settings->legal_name, $locale) : null,
            'tagline' => $settings->tagline_approved ? $this->translated($settings->tagline, $locale) : null,
            'phone' => $settings->phone_confirmed ? $this->phone($settings->phone, PhoneNumber::telUrl(...)) : null,
            'whatsapp' => $settings->whatsapp_confirmed ? $this->phone($settings->whatsapp, fn (string $number) => PhoneNumber::whatsappUrl($number)) : null,
            'email' => $settings->public_email_confirmed ? $settings->public_email : null,
            'address' => $settings->location_confirmed ? $this->translated($settings->address, $locale) : null,
            'mapsUrl' => $settings->location_confirmed ? $settings->maps_url : null,
            'geo' => $settings->location_confirmed && $settings->latitude !== null && $settings->longitude !== null
                ? ['lat' => $settings->latitude, 'lng' => $settings->longitude]
                : null,
            'hours' => $settings->working_hours_confirmed && $settings->working_hours !== [] ? array_values($settings->working_hours) : null,
            'hoursNote' => $settings->working_hours_confirmed ? $this->translated($settings->working_hours_note, $locale) : null,
            'serviceAreas' => $this->translated($settings->service_areas, $locale),
            'social' => array_filter($settings->social, fn ($url) => filled($url)),
        ];
    }

    private function translated(array $values, string $locale): ?string
    {
        $value = $values[$locale] ?? null;

        return filled($value) ? $value : null;
    }

    private function phone(?string $number, callable $href): ?array
    {
        if (blank($number)) {
            return null;
        }

        $link = $href($number);

        return $link === null ? null : ['display' => $number, 'href' => $link];
    }
}
