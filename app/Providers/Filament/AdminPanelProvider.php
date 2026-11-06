<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Navigation\NavigationGroup;
use App\Http\Middleware\SetPanelLocale;
use App\Support\BrandColors;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            ->multiFactorAuthentication(
                AppAuthentication::make()->recoverable(),
                isRequired: true,
            )
            ->brandName('HEC')
            ->brandLogo(asset('images/brand/logo-horizontal.webp'))
            ->darkModeBrandLogo(asset('images/brand/logo-horizontal-on-dark.webp'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicon-32x32.png'))
            ->font('IBM Plex Sans Arabic')
            ->colors([
                'primary' => BrandColors::palette(BrandColors::BLUE),
                'info' => Color::Sky,
                'warning' => BrandColors::palette(BrandColors::GOLD),
                'gray' => Color::Slate,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups(NavigationGroup::registrations())
            ->databaseNotifications()
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.admin.locale-switch'))
            ->renderHook(PanelsRenderHook::SIMPLE_PAGE_START, fn () => view('filament.admin.locale-switch-simple'))
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([SetPanelLocale::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
