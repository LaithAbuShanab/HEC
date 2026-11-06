<?php

use Inertia\Testing\AssertableInertia as Assert;

it('serves both locales under their own prefix', function (string $locale, string $direction) {
    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee("lang=\"{$locale}\"", false)
        ->assertSee("dir=\"{$direction}\"", false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('home')
            ->where('locale', $locale)
            ->where('direction', $direction));
})->with([['ar', 'rtl'], ['en', 'ltr']]);

it('shares alternate urls that keep the same page', function () {
    $this->get('/ar')->assertInertia(fn (Assert $page) => $page
        ->where('alternates.ar', url('/ar'))
        ->where('alternates.en', url('/en')));
});

it('shows a language page at the root without geo redirects', function () {
    $this->get('/', ['Accept-Language' => 'en-US'])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('language-select'));
});

it('rejects unsupported locales', function () {
    $this->get('/fr')->assertNotFound();
});

it('generates routes with the active locale by default', function () {
    $this->get('/en');

    expect(route('home'))->toBe(url('/en'));
});
