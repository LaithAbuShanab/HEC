<?php

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Jobs\SendQuoteNotification;
use App\Domain\Quotes\Mail\QuoteRequestReceived;
use App\Domain\Quotes\QuoteCartManager;
use App\Http\Controllers\Site\QuoteRequestController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteAttachment;
use App\Models\QuoteCart;
use App\Models\QuoteCartItem;
use App\Models\QuoteRequest;
use App\Settings\CompanySettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
    config(['honeypot.enabled' => false]);
    $this->seed(DatabaseSeeder::class);

    Category::where('slug', 'pipes-tubes')->first()->update(['description' => ['ar' => 'وصف', 'en' => 'Description'], 'review_status' => ReviewStatus::Published]);
    $this->pipe = quotableProduct('black-steel-pipes');
    $this->galvanized = quotableProduct('galvanized-steel-pipes');
    $this->variants = collect(['1/2"', '3/4"'])->map(fn (string $size, int $index) => ProductVariant::create([
        'product_id' => $this->pipe->id,
        'label' => ['ar' => "قطر {$size}", 'en' => "{$size} diameter"],
        'nominal_size' => $size,
        'sort' => $index,
    ]));
});

function quotableProduct(string $slug): Product
{
    $product = Product::where('slug', $slug)->first();
    $product->update(['short_description' => ['ar' => 'وصف مختصر', 'en' => 'Short description']]);
    $product->addMedia(UploadedFile::fake()->image("{$slug}.jpg", 800, 600))->toMediaCollection('main');
    $product->update(['review_status' => ReviewStatus::Published]);

    return $product->fresh();
}

function realUpload(string $name, string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'hec');
    file_put_contents($path, $content);

    return new UploadedFile($path, $name, null, null, true);
}

function pdfBytes(): string
{
    return "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
}

function quoteCookie(): array
{
    return [QuoteCartManager::COOKIE => QuoteCart::latest('id')->value('token')];
}

function addToQuote($test, array $payload)
{
    $cookies = QuoteCart::exists() ? quoteCookie() : [];

    return $test->withCookies($cookies)->from('/ar/products')->post('/ar/request-quote/items', $payload);
}

function quotePayload(array $overrides = []): array
{
    return $overrides + [
        'idempotency_key' => (string) Str::uuid(),
        'name' => 'ليث',
        'phone' => '0791234567',
        'company' => 'شركة البناء',
        'email' => 'buyer@example.com',
        'city' => 'عمّان',
        'fulfillment' => 'delivery',
        'needed_by' => now()->addWeek()->toDateString(),
        'notes' => 'نحتاج قصًا حسب المخطط',
        'privacy' => '1',
    ];
}

function fillQuote($test): void
{
    addToQuote($test, ['product' => 'black-steel-pipes', 'variant' => $test->variants[1]->id]);
    addToQuote($test, ['product' => 'galvanized-steel-pipes']);
    addToQuote($test, ['free_text' => 'صاج أسود 2 ملم 1.25×2.5']);
}

it('adds several products, a chosen size and a free-text item to one server-side list', function () {
    fillQuote($this);
    addToQuote($this, ['product' => 'galvanized-steel-pipes'])->assertRedirect('/ar/products')->assertSessionHas('quote_added');

    expect(QuoteCart::count())->toBe(1)
        ->and(QuoteCartItem::count())->toBe(3)
        ->and(QuoteCartItem::where('product_variant_id', $this->variants[1]->id)->exists())->toBeTrue();

    $this->withCookies(quoteCookie())->get('/ar/request-quote')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('quote/create')
        ->has('items', 3)
        ->where('items.0.name', $this->pipe->getTranslation('name', 'ar'))
        ->where('items.0.variant', $this->variants[1]->id)
        ->has('items.0.variants', 2)
        ->where('items.2.custom', true)
        ->where('items.2.name', 'صاج أسود 2 ملم 1.25×2.5')
        ->where('itemsLabel', '3 أصناف')
        ->where('quote.count', 3)
        ->where('seo.robots', 'noindex, follow'));
});

