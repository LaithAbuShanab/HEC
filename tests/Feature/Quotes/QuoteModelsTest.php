<?php

use App\Domain\Quotes\Enums\FulfillmentMethod;
use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Models\QuoteCart;
use App\Models\QuoteRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

function makeQuote(array $overrides = []): QuoteRequest
{
    return QuoteRequest::create($overrides + [
        'idempotency_key' => (string) Str::uuid(),
        'name' => 'Customer',
        'phone' => '0790000000',
        'city' => 'Amman',
        'fulfillment' => FulfillmentMethod::Pickup,
        'privacy_accepted_at' => now(),
        'locale' => 'ar',
    ]);
}

it('assigns a unique, readable reference after saving', function () {
    $this->travelTo('2026-11-06 10:00:00');

    $first = makeQuote();
    $second = makeQuote();

    expect($first->fresh()->reference)->toBe(sprintf('HEC-2026-%06d', $first->id))
        ->and($second->fresh()->reference)->not->toBe($first->fresh()->reference);
});

it('starts new quotes as new with a pending notification', function () {
    $quote = makeQuote()->fresh();

    expect($quote->status)->toBe(QuoteStatus::New)
        ->and($quote->notification_status)->toBe(NotificationStatus::Pending)
        ->and($quote->notification_attempts)->toBe(0);
});

it('rejects a second quote with the same idempotency key', function () {
    $key = (string) Str::uuid();
    makeQuote(['idempotency_key' => $key]);

    expect(fn () => makeQuote(['idempotency_key' => $key]))->toThrow(UniqueConstraintViolationException::class);
});

it('hides internal identifiers when serialized', function () {
    $quote = makeQuote(['ip_hash' => hash('sha256', '127.0.0.1')]);

    expect($quote->toArray())->not->toHaveKeys(['ip_hash', 'idempotency_key']);
});

it('creates carts with a random hidden token', function () {
    $cart = QuoteCart::create(['locale' => 'en']);

    expect(strlen($cart->token))->toBe(64)
        ->and($cart->toArray())->not->toHaveKey('token')
        ->and($cart->last_activity_at)->not->toBeNull();
});

it('treats cart items without a product as free text', function () {
    $cart = QuoteCart::create(['locale' => 'ar']);
    $item = $cart->items()->create(['free_text' => 'صاج 3 ملم', 'quantity' => 2, 'unit' => 'sheet']);

    expect($item->isCustom())->toBeTrue();
});
