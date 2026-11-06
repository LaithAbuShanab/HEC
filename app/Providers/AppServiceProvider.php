<?php

namespace App\Providers;

use App\Domain\Access\Role;
use App\Http\Middleware\SetPanelLocale;
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
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimits();

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $chosen = session()->get(SetPanelLocale::SESSION_KEY);

                $event->user->forceFill(array_filter([
                    'last_login_at' => now(),
                    'locale' => is_string($chosen) ? $chosen : null,
                ]))->saveQuietly();
            }
        });
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

    protected function configureRateLimits(): void
    {
        RateLimiter::for('quote-submit', fn (Request $request) => [
            Limit::perMinute(5)->by('quote-submit:'.$request->ip()),
            Limit::perDay(30)->by('quote-submit-day:'.$request->ip()),
        ]);

        RateLimiter::for('quote-cart', fn (Request $request) => Limit::perMinute(90)->by('quote-cart:'.$request->ip()));
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
