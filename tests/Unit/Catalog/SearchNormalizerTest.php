<?php

use App\Domain\Catalog\Support\SearchNormalizer;

it('unifies hamza forms of alef', function (string $input) {
    expect(SearchNormalizer::normalize($input))->toBe('اسطوانه');
})->with(['أسطوانة', 'إسطوانة', 'آسطوانة', 'اسطوانه']);

it('maps taa marbuta and alef maqsura', function () {
    expect(SearchNormalizer::normalize('ماسورة'))->toBe('ماسوره')
        ->and(SearchNormalizer::normalize('مستوى'))->toBe('مستوي');
});

it('strips tashkeel and tatweel', function () {
    expect(SearchNormalizer::normalize('صَاجٌ مُجَلْفَـــن'))->toBe('صاج مجلفن');
});

it('converts arabic-indic and persian digits to latin', function () {
    expect(SearchNormalizer::normalize('سلك ٥٫٥ ملم'))->toBe('سلك 5.5 ملم')
        ->and(SearchNormalizer::normalize('۱۲'))->toBe('12');
});

it('lowercases latin text and collapses whitespace', function () {
    expect(SearchNormalizer::normalize("  Galvanized   STEEL\tCoils "))->toBe('galvanized steel coils');
});

it('expands a query with synonym groups', function () {
    $groups = [['مجلفن', 'مقلفن', 'galvanized'], ['رول', 'رولات', 'coil']];

    expect(SearchNormalizer::expand('مقلفن', $groups))
        ->toContain('مقلفن', 'مجلفن', 'galvanized')
        ->not->toContain('رول');
});

it('leaves the original value untouched', function () {
    $original = 'أسطوانات قص';
    SearchNormalizer::normalize($original);

    expect($original)->toBe('أسطوانات قص');
});
