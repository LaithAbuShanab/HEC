<?php

use App\Models\Redirect;
use Inertia\Testing\AssertableInertia as Assert;

it('renders a branded 404 in the language of the url', function (string $locale) {
    $this->get("/{$locale}/does-not-exist")
        ->assertNotFound()
        ->assertHeader('X-Robots-Tag', 'noindex')
        ->assertSee("lang=\"{$locale}\"", false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('error')
            ->where('status', 404)
            ->where('seo.robots', 'noindex, follow')
            ->has('nav')
            ->has('ui.errors'));
})->with(['ar', 'en']);

it('renders a branded 410 for permanently removed pages', function () {
    Redirect::create(['from_path' => '/ar/products/old-item', 'status_code' => 410]);

    $this->get('/ar/products/old-item')->assertStatus(410)->assertInertia(fn (Assert $page) => $page->component('error')->where('status', 410));
});

it('falls back to arabic for urls without a locale', function () {
    $this->get('/missing-page')->assertNotFound()->assertSee('lang="ar"', false);
});

it('leaves admin errors to filament', function () {
    $this->get('/admin/does-not-exist')->assertNotFound()->assertDontSee('data-page', false);
});
