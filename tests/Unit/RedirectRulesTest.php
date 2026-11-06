<?php

use App\Domain\Seo\RedirectRules;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('validates the old path', function () {
    Redirect::create(['from_path' => '/taken', 'to_path' => '/ar']);

    expect(RedirectRules::fromPathError('old'))->toBe('format')
        ->and(RedirectRules::fromPathError('/admin/users'))->toBe('protected')
        ->and(RedirectRules::fromPathError('/Taken/'))->toBe('duplicate')
        ->and(RedirectRules::fromPathError('/fresh'))->toBeNull();
});

it('validates the target', function () {
    Redirect::create(['from_path' => '/middle', 'to_path' => '/ar/final']);

    expect(RedirectRules::toPathError('ftp://x', '/old'))->toBe('format')
        ->and(RedirectRules::toPathError('//evil.example', '/old'))->toBe('format')
        ->and(RedirectRules::toPathError('/old/', '/old'))->toBe('same')
        ->and(RedirectRules::toPathError('/middle', '/old'))->toBe('chain')
        ->and(RedirectRules::toPathError('/ar/final', '/old'))->toBeNull()
        ->and(RedirectRules::toPathError('https://web.facebook.com/HECjordan', '/old'))->toBeNull();
});
