<?php

use App\Domain\Access\Role;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Filament\Admin\Widgets\CatalogHealthStats;
use App\Filament\Admin\Widgets\LatestQuotesTable;
use App\Filament\Admin\Widgets\ProductsNeedingAttentionTable;
use App\Filament\Admin\Widgets\QuoteSourcesChart;
use App\Filament\Admin\Widgets\QuoteStatsWidget;
use App\Filament\Admin\Widgets\QuotesTrendChart;
use App\Filament\Admin\Widgets\SearchInsightsTable;
use App\Filament\Admin\Widgets\ZeroResultSearchesTable;
use App\Models\QuoteRequest;
use App\Models\SearchLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

const SALES_WIDGETS = [QuoteStatsWidget::class, QuotesTrendChart::class, QuoteSourcesChart::class, LatestQuotesTable::class];
const EDITOR_WIDGETS = [CatalogHealthStats::class, ProductsNeedingAttentionTable::class, SearchInsightsTable::class, ZeroResultSearchesTable::class];

function dashboardUser(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    if ($role === Role::Admin) {
        $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    }

    return $user;
}

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('shows each role only the widgets it needs', function (Role $role, array $visible, array $hidden) {
    $this->actingAs(dashboardUser($role));

    foreach ($visible as $widget) {
        expect($widget::canView())->toBeTrue("{$role->value} should see {$widget}");
    }

    foreach ($hidden as $widget) {
        expect($widget::canView())->toBeFalse("{$role->value} should not see {$widget}");
    }
})->with([
    'sales' => [Role::Sales, SALES_WIDGETS, EDITOR_WIDGETS],
    'editor' => [Role::Editor, EDITOR_WIDGETS, SALES_WIDGETS],
    'admin' => [Role::Admin, [...SALES_WIDGETS, ...EDITOR_WIDGETS], []],
]);

it('renders the dashboard for every role', function (Role $role) {
    $this->actingAs(dashboardUser($role))->get('/admin')->assertOk();
})->with(Role::cases());

it('renders sales widgets with live numbers', function () {
    $this->actingAs(dashboardUser(Role::Sales));
    $latest = QuoteRequest::factory()->withItems()->create(['source' => ['utm_source' => 'google']]);
    QuoteRequest::factory()->create(['status' => QuoteStatus::Spam]);

    Livewire::test(QuoteStatsWidget::class)->assertOk()->assertSee(__('admin.dashboard.quotes.new'));
    Livewire::test(QuotesTrendChart::class)->assertOk();
    Livewire::test(QuoteSourcesChart::class)->assertOk();
    Livewire::test(LatestQuotesTable::class)
        ->assertCanSeeTableRecords([$latest])
        ->assertCountTableRecords(1);
});

it('renders editor widgets with catalog gaps and search insights', function () {
    $this->actingAs(dashboardUser(Role::Editor));
    SearchLog::create(['locale' => 'ar', 'query' => 'زاوية 50', 'results_count' => 0]);
    SearchLog::create(['locale' => 'ar', 'query' => 'مجلفن', 'results_count' => 3]);

    Livewire::test(CatalogHealthStats::class)->assertOk()->assertSee(__('admin.dashboard.catalog.missing_main_image'));
    Livewire::test(ProductsNeedingAttentionTable::class)
        ->assertOk()
        ->assertSee(__('admin.publication.blockers.main_image'));
    Livewire::test(SearchInsightsTable::class)->assertOk()->assertSee('مجلفن')->assertSee('زاوية 50');
    Livewire::test(ZeroResultSearchesTable::class)->assertOk()->assertSee('زاوية 50')->assertDontSee('مجلفن');
});
