<?php

use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Domain\Reporting\CatalogHealth;
use App\Domain\Reporting\QuoteMetrics;
use App\Domain\Reporting\SearchInsights;
use App\Models\QuoteRequest;
use App\Models\SearchLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('summarizes catalog health from the seeded appendix', function () {
    $health = app(CatalogHealth::class)->summary();

    expect($health)->toMatchArray([
        'total' => 42,
        'published' => 0,
        'pending' => 30,
        'draft' => 12,
        'missing_main_image' => 42,
        'with_missing_fields' => 42,
    ]);
});

it('lists unpublished products with what still blocks them', function () {
    $rows = app(CatalogHealth::class)->needingAttention(5);

    expect($rows)->toHaveCount(5)
        ->and($rows->first()['blockers'])->toContain('main_image')
        ->and($rows->first())->toHaveKeys(['id', 'name', 'status', 'blockers', 'missing']);
});

it('counts quote activity', function () {
    $user = User::factory()->create();
    QuoteRequest::factory()->count(2)->create();
    QuoteRequest::factory()->create(['status' => QuoteStatus::Reviewing, 'assigned_to' => $user->id]);
    QuoteRequest::factory()->create(['status' => QuoteStatus::Closed, 'assigned_to' => $user->id]);
    QuoteRequest::factory()->create(['notification_status' => NotificationStatus::Failed]);
    QuoteRequest::factory()->create(['created_at' => now()->subDays(20)]);
    QuoteRequest::factory()->create(['status' => QuoteStatus::Spam]);

    $metrics = app(QuoteMetrics::class)->summary($user->id);

    expect($metrics)->toMatchArray([
        'new' => 4,
        'last_7_days' => 5,
        'assigned_open' => 1,
        'notification_failed' => 1,
    ]);
});

it('builds a 30 day series with empty days filled in', function () {
    QuoteRequest::factory()->count(2)->create(['created_at' => now()->subDays(3)]);
    QuoteRequest::factory()->create(['created_at' => now()]);

    $series = app(QuoteMetrics::class)->dailySeries(30);

    expect($series)->toHaveCount(30)
        ->and(array_sum($series))->toBe(3)
        ->and($series[now()->subDays(3)->toDateString()])->toBe(2);
});

it('groups quote sources without counting spam', function () {
    QuoteRequest::factory()->create(['source' => ['utm_source' => 'google']]);
    QuoteRequest::factory()->create(['source' => ['utm_source' => 'google']]);
    QuoteRequest::factory()->create(['source' => ['utm_source' => 'facebook']]);
    QuoteRequest::factory()->create(['source' => ['landing_path' => '/ar']]);
    QuoteRequest::factory()->create(['source' => ['utm_source' => 'bot'], 'status' => QuoteStatus::Spam]);

    expect(app(QuoteMetrics::class)->sources(90))->toBe(['google' => 2, 'facebook' => 1, 'direct' => 1]);
});

it('ranks searches and surfaces queries with no results', function () {
    foreach ([['مجلفن', 4], ['مجلفن', 4], ['صاج', 9], ['زاوية 50', 0], ['زاوية 50', 0], ['زاوية 50', 0]] as [$query, $results]) {
        SearchLog::create(['locale' => 'ar', 'query' => $query, 'results_count' => $results]);
    }
    SearchLog::forceCreate(['locale' => 'ar', 'query' => 'قديم', 'results_count' => 0, 'created_at' => now()->subDays(60)]);

    $insights = app(SearchInsights::class);

    expect($insights->top(30)->pluck('searches', 'query')->all())->toBe(['زاوية 50' => 3, 'مجلفن' => 2, 'صاج' => 1])
        ->and($insights->withoutResults(30)->pluck('query')->all())->toBe(['زاوية 50']);
});
