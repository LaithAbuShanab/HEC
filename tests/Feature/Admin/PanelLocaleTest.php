<?php

use App\Domain\Access\Role;
use App\Filament\Admin\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Events\Login;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function panelUser(string $locale = 'ar'): User
{
    $user = User::factory()->create(['locale' => $locale]);
    $user->assignRole(Role::Editor->value);

    return $user;
}

it('opens the panel in the language each user chose', function () {
    $this->actingAs(panelUser('ar'))->get('/admin')->assertOk()->assertSee('lang="ar"', false)->assertSee('dir="rtl"', false);
    $this->actingAs(panelUser('en'))->get('/admin')->assertOk()->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
});

it('switches the language from the top bar and remembers it', function () {
    $user = panelUser('ar');

    $this->actingAs($user)
        ->from('/admin/products')
        ->post('/admin/locale/en')
        ->assertRedirect('/admin/products');

    expect($user->fresh()->locale)->toBe('en');

    $this->actingAs($user->fresh())->get('/admin/products')
        ->assertSee('lang="en"', false)
        ->assertSee('Products')
        ->assertSee('Catalog')
        ->assertSee('Content')
        ->assertDontSee('الكتالوج')
        ->assertDontSee('المحتوى');
});

it('shows the switch on every panel page and on the login screen', function () {
    $this->actingAs(panelUser())->get('/admin')->assertSee(route('admin.locale.switch', 'en'), false);

    auth()->logout();
    $this->get('/admin/login')->assertSee(route('admin.locale.switch', 'en'), false);
});

it('lets guests switch the login screen language', function () {
    $this->post('/admin/locale/en')->assertRedirect('/admin');

    $this->get('/admin/login')->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
});

it('never redirects outside the panel after switching', function () {
    $this->actingAs(panelUser())->from('https://evil.example/phish')->post('/admin/locale/en')->assertRedirect('/admin');
});

it('rejects unsupported languages', function () {
    $this->actingAs(panelUser())->post('/admin/locale/fr')->assertNotFound();
});

it('renders labels, statuses and record names in the selected language', function () {
    $this->actingAs(panelUser('en'));
    app()->setLocale('en');
    $product = Product::where('slug', 'galvanized-steel-coils')->first();

    Livewire::test(ListProducts::class)
        ->assertSee('Pending approval')
        ->assertSee('Needs confirmation')
        ->assertTableColumnStateSet('name', 'Galvanized Steel Coils', $product);
});

it('keeps the language chosen on the login screen after signing in', function () {
    $user = panelUser('ar');

    $this->post('/admin/locale/en');
    event(new Login('web', $user, false));

    expect($user->fresh()->locale)->toBe('en');
    $this->actingAs($user->fresh())->get('/admin')->assertSee('lang="en"', false);
});