it('edits quantity, unit, size and notes, accepting arabic digits', function () {
    fillQuote($this);
    $item = QuoteCartItem::where('product_id', $this->pipe->id)->first();

    $this->withCookies(quoteCookie())->from('/ar/request-quote')->patch("/ar/request-quote/items/{$item->id}", [
        'variant' => $this->variants[0]->id,
        'quantity' => '١٢٫٥',
        'unit' => 'طن',
        'thickness' => '3 ملم',
        'dimensions' => '6 م',
        'notes' => 'قص على 3 م',
    ])->assertRedirect('/ar/request-quote')->assertSessionHasNoErrors();

    expect($item->fresh())
        ->product_variant_id->toBe($this->variants[0]->id)
        ->quantity->toBe('12.500')
        ->unit->toBe('طن')
        ->notes->toBe('قص على 3 م');
});

it('rejects a size from another product and a zero quantity', function () {
    fillQuote($this);
    $item = QuoteCartItem::where('product_id', $this->galvanized->id)->first();

    $this->withCookies(quoteCookie())->patch("/ar/request-quote/items/{$item->id}", ['variant' => $this->variants[0]->id])->assertSessionHasErrors('variant');
    $this->withCookies(quoteCookie())->patch("/ar/request-quote/items/{$item->id}", ['quantity' => '0'])->assertSessionHasErrors('quantity');

    expect($item->fresh()->product_variant_id)->toBeNull();
});

it('removes an item from the list', function () {
    fillQuote($this);
    $item = QuoteCartItem::first();

    $this->withCookies(quoteCookie())->delete("/ar/request-quote/items/{$item->id}")->assertRedirect();

    expect(QuoteCartItem::count())->toBe(2);
});

it('never lets one visitor read or change another visitor\'s list', function () {
    fillQuote($this);
    $item = QuoteCartItem::first();
    $other = QuoteCart::create(['locale' => 'ar']);

    $this->withCookies([QuoteCartManager::COOKIE => $other->token])->patch("/ar/request-quote/items/{$item->id}", ['quantity' => '5'])->assertNotFound();
    $this->withCookies([QuoteCartManager::COOKIE => $other->token])->delete("/ar/request-quote/items/{$item->id}")->assertNotFound();
    $this->delete("/ar/request-quote/items/{$item->id}")->assertNotFound();

    $this->withCookies([QuoteCartManager::COOKIE => $other->token])->get('/ar/request-quote')->assertInertia(fn (Assert $page) => $page->has('items', 0));

    expect(QuoteCartItem::count())->toBe(3)->and($item->fresh()->quantity)->toBeNull();
});

it('refuses unpublished products', function () {
    addToQuote($this, ['product' => 'black-steel-sheets'])->assertSessionHasErrors('product');
    addToQuote($this, ['product' => 'black-steel-pipes', 'variant' => 999999])->assertSessionHasErrors('variant');

    expect(QuoteCartItem::count())->toBe(0);
});

it('drops items whose product was unpublished after being added', function () {
    fillQuote($this);
    $this->galvanized->update(['review_status' => ReviewStatus::Draft]);

    $this->withCookies(quoteCookie())->get('/ar/request-quote')->assertInertia(fn (Assert $page) => $page->has('items', 2));
});

it('saves a real request in a transaction with a reference and queues the email', function () {
    Queue::fake();
    $this->withSession([])->get('/ar/products?utm_source=facebook&utm_medium=cpc');
    fillQuote($this);
    QuoteCartItem::where('product_id', $this->pipe->id)->update(['quantity' => 4, 'unit' => 'طن']);

    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload())
        ->assertRedirect('/ar/request-quote/success')
        ->assertSessionHas(QuoteRequestController::SESSION_KEY);

    $quote = QuoteRequest::with('items')->sole();

    expect($quote->reference)->toMatch('/^HEC-\d{4}-\d{6}$/')
        ->and($quote->name)->toBe('ليث')
        ->and($quote->fulfillment->value)->toBe('delivery')
        ->and($quote->notification_status)->toBe(NotificationStatus::Pending)
        ->and($quote->ip_hash)->toHaveLength(64)
        ->and($quote->source)->toMatchArray(['utm_source' => 'facebook', 'utm_medium' => 'cpc', 'landing_path' => '/ar/products'])
        ->and($quote->items)->toHaveCount(3)
        ->and($quote->items[0]->product_name)->toBe($this->pipe->getTranslation('name', 'ar'))
        ->and($quote->items[0]->variant_label)->toBe('قطر 3/4"')
        ->and($quote->items[0]->quantity)->toBe('4.000')
        ->and($quote->items[0]->product_url)->toBe(url('/ar/products/pipes-tubes/black-steel-pipes'))
        ->and($quote->items[2]->is_custom)->toBeTrue()
        ->and(QuoteCart::count())->toBe(0);

    Queue::assertPushed(SendQuoteNotification::class, fn (SendQuoteNotification $job) => $job->quoteId === $quote->id);
});

