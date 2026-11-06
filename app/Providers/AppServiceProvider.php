<?php

namespace App\Providers;

use App\Domain\Access\Role;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\Redirect;
use App\Models\SaleUnit;
use App\Models\SearchSynonym;
use App\Models\Service;
use App\Models\SpecAttribute;
use App\Models\User;
use App\Models\WorkItem;
use App\Policies\AdminOnlyPolicy;
use App\Policies\EditorialPolicy;
use App\Policies\QuoteRequestPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    private const EDITORIAL_MODELS = [
        Category::class, Product::class, SaleUnit::class, SpecAttribute::class, SearchSynonym::class,
        Service::class, Guide::class, Industry::class, WorkItem::class, Faq::class, Page::class, Redirect::class,
    ];

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(app()->isProduction());

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
            : null,
        );
    }

    protected function configureAuthorization(): void
    {
        Gate::before(fn (User $user) => $user->is_active && $user->hasRole(Role::Admin->value) ? true : null);

        foreach (self::EDITORIAL_MODELS as $model) {
            Gate::policy($model, EditorialPolicy::class);
        }

        Gate::policy(QuoteRequest::class, QuoteRequestPolicy::class);
        Gate::policy(User::class, AdminOnlyPolicy::class);
        Gate::policy(Activity::class, AdminOnlyPolicy::class);
    }
}
