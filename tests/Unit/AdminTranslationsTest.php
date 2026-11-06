<?php

function flattenKeys(array $values, string $prefix = ''): array
{
    $keys = [];

    foreach ($values as $key => $value) {
        $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
        $keys = is_array($value) ? [...$keys, ...flattenKeys($value, $path)] : [...$keys, $path];
    }

    return $keys;
}

it('keeps the arabic and english translations in sync', function (string $file) {
    $arabic = flattenKeys(require dirname(__DIR__, 2)."/lang/ar/{$file}.php");
    $english = flattenKeys(require dirname(__DIR__, 2)."/lang/en/{$file}.php");

    expect(array_values(array_diff($arabic, $english)))->toBe([], 'Missing in English')
        ->and(array_values(array_diff($english, $arabic)))->toBe([], 'Missing in Arabic');
})->with(['admin', 'site']);

it('keeps placeholders identical across languages', function () {
    $arabic = require dirname(__DIR__, 2).'/lang/ar/admin.php';
    $english = require dirname(__DIR__, 2).'/lang/en/admin.php';

    foreach (flattenKeys($arabic) as $key) {
        preg_match_all('/:\w+/', (string) data_get($arabic, $key), $arabicPlaceholders);
        preg_match_all('/:\w+/', (string) data_get($english, $key), $englishPlaceholders);
        sort($arabicPlaceholders[0]);
        sort($englishPlaceholders[0]);

        expect($englishPlaceholders[0])->toBe($arabicPlaceholders[0], "Placeholders differ for {$key}");
    }
});