it('shows the reference on a noindex success page and nowhere in the url', function () {
    $this->withSession([QuoteRequestController::SESSION_KEY => ['reference' => 'HEC-2026-000042', 'items' => 3]])
        ->get('/ar/request-quote/success')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('quote/success')
            ->where('reference', 'HEC-2026-000042')
            ->where('itemsLabel', 'يتضمن الطلب 3 أصناف.')
            ->where('seo.robots', 'noindex, follow'));

    $this->flushSession();
    $this->get('/ar/request-quote/success')->assertRedirect('/ar/request-quote');
});

it('rejects missing required fields without saving anything', function () {
    Queue::fake();
    fillQuote($this);

    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload(['name' => '', 'phone' => '12', 'city' => '', 'privacy' => '0', 'email' => 'not-an-email', 'fulfillment' => 'teleport']))
        ->assertSessionHasErrors(['name', 'phone', 'city', 'privacy', 'email', 'fulfillment']);

    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload(['needed_by' => now()->subDay()->toDateString()]))
        ->assertSessionHasErrors('needed_by');

    expect(QuoteRequest::count())->toBe(0)->and(QuoteCartItem::count())->toBe(3);
    Queue::assertNothingPushed();
});

it('refuses to send an empty list', function () {
    $this->post('/ar/request-quote', quotePayload())->assertSessionHasErrors('items');

    expect(QuoteRequest::count())->toBe(0);
});

it('does not create a duplicate when the same form is submitted twice', function () {
    Queue::fake();
    fillQuote($this);
    $payload = quotePayload();
    $cookies = quoteCookie();

    $this->withCookies($cookies)->post('/ar/request-quote', $payload)->assertRedirect('/ar/request-quote/success');
    $this->withCookies($cookies)->post('/ar/request-quote', $payload)->assertRedirect('/ar/request-quote/success');

    expect(QuoteRequest::count())->toBe(1);
    Queue::assertPushed(SendQuoteNotification::class, 1);
});

it('stores allowed attachments privately and checks the real file type', function () {
    Queue::fake();
    fillQuote($this);

    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload([
        'attachments' => [
            realUpload('drawing.pdf', pdfBytes()),
            UploadedFile::fake()->image('site.png', 400, 300),
            UploadedFile::fake()->image('photo.jpg', 400, 300),
        ],
    ]))->assertRedirect('/ar/request-quote/success');

    $attachments = QuoteAttachment::orderBy('id')->get();

    expect($attachments)->toHaveCount(3)
        ->and($attachments->pluck('mime_type')->all())->toBe(['application/pdf', 'image/png', 'image/jpeg'])
        ->and($attachments[0]->disk)->toBe('local')
        ->and($attachments[0]->original_name)->toBe('drawing.pdf')
        ->and($attachments[0]->sha256)->toHaveLength(64)
        ->and($attachments[0]->path)->not->toContain('drawing');

    $attachments->each(fn (QuoteAttachment $attachment) => Storage::disk('local')->assertExists($attachment->path));
    expect(Storage::disk('public')->allFiles('quotes'))->toBe([]);
});

