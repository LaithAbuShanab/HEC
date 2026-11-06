<?php

use App\Models\Redirect;

it('normalizes the source path', function (string $input, string $expected) {
    $redirect = Redirect::create(['from_path' => $input, 'to_path' => '/ar/products']);

    expect($redirect->from_path)->toBe($expected);
})->with([
    ['old-products/', '/old-products'],
    ['/Old-Products?page=2', '/old-products'],
    ['https://hec.jo/ar/%D8%B5%D8%A7%D8%AC/', '/ar/صاج'],
]);

it('marks 410 redirects as gone', function () {
    expect(Redirect::create(['from_path' => '/removed', 'status_code' => 410])->isGone())->toBeTrue();
});
