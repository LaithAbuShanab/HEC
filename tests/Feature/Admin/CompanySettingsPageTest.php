<?php

use App\Domain\Access\Role;
use App\Filament\Admin\Pages\ManageCompany;
use App\Models\User;
use App\Settings\CompanySettings;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

function staffMember(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    if ($role === Role::Admin) {
        $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    }

    return $user;
}

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('is reserved for administrators', function (Role $role) {
    $this->actingAs(staffMember($role))->get('/admin/manage-company')->assertForbidden();
})->with([Role::Editor, Role::Sales]);

it('loads the requirement values as unconfirmed defaults', function () {
    $this->actingAs(staffMember(Role::Admin));

    Livewire::test(ManageCompany::class)
        ->assertOk()
        ->assertSchemaStateSet([
            'phone' => '0788878327',
            'whatsapp' => '0781221617',
            'phone_confirmed' => false,
            'social.facebook' => 'https://web.facebook.com/HECjordan',
        ]);
});

it('saves confirmed contact details and hours', function () {
    $this->actingAs(staffMember(Role::Admin));

    Livewire::test(ManageCompany::class)
        ->fillForm([
            'phone_confirmed' => true,
            'whatsapp' => '+962 78 122 1617',
            'whatsapp_confirmed' => true,
            'working_hours' => [['days' => ['saturday', 'sunday', 'monday'], 'opens' => '08:00', 'closes' => '17:00']],
            'working_hours_confirmed' => true,
            'social.instagram' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(CompanySettings::class)->refresh();

    expect($settings->phone_confirmed)->toBeTrue()
        ->and($settings->whatsapp)->toBe('+962 78 122 1617')
        ->and($settings->working_hours)->toHaveCount(1)
        ->and($settings->working_hours[0]['days'])->toBe(['saturday', 'sunday', 'monday'])
        ->and($settings->social)->toBe(['facebook' => 'https://web.facebook.com/HECjordan']);
});

it('validates phones, links, coordinates and hours', function () {
    $this->actingAs(staffMember(Role::Admin));

    Livewire::test(ManageCompany::class)
        ->fillForm([
            'phone' => 'call us',
            'maps_url' => 'http://maps.example.com',
            'latitude' => 51.5,
            'quote_notification_email' => 'not-an-email',
            'working_hours' => [['days' => ['friday'], 'opens' => '17:00', 'closes' => '08:00']],
        ])
        ->call('save')
        ->assertHasFormErrors(['phone', 'maps_url', 'latitude', 'quote_notification_email', 'working_hours.0.closes']);
});

it('shares only confirmed company details with the public site', function () {
    $settings = app(CompanySettings::class);
    $settings->whatsapp_confirmed = true;
    $settings->save();

    $this->get('/ar')->assertInertia(fn (Assert $page) => $page
        ->where('company.name', 'هديب للحديد والمعدات')
        ->where('company.phone', null)
        ->where('company.whatsapp.href', 'https://wa.me/962781221617')
        ->missing('company.quote_notification_email'));
});