it('rejects disguised, dangerous, oversized and extra attachments', function (array $files, string $error) {
    fillQuote($this);

    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload(['attachments' => $files]))->assertSessionHasErrors($error);

    expect(QuoteRequest::count())->toBe(0)->and(Storage::disk('local')->allFiles())->toBe([]);
})->with([
    'html renamed to pdf' => [fn () => [realUpload('plan.pdf', '<!doctype html><html><body><script>alert(1)</script></body></html>')], 'attachments.0'],
    'svg image' => [fn () => [realUpload('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')], 'attachments.0'],
    'executable' => [fn () => [realUpload('setup.exe', "MZ\x90\x00\x03\x00\x00\x00")], 'attachments.0'],
    'executable renamed to pdf' => [fn () => [realUpload('setup.pdf', "MZ\x90\x00\x03\x00\x00\x00".str_repeat("\x00", 64))], 'attachments.0'],
    'svg renamed to png' => [fn () => [realUpload('logo.png', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')], 'attachments.0'],
    'png renamed to exe' => [fn () => [UploadedFile::fake()->image('tool.exe', 10, 10)], 'attachments.0'],
    'over 10 MB' => [fn () => [UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf')], 'attachments.0'],
    'four files' => [fn () => array_map(fn (int $index) => UploadedFile::fake()->image("p{$index}.png", 10, 10), range(1, 4)), 'attachments'],
]);

it('silently drops bot submissions caught by the honeypot', function () {
    config(['honeypot.enabled' => true]);
    fillQuote($this);

    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload(['my_name_abc' => 'bot', 'valid_from' => 'x']))->assertSessionHasErrors('form');

    expect(QuoteRequest::count())->toBe(0);
});

it('limits how often one address can submit', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/ar/request-quote', [])->assertSessionHasErrors();
    }

    $this->post('/ar/request-quote', [])->assertTooManyRequests();
});

it('keeps the request when the mail server is down and marks it failed after the last retry', function () {
    Queue::fake();
    app(CompanySettings::class)->fill(['quote_notification_email' => 'sales@example.com'])->save();
    fillQuote($this);
    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload());
    $quote = QuoteRequest::sole();

    config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 9, 'mail.mailers.smtp.timeout' => 1]);
    $job = new SendQuoteNotification($quote->id);

    $thrown = null;

    try {
        app()->call([$job, 'handle']);
    } catch (Throwable $exception) {
        $thrown = $exception;
    }

    expect($thrown)->not->toBeNull();

    $quote->refresh();
    expect($quote->exists)->toBeTrue()
        ->and($quote->notification_status)->toBe(NotificationStatus::Pending)
        ->and($quote->notification_attempts)->toBe(1)
        ->and($quote->last_notification_error)->not->toBeEmpty()
        ->and($job->tries)->toBeGreaterThan(1)
        ->and($job->backoff())->not->toBeEmpty();

    $job->failed(new RuntimeException('Connection refused'));

    expect($quote->fresh()->notification_status)->toBe(NotificationStatus::Failed)
        ->and($quote->fresh()->last_notification_error)->toBe('Connection refused');
});

it('emails the configured sales address once and records success', function () {
    Mail::fake();
    app(CompanySettings::class)->fill(['quote_notification_email' => 'sales@example.com'])->save();
    Queue::fake();
    fillQuote($this);
    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload());
    $quote = QuoteRequest::sole();

    app()->call([new SendQuoteNotification($quote->id), 'handle']);
    app()->call([new SendQuoteNotification($quote->id), 'handle']);

    Mail::assertSent(QuoteRequestReceived::class, 1);
    Mail::assertSent(QuoteRequestReceived::class, fn (QuoteRequestReceived $mail) => $mail->hasTo('sales@example.com') && $mail->quote->is($quote));

    expect($quote->fresh())
        ->notification_status->toBe(NotificationStatus::Sent)
        ->notified_at->not->toBeNull()
        ->notification_attempts->toBe(1);
});

it('renders the notification email with the request details', function () {
    Queue::fake();
    fillQuote($this);
    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload());
    $quote = QuoteRequest::with(['items', 'attachments'])->sole();

    $html = (new QuoteRequestReceived($quote))->render();

    expect($html)->toContain($quote->reference)
        ->toContain('ليث')
        ->toContain('صاج أسود 2 ملم 1.25×2.5')
        ->toContain('/admin/quote-requests/');
});

it('records a failure instead of losing the request when no recipient is configured', function () {
    Mail::fake();
    app(CompanySettings::class)->fill(['quote_notification_email' => null])->save();
    Queue::fake();
    fillQuote($this);
    $this->withCookies(quoteCookie())->post('/ar/request-quote', quotePayload());
    $quote = QuoteRequest::sole();

    expect(fn () => app()->call([new SendQuoteNotification($quote->id), 'handle']))->toThrow(RuntimeException::class);

    Mail::assertNothingSent();
    expect($quote->fresh()->last_notification_error)->toContain('No quote notification email');
});

it('prunes abandoned lists after thirty days', function () {
    $old = QuoteCart::create(['locale' => 'ar']);
    $old->forceFill(['last_activity_at' => now()->subDays(31)])->save();
    $fresh = QuoteCart::create(['locale' => 'ar']);

    $this->artisan('model:prune', ['--model' => [QuoteCart::class]])->assertSuccessful();

    expect(QuoteCart::pluck('id')->all())->toBe([$fresh->id]);
});
