<?php

use App\Models\Redirect;

it('permanently redirects an old path and counts the hit', function () {
    $redirect = Redirect::create(['from_path' => '/old-products', 'to_path' => '/ar/products']);

    $this->get('/old-products/')->assertStatus(301)->assertRedirect('/ar/products');

    expect($redirect->fresh()->hits)->toBe(1)
        ->and($redirect->fresh()->last_hit_at)->not->toBeNull();
});

it('keeps the query string when redirecting', function () {
    Redirect::create(['from_path' => '/catalog', 'to_path' => '/en/products']);

    $this->get('/catalog?utm_source=facebook')->assertRedirect('/en/products?utm_source=facebook');
});

it('supports temporary redirects and absolute targets', function () {
    Redirect::create(['from_path' => '/facebook', 'to_path' => 'https://web.facebook.com/HECjordan', 'status_code' => 302]);

    $this->get('/facebook')->assertStatus(302)->assertRedirect('https://web.facebook.com/HECjordan');
});

it('answers 410 gone for permanently removed pages', function () {
    Redirect::create(['from_path' => '/discontinued-item', 'status_code' => 410]);

    $this->get('/discontinued-item')->assertStatus(410)->assertHeader('X-Robots-Tag', 'noindex');
});

it('matches arabic paths', function () {
    Redirect::create(['from_path' => '/ar/صاج', 'to_path' => '/ar/products/sheets-coils']);

    $this->get('/ar/'.rawurlencode('صاج'))->assertRedirect('/ar/products/sheets-coils');
});

it('ignores inactive redirects and non-get requests', function () {
    Redirect::create(['from_path' => '/ar', 'to_path' => '/en', 'is_active' => false]);
    Redirect::create(['from_path' => '/contact-form', 'to_path' => '/ar/contact']);

    $this->get('/ar')->assertOk();
    $this->post('/contact-form')->assertNotFound();
});

it('never redirects the admin panel', function () {
    Redirect::create(['from_path' => '/admin/login', 'to_path' => '/ar']);

    $this->get('/admin/login')->assertOk();
});

it('picks up new redirects immediately', function () {
    $this->get('/brand-new-old-url')->assertNotFound();

    Redirect::create(['from_path' => '/brand-new-old-url', 'to_path' => '/ar']);

    $this->get('/brand-new-old-url')->assertRedirect('/ar');
});
