<?php

use App\Domain\Company\CompanyProfile;
use App\Settings\CompanySettings;

function settings(array $overrides = []): CompanySettings
{
    $settings = app(CompanySettings::class);

    foreach ($overrides as $key => $value) {
        $settings->{$key} = $value;
    }

    $settings->save();

    return $settings;
}

it('hides every contact detail the client has not confirmed yet', function () {
    $profile = app(CompanyProfile::class)->toArray('ar');

    expect($profile['name'])->toBe('هديب للحديد والمعدات')
        ->and($profile['phone'])->toBeNull()
        ->and($profile['whatsapp'])->toBeNull()
        ->and($profile['email'])->toBeNull()
        ->and($profile['address'])->toBeNull()
        ->and($profile['hours'])->toBeNull()
        ->and($profile['tagline'])->toBeNull()
        ->and($profile['legalName'])->toBeNull();
});

it('exposes confirmed contact details with ready-made links', function () {
    settings(['phone_confirmed' => true, 'whatsapp_confirmed' => true, 'public_email_confirmed' => true]);

    $profile = app(CompanyProfile::class)->toArray('en');

    expect($profile['phone'])->toBe(['display' => '0788878327', 'href' => 'tel:+962788878327'])
        ->and($profile['whatsapp'])->toBe(['display' => '0781221617', 'href' => 'https://wa.me/962781221617'])
        ->and($profile['email'])->toBe('hdaibahmad8@gmail.com');
});

it('exposes the location and opening hours only once confirmed', function () {
    settings([
        'location_confirmed' => true,
        'maps_url' => 'https://maps.app.goo.gl/example',
        'latitude' => 31.9539,
        'longitude' => 35.9106,
        'working_hours' => [['days' => ['saturday', 'sunday'], 'opens' => '08:00', 'closes' => '17:00']],
        'working_hours_confirmed' => true,
    ]);

    $profile = app(CompanyProfile::class)->toArray('en');

    expect($profile['address'])->toBe('Amman — Al-Rujeib')
        ->and($profile['geo'])->toBe(['lat' => 31.9539, 'lng' => 35.9106])
        ->and($profile['hours'])->toBe([['days' => ['saturday', 'sunday'], 'opens' => '08:00', 'closes' => '17:00']]);
});

it('never exposes the internal quote notification email', function () {
    settings(['quote_notification_email' => 'sales-inbox@example.com', 'public_email_confirmed' => true]);

    expect(json_encode(app(CompanyProfile::class)->toArray('ar')))->not->toContain('sales-inbox@example.com');
});

it('drops empty social links', function () {
    settings(['social' => ['facebook' => 'https://web.facebook.com/HECjordan', 'instagram' => '', 'youtube' => null]]);

    expect(app(CompanyProfile::class)->toArray('ar')['social'])->toBe(['facebook' => 'https://web.facebook.com/HECjordan']);
});
