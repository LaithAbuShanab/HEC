<?php

use App\Domain\Access\Role;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function staff(?Role $role, bool $active = true): User
{
    $user = User::factory()->create(['is_active' => $active]);

    if ($role) {
        $user->assignRole($role->value);
    }

    return $user;
}

it('lets every active staff role into the panel', function (Role $role) {
    expect(staff($role)->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
})->with(Role::cases());

it('keeps users without a role or inactive users out of the panel', function () {
    $panel = Filament::getPanel('admin');

    expect(staff(null)->canAccessPanel($panel))->toBeFalse()
        ->and(staff(Role::Admin, active: false)->canAccessPanel($panel))->toBeFalse();
});

it('redirects guests to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
});

it('denies the panel to a logged-in user without a role', function () {
    $this->actingAs(staff(null))->get('/admin')->assertForbidden();
});

it('gives editors the catalog but not quote requests or users', function () {
    $editor = staff(Role::Editor);

    expect($editor->can('viewAny', Product::class))->toBeTrue()
        ->and($editor->can('update', Product::class))->toBeTrue()
        ->and($editor->can('forceDelete', Product::class))->toBeFalse()
        ->and($editor->can('viewAny', QuoteRequest::class))->toBeFalse()
        ->and($editor->can('viewAny', User::class))->toBeFalse();
});

it('gives sales quote requests and export but not the catalog', function () {
    $sales = staff(Role::Sales);

    expect($sales->can('viewAny', QuoteRequest::class))->toBeTrue()
        ->and($sales->can('export', QuoteRequest::class))->toBeTrue()
        ->and($sales->can('delete', QuoteRequest::class))->toBeFalse()
        ->and($sales->can('update', Product::class))->toBeFalse()
        ->and($sales->can('viewAny', User::class))->toBeFalse();
});

it('gives active admins everything and inactive admins nothing', function () {
    expect(staff(Role::Admin)->can('viewAny', User::class))->toBeTrue()
        ->and(staff(Role::Admin)->can('export', QuoteRequest::class))->toBeTrue()
        ->and(staff(Role::Admin, active: false)->can('viewAny', User::class))->toBeFalse();
});

it('forces staff without an authenticator to set one up before using the panel', function (Role $role) {
    $user = User::factory()->withoutMfa()->create();
    $user->assignRole($role->value);

    $this->actingAs($user)->get('/admin')->assertRedirect('/admin/multi-factor-authentication/set-up');
    $this->actingAs($user)->get('/admin/products')->assertRedirect('/admin/multi-factor-authentication/set-up');
})->with(Role::cases());

it('lets staff with an authenticator into the panel', function () {
    $user = staff(Role::Editor);

    expect(filled($user->getAppAuthenticationSecret()))->toBeTrue();
    $this->actingAs($user)->get('/admin')->assertOk();
});

it('creates staff users from the console', function () {
    $this->artisan('hec:create-user', ['--role' => 'sales'])
        ->expectsQuestion('Name', 'Sales Person')
        ->expectsQuestion('Email', 'sales@example.com')
        ->expectsQuestion('Password', 'a-strong-password-1')
        ->assertSuccessful();

    expect(User::where('email', 'sales@example.com')->first()->hasRole('sales'))->toBeTrue();
});
