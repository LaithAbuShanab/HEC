<?php

use App\Domain\Shared\Support\PhoneNumber;

it('converts jordanian numbers to international digits', function (string $input, ?string $expected) {
    expect(PhoneNumber::international($input))->toBe($expected);
})->with([
    ['0788878327', '962788878327'],
    ['078 887 8327', '962788878327'],
    ['+962 78 887 8327', '962788878327'],
    ['00962788878327', '962788878327'],
    ['962788878327', '962788878327'],
    ['٠٧٨٨٨٧٨٣٢٧', '962788878327'],
    ['788878327', '962788878327'],
    ['+44 20 7946 0958', '442079460958'],
    ['abc', null],
    ['12', null],
]);

it('builds a whatsapp link with an encoded message', function () {
    expect(PhoneNumber::whatsappUrl('0788878327', 'مرحبا HEC'))
        ->toBe('https://wa.me/962788878327?text='.rawurlencode('مرحبا HEC'));
});
